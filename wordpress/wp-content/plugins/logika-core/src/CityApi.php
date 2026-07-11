<?php

declare(strict_types=1);

namespace Logika\Core;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class CityApi {
	public static function register(): void {
		register_rest_route(
			'logika/v1',
			'/cities',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'index' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$cities = get_posts(
			array(
				'post_type'      => 'city',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => 'city_index_status',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'city_index_status',
						'value'   => 'noindex',
						'compare' => '!=',
					),
				),
			)
		);

		return new WP_REST_Response(
				array_map(
					static fn( \WP_Post $city ): array => array(
						'id'     => $city->ID,
						'label'  => get_field( 'city_selected_label', $city->ID ) ?: $city->post_title,
						'url'    => get_permalink( $city ),
						'lat'    => (float) get_field( 'city_lat', $city->ID ),
						'lng'    => (float) get_field( 'city_lng', $city->ID ),
						'region' => self::region( $city->ID ),
					),
					$cities
				)
			);
		}

	private static function region( int $city_id ): array {
		$terms = get_the_terms( $city_id, 'region' );
		$term  = is_array( $terms ) ? current( $terms ) : false;

		return $term instanceof \WP_Term ? array(
			'id'    => $term->term_id,
			'label' => $term->name,
			'slug'  => $term->slug,
		) : array(
			'id'    => 0,
			'label' => 'Інші міста',
			'slug'  => 'other',
		);
	}
	}
