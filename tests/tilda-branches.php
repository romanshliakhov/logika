<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$expected = array(
	'Черкаси' => 'вул. Гуржіївська, 31, (каб. 306)',
	'Сміла'    => 'пров. Бобринського, 2, БЦ "Факел"',
	'Канів'    => 'вул. Героїв Дніпра, 1А/3 (2-й поверх)',
	'Умань'    => 'вул. Європейська, 8, 3 поверх',
);
$cities = get_posts( array( 'post_type' => 'city', 'post_status' => 'publish', 'posts_per_page' => -1 ) );

foreach ( $expected as $city_name => $address ) {
	$city = current( array_filter( $cities, static fn( WP_Post $city ): bool => $city_name === $city->post_title ) );
	$branches = $city instanceof WP_Post ? get_posts(
		array(
			'post_type'   => 'branch',
			'post_status' => 'publish',
			'meta_query'  => array(
				array( 'key' => 'branch_city_id', 'value' => (string) $city->ID ),
				array( 'key' => 'branch_is_active', 'value' => '1' ),
			),
		)
	) : array();
	$branch = current( $branches );

	if ( ! $branch instanceof WP_Post || $address !== get_field( 'branch_address', $branch->ID ) ) {
		fwrite( STDERR, "Tilda branch address is missing for {$city_name}.\n" );
		exit( 1 );
	}
}

echo "Tilda branch addresses are available through WordPress.\n";
