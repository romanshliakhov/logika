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

$map_city = get_page_by_path( 'map-api-city', OBJECT, 'city' );
$map_city_id = $map_city ? (int) $map_city->ID : (int) wp_insert_post( array( 'post_type' => 'city', 'post_name' => 'map-api-city', 'post_title' => 'Місто для карти', 'post_status' => 'publish' ) );

foreach ( array( 'active-map-branch' => array( 'Активна філія', 'publish', 1 ), 'inactive-map-branch' => array( 'Неактивна філія', 'publish', 0 ), 'draft-map-branch' => array( 'Чернетка філії', 'draft', 1 ) ) as $slug => $branch_data ) {
	list( $title, $status, $active ) = $branch_data;
	$branch = get_page_by_path( $slug, OBJECT, 'branch' );
	$branch_id = $branch ? (int) $branch->ID : (int) wp_insert_post( array( 'post_type' => 'branch', 'post_name' => $slug, 'post_title' => $title, 'post_status' => $status ) );
	wp_update_post( array( 'ID' => $branch_id, 'post_status' => $status, 'post_title' => $title ) );
	update_field( 'branch_city_id', $map_city_id, $branch_id );
	update_field( 'branch_address', 'вул. Тестова, 10', $branch_id );
	update_field( 'branch_is_active', $active, $branch_id );
}

$branch_response = rest_do_request( new WP_REST_Request( 'GET', '/logika/v1/cities/' . $map_city_id . '/branches' ) );
$branches = $branch_response->get_data();

if ( 200 !== $branch_response->get_status() || array( 'Активна філія' ) !== array_column( $branches, 'label' ) ) {
	fwrite( STDERR, "Map branch API must expose only active published branches.\n" );
	exit( 1 );
}

echo "City selector and map APIs expose public city data and active branches.\n";
