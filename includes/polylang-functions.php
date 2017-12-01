<?php
/**
 * Functions and definitions for polylang plugin
 * 
 * @package rabe
 * @since version 1.0.0
 */


/**
 * Disables polylang translations for posts
 * 
 * Translations are only valid for pages
 * 
 * @package rabe
 * @since version 1.0.0
 */
// add_filter( 'pll_get_post_types', 'ngcu_pll_get_post_types' );
function ngcu_pll_get_post_types( $types ) {
	unset ( $types['post'] );
	return $types;
}


/**
 * Prints language chooser
 * 
 * @package rabe
 * @since version 1.0.0
 */
function print_rabe_polylang_chooser() {

	$args = array(
		'dropdown' 				 => 0,
		'show_names' 			 => 0,
		'show_flags'			 => 0,
		'echo'					 => 0,
		'hide_if_no_translation' => 1,
		'hide_current'			 => 1,
		'display_names_as'		 => 'slug',
	);

	// FIXME polylang chooser
	// echo '<ul class="languages">' . pll_the_languages( $args ) . '</ul>';

}


/**
 * Rewrite
 * http://domain.tld/de/broadcast/broadcast-slug or
 * http://domain.tld/fr/broadcast/broadcast-slug or
 * http://domain.tld/en/broadcast/broadcast-slug to
 * http://domain.tld/broadcast-slug
 * 
 * @package rabe
 * @since version 1.0.0
 */
function broadcast_rewrite_polylang() {
	$broadcast_terms = get_terms( array( 'taxonomy' => 'broadcast', 'hide_empty' => false ) );
	foreach ( $broadcast_terms as $broadcast ) {
		// Add two letters format for polylang
		add_rewrite_rule( '[a-zA-Z]{2}/' . $broadcast->slug . '([^/].+)?', 'index.php?broadcast=' . $broadcast->slug . '&$matches[1]', 'top');
		add_rewrite_rule( '[a-zA-Z]{2}/' . $broadcast->slug . '/page/?([0-9]{1,})/?$', 'index.php?broadcast=' . $broadcast->slug . '&paged=$matches[1]', 'top' );

	}
}
add_action('init', 'broadcast_rewrite_polylang', 13 );
?>
