/**
 * Get currently running broadcast
 *
 * @package rabe
 * @since version 1.0.0
 * @link https://premium.wpmudev.org/blog/using-ajax-with-wordpress/
 * @link http://solislab.com/blog/5-tips-for-using-ajax-in-wordpress/
 * @link https://designorbital.com/tutorials/how-to-use-ajax-in-wordpress/
 */

jQuery(document).ready(function() {
	var getBroadcast = function() {
		// First, get currently running (old) broadcast
		var old_broadcast = jQuery('#ticker').find( '.broadcast-show' ).text();

		// Ajax call: Could use .post or .ajax, but .get is sufficient
		// @see https://api.jquery.com/jquery.get/
		jQuery.get({

			url: LivePlayer.ajax_url,
			// Use this for not modified header
			cache: true,
			data: ({
				action: 'ajax-liveplayer',
				// send the nonce along with the request
				ticker_nonce: LivePlayer.ticker_nonce
			}),

			success: function( data, textStatus, jqXHR ){
				// Only update ticker HTML when it's html response code is modified
				if ( jqXHR.status != '304' ) {
					jQuery( '#ticker' ).html( data );
				}
			},

			error: function( jqXHR, textStatus, errorThrown ){
				console.log( 'Error: ' + textStatus, errorThrown );   
			},

			dataType: 'html'

		});
	};
	// Call it on page load
	getBroadcast();

	// Call it every six seconds
	setInterval( getBroadcast, 1000 * 5 );
});
