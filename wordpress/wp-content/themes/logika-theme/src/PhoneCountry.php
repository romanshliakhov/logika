<?php

declare(strict_types=1);

final class Logika_Theme_Phone_Country {
	private const HEADERS = array(
		'HTTP_CF_IPCOUNTRY',
		'HTTP_CLOUDFRONT_VIEWER_COUNTRY',
		'HTTP_X_VERCEL_IP_COUNTRY',
		'HTTP_X_GEO_COUNTRY',
		'HTTP_X_COUNTRY_CODE',
		'GEOIP_COUNTRY_CODE',
	);

	private const UNKNOWN_COUNTRIES = array( 'A1', 'A2', 'EU', 'O1', 'T1', 'XX' );

	public static function register(): void {
		register_rest_route(
			'logika/v1',
			'/phone-country',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'rest' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function rest(): WP_REST_Response {
		$response = new WP_REST_Response( array( 'country' => self::resolve() ) );
		$response->header( 'Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0' );

		return $response;
	}

	public static function resolve( ?array $server = null ): string {
		return self::country_from_server( $server ?? $_SERVER ) ?? self::default_country();
	}

	public static function country_from_server( array $server ): ?string {
		foreach ( self::HEADERS as $header ) {
			$country = self::normalize( (string) ( $server[ $header ] ?? '' ) );
			if ( null !== $country ) {
				return $country;
			}
		}

		return null;
	}

	private static function default_country(): string {
		$country = function_exists( 'get_field' ) ? (string) get_field( 'form_phone_country_default', 'option' ) : '';

		return self::normalize( $country ) ?? 'UA';
	}

	private static function normalize( string $country ): ?string {
		$country = strtoupper( trim( $country ) );

		if ( ! preg_match( '/^[A-Z]{2}$/', $country ) || in_array( $country, self::UNKNOWN_COUNTRIES, true ) ) {
			return null;
		}

		return $country;
	}
}
