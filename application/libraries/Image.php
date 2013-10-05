<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

// Upload image.
// Resize, should take file path, and 

class Image {
	public $use_library;
	
	public $output_format = 'png';
	
	protected $imagick_import;
	protected $gd_import;
	protected $import = FALSE;

	public function __construct() {
		// If magickwand extension is present
		if(extension_loaded('magickwand') && function_exists("NewMagickWand")) {
			/* ImageMagick is installed and working */
			$this->use_library = 'magickwand';
		} elseif (extension_loaded('gd') && function_exists('gd_info')) {
			$this->use_library = 'gd';
		} 
	}
	
	// This function imports the image and stores it in the object. 
	// Need full path, file ext, raw_name
	public function import($image_info) {
		if($this->use_library == 'magickwand') {
			$this->imagick_import = new Imagick($image_info['full_path']);
			$this->import = $image_info;
		} else if($this->use_library == 'gd') {
			
			if($image_info['file_ext'] == '.png'){			
				// Load PNG image.
				$this->gd_import = imagecreatefrompng($image_info['full_path']);				
			} elseif($image_info['file_ext'] == '.jpeg' || $image_info['file_ext'] == '.jpg'){
				// Load JPEG image.
				$this->gd_import = imagecreatefromjpeg($image_info['full_path']);
			} elseif($image_info['file_ext'] == '.gif' ){
				// Load GIF image
				$this->gd_import = imagecreatefromgif($image_info['full_path']);
			}	
			$this->import = $image_info;	
		}
	}
	
	// General function to resize an imported image, and export to new filename.
	public function resize($width, $height, $new_name){
		if($this->import == FALSE)
			return FALSE;
	
		$results['file_name'] = "{$new_name}.{$this->output_format}";
		$results['file_ext'] = $this->output_format;
		$results['file_path'] = $this->import['file_path'];
		$results['full_path'] = $this->import['file_path'].$results['file_name'];
		$results['raw_name'] = "{$new_name}";
					
		if($this->use_library == "magickwand") {
			$copy = $this->imagick_import;
			// Strip EXIF data.
			$copy->stripImage();
			$copy->setImageFormat( $this->output_format );
			$copy->setImageOpacity(1.0);
	     	$copy->resizeImage($width, $height, imagick::FILTER_CATROM, 0.9, true);
			$copy->writeImage($results['full_path']);			
		} else if($this->use_library == "gd") {
			
			$our_ratio = $width/$height;
			
			// Extract the width and heightfrom the new image.
			list($curr_width, $curr_height) = getimagesize($this->import['full_path']);

			// Calculate the ratio of the new image.
    		$curr_ratio = $curr_width / $curr_height;
			
        	if ($curr_ratio < $our_ratio) {
				// Dimensions for the thumbnail. Height is max, and relative width calculated.
				$new_height = $height;
				$new_width = $height*$our_ratio;
       		} else {
				// In this case the ratio is greater (in favour of the width) 
				$new_width = $width;
				$new_height = $width/$our_ratio;
			}	

   			$new_image = imagecreatetruecolor($new_width, $new_height);
   			imagecopyresampled($new_image, $this->gd_import, 0, 0, 0, 0, $new_width, $new_height, $curr_width, $curr_height);
			imagepng($new_image, $results['full_path']);
			
		}
			
		return $results;
	}

	
	public function display($img_hash, $height = NULL, $width = NULL){}
	
	// Create a temporary base64 image from a file.
	public function encode($filename){
		$file = file_get_contents('./assets/images/'.$filename);
		
		// Encode to base64
		return base64_encode($file);
	
	}
		
	public function temp($filename){
		$image = $this->encode($filename);
		$html = "<img src=\"data:image/png;base64,{$image}\" />\n";
		return $html;
	}
	

};
