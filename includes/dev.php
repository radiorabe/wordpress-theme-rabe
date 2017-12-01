<?php
/**
 * development functions and definitions
 *
 * @package rabe
 */
 
// Enable displaying errors
ini_set( 'display_errors', 'On' );

// Set global develop variable
global $develop;
$develop = true;

/**
 * Print error reporting on top of front
 */
function print_error_reporting() {
	if ( ! is_admin() ) {
		// Enable error reporting
		error_reporting(E_ALL | E_STRICT);
		echo '<div style="position:absolute;top:0;leftl:0">ERROR REPORTING ON</div>';
	}
}
add_action( 'omega_before_header', 'print_error_reporting', 1 );

function debug_to_console( $data ) {
    $output = $data;
    if ( is_array( $output ) )
        $output = implode( ',', $output);

    echo "<script>console.log( 'DEBUG: " . $output . "' );</script>";
}

/**
 * Debug in admin_head
 */
add_action('admin_head', 'print_debug'); 
function print_debug() {
	
	global $pagenow;
	
	// media upload not working with error reporting
	if ( $pagenow != 'upload.php' ) {
		
		// Enable error reporting
		error_reporting(E_ALL | E_STRICT);
	
		// Dump variables
		echo '<div style="margin-left:180px;">DEV ERROR REPORTING ON';
		$role_restrictions = get_option( 'RestrictTaxs_options' );
		$all_restrictions = get_option( 'RestrictTaxs_user_options' );
		echo '</div>';

		// pre formatting start
		echo '<pre style="margin-left:180px;">';
		global $post_type;
		$screen = get_current_screen();
		echo 'SCREEN ID: ' . $screen->id;
		echo '<br />SCREEN BASE: ' . $screen->base;
		// echo 'SCREEN: ' . var_dump($screen);
		
		// pre formatting end
		echo '</pre>';
	}
}


/**
 * Change oembed arguments
 */
function rabe_oembed_args( $provider, $url, $args ) {

    if ( strpos( $provider, 'soundcloud' ) !== false 
		|| strpos( $provider, 'vimeo' ) !== false
		|| strpos( $provider, 'mixcloud' ) !== false ) {
		
		$args = array(
			'color' => '00e1d4',
			);
        $provider = add_query_arg( $args, $provider );
        
    
    }

	return $provider;
}
add_filter('oembed_fetch_url','rabe_oembed_args', 10, 3);


/**
 *  Rewrite check
 */
function dev_call_add_rewrite_page() {
	add_menu_page('Rewrite Page', 'Rewrite Page', 'manage_options', __FILE__, 'dev_add_rewrite_page', 'dashicons-edit');
}
add_action('admin_menu', 'dev_call_add_rewrite_page');

function dev_add_rewrite_page() {
	?>
	<div class="wrap">
		<h2>Rewrite Page</h2>
		<?php
		global $wp_rewrite;
		echo '<pre>'; var_dump($wp_rewrite);echo '</pre>';
		?>
	</div>
	<?php
}



/*********************************************
 * 
 * Collection of unused but usefull functions
 *
 *********************************************/
 
/*
 * Add a custom background if there is an image specified
 */
function broadcast_background_css() {

	// Get term_id
	$term_id = get_queried_object()->term_id;

	if ( function_exists( 'get_term_meta' ) ) {
		$broadcast_image = get_term_meta( $term_id, 'broadcast_image' );
	} else  {
		$broadcast_image = get_tax_meta( $term_id, 'broadcast_image' );
	}

	if ( $broadcast_image ) {
		remove_theme_support('custom-background');
		$broadcast_image = $broadcast_image['url'];
	}

    $styles = array();

    $css['body']['background']			  = 'b8ddc2';
    $css['body']['background-image']	  = 'url("' . $broadcast_image . '")';
    $css['body']['background-repeat']	  = 'no-repeat';
    $css['body']['background-position']	  = 'top center';
    $css['body']['background-attachment'] = 'scroll';

    $final_css = '';
    foreach ( $css as $style => $style_array ) {
        $final_css .= $style . '{';
        foreach ( $style_array as $property => $value ) {
            $final_css .= $property . ':' . $value . ';';
        }
        $final_css .= '}';
    }

    echo '<style>' . $final_css. '</style>';
}
// add_action( 'wp_head', 'broadcast_background_css', 11 );


/*
 * Set default theme settings
 * DOESN'T WORK SOMEHOW!
 */
function rabe_set_default_theme_settings( $settings ) {
}


?>
