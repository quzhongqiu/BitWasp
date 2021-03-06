        <div class="span9 mainContent" id="review_form">
			<h2>Review Order #<?php echo $review_state['order_id']; ?></h2>
			
				<?php if(isset($returnMessage)) { ?><div class='alert'><?php echo $returnMessage; ?></div><?php } ?>
			
				<?php if($review_state['review_type'] == 'buyer') { ?>
				A short review will use the same feedback for each item in an order. A long review allows you to give feedback for each individual item on the order. 
				<br /><br />
				
				<div class='row-fluid'>
					<div class='span6'>
						<b>Order Information</b>
						<ul>
							<li>This order was with <?php echo anchor("user/".$review_info['vendor']['user_hash'], $review_info['vendor']['user_name']); ?>.</li>
							<li><?php if($review_info['vendor_selected_escrow'] == '1') { ?>
							Escrow Payment.
							<?php } else { ?>
							Paid Up-front.
							<?php } ?></li>
							<li>Paid for: <?php echo $review_info['paid_time_f']; ?>.</li>
							<li>Dispatched: <?php echo $review_info['dispatched_time_f']; ?>.</li>
							<li>Complete: <?php echo $review_info['finalized_time_f']; ?>.</li>							
							<li>Order cost + Shipping: <?php echo $coin['symbol']; ?> <?php echo $review_info['price']; ?></li>
							<li>Site Fee's: <?php echo $coin['symbol']; ?> <?php echo $review_info['fees']; ?></li>
							<?php if($review_info['disputed'] == '1') { ?>
							<li>This order was <b>disputed</b> on <?php echo $review_info['disputed_time_f']; ?></li><?php } ?>
						</ul>
					</div>
					<div class='span3'>
						<b>Items</b>
						<ul>
							<?php foreach($review_info['items'] as $item) { ?>
							<li><?php echo $item['quantity']; ?> x <?php echo $item['name']; ?></li><?php } ?>
						</ul>
					</div>
				</div>
				<br />

				<?php echo form_open($action_page, array('class' => 'form-horizontal')); ?>
				
				<?php echo validation_errors().form_error('review_length'); ?>
					<div class='row-fluid'>
						<div class='well' style='background-color:white;'>
							<h4>Vendor Feedback</h4>
							<div class='row-fluid'>
								<div class='span3'>Communication</div>
								<div class='span7'>
									<select name='vendor_communication' autocomplete='off'>
										<option value=''></option>
										<option value='1'>1</option>
										<option value='2'>2</option>
										<option value='3'>3</option>
										<option value='4'>4</option>
										<option value='5'>5</option>
									</select>
								</div>
								<?php echo form_error('vendor_communication'); ?>
							</div>
							<div class='row-fluid'>
								<div class='span3'>Shipping</div>
								<div class='span7'>
									<select name='vendor_shipping' autocomplete='off'>
										<option value=''></option>
										<option value='1'>1</option>
										<option value='2'>2</option>
										<option value='3'>3</option>
										<option value='4'>4</option>
										<option value='5'>5</option>
									</select>
								</div>
								<?php echo form_error('vendor_shipping'); ?>
							</div>
							<div class='row-fluid'>
								<div class='span3'>Comments</div>
								<div class='span7'>
									<?php echo form_error('vendor_comments_source'); ?>
									<input type='radio' name='vendor_comments_source' value='prepared' /> Use prepared statements? <br />
									<select name='vendor_prepared_comments' autocomplete='off'>
										<option value=''></option>
										<option value='Excellent vendor, would do business again.'>Excellent vendor, would do business again.</option>
										<option value='Slow delivery.'>Poor delivery time.</option>
										<option value='Poor communication.'>Poor communication.</option>
										<option value='Poor communication & slow delivery.'>Poor communication & slow delivery.</option>
										<option value='Fast delivery.'>Fast delivery.</option>
									</select><br />

									<input type='radio' name='vendor_comments_source' value='input' /> Write own comment? <br />
									<textarea name='vendor_free_comments'></textarea>
								</div>
							</div>
						</div>
						
						
						<div class='well' style='background:white;'>
							<h4><?php echo ((count($review_info['items']) > 1) ? "<input type='radio' name='review_length' value='short' /> Submit Short Feedback?" : "Item Feedback <input type='hidden' name='review_length' value='short' />"); ?></h4>
							<div class='row-fluid'>
								<div class='span3'>Quality</div>
								<div class='span7'>
									<select name='short_item_quality' autocomplete='off'>
										<option value=''></option>
										<option value='1'>1</option>
										<option value='2'>2</option>
										<option value='3'>3</option>
										<option value='4'>4</option>
										<option value='5'>5</option>
									</select>
								</div>
								<?php echo form_error('short_item_quality'); ?>
							</div>
							<div class='row-fluid'>
								<div class='span3'>Matches Description</div>
								<div class='span7'>
									<select name='short_item_matches_desc' autocomplete='off'>
										<option value=''></option>
										<option value='1'>1</option>
										<option value='2'>2</option>
										<option value='3'>3</option>
										<option value='4'>4</option>
										<option value='5'>5</option>
									</select>
								</div>
								<?php echo form_error('short_item_matches_desc'); ?>
							</div>
							<div class='row-fluid'>
								<div class='span3'>Comments</div>
								<div class='span7'>
									<span class='help-inline'><?php echo form_error('short_item_comments_source'); ?></span><br />
									<input type='radio' name='short_item_comments_source' value='prepared' /> Use prepared statements? <br />
									<select name='short_item_prepared_comments' autocomplete='off'>
										<option value=''></option>
										<option value='Did not match description.'>Did not match description.</option>
										<option value='Poor quality.'>Poor quality.</option>
										<option value='Excellent quality.'>Excellent quality.</option>
										<option value='Would purchase again.'>Would purchase again.</option>
									</select><br />
									<input type='radio' name='short_item_comments_source' value='input' /> Write own comment? <br />
									<textarea name='short_item_free_comments'></textarea>							
								</div>
							</div>
						</div>
						<?php 	if(count($review_info['items']) > 1) { ?>
						<div class='well' style='background:white;'>
							<h4><input type='radio' name='review_length' value='long' /> Submit long review?</h4>
							
							<?php $c = 0; foreach($review_info['items'] as $item) { ?>
							<b><?php echo ($c+1).": ".$item['name']; ?></b>
							<div class='row-fluid'>
								<div class='span3'>Quality</div>
								<div class='span7'>
									<select name='item[<?php echo $c; ?>][quality]' autocomplete='off'>
										<option value=''></option>
										<option value='1'>1</option>
										<option value='2'>2</option>
										<option value='3'>3</option>
										<option value='4'>4</option>
										<option value='5'>5</option>
									</select>
								</div>
								<?php echo form_error("item[{$c}][quality]"); ?>
							</div>
							<div class='row-fluid'>
								<div class='span3'>Matches Description</div>
								<div class='span7'>
									<select name='item[<?php echo $c; ?>][matches_desc]' autocomplete='off'>
										<option value=''></option>
										<option value='1'>1</option>
										<option value='2'>2</option>
										<option value='3'>3</option>
										<option value='4'>4</option>
										<option value='5'>5</option>
									</select>
								</div>
								<?php echo form_error("item[{$c}][matches_desc]"); ?>
							</div>
							<div class='row-fluid'>
								<div class='span3'>Comments</div>
								<div class='span7'>
									<?php echo form_error("item[{$c}][comments_source]"); ?>
									<input type='radio' name='item[<?php echo $c; ?>][comments_source]' value='prepared' /> Use prepared statements? <br />
									<select name='item[<?php echo $c; ?>][prepared_comments]' autocomplete='off'>
										<option value=''></option>
										<option value='Did not match description.'>Did not match description.</option>
										<option value='Poor quality.'>Poor quality.</option>
										<option value='Excellent quality.'>Excellent quality.</option>
										<option value='Would purchase again.'>Would purchase again.</option>
									</select><br />
									<input type='radio' name='item[<?php echo $c; ?>][comments_source]' value='input' /> Write own comment? <br />
									<textarea name='item[<?php echo $c; ?>][free_comments]'></textarea>							
								</div>
							</div>
							<br />								
							<?php $c++; } ?>
						</div>
						<?php } ?>
					</div>
					<div class="form-actions">
						<input type='submit' name='buyer_submit_review' value='Submit Review' class='btn btn-primary'/>
						<?php echo anchor($cancel_page, 'Cancel', 'class="btn"'); ?>
					</div>
				</form>

				<?php } else if($review_state['review_type'] == 'vendor') { ?>
				<br />
				
				<div class='row-fluid'>
					<div class='span6'>
						<b>Order Information</b>
						<ul>
							<li>This order was made by <?php echo anchor("user/".$review_info['buyer']['user_hash'], $review_info['buyer']['user_name']); ?>.</li>
							<li><?php if($review_info['vendor_selected_escrow'] == '1') { ?>
							Escrow Payment.</li>
							<li>Dispatched: <?php echo $review_info['dispatched_time_f']; ?>.</li>
							<li>Paid for: <?php echo $review_info['paid_time_f']; ?>.</li>
							<li>Completed: <?php echo $review_info['finalized_time_f']; ?>.</li>
							<?php } else { ?>
							Finalized Early.
							<li>Paid for: <?php echo $review_info['paid_time_f']; ?>.</li>
							<li>Dispatched: <?php echo $review_info['dispatched_time_f']; ?>.</li>
							<li>Completed: <?php echo $review_info['finalized_time_f']; ?>.</li>
							<?php } ?></li>
							<li>Order cost + Shipping: <?php echo $coin['symbol']; ?> <?php echo $review_info['price']; ?></li>
							<li>Site Fee's: <?php echo $coin['symbol']; ?> <?php echo $review_info['fees']; ?></li>
							<?php if($review_info['disputed'] == '1') { ?>
							<li>This order was <b>disputed</b> on <?php echo $review_info['disputed_time_f']; ?></li><?php } ?>
						</ul>
					</div>
					<div class='span3'>
						<b>Items</b>
						<ul>
							<?php foreach($review_info['items'] as $item) { ?>
							<li><?php echo $item['quantity']; ?> x <?php echo $item['name']; ?></li><?php } ?>
						</ul>
					</div>
				</div>
				<br />				
				
				<?php echo form_open($action_page, array('class' => 'form-horizontal')); ?>
				<?php echo validation_errors(); ?>
					<div class='well' style='background-color:white;'>
						<h4>Buyer Feedback</h4>
						<div class='row-fluid'>
							<div class='span3'>Communication</div>
							<div class='span7'>
								<select name='buyer_communication' autocomplete='off'>
									<option value=''></option>
									<option value='1'>1</option>
									<option value='2'>2</option>
									<option value='3'>3</option>
									<option value='4'>4</option>
									<option value='5'>5</option>
								</select>
							</div>
							<?php echo form_error('buyer_communication'); ?>
						</div>
						<div class='row-fluid'>
							<div class='span3'>Cooperation</div>
							<div class='span7'>
								<select name='buyer_cooperation' autocomplete='off'>
									<option value=''></option>
									<option value='1'>1</option>
									<option value='2'>2</option>
									<option value='3'>3</option>
									<option value='4'>4</option>
									<option value='5'>5</option>
								</select>
							</div>
							<?php echo form_error('buyer_cooperation'); ?>
						</div>
						<div class='row-fluid'>
							<div class='span3'>Comments</div>
							<div class='span7'>
								<?php echo form_error('buyer_comments_source'); ?>
								<input type='radio' name='buyer_comments_source' value='prepared' /> Use prepared statements? <br />
								<select name='buyer_prepared_comments' autocomplete='off'>
									<option value=''></option>
									<option value='Fast payer.'>Fast payer.</option>
									<option value='Would do business again.'>Would do business again.</option>
									<option value='Will avoid in future.'>Will avoid in future.</option>
								</select><br />
								<input type='radio' name='buyer_comments_source' value='input' /> Write own comment? <br />
								<textarea name='buyer_free_comments'></textarea>
							</div>
						</div>
					</div>
					<div class="form-actions">
						<input type='submit' name='vendor_submit_review' value='Submit Review' class='btn btn-primary'/>
						<?php echo anchor($cancel_page, 'Cancel', 'class="btn"'); ?>
					</div>

				</form>				
				<?php } ?>

		</div>
