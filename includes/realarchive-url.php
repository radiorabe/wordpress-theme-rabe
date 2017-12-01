<?php
/**
 * Generates downloadable link to real archive stream
 * 
 * This file gets called from [real_archive] shortcode (@see realarchive-shortcode.php)
 * 
 * @package rabe
 * @since version 1.0.0
 */


// Get date
$date = ( isset( $_REQUEST['real_date'] ) ) ? preg_replace( '/[^0-9]/' , '', $_REQUEST['real_date'] ) : '12072016';

// Reverse order to YYYYMMDD
$real_date = substr( $date, -4 ) . substr( $date, -6, 2) . substr( $date, -8, 2);

// Get time Time has to be exactly in this format (00:00:00), but we are only requesting hours and minutes
$time = ( isset( $_REQUEST['real_time'] ) ) ? $_REQUEST['real_time'] . ':00' : '00:00:00';

// Generate url to real audio
$url = 'http://archivserver.rabe.ch:554/ramgen/' . $real_date . '.rm?start=' . $time;

// Send URL
header( 'Location: ' . $url );
exit;
?>
