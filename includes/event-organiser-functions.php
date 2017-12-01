<?php
/**
 * Functions and definitions for event organiser plugin
 * 
 * @package rabe
 */


if ( is_admin() ) {
	/**
	 * Replace meta boxes in event editor
	 * 
	 * @package rabe
	 * @since version 1.0.0
	 */
	function rabe_remove_eo_metaboxes() {
		// Only for non editors
		if( ! current_user_can( 'edit_pages' ) ) {
			// No specialposts
			remove_meta_box('specialpost', 'event', 'side');
			remove_meta_box('postexcerpt', 'event', 'normal');
			remove_meta_box('postcustom', 'event', 'normal');
		}
		// We don't use categories
		remove_meta_box('event-categorydiv', 'event', 'side');
	}
	add_action( 'admin_menu' , 'rabe_remove_eo_metaboxes' );


	/**
	 * Replace category column in events
	 * 
	 * @package rabe
	 * @since version 1.0.0
	 */
	function rabe_event_columns( $columns ) {
		if( ! current_user_can( 'edit_pages' ) ) {
			unset( $columns['eventcategories'] );
			return $columns;
		} else {
			return $columns;
		}
	}
	add_filter( 'manage_posts_columns' , 'rabe_event_columns' );


	/**
	 * Notify someone upon a pending event
	 * 
	 * An email is sent to a specified mail address (@see settings.php) when an event is saved for review.
	 * 
	 * @package rabe
	 * @since version 1.0.0
	 * @param string $new_status New status of post
	 * @param string $old_statu Old status of post
	 * @param mixed $post Full post
	 */
	function pending_event( $new_status, $old_status, $post ) {

		if ( 'pending' === $new_status && 'pending' !== $old_status && 'event' === get_post_type( $post->ID ) ) {

			// Get notification mail for pending event
			$rabe_options = get_option( 'rabe_option_name' );
			$event_notification_mail = ( isset( $rabe_options['pending_event_notification_email'] ) ) ? $rabe_options['pending_event_notification_email'] : '' ;
			
			// No mail specified?
			if ( ! $event_notificatoin_mail ) return;

			$post_edit_link = get_edit_post_link( $post->ID );
			
			// Set up mail
			// FIXME: Make text configurable in backend
			$host = $_SERVER['HTTP_HOST'];
			$subject = $host . ": Neuer Termin hängig";

			$message = "Hallo!
			
Es wurde ein neuer Termin eingetragen. Bitte kontrollieren, anpassen und allenfalls freischalten!
			
Siehe: $post_edit_link
			
Danke!";

			$headers = array(
				'Reply-To' => get_option( 'admin_email' ),
			);

			wp_mail( $event_notification_mail, $subject, $message, $headers );

		}
	}
	add_action( 'transition_post_status', 'pending_event', 10, 3 );
}

/**
 * Replace event permalink
 * 
 * Replaces broadcast page link with actual event page permalink when rabe_broadcast_link is set
 * 
 * @package rabe
 * @since version 1.0.0
 * @uses get_broadcast(), get_broadcast_link()
 * @param string $link URL of permalink
 */ 
function rabe_broadcast_link( $link ){
   	$broadcast = get_broadcast();
    if( $broadcast && 'event' === get_post_type() && get_post_meta( get_the_ID(), 'rabe_broadcast_link', true ) ) {
		$link = get_broadcast_link( $broadcast );
		return $link;
    }
    return $link;
}
add_filter( 'eventorganiser_calendar_event_link', 'rabe_broadcast_link', 10, 1 );
add_filter( 'eventorganiser_get_permalink', 'rabe_broadcast_link', 10, 1 );
add_filter( 'the_permalink', 'rabe_broadcast_link' );


/**
 * Add specialpost class to a specialpost
 * 
 * @package rabe
 * @since version 1.0.0
 * @param mixed $event Full event post
 * @param int $event_id Event ID (post ID)
 * @param int $occurrence_id Occurrence ID of event
 */
function specialpost_event_class( $event, $event_id, $occurrence_id ) {

	$specialpost = ( 1 === (int) get_post_meta( $event_id, 'rabe_specialpost', true ) ) ? true : false;

	if ( $specialpost ) {
		$event['className'][] = 'specialpost';
	}

	return $event;
};
add_filter( 'eventorganiser_fullcalendar_event', 'specialpost_event_class', 10, 3 );


/**
 * Change tooltip content in fullcalendar
 * 
 * @package rabe
 * @since version 1.0.0
 * @uses get_broadcast()
 * @param string $description
 * @param int $event_id Event ID (post ID)
 * @param int $occurrence_id Occurrence ID of event
 * @param mixed $event Full event post
 */
function rabe_fullcalendar_tooltip( $description, $event_id, $occurrence_id, $post ){

	// Get broadcast time
	$broadcast_time = eo_get_the_start( 'l, j. F', $event_id, null, $occurrence_id ) . ', '
				. eo_get_the_start( get_option( 'time_format' ), $event_id, null, $occurrence_id ) . ' - '
				. eo_get_the_end( get_option( 'time_format' ), $event_id, null, $occurrence_id );
	
	// Link to broadcast page? Print broadcast description
	$broadcast_link = (int) get_post_meta( $event_id, 'rabe_broadcast_link', true );

	if ( 1 === $broadcast_link ) {

		$broadcast_id = get_broadcast( $post );
		
		$rabe_options = get_option( 'rabe_option_name' );
		$general_broadcast = $rabe_options['general_broadcast'];
		
		// Take custom description only for custom broadcasts
		if ( $broadcast_id != $general_broadcast ) {
			$tooltip_desc = term_description( $broadcast_id );
		}

	// Else? Print excerpt of event
	} else {
		$tooltip_desc = get_the_excerpt( $post );
	}
	
	$tooltip_desc = '<br><br>' . wp_trim_words( $tooltip_desc, 100, '&hellip;' );
	$tooltip = '<strong>' . $broadcast_time . '</strong>' . $tooltip_desc;

	return $tooltip;

};
add_filter( 'eventorganiser_event_tooltip', 'rabe_fullcalendar_tooltip', 10, 4 );


?>
