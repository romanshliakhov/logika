<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

if ( ! class_exists( 'Logika_Theme_Phone_Country' ) ) {
	fwrite( STDERR, "Phone country resolver is not available.\n" );
	exit( 1 );
}

$cases = array(
	array( 'server' => array( 'HTTP_CF_IPCOUNTRY' => 'CZ' ), 'expected' => 'CZ' ),
	array( 'server' => array( 'HTTP_CLOUDFRONT_VIEWER_COUNTRY' => 'pl' ), 'expected' => 'PL' ),
	array( 'server' => array( 'HTTP_X_VERCEL_IP_COUNTRY' => 'US' ), 'expected' => 'US' ),
	array( 'server' => array( 'HTTP_CF_IPCOUNTRY' => 'XX', 'HTTP_X_GEO_COUNTRY' => 'DE' ), 'expected' => 'DE' ),
	array( 'server' => array( 'HTTP_CF_IPCOUNTRY' => 'USA' ), 'expected' => null ),
);

foreach ( $cases as $case ) {
	$actual = Logika_Theme_Phone_Country::country_from_server( $case['server'] );
	if ( $case['expected'] !== $actual ) {
		fwrite( STDERR, 'Unexpected phone country resolver result: ' . var_export( $actual, true ) . PHP_EOL );
		exit( 1 );
	}
}

if ( 'UA' !== Logika_Theme_Phone_Country::resolve( array() ) ) {
	fwrite( STDERR, "Phone country fallback is not UA-compatible.\n" );
	exit( 1 );
}

$routes = rest_get_server()->get_routes();
if ( ! isset( $routes['/logika/v1/phone-country'] ) ) {
	fwrite( STDERR, "Phone country REST route is not registered.\n" );
	exit( 1 );
}

$original = $_SERVER;
$_SERVER['HTTP_CF_IPCOUNTRY'] = 'CZ';
$request = new WP_REST_Request( 'GET', '/logika/v1/phone-country' );
$response = rest_do_request( $request );
$_SERVER = $original;

if ( 200 !== $response->get_status() || 'CZ' !== ( $response->get_data()['country'] ?? '' ) ) {
	fwrite( STDERR, "Phone country REST route does not return the geo country.\n" );
	exit( 1 );
}

echo "Phone country auto-detection is available.\n";
