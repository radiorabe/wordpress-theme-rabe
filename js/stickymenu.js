/**
 * Display menu on top of page while scrolling
 *
 * @package rabe
 * @since version 1.0.0
 */
jQuery(document).ready(function() {
	jQuery(window).scroll(function () {
		if ( ( jQuery(window).scrollTop() > 340 ) && ( jQuery(window).width() > 1050 ) ) {
			jQuery('#navigation').addClass('sticky-menu');
			// jQuery('.menu-site-title a').css('display','inline');
			jQuery('.menu-site-title a').show();
		}
		if ( jQuery(window).scrollTop() < 341 ) {
			jQuery('#navigation').removeClass('sticky-menu');
			// jQuery('.menu-site-title a').css('display','none');
			jQuery('.menu-site-title a').hide();

		}
	});
});
