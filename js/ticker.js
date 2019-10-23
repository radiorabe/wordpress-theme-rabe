/**
 * Get json data with ticker information (broadcast, broadcast link, 
 * artist, song and expiration) and update/display in the ticker
 *
 * @package rabe
 * @since version 1.1.0
 * @link https://premium.wpmudev.org/blog/using-ajax-with-wordpress/
 * @link https://designorbital.com/tutorials/how-to-use-ajax-in-wordpress/
 * @link https://snippets.webaware.com.au/snippets/session-storage-with-expiry-time/
 */


jQuery( document ).ready( function() {
	
	var getTicker = function() {

		// Variables
		var sessionStorage = ( 'sessionStorage' in window && window.sessionStorage ),
			storageKey = 'rabeTicker',
			delay = 5000, // Default timeout delay of 5 seconds
			nextTicker = setTimeout( getTicker, delay ), // Set timout for getTicker
			now, expiration, data, newdelay = false;
		
		try {
			if ( sessionStorage ) {
				data = sessionStorage.getItem( storageKey );
				if ( data ) {
					// extract saved object from JSON encoded string
					data = JSON.parse( data );
					now = Math.floor( new Date().getTime() ); // get unix timestamp in miliseconds
					expiration = ( data.expires * 1000 ) + delay; // Get expiration time plus delay in miliseconds
					// Get new delay
					newdelay = expiration - now;
					if ( newdelay < 0 ) {
						newdelay = delay;
					}
					// Ditch the content if too old and set short interval
					if ( now > expiration ) {
						data = false;
						sessionStorage.removeItem( storageKey );
					}
					// Reset timeout for getTicker
					clearTimeout( nextTicker );
					nextTicker = setTimeout( getTicker, newdelay );
				}
			}
		}
		catch ( e ) {
			data = false;
		}
		// We got stored sessionStorage
		if ( data ) {
			// Show data from sessionStorage
			showTicker( data );	
		} else {
			// Ajax call: Could use .post or .ajax, but .get is sufficient
			// @see https://api.jquery.com/jquery.get/
			jQuery.get({
				url: LivePlayer.ajax_url,
				cache: true,
				dataType: 'json',
				data: ({
					action: 'ajax-liveplayer',
					// Send the nonce along with the request
					ticker_nonce: LivePlayer.ticker_nonce
				}),
				success: function( jsonData ){
					if ( sessionStorage ) {
						try {
							sessionStorage.setItem( storageKey, JSON.stringify( jsonData ));
						}
						catch (e) {
							console.log( 'Error: ' + e );
						}
					}
				},
				error: function( xhr, status, error ){
					console.log( 'Error: ' + status, error );
				}

			});
		}
	};
	
	/**
	* Display new content in ticker
	* 
	* When a broadcast is running data.song contains "on air" and 
	* data.artist is null
	* 
	* @param {String} data
	*/
	function showTicker( data ) {
		
		jQuery( '#ticker .broadcast-show a' ).html( data.broadcast );
		jQuery( '#ticker .broadcast-show a' ).attr( 'href', data.link );
		// Append song if one is set
		if ( data.artist ) {
			data.song = data.song + ' - ' + data.artist;
		}
		jQuery( '#ticker .live' ).html( data.song );

	}
	
	// Run once upon loading	
	getTicker();

});


