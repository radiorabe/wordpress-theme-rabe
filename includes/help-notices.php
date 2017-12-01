<?php
/**
 * Print help notices
 * 
 * Display help notices according context (post, event or broadcast term).
 * Enable link to disable help notices
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_help_notices() {
	
	// Get rabe_help_notices
	$user_settings = get_user_meta( get_current_user_id(), 'rabe_user_settings', true );
	$show_help_notices = ( isset( $user_settings['show_help_notices'] ) ) ? $user_settings['show_help_notices'] : 1;
	
	if ( 0 === $show_help_notices ) {
		return;
	}

	$screen = get_current_screen();	
	
	// Show notices only on braodcast term, events and posts
	if ( $screen->id !== 'edit-broadcast' && $screen->id !== 'event' && $screen->id !== 'post' ) {
		return;
	}

    // Show notices only on 1. new posts (and events)  // 2. edit term
    if ( $screen->base != 'post' && $screen->base != 'term') {
		return;
	}
	
	// Now we can start with the help notices, first get and set the help notices
	$rabe_options = get_option( 'rabe_option_name' ); 
	$post_help_notice = ( isset ( $rabe_options['post_help_notice'] ) ) ? $rabe_options['post_help_notice'] : __( 'Configure a help notice for posts.', 'rabe' ) ;
	$event_help_notice = ( isset ( $rabe_options['event_help_notice'] ) ) ? $rabe_options['event_help_notice'] : __( 'Configure a help notice for events.', 'rabe' ) ;
	$broadcast_help_notice = ( isset ( $rabe_options['broadcast_help_notice'] ) ) ? $rabe_options['broadcast_help_notice'] : __( 'Configure a help notice for broadcasts.', 'rabe' ) ;
	
	$message = array(
		'event' => $event_help_notice,
		'post' => $post_help_notice,
		'broadcast' => $broadcast_help_notice
	);
	
	$class = 'notice notice-success is-dismissible';

	// Set correct help notice
	$post_type = get_post_type();
	
	if ( $post_type === 'post' ) {
		$message = $message['post'];
	} elseif ( $post_type === 'event' ) {
		$message = $message['event'];
	} else {
		$message = $message['broadcast'];
	}

	// Build URL for hiding help notices
	$url = get_admin_url();
	$url = add_query_arg( array( 'no_help'=> 1 ), 'profile.php'	);
	$url .= '#rabe-user-settings';
          
	// Add Link to remove help notices in user profile
	$remove_me = '<br /><a href="' . $url . '">' . __( 'Remove help notices', 'rabe' ) . '</a>';
	$message .= $remove_me;

	// Print help notice
	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
}
add_action( 'admin_notices', 'rabe_help_notices' );

?>
