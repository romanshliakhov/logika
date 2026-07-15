<?php

declare(strict_types=1);

final class Logika_Theme_Course_Schema {
	public static function build( int $course_id ): array {
		$min = get_field( 'course_age_min', $course_id );
		$max = get_field( 'course_age_max', $course_id );

		return array_filter(
			array(
				'@context'        => 'https://schema.org',
				'@type'           => 'Course',
				'@id'             => get_permalink( $course_id ) . '#course',
				'name'            => get_the_title( $course_id ),
				'description'     => get_field( 'course_short_description', $course_id ) ?: null,
				'url'             => get_permalink( $course_id ),
				'typicalAgeRange' => $min && $max ? sprintf( '%d-%d', $min, $max ) : null,
			)
		);
	}

	public static function render(): void {
		if ( is_singular( 'course' ) ) {
			echo '<script type="application/ld+json">' . wp_json_encode( self::build( get_queried_object_id() ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
		}
	}
}
