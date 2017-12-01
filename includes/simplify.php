<?php
/**
 * Remove a lot of stuff in the admin backend
 *
 * @package rabe
 */

if ( is_admin() ) {

	/**
	 * Remove menu items
	 * 
	 * @link ttp://wordpress.stackexchange.com/questions/28782/possible-to-hide-custom-post-type-ui-menu-from-specific-user-roles
	 */
	function rabe_remove_menu_items() {
		// Remove menu items for non administrators
		if ( ! current_user_can( 'manage_options' ) ) {
			remove_menu_page( 'edit-comments.php' ); // Comments
			remove_menu_page( 'plugins.php' ); // Plugins
			remove_menu_page( 'themes.php' ); // Appearance
			remove_menu_page( 'users.php' ); // Users
			remove_menu_page( 'tools.php' ); // Tools
			remove_menu_page( 'options-general.php' ); // Options
			remove_submenu_page( 'edit-tags.php', 'categories' ); // Child themes
		}
		// Remove dashboard for non editors
		if ( ! current_user_can( 'edit_pages' )) {
			remove_menu_page( 'index.php' ); // Dashboard
		}
		// Remove for all users
		remove_menu_page( 'link-manager.php' ); // Links
		remove_menu_page( 'edit-comments.php' ); // Comments 
	}
	add_action( 'admin_menu', 'rabe_remove_menu_items', 11 );
	
	/**
	 * Remove metaboxes
	 */
	function rabe_remove_metaboxes() {
		// Remove metaboxes for non administrators
		if( ! current_user_can( 'manage_options' ) ) {
			remove_meta_box( 'specialpost', 'post', 'side' );
			// remove_meta_box( 'postexcerpt', 'post', 'normal' );
			remove_meta_box( 'trackbacksdiv', 'post', 'normal' );
			remove_meta_box( 'postcustom', 'post', 'normal' );
			// remove_meta_box( 'revisionsdiv', 'post', 'normal' );
			remove_meta_box( 'authordiv', 'post', 'normal' );
			remove_meta_box( 'slugdiv', 'post', 'normal' );
		}
		// Remove for all users
		remove_meta_box( 'commentstatusdiv', 'post', 'normal' );
		remove_meta_box( 'commentsdiv', 'post', 'normal' );
				// Remove "Personal Information" meta box
		if ( isset( $meta_box['id'] ) && 'personal' == $meta_box['id'] )
		{
			unset( $meta_boxes[$k] );
		}
		// Shariff plugin
		remove_meta_box( 'postimagediv', 'post', 'normal' );
	}
	add_action( 'admin_menu' , 'rabe_remove_metaboxes', 99 );


	/**
	 * Remove plugin metaboxes
	 */
	function rabe_remove_plugin_metaboxes(){
		if ( ! current_user_can( 'manage_options' ) ) {
			remove_meta_box( 'shariff_metabox', 'post', 'side' );
			remove_meta_box( 'shariff_metabox', 'event', 'side' );
		}
	}
	add_action( 'do_meta_boxes', 'rabe_remove_plugin_metaboxes' );
	

	/**
	 * Remove admin footer
	 */
	function rabe_remove_footer() {
		if ( ! current_user_can( 'manage_options' ) ) {
			remove_filter( 'update_footer', 'core_update_footer' );
			function remove_footer_text(){}
			add_filter( 'admin_footer_text', 'remove_footer_text' );		
		}
	}
	add_action( 'admin_menu', 'rabe_remove_footer', 11 );
		
	/**
	 * Remove comments column
	 */
	function rabe_columns( $columns ) {
		unset( $columns['comments'] );
		return $columns;
	}
	add_filter( 'manage_posts_columns' , 'rabe_columns' );
	add_filter( 'manage_pages_columns' , 'rabe_columns' );
	add_filter( 'manage_media_columns' , 'rabe_columns' );

	
	// Remove admin color scheme
	remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker', 11 );
	
	/**
	 * Remove dashboard widgets
	 */
	function rabe_remove_dashboard_widgets(){
		if ( ! current_user_can( 'manage_options' ) ) {
			remove_meta_box( 'dashboard_welcome-panel', 'dashboard', 'normal' ); // Welcome
			remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' ); // Right Now
			remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' ); // Recent Comments
			remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );  // Incoming Links
			remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );   // Plugins
			remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );   // WordPress blog
			remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );   // Other WordPress News
		}
	}
	add_action( 'wp_dashboard_setup', 'rabe_remove_dashboard_widgets', 11 );
	
	/**
	 * Hide elements via inline stylesheet
	 */
	function admin_css() {
		/**
		 * Hide a lot of unneeded stuff via CSS
		 *  - Hide help tab
		 *  - Custom taxonomy broadcast can't have parents
		 *  - Small preview image on broadcast taxonomy
		 *  - Hide reset button of restrict taxonomies (dangerous)
		 *  - Hide latest comments in dashboard
		 */

		// Start stylesheet
		echo '<style type="text/css">
				#contextual-help-link-wrap,
				.taxonomy-broadcast .term-parent-wrap,
				.settings_page_restrict-taxonomies #reset,
				#latest-comments {
					display:none !important;
				}
				.taxonomy-broadcast .simplePanelImagePreview img {
					max-width: 350px;
				}';
		
		// End stylesheet
		echo '</style>';
	}
	add_action( 'admin_head', 'admin_css', 11 );

	/**
	 * Load extra stylesheet for non administrators (broadcasters)
	 */
	function admin_broadcast_css() {
		if ( ! current_user_can( 'manage_categories' ) ) {
			// Hide creation column for new boradcasts
			wp_enqueue_style( 'broadcaster_css', get_stylesheet_directory_uri() . '/css/broadcaster.css' );
		}
	}
	add_action( 'admin_head', 'admin_broadcast_css' );
	
	/**
	 * Disable changing of mail address for non editors
	 */
	function disable_userprofile_fields() {

		global $pagenow;
	
		if ( ! current_user_can( 'edit_pages' ) && 'profile.php' === $pagenow ) {
			?>
			<script>
				jQuery(document).ready( function($) {
					if ( $( 'input[name=email]' ).length ) {
						$( 'input[name=email]' ).prop( 'readonly', true );
					}
				});
			</script>
			<?php
		}
	}
	add_action( 'admin_footer', 'disable_userprofile_fields' );
}

/**
 * Hide post settings in customizer view
 */
function customizer_css() {
	if ( ! current_user_can( 'switch_themes' ) ) {
		wp_enqueue_style( 'rabe-customizer', get_stylesheet_directory_uri() . '/css/customizer.css' );
	}
}
add_action('customize_controls_print_scripts', 'customizer_css', 11);

/**
 * Remove links in adminbar
 */
function rabe_remove_admin_bar_links() {
	
	global $wp_admin_bar;
	
	// Remove links for non administrators
	if( ! current_user_can( 'manage_options' ) ) {
		$wp_admin_bar->remove_menu( 'widgets' ); // Remove the widgets link
		$wp_admin_bar->remove_menu( 'customize' ); // Remove the customize link
		$wp_admin_bar->remove_menu( 'themes' ); // Remove the themes link
	}
	
	// Remove other links
	$wp_admin_bar->remove_menu( 'about' ); // Remove the about WordPress link
	$wp_admin_bar->remove_menu( 'wporg' ); // Remove the WordPress.org link
	$wp_admin_bar->remove_menu( 'support-forums' ); // Remove the support forums link
	$wp_admin_bar->remove_menu( 'feedback' ); // Remove the feedback link
	$wp_admin_bar->remove_menu( 'wp-logo' ); // Remove the WordPress logo
	$wp_admin_bar->remove_menu( 'comments' ); // Remove the comments link
	$wp_admin_bar->remove_menu( 'omega-child-themes' ); // Remove omega child themes
}
add_action( 'wp_before_admin_bar_render', 'rabe_remove_admin_bar_links', 11 );
?>
