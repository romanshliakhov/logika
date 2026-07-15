<?php

declare(strict_types=1);

final class Logika_Theme_City_Seo {
	public static function should_noindex( int $city_id ): bool {
		return 'noindex' === get_field( 'city_index_status', $city_id );
	}

	public static function robots( array $robots ): array {
		if ( is_singular( 'city' ) && self::should_noindex( get_queried_object_id() ) ) {
			$robots['noindex'] = true;
			$robots['nofollow'] = false;
		}

		return $robots;
	}

	public static function canonical( ?string $url ): ?string {
		return is_singular( 'city' ) ? get_permalink( get_queried_object_id() ) : $url;
	}
}
