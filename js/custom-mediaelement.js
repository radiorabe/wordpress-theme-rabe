/**
 * mediaelement.js customizations
 * 
 * @package rabe
 * @since version 1.0.0
 */
jQuery(document).ready(function() {
	/**
	 * Don't stop the radio
	 * 
	 * Very nice workaround for not interrupting playback
	 * Adds target="_blank" to links, when some player was started
	 * 
	 * @package rabe
	 * @since version 1.0.0
	 * @https://css-tricks.com/dont-stop-the-audio/
	 */

	var player = jQuery('audio').mediaelementplayer();

	jQuery(player).on("play", function(event) {

		// Links in new tabs
		jQuery("a").attr("target", "_blank");

	});

	/**
	 * Refresh audio Stream
	 * 
	 * Get fresh stream, don't start where stopped
	 * 
	 * @package rabe
	 * @since version 1.0.0
	 * @link https://github.com/johndyer/mediaelement/issues/1321
	 * @link https://bavotasan.com/2015/working-with-wordpress-and-mediaelement-js/
	 */
	var liveplayer = jQuery('#webplayer audio')[0];
	var button = jQuery('.mejs-playpause-button');
	var source = jQuery( liveplayer ).attr('src');
	var playpause = document.querySelector('.mejs-playpause-button');
	playpause.addEventListener('click', function() {
		if ( liveplayer.paused ) {
			button.removeClass('mejs-pause').addClass('mejs-play');
		} else {
			var i = Math.floor( ( Math.random() * 100000 ) + 1)
			liveplayer.player.setSrc(source + '?nocache=' + i);
			liveplayer.player.load();
			liveplayer.player.play();
			button.removeClass('mejs-play').addClass('mejs-pause');
		}
	});

	/**
	 * Add download button to audio files
	 * 
	 * @package rabe
	 * @since version 1.0.0
	 * @link http://alexmansfield.com/hacks/wordpress-audio-download-button

    jQuery('audio').each( function(index) {
 
        var source = jQuery(this).find('source').attr('src');
 
        if ( source != '' ) {
            jQuery(this).after('<a href="' + source + '" class="audio-download" download="' + source + '">Download</a>');
        }
 
    });
    */
});

