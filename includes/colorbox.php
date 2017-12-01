<?php
/**
 * Definitions for simple colorbox plugin
 * 
 * @package rabe
 * @since version 1.0.0
 */

// Choose colorobox theme
function rabe_colorbox_theme() {
	return 2;	
}
add_filter('simple_colorbox_theme', 'rabe_colorbox_theme');


// Adjust some colorbox settings
function rabe_colorbox_settings() {
	$colorbox_selector = "a[href$=\'jpg\'],a[href$=\'jpeg\'],a[href$=\'png\'],a[href$=\'gif\'],a[href$=\'JPG\'],a[href$=\'JPEG\'],a[href$=\'PNG\'],a[href$=\'GIF\']"; 
	$colorbox_settings = array( 
		'rel'            => 'group', 
		'maxWidth'       => "92%", 
		'maxHeight'      => "92%", 
		'opacity'        => "0.7", 
		'current'        => sprintf( __( 'image %1$s of %2$s', 'rabe' ), '{current}', '{total}' ), // Text or HTML for the group counter while viewing a group. {current} and {total} are detected and replaced with actual numbers while Colorbox runs.
		'previous'       => __( 'previous', 'rabe' ), // Text or HTML for the previous button while viewing a group.
		'next'           => __( 'next', 'rabe' ), // Text or HTML for the next button while viewing a group.
		'close'          => __( 'close', 'rabe' ), // Text or HTML for the close button. The 'esc' key will also close Colorbox.
		'xhrError'       => __( 'This content failed to load.', 'rabe' ), // Error message given when ajax content for a given URL cannot be loaded.
		'imgError'       => __( 'This image failed to load.', 'rabe' ), // Error message given when a link to an image fails to load.
		'slideshowStart' => __( 'start slideshow', 'rabe' ), // Text for the slideshow start button.
		'slideshowStop'  => __( 'stop slideshow', 'rabe' ), // Text for the slideshow stop button
	);
	$colorbox_settings['l10n_print_after'] = ' 
		jQuery(function($){ 
			// Examples of how to assign the ColorBox event to elements 
			$("' . apply_filters( 'simple_colorbox_selector', $colorbox_selector ) . '").colorbox(colorboxSettings); 
		});'; 
	return $colorbox_settings;
}
add_filter('simple_colorbox_settings', 'rabe_colorbox_settings');
?>
