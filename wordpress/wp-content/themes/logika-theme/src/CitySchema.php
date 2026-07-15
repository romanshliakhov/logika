<?php

declare(strict_types=1);

final class Logika_Theme_City_Schema {
	public static function build( int $city_id ): array {
		$local = (array) get_field( 'city_schema_local_business', $city_id );
		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'LocalBusiness',
			'@id'       => get_permalink( $city_id ) . '#localbusiness',
			'url'       => get_permalink( $city_id ),
			'name'      => $local['name'] ?? get_the_title( $city_id ),
		);

		foreach ( array( 'telephone' => 'telephone', 'description' => 'description', 'price_range' => 'priceRange' ) as $field => $schema_field ) {
			if ( ! empty( $local[ $field ] ) ) {
				$schema[ $schema_field ] = $local[ $field ];
			}
		}
		if ( ! empty( $local['address'] ) ) {
			$schema['address'] = array( '@type' => 'PostalAddress', 'streetAddress' => $local['address'] );
		}
		if ( get_field( 'city_lat', $city_id ) && get_field( 'city_lng', $city_id ) ) {
			$schema['geo'] = array( '@type' => 'GeoCoordinates', 'latitude' => (float) get_field( 'city_lat', $city_id ), 'longitude' => (float) get_field( 'city_lng', $city_id ) );
		}

		return $schema;
	}

	public static function render(): void {
		if ( is_singular( 'city' ) ) {
			echo '<script type="application/ld+json">' . wp_json_encode( self::build( get_queried_object_id() ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
		}
	}
}
