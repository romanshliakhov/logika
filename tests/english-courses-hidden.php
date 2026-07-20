<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

$page = get_page_by_path( 'english-courses' );
$course = get_page_by_path( 'english-b2-1', OBJECT, 'course' );
$original = $page instanceof WP_Post ? get_field( 'english_courses_featured_courses', $page->ID ) : array();

if ( ! $page instanceof WP_Post || ! $course instanceof WP_Post ) {
	fwrite( STDERR, "English courses fixture is missing.\n" );
	exit( 1 );
}

try {
	update_field( 'english_courses_featured_courses', array( $course->ID ), $page->ID );
	ob_start();
	Logika_Theme_Fixed_Page::render( 'en-courses', $page->ID );
	$markup = (string) ob_get_clean();
	if ( str_contains( $markup, 'Рівень B2.1' ) ) {
		fwrite( STDERR, "Unavailable B2.1 course is visible on the English courses page.\n" );
		exit( 1 );
	}
} finally {
	update_field( 'english_courses_featured_courses', $original, $page->ID );
}

echo "Unavailable English course is hidden.\n";
