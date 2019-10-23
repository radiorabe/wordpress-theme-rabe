<?php
/**
 * Functions for Restrict Taxonomies Plugin
 * 
 * @package rabe
 * @since version 1.0.0
 */

/**
 * Show restrictions menu item on a different place
 * 
 * @package rabe
 * @since version 1.0.0
 */
function add_permissions_menu() {
	add_menu_page( __('Permissions', 'rabe'), __('Permissions', 'rabe'), 'manage_options', 'options-general.php?page=restrict-taxonomies', null, 'dashicons-lock' );
}
add_action( 'admin_menu', 'add_permissions_menu' , 13);


/**
 * Add broadcast box to user profile page
 *
 * Displays broadcast selecting box and saves selected broadcasts to user meta
 * and to restrict taxonomies plugin settings in the wp_options table
 * 
 * @package rabe
 * @since version 1.0.0
 * @link http://wordpress.stackexchange.com/questions/51802/update-option-stored-in-multi-dimensional-array
 * @param mixed $user Full user information
 */
function broadcast_user_profile_fields( $user ) {

	// Don't display for those with manage_categories capability
    if( ! current_user_can( 'manage_categories' ) ) {
        return;
	}

	// If taxonomy "broadcast" isn't controlled by Restrict Taxonomies, stop
	$rc_broadcast = get_option('RestrictTaxs_post_type_options');
	if ( ! in_array( 'broadcast', $rc_broadcast['taxonomies'] ) ) {
		return;
	}

    // Get all user restrictions from Restrict Taxonomies
    $all_restrictions = get_option( 'RestrictTaxs_user_options' );
    
	// Check if there is already an user
    if( is_object( $user ) ) {
				
	    // Get braodcasts of user
		$user_broadcasts = get_user_meta( $user->ID, 'broadcast', true );
		
		// Create array key with user_login
		$user_restrictions_key = $user->user_login . '_user_cats';
		
		// Create array with user restrictions
		if ( ! isset( $all_restrictions['broadcast'][$user_restrictions_key]) ) {
			$all_restrictions['broadcast'][$user_restrictions_key] = array();
		}

	} else {
		$user_broadcasts = array();
	}
    
	// Check if user meta broadcast array and user broadcast restrictions array differ
	$user_broadcast_restrictions = $all_restrictions['broadcast'][$user_restrictions_key];
	if ( $user_broadcasts != $user_broadcast_restrictions ) {

		// Reset user broadcasts array
		$user_broadcasts = array();
		foreach ( $user_broadcast_restrictions as $user_broadcast_restriction ) {
			// Build array
			$user_broadcasts[] = $user_broadcast_restriction;
		}
		// Update user broadcasts before showing the ui
		update_user_meta ( $user->ID, 'broadcast', $user_broadcasts );

	}
	
	// Get all available broadcast terms
	$broadcasts = get_terms( 'broadcast', 
		array(
			'hide_empty' => false,
		)
	);
    ?>
    
    <h2><?php echo __( 'Broadcasts', 'rabe' ); ?></h2>
    <?php
	// START: dirty copy of divs/html-code from restrict-taxonomies.php from Restrict Taxonomies plugin
	?>
	<div style="float:left; padding:5px; width:100%;" class="metabox-holder" id="side-sortables">
	<div class="postbox">
	<div style="padding:0 10px;" class="inside">
	<div class="taxonomydiv">
	<div class="tabs-panel tabs-panel-active" id="<?php echo $user_restriction_key; ?>-all">
	<ul class="categorychecklist form-no-clear">
	<?php
	// Build rabe user broadcast checkbox list
	foreach ( $broadcasts as $broadcast ) {
		// Check if broadcast is already in array
		$checked = ( in_array( $broadcast->term_id, $user_broadcasts ) ) ? 'checked' : '';
		?>
		<li id="<?php echo $user_restrictions_key . '-' . $broadcast->term_id; ?>">
		<label class="selectit">
			<input type="checkbox" <?php echo $checked; ?> name="broadcast[]" value="<?php echo $broadcast->term_id; ?>"><?php echo $broadcast->name; ?>
		</label>
		</li>
		<?php
	}

	// END: dirty copy divs/html-code
	?>
	</ul></div></div></div></div></div>

<?php

}
add_action( 'show_user_profile', 'broadcast_user_profile_fields' );
add_action( 'edit_user_profile', 'broadcast_user_profile_fields' );
add_action( 'user_new_form', 'broadcast_user_profile_fields' );

/**
 * Update user meta table broadcast
 * 
 * Writes broadcast terms into user meta table
 *
 * @package rabe
 * @since version 1.0.0
 * @param int $user_ User ID
 */
function save_broadcast_user_profile_fields( $user_id ) {

    if( ! current_user_can( 'manage_categories' ) )
        return false;

    if ( isset( $_POST['broadcast'] ) ) {
		// FIXME: Maybe some sanitization?
		update_user_meta( $user_id, 'broadcast', $_POST['broadcast'] );
	}

}
add_action('user_register', 'save_broadcast_user_profile_fields', 12 );
add_action('profile_update', 'save_broadcast_user_profile_fields', 12 );


/**
 * Update restricted taxonomies settings
 * 
 * Writes new restrictions in settings for restricted taxonomies in wp_options
 * if there are any new restrictions
 *
 * @package rabe
 * @since version 1.0.0
 * @param int $user_id User ID
 */
function update_user_broadcast_restrictions( $user_id ) {

    if( ! current_user_can( 'manage_categories' ) ) {
        return;
	}
    
	// If taxonomy "broadcast" isn't controlled by Restrict Taxonomies, stop
	$restricted_post_types = get_option('RestrictTaxs_post_type_options');
	if ( ! in_array( 'broadcast', $restricted_post_types['taxonomies'] ) ) {
		return;
	}

    // Get all user restrictions
    // FIXME: What to do, when there are no RestrictTaxs_user_options set?
	//        That's rarely the case, but sometimes in the beginning
    $all_restrictions = get_option( 'RestrictTaxs_user_options' );
    
	// Get user meta broadcasts
	$user_broadcasts = get_user_meta( $user_id, 'broadcast', true );

	// Build user restriction array key
	$user_info = get_userdata( $user_id );
	$user_restrictions_key = $user_info->user_login . '_user_cats';

	// Create array key for user restrictions
   	if ( ! isset( $all_restrictions['broadcast'][$user_restrictions_key] ) ) {
		$all_restrictions['broadcast'][$user_restrictions_key] = array();
    }

	// Check if user meta broadcast array and user broadcast restrictions array differ, then update
	$user_broadcast_restrictions = $all_restrictions['broadcast'][$user_restrictions_key];
	
	if ( $user_broadcasts != $user_broadcast_restrictions ) {
	
		// Reset array
		$all_restrictions['broadcast'][$user_restrictions_key] = array();	
		foreach ( $user_broadcasts as $user_broadcast ) {
			// Build array with broadcast restrictions for user
			$all_restrictions['broadcast'][$user_restrictions_key][] = $user_broadcast;
		}
		
		// FIXME: Create other restrictions when it's a new user (no restrictions set yet)
		foreach ( $restricted_post_types['taxonomies'] as $post_type ) {
			if ( ! isset( $all_restrictions[$post_type][$user_restrictions_key] ) ) {
				$all_restrictions[$post_type][$user_restrictions_key] = array( 'RestrictCategoriesDefault' );
			}
		}
		// Set default cat
		$all_restrictions['broadcast'][$user_restrictions_key][] = 'RestrictCategoriesDefault';
						
		// Update all restrictions
		update_option( 'RestrictTaxs_user_options', $all_restrictions );
	}
}
add_action( 'user_register', 'update_user_broadcast_restrictions', 13 );
add_action( 'profile_update', 'update_user_broadcast_restrictions', 13 );


/**
 * Automatically add taxonomy broadcast restrictions for editors or administrators 
 * after inserting or deleting a broadcast term
 * 
 * @package rabe
 * @since version 1.0.0
 * @param int $term_id ID of broadcast term (actual broadcast slug name)
 * @param int $tt_id Taxonomy ID (ID of taxonomy "broadcast")
 */
function rabe_staff_broadcast_restrictions( $term_id, $tt_id ) {

	// Are roles existing?
	if ( ! get_role( 'rabe_staff' ) && ! get_role( 'rabe_webteam' ) && ! get_role( 'administrator' ) ) {
		return false;
	}

	$role_restrictions = get_option( 'RestrictTaxs_options' );

	$taxonomy = 'broadcast';
	
	// Get terms
	$terms = get_terms(
		array(
			'taxonomy'	 => 'broadcast',
			'hide_empty' => false,
		)
	);

	// Reset restrictions array
	$role_restrictions[$taxonomy]['rabe_webteam_cats'] = array();
	$role_restrictions[$taxonomy]['rabe_staff_cats'] = array();
	$role_restrictions[$taxonomy]['administrator_cats'] = array();
	
	// Add all term_ids to restrictions array
	foreach ( $terms as $term ) {
		$role_restrictions[$taxonomy]['rabe_webteam_cats'][] = $term->term_id;
		$role_restrictions[$taxonomy]['rabe_staff_cats'][] = $term->term_id;
		$role_restrictions[$taxonomy]['administrator_cats'][] = $term->term_id;
	}
	
	// Add default term_id
	$role_restrictions[$taxonomy]['rabe_webteam_cats'][] = 'RestrictCategoriesDefault';
	$role_restrictions[$taxonomy]['rabe_staff_cats'][] = 'RestrictCategoriesDefault';
	$role_restrictions[$taxonomy]['administrator_cats'][] = 'RestrictCategoriesDefault';
	
	// Update restrictions
	update_option( 'RestrictTaxs_options', $role_restrictions );

}
add_action( 'delete_broadcast', 'rabe_staff_broadcast_restrictions', 11, 2 );
add_action( 'created_broadcast', 'rabe_staff_broadcast_restrictions', 11, 2 );


/**
 * Automatically add category restrictions for webteam or administrators 
 * after inserting or deleting a category term
 * 
 * @package rabe
 * @since version 1.0.0
 * @param int $term_id ID of broadcast term (actual broadcast slug name)
 * @param int $tt_id Taxonomy ID (ID of taxonomy "category")
 */
function rabe_admin_category_restrictions( $term_id, $tt_id ) {

	if ( ! get_role( 'rabe_webteam' ) )
		return;

	$role_restrictions = get_option( 'RestrictTaxs_options' );
	
	$taxonomy = 'category';

	// Get terms
	$terms = get_terms(
		array(
			'taxonomy' 	 => 'category',
			'hide_empty' => false,
		)
	);

	// Reset restrictions array
	$role_restrictions[$taxonomy]['rabe_webteam_cats'] = array();
	$role_restrictions[$taxonomy]['administrator_cats'] = array();
	
	// Add all term_ids to restrictions array
	foreach ( $terms as $term ) {
		$role_restrictions[$taxonomy]['rabe_webteam_cats'][] = $term->term_id;
		$role_restrictions[$taxonomy]['administrator_cats'][] = $term->term_id;
	}
	
	// Add default term_id
	$role_restrictions[$taxonomy]['rabe_webteam_cats'][] = 'RestrictCategoriesDefault';
	$role_restrictions[$taxonomy]['administrator_cats'][] = 'RestrictCategoriesDefault';
	
	// Update restrictions
	update_option( 'RestrictTaxs_options', $role_restrictions );
	
}
add_action( 'delete_category', 'rabe_admin_category_restrictions', 11, 2 );
add_action( 'created_category', 'rabe_admin_category_restrictions', 11, 2 );
?>
