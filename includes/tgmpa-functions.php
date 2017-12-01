<?php
/**
 * Automatic activation of needed plugins with
 * TGM_Plugin_Activation class.
 * 
 * @package rabe
 * @since version 1.0.0
 */

add_action( 'tgmpa_register', 'rabe_register_required_plugins' );

/**
 * Register the required plugins for this theme.
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 */
function rabe_register_required_plugins() {

	$plugins = array(

		/* This is an example of how to include a plugin bundled with a theme.
		array(
			'name'               => 'TGM Example Plugin', // The plugin name.
			'slug'               => 'tgm-example-plugin', // The plugin slug (typically the folder name).
			'source'             => get_stylesheet_directory() . '/lib/plugins/tgm-example-plugin.zip', // The plugin source.
			'required'           => true, // If false, the plugin is only 'recommended' instead of required.
			'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
			'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
			'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			'external_url'       => '', // If set, overrides default API URL and points to an external URL.
			'is_callable'        => '', // If set, this callable will be be checked for availability to determine if a plugin is active.
		),
		*/

		array(
			'name'				 => 'RaBe Playlist',
			'slug'				 => 'rabe-playlist',
			'source'			 => get_stylesheet_directory() . '/lib/plugins/rabe-playlist.zip',
			'required'			 => true,
			'force_activaion'	 => true,
			'force_deactivation' => true,
			'external_url'		 => 'https://git.momou.ch',
	
		),
		array(
			// @link https://github.com/brknkfr/Restrict-Taxonomies
			'name'				 => 'Restrict Taxonomies',
			'slug'				 => 'restrict-taxonomies',
			'source'			 => get_stylesheet_directory() . '/lib/plugins/rabe-restrict-playlist.zip',
			'required'			 => true,
			'force_activaion'	 => true,
			'force_deactivation' => true, 
		),
		array(
			'name'			  => 'Event Organiser',
			'slug'			  => 'event-organiser',
			'required'		  => true,
			'force_activaion' => true,
			'version'		  => '3.2.1', // eo_get_permalink fix
		),
		array(
			'name'			  => 'Simple Colorbox',
			'slug'			  => 'simple-colorbox',
			'required'		  => true,
			'force_activaion' => true,
		),
		array(
			'name'			  => 'Fast Secure Contact Form',
			'slug'			  => 'si-contact-form',
		),
		array(
			'name'			  => 'Email Address Encoder',
			'slug'			  => 'email-address-encoder',
		),
		array(
			'name'			  => 'Import users from CSV with meta',
			'slug'			  => 'import-users-from-csv-with-meta',
			'version'		  => '1.9.7', // We need hooks of this plugin!
		),
	);

	/*
	 * Array of configuration settings. Amend each line as needed.
	 *
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 * Only uncomment the strings in the config array if you want to customize the strings.
	 */
	$config = array(
		'id'           => 'rabe_tgmpa',
		'menu'         => 'rabe-tgmpa-install-plugins',
		'dismissable'  => false,
		'dismiss_msg'  => __( 'Consider installing these plugins for a correctly working theme!', 'rabe' ),
		'is_automatic' => true,
		'strings'      => array(
			'nag_type' => 'error',
		)
	);

	tgmpa( $plugins, $config );

}
?>
