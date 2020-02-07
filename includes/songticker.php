<?php
/**
 * a very dirty proxy
 *
 * i will start changing this so it passes on
 * any proxy info coming from the browser or
 * elsewhere, for now at least this works for 
 * testing.
 * For rabe.ch this will not need apikey supprt
 * since rabe.ch has an allow rule on its ip.
 */

if ( array_key_exists( 'HTTP_IF_NONE_MATCH', $_SERVER ) ) {
    $if_none_match = $_SERVER['HTTP_IF_NONE_MATCH'];
    $header = 'If-None-Match: ' . $if_none_match . "\r\n";
} else {
    $header = '';
}

$context = stream_context_create(
    array(
        'http' => array(
            'method' => 'GET',
            'header' => $header
        )
    )
);
if ( file_get_contents( 'https://songticker.rabe.ch/songticker/0.9.3/current.xml', false, $context ) ) {
	$res = file_get_contents( 'https://songticker.rabe.ch/songticker/0.9.3/current.xml', false, $context );

	foreach ( $http_response_header as $header ) {
		if ( substr( $header, 0, 5 ) == 'ETag:' ) {
			$etag = substr( $header, 6 );
		}
	}


	if ( $http_response_header[0] == 'HTTP/1.1 304 Not Modified' ) {
		header( $http_response_header[0] );
		header( 'ETag: ' . $etag );
		exit( 'No change in songticker.' );
	}

	header( 'Content-Type: application/xml' );
	$http_origin = $_SERVER['HTTP_ORIGIN'];

	if ( $http_origin == "https://rabe.ch" 
		|| $http_origin == 'https://www.rabe.ch'
		|| $http_origin == 'https://dev.rabe.ch' ) {
		header( 'Access-Control-Allow-Origin: ' . $http_origin );
	}
	header( 'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, If-None-Match' );
	header( 'Access-Control-Allow-Methods: GET' );
	header( 'ETag: ' . $etag );

	// Print XML
	echo $res;
} else {
	//die;
	echo 'No songticker data available.';
}
?>
