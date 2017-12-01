<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package rabe
 */

// Where are you coming from?
$throw_url = ( isset( $_SERVER['HTTP_REFERER'] ) ) ? $_SERVER['HTTP_REFERER'] : site_url();

// Are you coming with a correct intention or from the membership page?
if ( in_array( $_REQUEST['intention'], array( 'support', 'membership', 'donation' ), false ) || isset( $_REQUEST['membershipform'] ) ) {

get_header(); ?>
<main  class="<?php echo omega_apply_atomic( 'main_class', 'content' );?>" <?php omega_attr( 'content' ); ?>>
	<?php 
	do_action( 'omega_before_content' ); 
	?>
	
		<article <?php omega_attr( 'post' ); ?>><div class="entry-wrap">
			<?php 
			
			// What do you want to pay and how much?
			if ( ( isset( $_GET['intention'] ) && ! isset( $_GET['amount'] ) &&  ! isset( $_GET['payment-order'] ) )
				// I know the amount, but not the broadcast and not coming directly from donation form
				|| ( isset( $_GET['intention'] ) && isset( $_GET['amount'] ) 
					&& empty( $_GET['broadcast-id'] ) && ! isset( $_GET['donationform'] ) ) ) {
				
				// get GET vars and prepare input hidden fields
				$input_fields = '';
				foreach( $_GET as $key => $value ) {
					// Don't add broadcast-id
					if ( $key !== 'broadcast-id' ) {
						$input_fields .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
					}
				}

				// I want to become member
				if ( $_GET['intention'] === 'membership' ) {
					// Choose what kind of member
					// FIXME: Make strings configurable
					?>
					<header class="entry-header"><h1 class="entry-title" itemprop="headline"><?php echo __( 'Choose membership', 'rabe' ); ?></h1></header>
					<form method="post" action="<?php echo get_permalink(); ?>" id="chooser" name="membertype-chooser">
						<input type="radio" id="normal" name="amount" value="150" checked> <label for="normal"><?php echo __( 'Normale Mitgliedschaft <strong>150.- pro Jahr</strong> (Halbjahresbeitrag ab 1. Juli 75.-)', 'rabe' );?></label><br>
						<input type="radio" id="reduced" name="amount" value="75"> <label for="reduced"><?php echo __( 'Studierende / RentnerInnen (AHV, IV), Besitzende einer Kulturlegi <strong>75.- pro Jahr</strong> (Halbjahresbeitrag ab 1. Juli 37.50)', 'rabe' );?></label><br>
						<input type="radio" id="family" name="amount" value="250"> <label for="family"><?php echo __( 'Familien oder WG <strong>250.- pro Jahr</strong>', 'rabe' );?></label><br>
						<input type="radio" id="gold" name="amount" value="500"> <label for="gold"><?php echo __( 'Goldmember <strong>ab 500.- pro Jahr</strong>', 'rabe' );?></label><br>
						<input type="radio" id="premium" name="amount" value="2000"> <label for="premium"><?php echo __( ' Premiummember <strong>ab 2’000.- pro Jahr</strong>', 'rabe' );?></label><br>
						<?php echo $input_fields; ?>
						<br>
						<input type="submit" value="<?php echo __( 'Submit', 'rabe' ); ?>" id="submit-membertype" name="submit">
					</form>
					<?php
				// I want to support a broadcast but don't know which one
				} elseif ( $_GET['intention'] === 'support' ) {
					?>
					<header class="entry-header"><h1 class="entry-title" itemprop="headline"><?php echo __( 'Choose broadcast to support', 'rabe' ); ?></h1></header>
					<form method="post" action="<?php echo get_permalink(); ?>" id="chooser" name="broadcast-chooser">
					<?php 
					
					$active_broadcasts = get_active_broadcasts();
					$active_broadcasts_options = '';
					foreach ( $active_broadcasts as $broadcast ) {
						$active_broadcasts_options .= '<option value="' . $broadcast->term_id . '">' . $broadcast->name . '</option>';
					}
					
					?>
					<label><?php echo __( 'Broadcast', 'rabe' ) ?>:
						<select id="broadcast-id" name="broadcast-id" required>
							<option value=""><?php echo __( 'None' , 'rabe' ); ?></option>
							<?php echo $active_broadcasts_options; ?>
						</select>
					</label>
					<input type="hidden" name="amount" value="30">
					<input type="hidden" name="intention" value="support">
					<?php echo $input_fields; ?>
					<br>
					<input type="submit" style="margin-top:15px;" value="<?php echo __( 'Submit', 'rabe' ); ?>" id="submit-broadcast" name="submit-broadcast">
					</form><?php
				// I want to donate something
				} elseif ( $_GET['intention'] === 'donation' ) {
					?>
					<header class="entry-header"><h1 class="entry-title" itemprop="headline"><?php echo __( 'Donation', 'rabe' ); ?></h1></header>
					<form method="post" action="<?php echo get_permalink(); ?>" id="chooser" name="donation">
						<label for="amount"><?php echo __( 'Donation in swiss francs', 'rabe' ); ?>: </label>
						<br>
						<input type="number" min="1" max="9999" name="amount" value="0" required>
						<?php echo $input_fields; ?>
						<br>
						<input type="submit" style="margin-top:15px;" value="<?php echo __( 'Submit', 'rabe' ); ?>" id="submit-donation" name="submit">
					</form><?php
				// I want something else and I'm coming from the membership page
				} elseif ( isset( $_GET['membershipform'] ) ) {
					echo '<header class="entry-header"><h1 class="entry-title" itemprop="headline">' . __( 'Thank you', 'rabe' ) .'</h1></header>';
					echo '<p>' . __( 'Thank you for your message!', 'rabe' ) . '</p>';
				}
			// I know the amount to pay and the intention
			} elseif ( isset( $_REQUEST['intention'] ) && ( isset( $_REQUEST['amount'] ) ||  isset( $_REQUEST['AMOUNT'] ) ) ) {

					// Prepare form parameters for payment form
					$first_name = ( isset( $_REQUEST['first_name'] ) ) ? $_REQUEST['first_name'] : false;
					$last_name = ( isset( $_REQUEST['last_name'] ) ) ? $_REQUEST['last_name'] : false;
					$broadcast_id = ( isset( $_REQUEST['broadcast-id'] ) ) ? intval( $_REQUEST['broadcast-id'] ) : false;
					$broadcast_slug = ( get_term_by( 'id', $broadcast_id, 'broadcast' ) ) ? get_term_by( 'id', $broadcast_id, 'broadcast' )->slug : '' ;
					$broadcast_slug_dash = ( $broadcast_slug ) ? $broadcast_slug . '_' : '';
					$broadcast_name = ( get_term_by( 'id', $broadcast_id, 'broadcast' ) ) ? get_term_by( 'id', $broadcast_id, 'broadcast' )->name : '' ;
					$formparams['CN'] = ( isset( $_REQUEST['from_name'] ) ) ? sanitize_text_field( $_REQUEST['from_name'] ) : false;
					$formparams['AMOUNT'] = ( isset( $_REQUEST['amount'] ) ) ? intval( $_REQUEST['amount'] * 100 ) : null;
					$formparams['EMAIL'] = ( isset( $_REQUEST['from_email'] ) ) ? sanitize_text_field( $_REQUEST['from_email'] ) : false;
					$formparams['OWNERADDRESS'] = ( isset( $_REQUEST['address'] ) ) ? sanitize_text_field( $_REQUEST['address'] ) : false;
					$formparams['OWNERZIP'] = ( isset( $_REQUEST['plz'] ) ) ? intval( $_REQUEST['plz'] ) : false;
					$formparams['OWNERTOWN'] = ( isset( $_REQUEST['town'] ) ) ? sanitize_text_field( $_REQUEST['town'] ) : false;
					$formparams['OWNERTELNO'] = ( isset( $_REQUEST['phone'] ) ) ? sanitize_text_field( $_REQUEST['phone'] ) : false;
					$formparams['CURRENCY'] = 'CHF';
					$formparams['LANGUAGE'] = 'de_DE';
					// $formparams['ORDERID'] = $_REQUEST['intention'] . '_' . $broadcast_slug_dash . strtoupper( uniqid() );
					$order_id = ( isset( $_REQUEST['order-id'] ) ) ? intval( $_REQUEST['order-id'] ) : strtoupper( uniqid() );
					$formparams['ORDERID'] = sanitize_text_field( $_REQUEST['intention'] . '_' . $broadcast_slug_dash . $order_id );
					
					// Some variables
					$rabe_options = get_option( 'rabe_option_name' ); // array of all settings
					$payment_thankyou_page = $rabe_options['payment_thankyou_page'];
					$payement_error_page = $rabe_options['payment_error_page'];
					
					// Postfinance settings
					$formparams['PSPID'] = $rabe_options['postfinance_pspid'];
					$postfinance_payment_url = $rabe_options['postfinance_url'];
					$shasign_key = $rabe_options['postfinance_shasign'];

					// Sort parameters and create postfinance form
					ksort( $formparams );
					$shasign_string = '';
					$postfinance_input_fields = '';	
					$payment_input_fields = '';
					
					// Build shastring and form fields for postfinance payment
					foreach ( $formparams as $param => $value ) {
						if ( ! empty ( $value ) ) {
							$shasign_string .= $param . '=' . $value . $shasign_key;
							$postfinance_input_fields .= '<input type="hidden" name="' . $param . '" value="' . $value . '">';
						}
					}
					// FIXME: make hash type configurable
					$shasign = strtoupper( hash( 'sha512', $shasign_string ) );
					$postfinance_input_fields .= '<input type="hidden" name="SHASIGN" value="' . $shasign . '">';
					
					// Build hidden fields for other payment
					foreach ( $formparams as $param => $value ) {
						if ( isset( $value ) && ! in_array( $param, array( 'PSPID', 'LANGUAGE', 'OWNERTELNO' ) ) ) {
							$payment_input_fields .= '<input type="hidden" name="' . $param . '" value="' . $value . '">';
						}
					}
					if ( isset( $broadcast_id ) ) {
						$payment_input_fields .= '<input type="hidden" name="broadcast-id" value="' . $broadcast_id . '">';
					}
					if ( isset( $order_id ) ) {
						$payment_input_fields .= '<input type="hidden" name="order-id" value="' . $order_id . '">';
					}

					// Paypal settings
					$paypal_payment_url = $rabe_options['paypal_url'];
					$paypal_receiver = $rabe_options['paypal_user'];
					
					// Send payment order mail and show confirmation page
					if ( isset( $_REQUEST['payment-order'] ) ) {

						// wp_mail( $to, $subject, $message, $headers );
						$wpmail_to = $rabe_options['email'];
						
						// Get host name
						$parse = parse_url( site_url() );
						$host = $parse['host'];
						
						// Build request string
						$request = ( $order_id ) ? ' (' . __( 'Request', 'rabe' ) . ' #' . $order_id . ')' : '';
						
						// Build subject
						$wpmail_subject = '[' . $host . '] ' . __( 'Send payment order!', 'rabe' ) . $request;
						
						// Is it a broadcast support?
						$broadcast_support = ( $broadcast_name ) ? __( '... wants to support the broadcast:', 'rabe' ) . ' ' . $broadcast_name . "\r\n\r\n" : '';
						
						$telno = ( isset( $_REQUEST['OWNERTELNO'] ) ) ? $_REQUEST['OWNERTELNO'] . "\r\n" : '';
										
						// FIXME: Make mail text configurable
						$wpmail_message = __( 'Hello', 'rabe' ) . "\r\n\r\n"
						. $_REQUEST['CN'] . "\r\n"
						. $_REQUEST['OWNERADDRESS'] . "\r\n"
						. $_REQUEST['OWNERZIP'] . ' ' . $_REQUEST['OWNERTOWN'] . "\r\n\r\n"
						. $telno
						. $_REQUEST['EMAIL'] . "\r\n\r\n"
						. __( '... wants to get an payment order!', 'rabe' ) . "\r\n\r\n"
						. $broadcast_support
						. __( 'To pay', 'rabe' ) . ': ' . $_REQUEST['AMOUNT'] / 100 . ' ' . $_REQUEST['CURRENCY'] . "\r\n\r\n"
						. __( 'Order ID', 'rabe' ) . ': ' . $_REQUEST['ORDERID'];
						
						wp_mail( $wpmail_to, $wpmail_subject, $wpmail_message );
						
						?>
						<h2><?php echo __( 'Thank you for the order.', 'rabe' ); ?></h2>
						<p><?php echo __( 'You are going to receive a payment order.', 'rabe' ); ?></p><?php
					
					} else {
						// Show payment page with payment buttons
						if ( $_REQUEST['intention'] === 'donation' ) {
							$intention_text = __( 'Donation', 'rabe' );
							$paypal_cmd = '_donations';
						} elseif ( $_REQUEST['intention'] === 'membership' ) {
							$intention_text = __( 'Pay membership', 'rabe' );
							$paypal_cmd = '_xclick';
						} elseif ( $_REQUEST['intention'] === 'support' ) {
							$intention_text = __( 'Support payment for', 'rabe' ) . ' ' . $broadcast_name;
							$paypal_cmd = '_xclick';
						} 				
						?>
						<header class="entry-header"><h1 class="entry-title" itemprop="headline"><?php echo $intention_text; ?></h1></header>
						<p><?php echo __( 'Order ID', 'rabe' ) ?>: <?php echo $formparams['ORDERID']; ?></p>
						<p><?php echo $formparams['CN']; ?><br>
						<?php echo $formparams['OWNERADDRESS']; ?><br>
						<?php echo $formparams['OWNERZIP'] . ' ' . $formparams['OWNERTOWN']; ?><br>
						</p>
						<p><strong><?php echo __( 'To pay', 'rabe' ) ?>: <?php echo $formparams['AMOUNT'] / 100 . ' ' . $formparams['CURRENCY']; ?></strong></p>
						<form method="post" action="<?php echo $postfinance_payment_url; ?>" id="postfinance-payment" name="postfinance-payment">
							<?php echo $postfinance_input_fields; ?>
							<input type="image" name="submit" style="padding:0;border:0;width:200px;float:left;" src="<?php echo get_stylesheet_directory_uri() . '/images/payment-postfinance.svg'; ?>" alt="<?php echo __( 'Pay with Postfinance', 'rabe' ); ?>" id="submit-postfinance-img">
							<input type="submit" name="submit" style="width: 250px;" value="<?php echo __( 'Pay with Postfinance', 'rabe' ); ?>" id="submit-postfinance" class="payment-button">
						</form>
						<br>
						<?php // https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/ ?>
						<form action="<?php echo $paypal_payment_url; ?>" method="post" id="paypal-payment" name="paypal-payment">
							<input type="hidden" name="business" value="<?php echo $paypal_receiver; ?>">
							<input type="hidden" name="cmd" value="<?php echo $paypal_cmd; ?>">
							<input type="hidden" name="item_name" value="<?php echo $formparams['ORDERID']; ?>">
							<input type="hidden" name="item_number" value="<?php echo $formparams['ORDERID']; ?>">
							<input type="hidden" name="invoice" value="<?php echo $formparams['ORDERID']; ?>">
							<input type="hidden" name="amount" value="<?php echo $formparams['AMOUNT'] / 100; ?>">
							<input type="hidden" name="currency_code" value="<?php echo $formparams['CURRENCY']; ?>">
							<input type="hidden" name="lc" value="<?php echo $formparams['LANGUAGE']; ?>">
							<input type="hidden" name="no_shipping" value="2">
							<input type="hidden" name="return" value="<? echo $payment_thankyou_page; ?>">
							<input type="hidden" name="cancel_return" value="<? echo $payment_error_page; ?>">
							<input type="hidden" name="first_name" value="<?php echo $first_name; ?>">
							<input type="hidden" name="last_name" value="<?php echo $last_name ?>">
							<input type="hidden" name="address1" value="<?php echo $formparams['OWNERADDRESS']; ?>">
							<input type="hidden" name="zip" value="<?php echo $formparams['OWNERZIP']; ?>">
							<input type="hidden" name="city" value="<?php echo $formparams['OWNERTOWN']; ?>">
							<input type="hidden" name="country" value="CH">
							<input type="hidden" name="email" value="<?php echo $formparams['EMAIL']; ?>">
							<input type="image" name="submit" style="padding:0;border:0;width:200px;float:left;" src="<?php echo get_stylesheet_directory_uri() . '/images/payment-paypal.png'; ?>" alt="<?php echo __( 'Pay with Paypal', 'rabe' ); ?>" id="submit-paypal-img">
							<input type="submit" name="submit" style="width: 250px;" value="<?php echo __( 'Pay with Paypal', 'rabe' ); ?>" id="submit-paypal">
						</form>
						<br>
						<form method="post" action="<?php echo get_permalink(); ?>" id="order-payment" name="order-payment" class="payment-button">
							<?php echo $payment_input_fields; ?>
							<input type="hidden" name="payment-order" value="1">
							<input type="hidden" name="intention" value="<?php echo $_REQUEST['intention']; ?>">
							<input type="image" name="submit" style="padding:0;border:0;width:200px;float:left;" src="<?php echo get_stylesheet_directory_uri() . '/images/payment-order.png'; ?>" alt="<?php echo __( 'Pay with payment order', 'rabe' ); ?>" id="submit-order-img" >
							<input type="submit" name="submit" style="width: 250px;" value="<?php echo __( 'Pay with payment order', 'rabe' ); ?>" id="submit-order" class="payment-button">
						</form>
						<?php
					}
			} else {
				echo '<header class="entry-header"><h1 class="entry-title" itemprop="headline">' . __( 'Error', 'rabe' ) .'</h1></header>';
				echo '<p>' . __( 'You don\'t want to pay anything, do you?!', 'rabe' ) . '</p>';
			}
		?>
		</div></article>

	<?php
	do_action( 'omega_after_content' ); 
	?>
</main><!-- .content -->
<?php get_footer(); 

} else {
	header( 'Location: ' . $throw_url );
}
?>
