<?php
/**
 * CAUTION: This is somehow complicated because the ticker gets 
 * different information from different sources ...
 * 
 * - Event organiser plugin for the running broadcasts
 * - Old songticker for song title and artist
 * 
 * There are workarounds needed for getting and setting the ticker 
 * because the information in the old songticker is often outdated and 
 * doesn't change when there is a new broadcast schedule.
 * 
 * And then there is the fallback_broadcast which gets in action 
 * (actual songtickering) in two situations, when there is no planned 
 * event running OR/AND when the fallback_broadcast is chosen as event.
 * 
 */
/**
 * Prepares the programm schedule and displays player with ticker
 * 
 * Depending on a real broadcast running or the fallback broadcast 
 * running, a different title is displayed in the player.
 *
 * @package rabe
 * @since version 1.0.0
 * @uses get_rabe_songticker() Gets ticker with song
 * 		 get_broadcast_link() Gets link of broadcast
 * 		 get_rabe_player_links() Displays links from the player
 * 		 get_rabe_current_event() Gets currently running event
 * @return string $ticker HTML of Broadcast and link
 */
function get_rabe_schedule() {

	if ( function_exists( 'eventorganiser_load_textdomain' ) ) {

		$running_event = get_rabe_current_event();

		if ( false !== $running_event ) {
								
			// Check if all day, set format accordingly
			$format = ( eo_is_all_day( $running_event['id'] ) ? get_option( 'date_format' ) : get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
			
			// Is event/broadcast linked to event page or broadcast page?
			$broadcast_link = (int) get_post_meta( $running_event['id'], 'rabe_broadcast_link', true );
			$ticker_url = ( 1 === $broadcast_link ) ? get_broadcast_link( get_broadcast( $running_event['id'] ) ) : eo_get_permalink( $running_event['id'], false );

			// Ticker of running broadcast
			$ticker = '<div class="broadcast-show"><a href="' . $ticker_url . '">' . get_the_title( $running_event['id'] ) . '</a></div>
				<div class="live">' . __( 'on air', 'rabe' ) . '</div>
				' . get_rabe_player_links();

		} else {
			
			// Get fallback broadcast id
			$rabe_options = get_option( 'rabe_option_name' );
			$fallback_broadcast_id = ( isset( $rabe_options['fallback_broadcast'] ) ) ? (int) $rabe_options['fallback_broadcast'] : false;
			$ticker = get_rabe_songticker( $fallback_broadcast_id );
			
		}	
		
	} else {
		
		$ticker = __( 'Event organiser plugin not active.', 'rabe' );

	}
	
	return $ticker;
}


/**
 * Prints ticker
 * 
 * @package rabe
 * @since version 1.1.0
 */
function rabe_schedule() {
	echo get_rabe_schedule();
}


/**
 * Gets currently running event
 * 
 * There should be only be one event. Return false, when the fallback 
 * broadcast or no broadcast is running.
 * 
 * @package rabe
 * @since version 1.1.0
 * @return mixed $running_event Array with event information of 
 * 				 currently running event or false 
 */
function get_rabe_current_event() {
	
	if ( false === ( $running_event = get_transient( 'rabe_event_schedule_running' ) ) ) {
	
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
			$running_event['id'] = (int) $running_events[0]->ID;
			
			// Get event name
			$running_event['name'] = get_the_title( $running_event['id'] );
			
			// Get event url
			$broadcast_link = (int) get_post_meta( $running_event['id'], 'rabe_broadcast_link', true );
			$running_event['url'] = ( 1 === $broadcast_link ) ? get_broadcast_link( get_broadcast( $running_event['id'] ) ) : eo_get_permalink( $running_event['id'], false );

			// Calculate how long to save schedule in transient
			$now = time();
			$end = eo_get_the_end( 'U', $running_event['id'], $running_events[0]->occurrence_id );
			$seconds = $end - $now;

			// Check if it's the fallback broadcast, then delete running and saved event
			$rabe_options = get_option( 'rabe_option_name' );
			$fallback_broadcast = ( isset( $rabe_options['fallback_broadcast'] ) ) ? (int) $rabe_options['fallback_broadcast'] : false;
			
			if ( get_broadcast( $running_event['id'] ) === $fallback_broadcast ) {
				
				$running_event = false;
				delete_transient( 'rabe_event_schedule_running' );
				
			} else {

				delete_transient( 'rabe_songticker_song_temp' );
				delete_transient( 'rabe_songticker_song_save' );

				// Save running event id to transient
				set_transient( 'rabe_event_schedule_running', $running_event, $seconds );
			}
		}
	}
	
	return $running_event;
}

/**
 * Gets fallback broadcast info (which is rarely changed) and saves it
 * to transient for later usage
 * 
 * @package rabe
 * @since version 1.1.0
 * @return array broadcast name, broadcast link and broadcast id of
 * 				 fallback broadcast
 */
function get_rabe_fallback_broadcast() {
	
	if ( false === ( $fallback_broadcast_info = get_transient( 'rabe_fallback_broadcast_save' ) ) ) {

		// Get fallback broadcast id
		$rabe_options = get_option( 'rabe_option_name' );
		$fallback_broadcast_id = ( isset( $rabe_options['fallback_broadcast'] ) ) ? (int) $rabe_options['fallback_broadcast'] : false;

		$fallback_broadcast_info = array(
			'id' => $fallback_broadcast_id,
			'name' => get_broadcast_name( $fallback_broadcast_id ),
			'url' => get_broadcast_link( $fallback_broadcast_id )
		);

		// Save fallback broadcast infos in transient for later usage
		set_transient( 'rabe_fallback_broadcast_save', $fallback_broadcast_info, DAY_IN_SECONDS );

	}
	
	return $fallback_broadcast_info;
}

/**
 * Gets ticker with currently played song
 * 
 * @package rabe
 * @since version 1.1.0
 * @uses get_broadcast_link() Gets link of broadcas
 *		 get_broadcast_name() Gets name of broadcast
 *		 get_rabe_songticker_html() Gets ticker html with song info
 * 		 get_rabe_player_links() Gets links from the player
 * @param int $fallback_broadcast Broadcast term ID of fallback broadcast
 * @return $string HTML code of songticker
 */
function get_rabe_songticker( $fallback_broadcast_id = '' ) {

	if ( $fallback_broadcast_id ) {
		
		$link = get_broadcast_link( $fallback_broadcast_id );
		$name = get_broadcast_name( $fallback_broadcast_id );

		$songticker = sprintf( 
			'<div class="broadcast-show ticker"><a href="%1s">%2s</a></div>%3s%4s',
			$link,
			$name,
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
	if ( false === ( $song = get_transient( 'rabe_songticker_song_temp' ) ) ) {
		
		// Get songticker url
		$rabe_options = get_option( 'rabe_option_name' );
		// FIXME
		$response_args = array( 
			'timeout' => 2,
			'redirect' => 1
		);

		$response = ( isset( $rabe_options['rabe_songticker_url'] ) ) ? wp_remote_get( $rabe_options['rabe_songticker_url'], $response_args ): wp_remote_get( get_stylesheet_directory_uri() . '/includes/songticker.php', $response_args );
		$response_code = wp_remote_retrieve_response_code( $response );
		
		$rabe_options = get_option( 'rabe_option_name' );
		$seconds = ( isset( $rabe_options['rabe_songticker_interval'] ) ) ? (int) $rabe_options['rabe_songticker_interval'] : 10; //  Default caching of song: 10 seconds
		
		// Songticker has changed!
		if ( $response_code == 200 ) {
			
			$xml = simplexml_load_string( wp_remote_retrieve_body( $response ) ) or die( 'Error: No valid songticker source.' );
			$song['song'] = $xml->track->title->__toString();
			$song['artist'] = $xml->track->artist->__toString();
			$song['expires'] = time() + $seconds;

			set_transient( 'rabe_songticker_song_temp', $song, $seconds );

		// We didn't receive new song data, so get old song and save it for 10 seconds
		} elseif ( false !== ( $song = get_transient( 'rabe_songticker_song_save' ) ) ) {

			set_transient( 'rabe_songticker_song_temp', $song, $seconds );
	
		} else {

			$song = array( __( 'No artist', 'rabe' ), __( 'No title', 'rabe' ) );

		}

	}
	
	return $song;
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
	
	$song = ( get_transient( 'rabe_songticker_song_temp' ) ) ? get_transient( 'rabe_songticker_song_temp' ) : get_rabe_songticker_song(); 			

	if ( $song ) {
		
		$song_html = '<div class="live"> ' . $song['song'] . ' - ' . $song['artist'] . ' </div>';
	
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
	

// 20181227: sl: quick fix
/*
$stream_context = stream_context_create(array('http' => array(
    'method' => 'HEAD',
    'timeout' => 1
)));

$response = file_get_contents($stream_url, false, $context);
$stream_exists = ($response !== false);
*/
	// Display player only, when URL is set
	// if ( $stream_exists ) {
	if ( $stream_url ) {

		// 20181227: sl: quick fix
		/*
		$stream_context = stream_context_create(array('http' => array(
		    'method' => 'HEAD',
		    'timeout' => 1
		)));

		$response = file_get_contents($stream_url, false, $context);
		$stream_exists = ($response !== false);

		*/
		/*
		// https://stackoverflow.com/questions/981954/how-can-one-check-to-see-if-a-remote-file-exists-using-php
		if ( true === file_get_contents( $stream_url, 0, null, 0, 1 ) ) {
			echo wp_audio_shortcode( $attr );
		} else {
			echo __( 'No stream available.', 'rabe' );
		}
		*/

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

function remoteFileExists($url) {
    $curl = curl_init($url);

    //don't fetch the actual page, you only want to check the connection is ok
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); 


    //do request
    $result = curl_exec($curl);

    $ret = false;

    //if request did not fail
    if ($result !== false) {
        //if request was ok, check response code
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  

        if ($statusCode == 200) {
            $ret = true;   
        }
    }

    curl_close($curl);

    return $ret;
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
		<a target="popup" onclick="window.open(\'\', \'popup\', \'width=440,height=120,scrollbars=no,toolbar=no,status=no,resizable=yes,menubar=no,location=no,directories=no,top=10,left=10\')" href="' . site_url( '/player' ) . '">Player</a>
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
 * Ticker scripts for songticker and for ticker (@see rabe_schedule())
 *
 * @package rabe
 * @since version 1.0.0
 */
function rabe_ticker_scripts() {

	// Ticker script
	wp_enqueue_script( 'rabe-ticker', get_stylesheet_directory_uri() . '/js/ticker.min.js', array( 'jquery' ), '1.0', true );

	wp_localize_script( 
		'rabe-ticker',
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
 * @since version 1.1.0
 * @uses get_rabe_schedule() Generates HTML div with currently running broadcast
 * 		 get_rabe_current_event() Gets currently running event
 * 		 get_rabe_songticker() Gets songticker
 * @return array JSON formatted array with actaul ticker html string and expriation time in seconds
 */
function rabe_liveplayer_ajax() {
	
	// Check nonce
	$nonce = $_REQUEST['ticker_nonce'];
	if ( ! wp_verify_nonce( $nonce, 'liveplayer-nonce' ) ) {
		wp_send_json_error( 'Error: No valid nonce.' );
	}

	// Check event organiser plugin
	if ( ! function_exists( 'eventorganiser_load_textdomain' ) ) {
		wp_send_json_error( 'Error: Event organiser plugin not active.');
	}
	
	// Get currently running event
	$running_event = ( get_transient( 'rabe_event_schedule_running' ) ) ? (array) get_transient( 'rabe_event_schedule_running' ) : get_rabe_current_event();

	// There is actually a running broadcast and it hasn't changed
	if ( false !== $running_event ) {
		
		$response['broadcast'] = $running_event['name'];
		$response['link'] = $running_event['url'];
		$response['song' ] = __( 'on air', 'rabe' );
		$response['artist' ] = '';
		$response['expires'] = get_option( '_transient_timeout_rabe_event_schedule_running' );
		
	// There is no running broadcast, so get songticker
	} else {

		$fallback_broadcast = get_rabe_fallback_broadcast();
		$song = get_rabe_songticker_song();

		$response['broadcast'] = $fallback_broadcast['name'];
		$response['link'] = $fallback_broadcast['url'];
		$response['song' ] = $song['song'];
		$response['artist' ] = $song['artist'];
		$response['expires'] = $song['expires'];
	}

	// Send JSON response
	wp_send_json( $response );

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
