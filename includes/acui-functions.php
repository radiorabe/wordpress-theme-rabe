<?php
/**
 * Functions for "Import users from CSV with meta" plugin
 * 
 * Allows to import a prepared CSV file with named plugin. CSV file structure to contain
 * following values
 * 
 * - Username: user login
 * - Email: mail address
 * - first_name: first name
 * - last_name: last name
 * - role: role in wordpress system
 * - broadcast: broadcast name
 * 
 * The file has to look like this:
 * Username,Email,first_name,last_name,role,broadcast
 * hamu,hans.mueller@emaildomain.tld,Hans,Müller,broadcaster,"Erschti Sändig, Zwöiti Sändig"
 * 
 * Email and Username have to start with a capital letter.
 * 
 * @package rabe
 * @since version 1.0.0
 */


/**
 * Inserts broadcasts from CSV
 * 
 * Inserts broadcasts to user meta table and restrict taxonomies settings table
 * and converts the "broadcast" string to an array after import
 * 
 * @package rabe
 * @since version 1.0.0
 */
function user_meta_broadcast() {

	// WP_User_Query arguments
	$args = array();
	
	// Get all broadcasters
	$users = get_users( $args );

	// Check for results
	if ( ! empty( $users ) ) {

		foreach ($users as $user) {
			
			// Get all the users info
			$user_info = get_userdata( $user->ID );

			// Convert user meta broadcast string into an array
			if ( is_string( $user_info->broadcast ) ) {
				
				// Create array of CSV row in user meta
				$broadcasts = str_getcsv( $user_info->broadcast );

				// Trim broadcast names and get broadcast slugs
				$cleaned_broadcasts = '';
				foreach ( $broadcasts as $broadcast ) {
					
					// Sanitize broadcast like in wp_insert_term
					// See acui_import_broadcast_terms()
					$cleaned_broadcasts[] = sanitize_title( trim( $broadcast ) );
					
				}
				
				// Add term_ids of broadcasts to user meta by comparing cleaned_broadcasts
				// variable slug name with existing term slug (Should be the same)
				$term_ids = '';
				foreach ( $cleaned_broadcasts as $broadcast ) {
					$term_ids[] = get_term_by( 'slug', $broadcast, 'broadcast')->term_id;
				}
				
				// Update user meta broadcast
				update_user_meta( $user_info->ID, 'broadcast', $term_ids );
			}
			update_user_broadcast_restrictions( $user->ID );
		}
	}
}
// Remove custom user profile fields of Import users of CSV with meta plugin, we don't need them
remove_action( 'show_user_profile', 'acui_extra_user_profile_fields' );
remove_action( 'edit_user_profile', 'acui_extra_user_profile_fields' );
remove_action( 'personal_options_update', 'acui_save_extra_user_profile_fields' );
remove_action( 'edit_user_profile_update', 'acui_save_extra_user_profile_fields' );
// Add our function
add_action('after_acui_import_users', 'user_meta_broadcast');


/**
 * Inserts new broadcast terms from CSV to broadcast taxonomy
 * 
 * @package rabe
 * @since version 1.0.0
 * @param array $headers Headers of csv file (@see acui-functions.php)
 * @param array $data Data of csv file
 */
function acui_import_broadcast_terms( $headers, $data ) {

	$single_user_array = array();
	
	// Create array with column header as key and data as value
	foreach ( $headers as $key => $header ) {
		$single_user_key = $headers[$key];
		$single_user_array[$single_user_key] = $data[$key];
	}
	
	// Create array out of broadcasts
	$broadcasts = $single_user_array['broadcast'];
	$broadcasts = str_getcsv( $broadcasts );
	
	// Insert broadcast terms into broadcast taxonomy
	if ( isset( $broadcasts ) ) {
		foreach ( $broadcasts as $broadcast ) {
			wp_insert_term( trim( $broadcast ), 'broadcast' );
		}
	}	
}
add_action( 'pre_acui_import_single_user', 'acui_import_broadcast_terms', 10, 2);
?>
