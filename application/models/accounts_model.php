<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Accounts Model
 *
 * This class handles the database queries relating to orders.
 * 
 * @package		BitWasp
 * @subpackage	Models
 * @category	Accounts
 * @author		BitWasp
 * 
 */
class Accounts_model extends CI_Model {
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Load an account
	 * 
	 * Will determine which columns to select from the database. If $opt['own'] == TRUE,
	 * we get more details than a normal account. Then $identifier is used to
	 * select the information information using either the hash/name/id. If the record is
	 * found, format and load more information. If the user has PGP, then
	 * add that to the returned array if successful. If there's no record, return false.
	 *
	 * @access	public
	 * @param	string
	 * @param	array optional
	 * @return	array / FALSE
	 */	
	public function get($identifier, $opt = array()) {

		if($identifier == NULL) 
			return FALSE;
		
		if(count($opt) == 0) {
			$this->db->select('id, banned, completed_order_count, display_login_time, force_pgp_messages, block_non_pgp, login_time, location, register_time, user_name, user_hash, user_role');
		} else if($opt['own'] == TRUE) {
			$this->db->select('id, banned, completed_order_count, display_login_time, local_currency, block_non_pgp, force_pgp_messages, login_time, location, register_time, totp_secret, totp_two_factor, pgp_two_factor, user_name, user_hash, user_role');
		}

		if (isset($identifier['user_hash'])) {
			$query = $this->db->get_where('users', array('user_hash' => $identifier['user_hash']));
		} elseif (isset($identifier['id'])) {
			$query = $this->db->get_where('users', array('id' => $identifier['id']));
		} elseif (isset($identifier['user_name'])) {
			$query = $this->db->get_where('users', array('user_name' => $identifier['user_name']));
		} else {
			$this->db->_reset_select();
			return FALSE; //No suitable field found.
		}
		
		if($query->num_rows() > 0) {
			$result = $query->row_array();
			$result['user_hash'] = $result['user_hash'];
			$result['register_time_f'] = $this->general->format_time($result['register_time']);
			$result['login_time_f'] = $this->general->format_time($result['login_time']);
			$this->load->model('location_model');
			$result['location_f'] = $this->location_model->location_by_id($result['location']);
			if(isset($opt['own']) && $opt['own'] == TRUE)
				$result['currency'] = $this->currencies_model->get($result['local_currency']);
			
			$pgp = $this->get_pgp_key($result['id']);
			if($pgp !== FALSE)
				$result['pgp'] = $pgp;
				
			return $result;
		}
		
		return FALSE;
	}
	
	/**
	 * Get PGP Key
	 * 
	 * Load a PGP key based on the $user_id. If the record exists, format 
	 * the fingerprint for display. If the record exists, the results get 
	 * returned as an array. If not, return FALSE;
	 * 
	 * @access	public
	 * @param	int
	 * @return	array / FALSE
	 */
	public function get_pgp_key($user_id) {
		$this->db->select('fingerprint, public_key');
		$query = $this->db->get_where('pgp_keys', array('user_id' => $user_id));
		
		if($query->num_rows() > 0) {
			$row = $query->row_array();
			$row['fingerprint_f'] = '0x'.substr($row['fingerprint'], (strlen($row['fingerprint'])-16), 16);
			return $row;
		}
		
		return FALSE;
	}
	
	/**
	 * Add PGP Key
	 * 
	 * Add a PGP key to the database. Returns TRUE if successful,
	 * FALSE if unsuccessful.
	 * $config = array(	'user_id' => '...',
	 * 					'fingerprint' => '...',
	 * 					'public_key' => '...');
	 * @access	public
	 * @param	array
	 * @return	bool
	 */	
	public function add_pgp_key($config) {
		return ($this->db->insert('pgp_keys', $config)) ? TRUE : FALSE;
	}
	
	/**
	 * Delete PGP key.
	 * 
	 * Delete a PGP public key for $user_id.
	 *
	 * @access	public
	 * @param	int
	 * @return	bool
	 */		
	public function delete_pgp_key($user_id) {
		$this->db->where('user_id', $user_id);
		
		if($this->db->delete('pgp_keys') == TRUE) {
			// When deleting the PGP key, 
			$changes = array('pgp_two_factor' => '0',
							 'force_pgp_messages' => '0',
							 'block_non_pgp' => '0');
			$this->update($changes);
			return TRUE;
		}
			
		return FALSE;
	}
	
	/**
	 * Replace PGP key.
	 * 
	 * Replace a PGP public key for $user_id. Return TRUE if successful,
	 * FALSE on failure.
	 *
	 * @access	public
	 * @param	int
	 * @return	bool
	 */		
	public function replace_pgp_key($user_id, $data) {
		$this->db->where('user_id', $user_id);
		return ($this->db->update('pgp_keys', array('public_key' => $data['public_key'],
												    'fingerprint' => $data['fingerprint'])) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Disable TOTP
	 * 
	 * This function removes the TOTP secret and disables two factor
	 * authentication.
	 * 
	 * @return	boolean
	 */
	public function disable_2fa_totp() {
		$this->db->where('id', $this->current_user->user_id);
		$update = array('totp_secret' => '',
						'totp_two_factor' => '0');
		return ($this->db->update('users', $update) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Disable PGP 2FA
	 * 
	 * This function turns off PGP two factor authentication.
	 * 
	 * @return	boolean
	 */
	public function disable_2fa_pgp() {
		$this->db->where('id', $this->current_user->user_id);
		$update = array('pgp_two_factor' => '0');
		return ($this->db->update('users', $update) == TRUE) ? TRUE : FALSE;
	}
	
	
	/**
	 * Enable TOTP
	 * 
	 * Adds a totp secret to the users account and enables TOTP. It also
	 * ensures that the PGP two factor setting is turned off.
	 * 
	 * @param	string	$secret
	 * @return	boolean
	 */
	public function enable_2fa_totp($secret) {
		$this->db->where('id', $this->current_user->user_id);
		$update = array('totp_secret' => $secret,
						'totp_two_factor' => '1',
						'pgp_two_factor' => '0');
		return ($this->db->update('users', $update) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Enable 2FA PGP
	 * 
	 * Sets up PGP two factor authentication for the users account and 
	 * disables TOTP 2FA.
	 * 
	 * @return	boolean
	 */
	public function enable_2fa_pgp() {
		$this->db->where('id', $this->current_user->user_id);
		$update = array('pgp_two_factor' => '1',
						'totp_two_factor' => '0',
						'totp_secret' => ''
					);
		return ($this->db->update('users', $update) == TRUE) ? TRUE : FALSE;
	}
	/**
	 * Toggle Ban
	 * 
	 * Change the banned setting for $user_id. Can be set by the Autorun 
	 * script, or manually toggled by an Admin.
	 *
	 * @access	public
	 * @param	int
	 * @param	int
	 * @return	bool
	 */	
	public function toggle_ban($user_id, $value) {
		$this->db->where('id', $user_id);
		return ($this->db->update('users', array('banned' => $value)) == TRUE) ? TRUE : FALSE;
	}		
	
	/**
	 * Update 
	 * 
	 * Updates a user row with the indexes supplied in $changes. Make
	 * the changes to the table.
	 *
	 * @access	public
	 * @param	array
	 * @return	bool
	 */		
	public function update($changes) {
		$this->db->where('id', $this->current_user->user_id);
		return ($this->db->update('users', $changes)) ? TRUE : FALSE;
	}

	/**
	 * Bitcoin Public Keys
	 * 
	 * This function accepts a $user_id and returns all the public keys
	 * on record. Returns an empty array if none exist, otherwise will
	 * calculate the address for the public key as well.
	 * 
	 * @param	int	$user_id
	 * @return	array
	 */
	public function bitcoin_public_keys($user_id) {
		$this->db->where('user_id', $user_id);
		$query = $this->db->get('bitcoin_public_keys');
		$result = $query->result_array();
		if(count($result) == 0)
			return $result;
		
		$this->load->library('BitcoinLib');
		$this->load->model('currencies_model');
		$coin = $this->currencies_model->get('0');
		
		foreach($result as &$res) {
			$res['address'] = BitcoinLib::public_key_to_address($res['public_key'], $coin['crypto_magic_byte']);
		}
		return $result;
	}

	/**
	 * Add Bitcoin Public Key
	 * 
	 * This function records the supplied $public_key and associates it
	 * with the currently logged in user.
	 * 
	 * @param	string	$public_key
	 * @return	boolean
	 */
	public function add_bitcoin_public_key($public_key) {
		return ($this->db->insert('bitcoin_public_keys', array('user_id' => $this->current_user->user_id, 'public_key' => $public_key)) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Delete Bitcoin Public Key
	 * 
	 * Delete public key identified by its $public_key_id in the table,
	 * restricted to the currently logged in user.
	 * 
	 * @param	int	$public_key_id
	 * @return	boolean
	 */
	public function delete_bitcoin_public_key($public_key_id) {
		$this->db->where('id', "$public_key_id");
		$this->db->where('user_id', "{$this->current_user->user_id}");
		return ($this->db->delete('bitcoin_public_keys') == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * User Forces PGP Messages 
	 * 
	 * Return a boolean indicating if the user forces incoming messages
	 * to be encrypted.
	 * 
	 * @param	string	$user_name
	 * @return	boolean
	 */
	public function user_requires_pgp_messages($user_name) {
		$this->db->where('user_name', $user_name);
		$this->db->select('block_non_pgp');
		$query = $this->db->get('users');
		$row = $query->row_array();
		return ($row['block_non_pgp'] == 1) ? TRUE : FALSE;
	}
};

/* End of file Accounts_model.php */
