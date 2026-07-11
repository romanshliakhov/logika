<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$request = new WP_REST_Request( 'GET', '/logika/v1/cities' );
$response = rest_do_request( $request );
$cities = $response->get_data();

if ( 200 !== $response->get_status() || ! isset( $cities[0]['url'], $cities[0]['label'] ) ) {
	fwrite( STDERR, "City selector API is not available.\n" );
	exit( 1 );
}

$labels = array_column( $cities, 'label' );
if ( ! in_array( 'Київ', $labels, true ) || ! in_array( 'Львів', $labels, true ) || in_array( 'Тестове місто', $labels, true ) ) {
	fwrite( STDERR, "City selector API does not expose seeded public cities cleanly.\n" );
	exit( 1 );
}

$kyiv = current( array_filter( $cities, static fn( array $city ): bool => 'Київ' === $city['label'] ) );
$bila_tserkva = current( array_filter( $cities, static fn( array $city ): bool => 'Біла Церква' === $city['label'] ) );
if ( empty( $kyiv['region']['label'] ) || 'Київська область' !== $kyiv['region']['label'] || empty( $bila_tserkva['region']['label'] ) || $kyiv['region']['label'] !== $bila_tserkva['region']['label'] ) {
	fwrite( STDERR, "City selector API does not expose city regions for grouped selector UI.\n" );
	exit( 1 );
}

echo "City selector API exposes public city labels, regions and URLs.\n";
