<?php
/**
 * Main theme functions and definitions
 *
 * @package rabe
 * @since version 1.0.0
 */

/*
 * Includes
 */
// Development
$host = $_SERVER['HTTP_HOST'];
global $develop;
$develop = false;
// if ( 'rabe.ch' !== $host && 'dev.rabe.ch' !== $host ) {
if ( 'rabe.ch' !== $host ) {
	include( 'includes/dev.php' );
}

if ( is_admin() ) {
	// Add a custom settings page
	include( 'includes/settings.php' );

	// Include automatic plugin activation
	include( 'includes/tgmpa-functions.php' );

}

// Simplify admin backend
include( 'includes/simplify.php' );

// Include files for broadcast taxonomy
include( 'includes/broadcast.php' );

// Include event-organiser-functions.php, special functions for Event Organiser plugin
if ( function_exists( 'eventorganiser_load_textdomain' ) ) {
	require_once( 'includes/event-organiser-functions.php' );
}
// Include si-contact-form-functions,php, special functions for Simple Contact Form plugin
if ( class_exists( 'siContactForm' ) ) {
	require_once( 'includes/si-contact-form-functions.php' );
}

// Include player functions
include( 'includes/player-functions.php' );

// Include secondary "sticky post" called specialpost
include( 'includes/specialpost.php' );

// Include user settings (for help notices and mailform)
include( 'includes/user-settings.php' );

// Include help notices
include( 'includes/help-notices.php' );

// Include restrict-taxonomies-functions.php, special functions for Restrict Taxonomies plugin
if ( class_exists( 'RestrictTaxonomies' ) ) {
	require_once('includes/restrict-taxonomies-functions.php');
}

// Include polylang-functions.php, special functions for Polylang plugin
if ( defined( 'POLYLANG_VERSION' ) ) {
	require_once('includes/polylang-functions.php');
}
// Include acui-functions.php, special functions for Import users from CSV with meta plugin
if ( function_exists( 'acui_init' ) ) {
	require_once('includes/acui-functions.php');
}

// Include colorbox.php, settings for Simple Colorbox plugin
if ( function_exists( 'simple_colorbox' ) ) {
	require_once( 'includes/colorbox.php' );
}

// Include broadcast-archive-shortcode.php, shortcode for real audio link generator
// OBSOLETE
// include( 'includes/realarchive-shortcode.php' );


/**
 * Setup theme
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_setup() {

	// Remove unnecessary header code
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_theme_support( 'custom-header' );
	
	// Remove unused stuff from omega theme
	remove_theme_support( 'omega-custom-css' );
	remove_theme_support( 'omega-child-page' );
	
	// Custom background
	$rabe_background = array(
		'default-color'      => 'b8ddc2',
		'default-repeat'     => 'no-repeat',
		'default-position-x' => 'center',
		'default-image'      => get_stylesheet_directory_uri() . '/images/bg.png',
	);
	add_theme_support( 'custom-background', $rabe_background );
	
	// Add plugin activation support
	add_theme_support( 'plugin-activation' );
	
	// First header-menu, then header, then main menu
	remove_action( 'omega_after_main', 'omega_primary_sidebar' );
	add_action( 'omega_after_header', 'rabe_header_menu' );

	// Add shortname string in menu
	add_action( 'omega_after_primary_menu', 'rabe_shortname', 11);

	// Add three home tiles before content
	add_action( 'omega_before_content', 'rabe_before_content');

	// Remove entry-footer and add custom site-footer
	add_filter( 'omega_footer_insert', 'rabe_footer_insert', 11 );
	
	// Remove all the comments
	remove_action( 'omega_after_loop', 'comments_template' );
	
	// Remove comment feed
	add_filter( 'feed_links_show_comments_feed', '__return_false' );
	
	// Custom image sizes for media
	add_action( 'init', 'rabe_add_image_sizes' );
	
	// Let user choose custom image sizes
	add_filter( 'image_size_names_choose', 'rabe_show_image_sizes' );

	// Max content width, It's 66% of content wrap minus site-container padding 
    omega_set_content_width( 750 );

	// Set language
	load_theme_textdomain( 'omega', get_stylesheet_directory() . '/languages' );
	load_child_theme_textdomain( 'rabe', get_stylesheet_directory() . '/languages' );
	
	// Global variables
	// Define gobal variable for category which isn't shown in header
	global $not_in_header;

	$rabe_options = get_option( 'rabe_option_name' );
	$not_in_header = ( isset( $rabe_options['not_in_header'] ) ) ? $rabe_options['not_in_header'] : (int) get_term_by( 'slug', 'not-in-header', 'category' )->term_id;

}
add_action( 'after_setup_theme', 'rabe_setup', 11 );


/**
 * Disables some omega customizer functions and set theme defaults
 * 
 * Taken out of omega/lib/extensions/custom-post.php and omega/lib/functions/settings.php
 * 
 * Actually this should work with filter omega_default_theme_settings, but somehow filter doesn't work
 * 
 * @package rabe
 * @since version 1.0.0
 * @param mixed $wp_customize All the customizer options
 */
function rabe_customize_register( $wp_customize ) {
	// Limit chaprs
	$wp_customize->remove_setting( 'excerpt_chars_limit' );
	$wp_customize->add_setting(
		'excerpt_chars_limit',
		array(
			'default'			=> '300',
			'type'				=> 'theme_mod',
			'capability'		=> 'edit_theme_options',
			'sanitize_callback'	=> 'sanitize_text_field',
		)
	);
	// More text
	$wp_customize->remove_setting( 'more_text' );
	$wp_customize->add_setting(
		'more_text',
		array(
			'default'			=> '>',
			'type'				=> 'theme_mod',
			'capability'		=> 'edit_theme_options',
			'sanitize_callback'	=> 'sanitize_text_field',
		)
	);
	// Post thumbnail image size
	$wp_customize->remove_setting( 'image_size' );
	$wp_customize->add_setting(
		'image_size',
		array(
			'default'			=> 'rabe_tile',
			'type'				=> 'theme_mod',
			'capability'		=> 'edit_theme_options',
			'sanitize_callback'	=> 'sanitize_text_field',
		)
	);
	// Single post navigation
	/* Add the 'single_nav' setting. */
	$wp_customize->remove_setting( 'single_nav' );
	$wp_customize->add_setting(
		'single_nav',
		array(
			'default'			=> 1,
			'type'				=> 'theme_mod',
			'capability'		=> 'edit_theme_options',
			'sanitize_callback'	=> 'sanitize_text_field',
		)
	);
	// Remove page comment setting, taken from omega/lib/extensions/custom-comment.php
	$wp_customize->remove_control( 'page_comment' );
}
add_action( 'customize_register', 'rabe_customize_register', 11 );


/**
 * Create extra roles for broadcasters, staff, graphics and webteam
 * 
 * @package rabe
 * @since version 1.0.0
 * @link https://codex.wordpress.org/Roles_and_Capabilities
 * @link https://codex.wordpress.org/Function_Reference/add_role
 */
function rabe_roles() {

	global $wp_roles;
	
	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	// Create new broadcaster role based on author role
	if ( ! get_role( 'rabe_broadcaster' ) ) {
		$author = $wp_roles->get_role( 'author' );
		$wp_roles->add_role( 'rabe_broadcaster', __( 'Broadcaster', 'rabe' ), $author->capabilities);
	}
	
	// Create new broadcaster_light role based on author role who can't publish posts
	if ( ! get_role( 'rabe_broadcaster_light' ) ) {
		$broadcaster = $wp_roles->get_role( 'broadcaster' );
		$wp_roles->add_role( 'rabe_broadcaster_light', __( 'Broadcaster Light', 'rabe' ), $broadcaster->capabilities);
		$broadcaster_light = get_role( 'rabe_broadcaster_light' );
		$broadcaster_light->remove_cap( 'publish_posts' );
	}

	// Create new staff role based on editor role
	if ( ! get_role( 'rabe_staff' ) ) {
		$editor = $wp_roles->get_role( 'editor' );
		$wp_roles->add_role( 'rabe_staff', __( 'Staff', 'rabe' ), $editor->capabilities);
	}

	// Create new webteam role based on editor role for updating wordpress aswell
	if ( ! get_role( 'rabe_webteam' ) ) {
		$editor = $wp_roles->get_role( 'editor' );
		$wp_roles->add_role( 'rabe_webteam', __( 'Webteam', 'rabe' ), $editor->capabilities);
		$webteam = get_role( 'rabe_webteam' );
		$webteam->add_cap( 'delete_users' ); 
		$webteam->add_cap( 'edit_users' ); 
		$webteam->add_cap( 'promote_users' ); 
		$webteam->add_cap( 'add_users' ); 
		$webteam->add_cap( 'list_users' ); 
		$webteam->add_cap( 'create_users' ); 
		$webteam->add_cap( 'remove_users' ); 
		$webteam->add_cap( 'update_plugins' ); 
		$webteam->add_cap( 'edit_theme_options' ); 
		$webteam->add_cap( 'manage_options' );
		$webteam->add_cap( 'export' );
		$webteam->add_cap( 'import' );
	}
}
add_action( 'after_switch_theme', 'rabe_roles', 11 );

/**
 * Create player and playlist page after switchting theme
 * 
 * @package rabe
 * @since version 1.0.0
 */
function create_rabe_pages() {
	// Player page
	$player_page_title	  = 'Player';
	$player_page_template = 'page-player.php';
	$player_page_check	  = get_page_by_title( $player_page_title );
	$player_page		  = array(
		'post_type'    => 'page',
		'post_title'   => $player_page_title,
		'post_content' => '',
		'post_status'  => 'publish',
		'post_author'  => 1,
	);
	
	if( ! isset( $player_page_check->ID ) ) {
		$player_page_id = wp_insert_post( $player_page );
		if ( ! empty( $player_page_template ) ) {
			update_post_meta( $player_page_id, '_wp_page_template', $player_page_template );
		}
	}
	// Playlist page
	$playlist_page_title	= 'Playlist';
	$playlist_page_template = 'page.php';
	$playlist_page_check	= get_page_by_title( $playlist_page_title );
	$playlist_page			= array(
		'post_type'    => 'page',
		'post_title'   => $playlist_page_title,
		'post_content' => '',
		'post_status'  => 'publish',
		'post_author'  => 1,
	);
	
	if( ! isset( $playlist_page_check->ID ) ) {
		$playlist_page_id = wp_insert_post( $playlist_page );
		if ( ! empty( $playlist_page_template ) ) {
			update_post_meta( $playlist_page_id, '_wp_page_template', $playlist_page_template );
		}
	}

	// Playlist page
	$mailcontact_page_title	= 'Mail Contact';
	$mailcontact_page_template = 'page-mailcontact.php';
	$mailcontact_page_check	= get_page_by_title( $mailcontact_page_title );
	$mailcontact_page			= array(
		'post_type'    => 'page',
		'post_title'   => $mailcontact_page_title,
		'post_content' => '',
		'post_status'  => 'publish',
		'post_author'  => 1,
	);
	
	if( ! isset( $mailcontact_page_check->ID ) ) {
		$mailcontact_page_id = wp_insert_post( $mailcontact_page );
		if ( ! empty( $mailcontact_page_template ) ) {
			update_post_meta( $mailcontact_page_id, '_wp_page_template', $mailcontact_page_template );
		}
	}
	
	// Payment page
	$payment_page_title	= 'Payment';
	$payment_page_template = 'page-payment.php';
	$payment_page_check	= get_page_by_title( $payment_page_title );
	$payment_page			= array(
		'post_type'    => 'page',
		'post_title'   => $payment_page_title,
		'post_content' => '',
		'post_status'  => 'publish',
		'post_author'  => 1,
	);
	
	if( ! isset( $payment_page_check->ID ) ) {
		$payment_page_id = wp_insert_post( $payment_page );
		if ( ! empty( $payment_page_template ) ) {
			update_post_meta( $payment_page_id, '_wp_page_template', $payment_page_template );
		}
	}
}
add_action( 'after_setup_theme', 'create_rabe_pages' ); 


/**
 * Create needed categories after switchting theme
 * 
 * @package rabe
 * @since version 1.0.0
 */
function create_rabe_categories() {
	wp_insert_term(
		__( 'Not in header', 'rabe' ),
		'category',
		array(
			'description' => __( 'Don\'t show posts of this category in header', 'rabe' ),
			'slug'		  => 'not-in-header'
		)
	);
}
add_action( 'after_setup_theme', 'create_rabe_categories' );


/**
 * Don't display certain categories in posts
 * 
 * @package rabe
 * @since version 1.0.0
 */

function rabe_exclude_terms( $terms ) {
	global $not_in_header;
	$unwanted = array( $not_in_header );

	foreach( $terms as $k => $term ) {
		if( in_array( $term->term_id, $unwanted, true ) ) {
			unset( $terms[$k] );
		}
	}
	return $terms;
}
add_filter( 'get_the_terms', 'rabe_exclude_terms', 10, 1 );



/**
 * Manual calculation of found_posts because of custom offset in home.php
 * 
 * @package rabe
 * @since version 1.0.0
 * @param int $found_posts
 */
function rabe_adjust_offset_pagination( $found_posts, $query ) {

	// Calculate offset
	$rabe_options = get_option( 'rabe_option_name' );
	$offset = $rabe_options['specialposts_per_page'] + 2; // We just take "+2" because of two header posts (info-post and latest sticky post)
	
	//Ensure we're modifying the right query object...
	if ( $query->is_home() ) {
		//Reduce WordPress's found_posts count by the offset... 
		return $found_posts - $offset;
	}
	return $found_posts;
}
add_filter( 'found_posts', 'rabe_adjust_offset_pagination', 11, 2 );



/**
 * New user registrations should have display_name set to 'firstname lastname' by default
 * 
 * @package rabe
 * @since version 1.0.0
 * @param int $user_id The user ID * @link https://codex.wordpress.org/Function_Reference/add_role
 */
function set_default_display_name( $user_id ) {
	$user = get_userdata( $user_id );
	$name = sprintf( '%s %s', $user->first_name, $user->last_name );
	$args = array(
		'ID' 		   => $user_id,
		'display_name' => $name
	);
	
	// Only udpate display_name when there is a first or last name
	if ( ! empty( $user->first_name ) || ! empty( $user->last_name ) ) {
		wp_update_user( $args );
	}
}
add_action( 'user_register', 'set_default_display_name' );

// Call set_default_display_name after switch theme
function change_default_display_name() {
	$users = get_users();
	foreach ( $users as $user ) {
		set_default_display_name( $user->ID );
	}
}
add_action( 'after_switch_theme', 'change_default_display_name', 12 );


/**
 * Delete cache of first page
 * 
 * Delete query transient on first page when new post is published
 * 
 * @package rabe
 * @since version 1.0.0
 */
function clear_cache_on_publish( $new_status, $old_status, $post ) {
    if ( 'publish' !== $new_status || 'publish' === $old_status ) {
        return;
	}

    if ( 'post' !== $post->post_type ) {
        return;
	}
	
	// see home.php
	delete_transient( 'home_query_first_page' );	
}
add_action( 'transition_post_status', 'clear_cache_on_publish', 10, 3 );


/**
 * Get header and before content parts
 * 
 * Before contents contains latest info post, latest sticky post and live player
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_before_content() {
	get_template_part( 'partials/content', 'before' );
}


/**
 * Get contact links and search glass
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_header_menu() {
	get_template_part( 'partials/header', 'menu' );
}


/**
 * Custom read more
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_excerpt_more( $more ) {
	return '<span class="more"><a class="more-link" href="'. get_permalink( get_the_ID() ) . '">' . get_theme_mod( 'more_text', '>' ) . '</a></span>';
}
remove_filter( 'excerpt_more', 'omega_excerpt_more' );
add_filter( 'excerpt_more', 'rabe_excerpt_more' );


/**
 * Custom more link text
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_more_link_text( $more_link_text ) {
	return '... ' . $more_link_text;
}
add_filter( 'get_the_content_more_link', 'rabe_more_link_text' );



/**
 * Create custom footer line
 * 
 * @package rabe
 * @since version 1.0.0
 * @param array $settings
 * @return string $footer_insert Returns modified footer
 */
function rabe_footer_insert( $settings ) {

	// Get impressum page
	$rabe_options = get_option( 'rabe_option_name' );
	$impressum = ( isset( $rabe_options['impressum_page'] ) ) ? $rabe_options['impressum_page'] : false;
	$print_impressum = ( $impressum ) ? '<a href="' . get_permalink( $impressum ) . '" rel="nofollow noindex">' . __( 'Impressum', 'rabe' ) . '</a> | ' : '';

	$footer_insert =  isset( $entry_footer_insert )
		. '<p class="copyright">' . __( 'Copyright', 'rabe' ) . ' &#169; ' . date('Y ') . __( 'by', 'rabe' )
		. ' <a href="' . get_site_url() . '" title="' . get_bloginfo( 'name' ) . '">' . get_bloginfo( 'name' ) . '</a>'
		. ' | Code by <a href="https://momou.ch" rel="nofollow noindex" targe="_blank">momou!</a></p>'
		. '<p class="login impressum">' . $print_impressum . '<a href="' . admin_url() . '">Login</a></p>';
	return $footer_insert;	
}


/**
 * Custom image sizes for theme
 * 
 * Always add images in double size as well for hdpi displays
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_add_image_sizes() {

	// Logo
	add_image_size( 'rabe_logo', 185, 400, false );

	// Post thumbnails tiles
	add_image_size( 'rabe_tile', 350, 9999, false );
	add_image_size( 'rabe_tile_x2', 700, 9999, false );
	
	// Post thumbnails for home/frontpage tiles - They have to be _exactly_ this size
	// 350px for width and 200px for height, needed for two-liner-title plus audio
	add_image_size( 'rabe_front_tile', 350, 200, true );
	add_image_size( 'rabe_front_tile_x2', 700, 400, true );

	// Full width in broadcast page view
	add_image_size( 'rabe_large', 750, 9999, false );
	add_image_size( 'rabe_large_x2', 1400, 9999, false );

	// Full width in singular page view
	add_image_size( 'rabe_full_width', 1150, 9999, false );

	// Background images
	add_image_size( 'rabe_background', 1200, 555, true );

}


/**
 * Allow user to choose from custom sizes
 * 
 * @package rabe
 * @since version 1.0.0
 * @param array $sizes Shown image sizes
 * @return array $sizes Modified image sizes
 */
function rabe_show_image_sizes( $sizes ) {
	$addsizes = array(
		'rabe_tile'		  => __( 'Frontpage tile', 'rabe' ),
		'rabe_large' 	  => __( 'Large', 'rabe' ),
		'rabe_full_width' => __( 'Full width', 'rabe' ),
	);
	$newsizes = array_merge($sizes, $addsizes);
	return $newsizes;
}


/**
 * Automatically add first image of post as thumbnail
 * 
 * @package rabe
 * @since version 1.0.0
 */
 function rabe_auto_thumbnail() {
	global $post;
	if ( isset( $post->ID ) && ! has_post_thumbnail( $post->ID ) ) {
		$attached_image = get_children( "post_parent=$post->ID&post_type=attachment&post_mime_type=image&numberposts=1" );
		if ( $attached_image ) {
			foreach ( $attached_image as $attachment_id => $attachment ) {
				set_post_thumbnail( $post->ID, $attachment_id );
			}
		}
	}
}
add_action( 'save_post', 'rabe_auto_thumbnail' );
add_action( 'draft_to_publish', 'rabe_auto_thumbnail' );
add_action( 'new_to_publish', 'rabe_auto_thumbnail' );
add_action( 'pending_to_publish', 'rabe_auto_thumbnail' );
add_action( 'future_to_publish', 'rabe_auto_thumbnail' );


/**
 * Alway open outbound links in a new tab/window
 * 
 * @package rabe
 * @since version 1.0.0
 * @link http://www.travelblogadvice.com/technical/open-external-links-new-tabs-windows-without-plugin-wordpress/
 * @param mixed $content Content of the a page/post
 */
function outbound_links( $content ) {
	$siteurl = site_url('/');
	$return = str_replace( 'href=\"http', 'target=\"_blank\" href=\"http', $content );
	$return = str_replace( 'target=\"_blank\" href=\"' . $siteurl, 'href=\"' . $siteurl, $return );
	$return = str_replace( 'target=\"_blank\" href=\"#', 'href=\"#', $return );
	$return = str_replace( ' target=\"_blank\">', '>', $return );
	return $return;
}
add_filter( 'content_save_pre', 'outbound_links' );


/**
 * Get fist attached audio file of a post
 * 
 * @package rabe
 * @since version 1.0.0
 * @param int $post_id ID of post
 * @return string $return wp_audio_shortcode with first audio of post
 */
function rabe_get_first_attached_audio( $post_id ) {
	$audio = get_attached_media( 'audio', $post_id );
	if ( $audio ) {
		$first_audio = array_values( $audio )[0]->guid;
		$attr = array( 'src' => $first_audio );
		$return = wp_audio_shortcode( $attr );
	} else {
		$return = '';
	}
	return $return;
}


/**
 * Get first embedded media file and but a container around it
 * 
 * @package rabe
 * @since version 1.0.0
 * @link http://wordpress.stackexchange.com/questions/175793/get-first-video-from-the-post-both-embed-and-video-shortcodes
 * @param int $post_id ID of post
 * @param string $embed_type Type of embedded source
 * @return string $return wp_audio_shortcode with first audio of post
 */
function rabe_get_first_embed( $post_id ) {

	// Get post content
	$content = do_shortcode( apply_filters( 'the_content', get_the_content() ) );

	// Get embedded media
	$embeds = get_media_embedded_in_content( $content );

	if ( ! empty( $embeds ) ) {
		
		// We need only the first element
		$embed = array_values( $embeds )[0];
		
		// Put a div around certain embeds as soundcloud, mixcloud, youtube
		if ( strpos( $embed, 'soundcloud' ) || strpos( $embed, 'mixcloud' ) || strpos( $embed, 'youtube' ) || strpos( $embed, 'vimeo') ) {
			// Put first found embed into container
			return '<div class="oembed-container">' . $embed . '</div>';
		} else {
			return $embed;
		}
	} else {
		// No audio embedded found
		return false;
	}
}

/**
 * Get first embedded audio file of a post
 * 
 * @package rabe
 * @since version 1.0.0
 * @link http://wordpress.stackexchange.com/questions/175793/get-first-video-from-the-post-both-embed-and-video-shortcodes
 * @param int $post_id ID of post
 * @param string $embed_type Type of embedded source
 * @return string $return wp_audio_shortcode with first audio of post
 */
function rabe_get_first_embed_audio( $post_id ) {

	// Get post content
	$content = do_shortcode( apply_filters( 'the_content', get_the_content() ) );

	// Get embedded media
	$embeds_audio = get_media_embedded_in_content( $content, array( 'audio' ) );

	// If its audio
	if( ! empty( $embeds_audio ) ) {
		// We need only the first element
		return array_values( $embeds_audio )[0];
	} else {
		// No audio embedded found
		return false;
	}
}


/**
 * Put oembeds into a container for responsive widths in styles.css
 * 
 * @package rabe
 * @since version 1.0.0
 * @link http://wordpress.stackexchange.com/questions/50779/how-to-wrap-oembed-embedded-video-in-div-tags-inside-the-content
 * @param string $html HTML of embed
 * @param string $url
 * @param string $attr
 * @param string $post_id
 * @return string $return wp_audio_shortcode with first audio of post
 */
function rabe_embed_oembed_html( $html, $url, $attr, $post_id ) {
	return '<div class="oembed-container">' . $html . '</div>';
}
add_filter( 'embed_oembed_html', 'rabe_embed_oembed_html', 99, 4 );


/**
 * Set default "link to" in media page to media-file and remove "attachment page" in "link to dropdown"
 * 
 * @package rabe
 * @since version 1.0.0
 * @link http://wordpress.stackexchange.com/questions/173022/insert-media-attachment-link-to-remove-the-attachment-page-option
 */
function rabe_default_media_insert() {
    echo '
        <style>       
            .post-php select.link-to option[value="post"],
            .post-php select[data-setting="link"] option[value="post"],
            .post-php select[data-setting="columns"] option[value="5"],
            .post-php select[data-setting="columns"] option[value="6"],
            .post-php select[data-setting="columns"] option[value="7"],
            .post-php select[data-setting="columns"] option[value="8"],
            .post-php select[data-setting="columns"] option[value="9"] {
				display: none;
			}
        </style>';
}
add_action( 'print_media_templates', 'rabe_default_media_insert' );


/**
 * Set defaults for galleries
 * 
 * Set gallery columns to 3 and "link to" in gallery to file
 * 
 * @package rabe
 * @since version 1.0.0
 * @link http://wordpress.stackexchange.com/questions/173022/insert-media-attachment-link-to-remove-the-attachment-page-option
 * @param array $settings Settings of media view (gallery)
 */
function rabe_gallery_defaults( $settings ) {
    $settings['galleryDefaults']['columns'] = 3;
    $settings['galleryDefaults']['link'] 	= 'file';
    $settings['galleryDefaults']['size'] 	= 'thumbnail';

    return $settings;
}
add_filter( 'media_view_settings', 'rabe_gallery_defaults');


/**
 * Title lenght checker
 * 
 * Displays a warning and a preview pane for titles longer then specified chars above the title in edit-post.php
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_title_length(){
	
	$title_field_events = '<div id=\"long-title\" style=\"z-index:10;border:2px solid red;background:white;width:250px;height:70px;position:absolute;top:-35px;right:0;color:black;line-height:1.1;text-transform:uppercase;word-wrap:break-word;font-weight:normal;font-size:30px; font-family:\'Fjalla One\'\"></div>';
	$title_field_posts  = '<div id=\"long-title\" style=\"z-index:10;border:2px solid red;background:white;width:350px;height:102px;position:absolute;top:-35px;right:0;color:black;line-height:1.1;text-transform:uppercase;word-wrap:break-word;font-weight:normal;font-size:30px; font-family:\'Fjalla One\'\"></div>';
	
	// Title lenght for events (25) and for posts (55)
	$title_length = ( 'event' === get_post_type() ) ? 25 : 55;
	$title_field = ( 'event' === get_post_type() ) ? $title_field_events : $title_field_posts;
	
	echo '
		<script>
		jQuery(document).ready(function(){
			jQuery("#titlediv .inside").after("<div id=\"title-too-long\" style=\"position:absolute;top:-55px;right:0;color:red;font-weight:bold;\">' . __( 'Title probably too long! Title preview:', 'rabe' ) . '</div> ' . $title_field . '");
			jQuery("#title-too-long").hide();
			jQuery("#long-title").hide();
			jQuery("#title").keyup( function() {
				if(jQuery(this).val().length > ' . $title_length . '){
					jQuery("#long-title").show();
					jQuery("#title-too-long").show();
					jQuery("#long-title").text(jQuery(this).val());
				} else {
					jQuery("#long-title").hide();
					jQuery("#title-too-long").hide();
				}
			});
		});
		</script>';
		
	// Enqueue styles
	rabe_admin_css();
}
add_action( 'admin_head-post.php', 'rabe_title_length' );
add_action( 'admin_head-post-new.php', 'rabe_title_length' );

/**
 * Fonts for title lenght checker
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_admin_css() {
	wp_register_style( 'fonts-admin', get_stylesheet_directory_uri() . '/css/font-faces.css', false, '1.0.0' );
	wp_enqueue_style( 'fonts-admin' );
}
add_action( 'admin_enqueue_scripts', 'rabe_admin_css' );

/**
 * Load scripts and styles
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_load_scripts_styles() {
	// Internet Explorer compatibility
	if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && ( preg_match( '/(?i)msie [5-8]/', $_SERVER['HTTP_USER_AGENT'] ) ) ) {
		wp_enqueue_style( 'iecompat', get_stylesheet_directory_uri().'/css/iecompat.css' );
		wp_enqueue_script( 'respond', get_stylesheet_directory_uri().'/js/respond.js', false, '1.4.2', true );
		wp_enqueue_script( 'selectivizr', get_stylesheet_directory_uri() . '/js/selectivizr.js', false, '1.0.2', true );
	}
	
	// Sticky menu
	wp_enqueue_script( 'stickymenu', get_stylesheet_directory_uri() . '/js/stickymenu.js', array( 'jquery' ), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'rabe_load_scripts_styles', 11 );


/**
 * Allow more HTML tags in omegas get_the_content_limit
 * 
 * @see omega/lib/functions/extras.php
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_content_limit_allowedtags() {
	
    // Add custom tags to this string
	return '<em>,<i>,<ul>,<ol>,<li>,<a>,<p>,<br>,<address>,<del>,<pre>,<blockquote>,<h3>,<h4>,<h5>';
	
}
add_filter( 'get_the_content_limit_allowedtags', 'rabe_content_limit_allowedtags' );

/**
 * Remove iframes from content
 * 
 * @package rabe
 * @since version 1.0.0
 * @param string $content Actual html and text of post content
 * @return string $content 
 */
function remove_iframes( $content ) {
    $content =  preg_replace( '@<iframe[^>]*?>.*?</iframe>@siu', '', $content );
    return $content;
}


/**
 * Remove http string url from content
 * 
 * @package rabe
 * @since version 1.0.0
 * @link http://stackoverflow.com/questions/4875085/php-remove-http-from-link-title
 * @param string $content Actual html and text of post content
 * @return string $content 
 */
function remove_embed_httpstring( $content ){
	$content = preg_replace( '/(?<!href=["\'])https?:\/\/.*+/', '', trim( $content ) );
	return $content;
}

/**
 * Allow SVG-File upload to mediathek
 * 
 * @package rabe
 * @since version 1.0.0
 * @param array $mimes Allowed mime formats
 * @return array $mimes Modified mime formats
 */
function allow_svg_upload( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter( 'upload_mimes', 'allow_svg_upload' );


/**
 * Prints short name of website
 * 
 * @package rabe
 * @since version 1.0.0
 * @return string $title Short name of website
 */
function rabe_shortname() {

	$title = '';
	$rabe_options = get_option( 'rabe_option_name' );
	$short_blogname = ( isset( $rabe_options['short_blogname'] ) ) ? $rabe_options['short_blogname'] : '';

	if ( isset( $short_blogname ) ) {
		$title = sprintf( 
			'<div class="wrap"><div class="menu-site-title wrap" itemprop="headline"><a href="%1$s" title="%2$s" rel="home">%3$s</a></div></div>',
			home_url(),
			esc_attr( get_bloginfo( 'name' ) ),
			$short_blogname
		);		
	}
	echo $title;
}

/***********************************************************************
 * RaBe specific functions
 **********************************************************************/
/**
 * Add custom theme logo to Wordpress-Login-Area
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_login_logo() { ?>
    <style type="text/css">
        #login h1 a,
        .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/rabe_login.png);
            padding-bottom: 30px;
			width: 150px;
			background-size: 150px;
			height: 131px;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'rabe_login_logo' );

?>
