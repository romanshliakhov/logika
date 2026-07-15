<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

function logika_media_api_post( string $slug, string $title, string $status, string $date, int $city_id = 0 ): int {
	$post = get_page_by_path( $slug, OBJECT, 'post' );
	$post_id = $post instanceof WP_Post ? $post->ID : wp_insert_post( array( 'post_type' => 'post', 'post_name' => $slug, 'post_title' => $title, 'post_status' => $status ) );
	wp_update_post( array( 'ID' => $post_id, 'post_title' => $title, 'post_status' => $status, 'post_date' => $date, 'post_date_gmt' => get_gmt_from_date( $date ) ) );
	update_field( 'post_related_city', $city_id, $post_id );

	return (int) $post_id;
}

$city = get_page_by_path( 'media-api-city', OBJECT, 'city' );
$city_id = $city instanceof WP_Post ? $city->ID : wp_insert_post( array( 'post_type' => 'city', 'post_name' => 'media-api-city', 'post_title' => 'Місто для медіа', 'post_status' => 'publish' ) );
$other_city = get_page_by_path( 'other-media-api-city', OBJECT, 'city' );
$other_city_id = $other_city instanceof WP_Post ? $other_city->ID : wp_insert_post( array( 'post_type' => 'city', 'post_name' => 'other-media-api-city', 'post_title' => 'Інше місто для медіа', 'post_status' => 'publish' ) );
$date = static fn( int $seconds ): string => wp_date( 'Y-m-d H:i:s', time() - $seconds, wp_timezone() );

logika_media_api_post( 'media-api-local-new', 'Локальна нова стаття', 'publish', $date( 60 ), $city_id );
logika_media_api_post( 'media-api-local-old', 'Локальна стара стаття', 'publish', $date( 120 ), $city_id );
logika_media_api_post( 'media-api-common-new', 'Загальна нова стаття', 'publish', $date( 180 ) );
logika_media_api_post( 'media-api-common-old', 'Загальна стара стаття', 'publish', $date( 240 ) );
logika_media_api_post( 'media-api-other-city', 'Чужа міська стаття', 'publish', $date( 30 ), $other_city_id );
logika_media_api_post( 'media-api-draft', 'Чернетка міста', 'draft', $date( 30 ), $city_id );

$request = new WP_REST_Request( 'GET', '/logika/v1/media' );
$request->set_param( 'city', $city_id );
$response = rest_do_request( $request );
$titles = array_column( $response->get_data(), 'title' );

if ( 200 !== $response->get_status() || array( 'Локальна нова стаття', 'Локальна стара стаття', 'Загальна нова стаття', 'Загальна стара стаття' ) !== array_slice( $titles, 0, 4 ) || array_intersect( array( 'Чужа міська стаття', 'Чернетка міста' ), $titles ) ) {
	fwrite( STDERR, "Media API must prioritise selected-city articles and include only published common articles.\n" );
	exit( 1 );
}

$invalid = new WP_REST_Request( 'GET', '/logika/v1/media' );
$invalid->set_param( 'city', $other_city_id + 100000 );

if ( 404 !== rest_do_request( $invalid )->get_status() ) {
	fwrite( STDERR, "Media API must reject an unknown city.\n" );
	exit( 1 );
}

$general = rest_do_request( new WP_REST_Request( 'GET', '/logika/v1/media' ) );

if ( 200 !== $general->get_status() || 7 !== count( $general->get_data() ) ) {
	fwrite( STDERR, "Media API must provide the general seven-card feed without a selected city.\n" );
	exit( 1 );
}

echo "Media API filters published city and common articles.\n";
