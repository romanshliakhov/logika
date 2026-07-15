<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

final class Logika_Theme_Routing {
	private const LEGACY_ROUTES = array(
		'about.html' => '/about/', 'faq.html' => '/faq/', 'it-courses.html' => '/it-courses/', 'en-courses.html' => '/english-courses/', 'camps.html' => '/camps/', 'media-center.html' => '/media-center/', 'article.html' => '/media-center/', 'it-course.html' => '/courses/', 'camp.html' => '/camps/', 'city.html' => '/',
	);

	public static function register(): void {
		add_action( 'init', array( self::class, 'rewriteRules' ), 20 );
		add_filter( 'query_vars', array( self::class, 'queryVars' ) );
		add_filter( 'post_type_link', array( self::class, 'postTypeLink' ), 10, 2 );
		add_filter( 'post_link', array( self::class, 'postLink' ), 10, 2 );
		add_filter( 'redirect_canonical', array( self::class, 'redirectCanonical' ), 10, 2 );
		add_action( 'template_redirect', array( self::class, 'validateContextCity' ), 1 );
		add_action( 'template_redirect', array( self::class, 'redirectLegacy' ) );
	}

	public static function rewriteRules(): void {
		add_rewrite_rule( '^cities/([^/]+)/(.+)/?$', 'index.php?pagename=$matches[2]&logika_city=$matches[1]', 'top' );
		add_rewrite_rule( '^cities/([^/]+)/camps/([^/]+)/?$', 'index.php?post_type=camp&name=$matches[2]&logika_city=$matches[1]', 'top' );
		add_rewrite_rule( '^cities/([^/]+)/courses/([^/]+)/?$', 'index.php?post_type=course&name=$matches[2]&logika_city=$matches[1]', 'top' );
		add_rewrite_rule( '^cities/([^/]+)/media-center/([^/]+)/?$', 'index.php?post_type=post&name=$matches[2]&logika_city=$matches[1]', 'top' );
		add_rewrite_rule( '^media-center/([^/]+)/?$', 'index.php?post_type=post&name=$matches[1]', 'top' );
	}

	public static function queryVars( array $vars ): array {
		$vars[] = 'logika_city';

		return $vars;
	}

	public static function postTypeLink( string $url, WP_Post $post ): string {
		$base = array( 'course' => 'courses', 'camp' => 'camps', 'city' => 'cities' );

		return isset( $base[ $post->post_type ] ) ? home_url( '/' . $base[ $post->post_type ] . '/' . $post->post_name . '/' ) : $url;
	}

	public static function postLink( string $url, WP_Post $post ): string {
		return 'post' === $post->post_type ? home_url( '/media-center/' . $post->post_name . '/' ) : $url;
	}

	public static function redirectCanonical( $redirect, string $requested_url ) {
		return get_query_var( 'logika_city' ) ? false : $redirect;
	}

	public static function validateContextCity(): void {
		$slug = (string) get_query_var( 'logika_city' );
		$city = $slug ? get_page_by_path( sanitize_title_for_query( $slug ), OBJECT, 'city' ) : null;

		if ( ! $slug || $city instanceof WP_Post && 'publish' === $city->post_status ) {
			return;
		}

		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}

	public static function redirectLegacy(): void {
		$path = trim( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( isset( self::LEGACY_ROUTES[ $path ] ) ) {
			wp_safe_redirect( home_url( self::LEGACY_ROUTES[ $path ] ), 301 );
			exit;
		}

		if ( str_ends_with( $path, '.html' ) ) {
			$post = get_page_by_path( substr( $path, 0, -5 ), OBJECT, 'post' );
			if ( $post instanceof WP_Post ) {
				wp_safe_redirect( get_permalink( $post ), 301 );
				exit;
			}
		}
	}
}
