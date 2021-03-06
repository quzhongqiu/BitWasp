        <div class="span9 mainContent" id="order_view">
			<h2>Orders</h2>
          
			<?php if(isset($returnMessage)) { ?>
			<div class='alert<?php echo (isset($success)) ? ' alert-success' : ''; ?>'><?php echo $returnMessage; ?></div>			
			<?php } ?>

<?php if(is_array($orders) && count($orders) > 0) { ?>
			<br />
	        <div class="row-fluid">
				<div class="span2"></div>
				<div class="span4"><strong>Review Orders</strong></div>
			</div>

			<div class="row-fluid">
				<div class="row-fluid"></div>
					<div class="span1">#</div>
					<div class="span2">Buyer</div>
					<div class="span3">Items</div>
					<div class="span2">Price</div>
					<div class="span3">Progress</div>
				</div>
<?php foreach($orders as $order) { ?>
				<div class="row-fluid">
					<?php echo form_open('orders', array('class' => 'form-horizontal')); ?>
						<div class="span1"><?php echo ($order['progress'] == 0) ? '#'.$order['id'] : anchor('orders/details/'.$order['id'], '#'.$order['id']); ?></div>
						<div class="span2"><?php echo anchor('user/'.$order['buyer']['user_hash'], $order['buyer']['user_name']); ?></div>
						<div class="span3">
<?php foreach($order['items'] as $item) { 
				echo $item['quantity'] . ' x '. (($item['hash'] == 'removed') ? $item['name'] : anchor('item/'.$item['hash'], $item['name'])). '<br />'; 
	} ?></div>
						<div class="span2"><?php
						echo $coin['symbol']." ".number_format($order['order_price'], 8); 
						if($local_currency['id'] !== '0') echo '<br />'.$local_currency['symbol'].$order['price_l']; ?></div>
						<div class="span3"><?php echo $order['progress_message']; 
						if(isset($review_auth[$order['id']]))
							echo '<br />'.anchor("reviews/form/{$review_auth[$order['id']]}/{$order['id']}",'Please review this order.');  ?></div>
					</form>
				</div>
<?php } ?>
			</div>
<?php } else { echo "You have no orders at this time"; } ?>
