<?php

declare(strict_types=1);

final class Logika_Theme_City_Faq_Schema {
	public static function build( int $city_id ): array {
		$ids = array_map( 'absint', (array) get_field( 'city_related_faq', $city_id ) );
		$ids = $ids ?: array_map( 'absint', (array) get_field( 'fallback_faq', 'option' ) );
		$items = $ids ? get_posts( array( 'post_type' => 'faq_item', 'post_status' => 'publish', 'post__in' => $ids, 'orderby' => 'post__in', 'posts_per_page' => -1, 'meta_key' => 'faq_is_active', 'meta_value' => '1' ) ) : array();

		return array(
			'@context'    => 'https://schema.org',
			'@type'       => 'FAQPage',
			'mainEntity'  => array_map(
				static fn( WP_Post $item ): array => array( '@type' => 'Question', 'name' => get_field( 'faq_question', $item->ID ) ?: $item->post_title, 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( (string) get_field( 'faq_answer', $item->ID ) ) ) ),
				$items
			),
		);
	}

	public static function render(): void {
		if ( is_singular( 'city' ) ) {
			$schema = self::build( get_queried_object_id() );
			if ( $schema['mainEntity'] ) {
				echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
			}
		}
	}
}
