<?php
/**
 * Prepares the programm schedule and displays player with ticker
 * 
 * Depending on a real broadcast running or the fallback broadcast 
 * running, a different title is displayed in the player.
 *
 * @package rabe
 * @since version 1.0.0
 * @uses get_broadcast() Gets broadcast id
 * 		 get_rabe_songticker() Gets ticker with song
 * 		 get_broadcast_link() Gets link of broadcast
 * 		 rabe_player_links() Displays links from the player
 * 		 get_rabe_current_event() Gets currently running event
 * @return string $ticker HTML of Broadcast and link
 */
function rabe_schedule() {

	if ( function_exists('eventorganiser_load_textdomain') ) {

		$running_event = get_rabe_current_event();

		// Get fallback broadcast id
		$rabe_options = get_option( 'rabe_option_name' );
		$fallback_broadcast = ( isset( $rabe_options['fallback_broadcast'] ) ) ? (int) $rabe_options['fallback_broadcast'] : false;
		
		if ( $running_event && $fallback_broadcast ) {

			if ( get_broadcast( $running_event ) === $fallback_broadcast ) {
			
				// Songticker if fallback broadcast is running
				$ticker = get_rabe_songticker( $fallback_broadcast );
				
			} else {
				
				// Ok, we're on air. Save broadcast to transient and  get infos about running broadcast
				set_transient( 'rabe_event_schedule_save', $running_event );
				
				// Check if all day, set format accordingly
				$format = ( eo_is_all_day( $running_event ) ? get_option( 'date_format' ) : get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
				
				// Is event/broadcast linked to event page or broadcast page?
				$broadcast_link = (int) get_post_meta( $running_event, 'rabe_broadcast_link', true );
				$ticker_url = ( 1 === $broadcast_link ) ? get_broadcast_link( get_broadcast( $running_event ) ) : eo_get_permalink( $running_event, false );
				
				// Ticker of running broadcast
				$ticker = '<div class="broadcast-show"><a href="' . $ticker_url . '">' . get_the_title( $running_event ) . '</a></div>
					<div class="live">' . __( 'Live!', 'rabe' ) . '</div>
					' . get_rabe_player_links();

			}

		} else {

			$ticker = get_rabe_songticker( $fallback_broadcast );
	
		}	
		
		echo $ticker;
	}
}

/**
 * Gets currently running event
 * 
 * There should be only be one event
 * 
 * @package rabe
 * @since version 1.1.0
 * @return mixed $running_event Integer or false of currently running event
 */
function get_rabe_current_event() {
	
	$running_event = false;
	
	// Get only currently running events
	$running_events = eo_get_events(
		array(
			'numberposts'			=> 1,
			'event_start_before'	=> 'now',
			'event_end_after'		=> 'now'
		)
	);
	
	if ( $running_events ) {
		
		// Get event ID
		$running_event = (int) $running_events[0]->ID;
		
		// Calculate how long to save schedule in transient
		$now = time();
		$end = eo_get_the_end( 'U', $running_event, $running_events[0]->occurrence_id );
		$seconds = $end - $now;

		// Save running event id to transient
		set_transient( 'rabe_event_schedule_running', $running_event, $seconds );

	}
	
	return $running_event;
}

/**
 * Gets ticker with currently played song
 * 
 * Checks for broadcast, else don't link stuff
 * 
 * @package rabe
 * @since version 1.1.0
 * @uses get_broadcast_link() Gets link of broadcast
 *		 get_broadcast_name() Gets name of broadcast
 *		 get_rabe_songticker_html() Gets ticker with song information
 * 		 get_rabe_player_links() Gets links from the player
 * @param int $fallback_broadcast Broadcast term ID of fallback broadcast
 * @return $string HTML code of songticker
 */
function get_rabe_songticker( $fallback_broadcast = '' ) {

	if ( $fallback_broadcast ) {

		$songticker = sprintf( 
			'<div class="broadcast-show ticker"><a href="%1s">%2s</a></div>%3s%4s',
			get_broadcast_link( $fallback_broadcast ),
			get_broadcast_name( $fallback_broadcast ),
			get_rabe_songticker_song_html(),
			get_rabe_player_links()
		);
		
	} else {
		
		$songticker = sprintf( '<small>%1s <a href="%2s">%3s</a></small>',
			__( 'No fallback broadcast set! Check', 'rabe' ),
			admin_url( 'admin.php?page=rabe_settings' ),
			__( 'Settings', 'rabe' )
		);
	}

	return $songticker;
}

/**
 * Prints songticker
 * 
 * Needs to have div or span tags with title- and artist class
 * 
 * @package rabe
 * @since version 1.0.0
 */
function rabe_songticker( $fallback_broadcast = '' ) {
	echo get_rabe_songticker( $fallback_broadcast );
}


/**
 * Get songticker song info and save it to transient
 * 
 * Only parse songticker xml and save song title and artist to transient 
 * when there was no change
 * 
 * @package rabe
 * @since version 1.1.0
 * @return array $song Array of artist and song title
 */
function get_rabe_songticker_song() {
	
	// Get songticker url
	$rabe_options = get_option( 'rabe_option_name' );
	$response = ( isset( $rabe_options['rabe_songticker_url'] ) ) ? wp_remote_get( $rabe_options['rabe_songticker_url'] ): wp_remote_get( get_stylesheet_directory_uri() . '/includes/songticker.php' );
	$response_code = wp_remote_retrieve_response_code( $response );

	if ( $response_code == 200 ) {
		
		$xml = simplexml_load_string( wp_remote_retrieve_body( $response ) ) or die( 'Error: No valid songticker source.' );
		$title = $xml->track->title->__toString();
		$artist = $xml->track->artist->__toString();
		$song = array( $title, $artist );

		$rabe_options = get_option( 'rabe_option_name' );
		$seconds = ( isset( $rabe_options['rabe_songticker_interval'] ) ) ? (int) $rabe_options['rabe_songticker_interval'] : 10; //  Default caching of song: 10 seconds

		set_transient( 'rabe_songticker_song', $song, $seconds );
		if ( false === get_transient( 'rabe_songticker_song_save' ) ) {
			
			set_transient( 'rabe_songticker_song_save', $song );
			
		}


	} else {
		
		$song = get_transient( 'rabe_songticker_song_save' );
		
	}
	
	return $song;
}


/**
 * Has song in ticker changed?
 * 
 * @package rabe
 * @since version 1.1.0
 * @return boolean $changed Boolean if song has changed
 */
function rabe_songticker_song_changed() {
	
	$changed = false;

	// Save song to transient rabe_songticker_song_save for later usage
	if ( get_transient( 'rabe_songticker_song' ) ) {
		
		set_transient( 'rabe_songticker_song_save', get_transient( 'rabe_songticker_song' ) );
		
	} else {
		
		$song = get_rabe_songticker_song();
		
		$old_song = get_transient( 'rabe_songticker_song_save' );
		
		if ( $song !== $old_song ) {
			
			delete_transient( 'rabe_songticker_song_save' );
			$changed = true;
			
		}
	}
	
	return $changed;
}


/**
 * Build songticker song html
 * 
 * @package rabe
 * @since version 1.1.0
 * @uses get_rabe_songticker_song() Gets currently played song
 * @return $string HTML code of songticker song info
 */
function get_rabe_songticker_song_html() {
	
	$song = ( get_transient( 'rabe_songticker_song' ) ) ? get_transient( 'rabe_songticker_song' ) : get_rabe_songticker_song();

	if ( $song ) {
		
		$song_html = sprintf( '<span class="title ticker">%1s</span> - <span class="artist ticker">%1s</span>',
			$song[0], // title
			$song[1] // artist
		);
		
	} else {
		
		$song_html = '<small>' . __( 'Couldn\'t retrieve songticker song data.', 'rabe' ) . '</small>';
		
	}

	return $song_html;
	
}


/**
 * Prints songticker songticker song html
 * 
 * @package rabe
 * @since version 1.0.0
 * @uses get_rabe_songticker_song() Gets currently played song
 * @return $string HTML code of songticker song
 */
function rabe_songticker_song_html() {
	echo get_rabe_songticker_song_html();
}


/**
 * Prepare and display audio player
 *
 * @package rabe
 * @since version 1.0.0
 */
function rabe_live_player() {

	// Enqueue style for player
	wp_enqueue_style( 'live-player',  get_stylesheet_directory_uri() . '/css/live-player.css', array( 'wp-mediaelement' ) );
	
	// Popup player
	$autoplay = '';
	if ( is_page( 'player' ) ) {
		wp_enqueue_style( 'popup-player',  get_stylesheet_directory_uri() . '/css/popup-player.css', array( 'live-player', 'wp-mediaelement' ), 1.0 );
		$autoplay = 'on';
	}

	// Don't stop the audio
	wp_enqueue_script( 'custom-medialement',  get_stylesheet_directory_uri() . '/js/custom-mediaelement.js', array( 'jquery', 'wp-mediaelement' ), 1.0, true );

	// Get stream URL
	$rabe_options = get_option( 'rabe_option_name' );
	$stream_url = ( $rabe_options['stream_url'] ) ? $rabe_options['stream_url'] : false;

	$attr = array(
		'src'		=> $stream_url,
		'preload'	=> 'metadata',
		'loop'		=> 'on',
		'autoplay'	=> $autoplay
	);
		
	// Display player only, when URL is set
	if ( $stream_url ) {
		
		echo wp_audio_shortcode( $attr );
		
	} else {
		
		// No stream URL set
		$no_stream = sprintf( '<small>%1s <a href="%2s">%3s</a></small>',
			__( 'No stream URL set! Check', 'rabe' ),
			admin_url( 'admin.php?page=rabe_settings' ),
			__( 'Settings', 'rabe' )
		);
		
		echo $no_stream;
	}
}


/**
 * Get player links
 *
 * Creates the player links (popup player and playlist link)
 * 
 * @package rabe
 * @since version 1.0.0
 */
function get_rabe_player_links() {
	$player_links = '<div id="live-player-links">
		<a target="popup" <a target="popup" onclick="window.open(\'\', \'popup\', \'width=440,height=120,scrollbars=no,toolbar=no,status=no,resizable=yes,menubar=no,location=no,directories=no,top=10,left=10\')" href="' . site_url( '/player' ) . '">Player</a>
		<a href="' . site_url( '/playlist' ) . '">Playlist</a>
		</div>';
	return $player_links;
}


/**
 * Prints player links
 *
 * @package rabe
 * @since version 1.0.0
 */
function rabe_player_links() {
	echo get_rabe_player_links();
}


/**
 * Enqueue ticker scripts
 * 
 * Ticker scripts for songticker and for broadcast ticker (@see rabe_schedule())
 *
 * @package rabe
 * @since version 1.0.0
 */
function rabe_ticker_scripts() {

	// Eventorganiser/Broadcast ticker
	wp_enqueue_script( 'broadcast-ticker', get_stylesheet_directory_uri() . '/js/broadcast-ticker.js', array( 'jquery' ), '1.0', true );
	wp_localize_script( 
		'broadcast-ticker',
		'LivePlayer',
		array(
			'ajax_url'		=> admin_url( 'admin-ajax.php' ),
			'ticker_nonce'	=> wp_create_nonce( 'liveplayer-nonce' )
		)
	);
}
add_action( 'wp_enqueue_scripts', 'rabe_ticker_scripts' );


/**
 * Ajax call for getting currently running broadcast
 * 
 * @package rabe
 * @since version 1.0.0
 * @uses rabe_schedule() Generates HTML div with currently running broadcast
 */
function rabe_liveplayer_ajax() {
	
	// Check nonce
	$nonce = $_REQUEST['ticker_nonce'];
	if ( ! wp_verify_nonce( $nonce, 'liveplayer-nonce' ) ) {
		wp_die( 'Error: No valid nonce.' );
	}

	// Check event organiser plugin
	if ( ! function_exists( 'eventorganiser_load_textdomain' ) ) {
		wp_die( 'Error: Event organiser plugin not active.');
	}


	// Get currently running event
	$running_event = ( get_transient( 'rabe_event_schedule_running' ) ) ? get_transient( 'rabe_event_schedule_running' ) : get_rabe_current_event();

	// Get saved event
	$saved_event = ( get_transient( 'rabe_event_schedule_save' ) ) ? get_transient( 'rabe_event_schedule_save' ) : false;

	// FIXME: Somehow wp_die() isn't working here in these ajax responses?!
	// Check https://wordpress.stackexchange.com/questions/250280/best-way-to-end-wordpress-ajax-request-and-why/250282
	// wp_die( 'Ajax call rabe_liveplayer_ajax ended.' );

	// There is actually a running broadcast and it hasn't changed (== and != because $running event can be int or bool)
	if ( $running_event === $saved_event ) {
		
		header( 'HTTP/1.1 304 Not Modified' );
		die;

	// There is no running broadcast, so get songticker
	} elseif  ( false === $running_event ) {
		
		if ( false === rabe_songticker_song_changed() ) {
			header( 'HTTP/1.1 304 Not Modified' );
			die;
			
		} else {
			
			$rabe_options = get_option( 'rabe_option_name' );
			$fallback_broadcast = ( $rabe_options['fallback_broadcast'] ) ? (int) $rabe_options['fallback_broadcast'] : false;	
			header( 'Content-Type: text/html; charset=utf-8' );
			rabe_songticker( $fallback_broadcast );
			die;
			
		}

	// Update schedule
	} else {
		header( 'Content-Type: text/html; charset=utf-8' );
		delete_transient( 'rabe_songticker_song' );
		delete_transient( 'rabe_songticker_song_save' );
		delete_transient( 'rabe_event_schedule_running' );
		delete_transient( 'rabe_event_schedule_save' );
		rabe_schedule();
		die;

	}
}
add_action( 'wp_ajax_ajax-liveplayer', 'rabe_liveplayer_ajax' );
add_action( 'wp_ajax_nopriv_ajax-liveplayer', 'rabe_liveplayer_ajax' );

/**
 * Add a download button to embedded audio files
 * 
 * @package rabe
 * @since version 1.0.0
 * @link http://www.damiencarbery.com/2017/02/add-download-link-to-wordpress-audio-player/
 */
function rabe_audio_shortcode_download( $html, $atts, $audio, $post_id, $library ) {
	
	if ( ! is_admin() ) {
		
		$audio_types = array( 'mp3', 'ogg', 'm4a', 'wav' );
		
		// Use the first audio type that has data.
		foreach ( $audio_types as $extension ) {
			
			if ( strlen( $atts[ $extension ] ) ) {
				
				$audiosrc = $atts[ $extension ];
				return '<div class="with-download"><div class="audio">' 
					. $html 
					. sprintf( '</div><div class="download"><a class="audio-download" href="%s" download="%s"><img src="%s" alt="Download"></a></div></div><div class="clearfix"></div>',
							$audiosrc,
							$audiosrc,
							get_stylesheet_directory_uri() . '/images/download.svg'
					);
				break;
			
			}
		
		}
	
	}
    
    // Otherwise return the original html.
    return $html;
}
add_filter( 'wp_audio_shortcode', 'rabe_audio_shortcode_download', 10, 5 );
?>
