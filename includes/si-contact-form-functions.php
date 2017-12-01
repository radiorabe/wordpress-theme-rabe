<?php
/**
 * Functions and definitions for si-contact-form plugin
 * 
 * @package rabe
 */
 
/**
* Add order-id and broadcast-id to contact email fields
* 
* @package rabe
* @since version 1.0.0
* @param array $email_fields
* @param int $form_id_num
* @return array $email_fields Returns modified email_fields
*/
function rabe_email_fields( $email_fields, $form_id_num ) {
	
	$all_forms = false;
	$rabe_options = get_option( 'rabe_option_name' );
	$forms = array( $rabe_options['membership_form'], $rabe_options['support_form'], $rabe_options['donation_form'] );
	
	if ( ! in_array( $form_id_num, $forms ) && $all_forms !== true ) {
		return $email_fields;
	}

	$order_id = 1; // default start number
	
	// set an option for remembering the increment number
	$order_id = get_option( 'rabe_order_number', $order_id );

	// increase the number and save it
	update_option( 'rabe_order_number', $order_id + 1 );

	// uncomment and make a form post if you need to reset this
	// delete_option( 'order_number_form_' . $form_id_num );

	// Add broadcast-id when coming from support form
	if ( $form_id_num == $rabe_options['support_form'] ) {
		$email_fields['broadcast-id'] = $_REQUEST['broadcast-id'];
	}
	$email_fields['order-id'] = $order_id;

	return $email_fields;

}
add_filter( 'si_contact_email_fields', 'rabe_email_fields', 10, 2 );


/**
* Add host name and/or order id to contact email subject
* 
* @package rabe
* @since version 1.0.0
* @param string $subj
* @param int $form_id_num
* @return string $subj Returns modified subject
*/
function rabe_email_subject( $subj, $form_id_num ) {

	$all_forms = true;
	$rabe_options = get_option( 'rabe_option_name' );
	$forms = array( $rabe_options['membership_form'], $rabe_options['support_form'], $rabe_options['donation_form'] );

	if ( ! in_array( $form_id_num, $forms ) && $all_forms !== true) {
		return $subj;
	}

	// HINT: Order number is already increased in rabe_email_fields(), which is called before this function
	$order_id = get_option( 'rabe_order_number', $order_id );

	// modify the email subject
	$parse = parse_url( site_url() );
	$host = $parse['host']; 
	if ( in_array ( $form_id_num, $forms ) ) {
		if ( $order_id ) {
			// HINT: See ordernumber_email_fields(), order_number was already increased now we have to decrease one
			$order_id = $order_id - 1;
			$subj = '[' . $host . '] ' .$subj . ' (' . __( 'Request', 'rabe' ) . ' #' . $order_id . ')' ;
		}
	} else {
		$subj = '[' . $host . '] ' .$subj;
	}

	return $subj;

}
add_filter( 'si_contact_email_subject', 'rabe_email_subject', 11, 2 );


/**
* Add order id to email message
* 
* @package rabe
* @since version 1.0.0
* @param string $email_msg
* @param int $form_id_num
* @return string $email_msg Returns modified email_msg
*/
function ordernumber_email_msg( $email_msg, $inline_or_newline, $php_eol, $form_id_num ) {

	$all_forms = false;
	$rabe_options = get_option( 'rabe_option_name' );
	$forms = array( $rabe_options['membership_form'], $rabe_options['support_form'], $rabe_options['donation_form'] );

	if ( ! in_array( $form_id_num, $forms ) && $all_forms !== true) {
		return $email_msg;
	}

	// HINT: Order number is already increased in rabe_email_fields(), which is called before this function
	$order_id = get_option( 'rabe_order_number', $order_id );

	if ( $order_id ) {
		// HINT: See ordernumber_email_fields(), order_number was already increased now we have to decrease one
		$order_id = $order_id - 1;
		$email_msg .= __('Order ID', 'rabe' ) . ':' . $inline_or_newline . '#' . $order_id;
	}

	return $email_msg;

}
add_filter( 'si_contact_email_msg', 'ordernumber_email_msg', 12, 4 );


/**
* Add host name and order-id to email autoresponder message subject
* 
* @package rabe
* @since version 1.0.0
* @param string $subj
* @param int $form_id_num
* @return string $subj Returns modified subject
*/
function rabe_autoresp_email_subject( $subj, $form_id_num ) {

	$all_forms = true;
	$rabe_options = get_option( 'rabe_option_name' );
	$forms = array( $rabe_options['membership_form'], $rabe_options['support_form'], $rabe_options['donation_form'] );

	if ( ! in_array( $form_id_num, $forms ) && $all_forms !== true) {
		return $subj;
	}

	// HINT: Order number is already increased in rabe_email_fields(), which is called before this function
	$order_id = get_option( 'rabe_order_number', $order_id );

	// modify the email subject
	$parse = parse_url( site_url() );
	$host = $parse['host']; 
	if ( in_array ( $form_id_num, $forms ) ) {
		if ( $order_id ) {
			// HINT: See ordernumber_email_fields(), order_number was already increased now we have to decrease one
			$order_id = $order_id - 1;
			$subj = '[' . $host . '] ' .$subj . ' (' . __( 'Request', 'rabe' ) . ' #' . $order_id . ')' ;
		}
	} else {
		$subj = '[' . $host . '] ' .$subj;
	}

	return $subj;

}
add_filter( 'si_contact_autoresp_email_subject', 'rabe_autoresp_email_subject', 13, 2 );

/**
* Adds broadcast chooser to form
* 
* @package rabe
* @since version 1.0.0
* @param string $subj
* @param int $form_id_num
* @return string $subj Returns modified subject
*/
function broadcast_chooser_field( $string, $style, $form_errors, $form_id_num ) {

	$all_forms = false;
	$rabe_options = get_option( 'rabe_option_name' );
	$forms = array( $rabe_options['support_form'] ); 
	
	if ( ! in_array( $form_id_num, $forms ) && $all_forms !== true) {
		return $string;
	}

	if ( isset( $_REQUEST['broadcast-id'] ) ) {
		$broadcast_id = intval( $_REQUEST['broadcast-id'] );
		$broadcast = get_term_by( 'id', $broadcast_id, 'broadcast' );
		$contact_name = $broadcast->name;
		$input_fields = '<input type="hidden" id="broadcast-id" name="broadcast-id" value="' . $broadcast_id . '">
			<p style="padding-top:5px;">' . __( 'Broadcast', 'rabe' ) . ': <strong>' . $contact_name . '</strong></p>';
		
	} else {
		$active_broadcasts = get_active_broadcasts();
		$active_broadcasts_options = '';
		$input_fields = '';
		
		foreach ( $active_broadcasts as $broadcast ) {
			$active_broadcasts_options .= '<option value="' . $broadcast->term_id . '">' . $broadcast->name . '</option>';
		}
		$input_fields = '<div style="padding-top:5px;"><label>' . __( 'Broadcast', 'rabe' ) 
			. ':<br><select id="broadcast-id" name="broadcast-id" required>
			<option value="">' . __( 'None' , 'rabe' ) . '</option>'
			. $active_broadcasts_options
			. '</select></label></div>';
	}
		
	$string .= $input_fields;
	
	return $string;

}
add_filter( 'si_contact_display_after_fields', 'broadcast_chooser_field', 10, 4 );

?>
