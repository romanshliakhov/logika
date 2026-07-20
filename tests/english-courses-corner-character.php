<?php

declare(strict_types=1);

$theme = dirname( __DIR__ ) . '/wordpress/wp-content/themes/logika-theme';
$template = file_get_contents( $theme . '/template-parts/courses/english.php' ) ?: '';
$css = file_get_contents( $theme . '/assets/css/english-course.css' ) ?: '';

if ( ! is_file( $theme . '/assets/img/english-levels/characters/hero-corner-character.svg' ) || ! str_contains( $template, 'english-course-hero__corner-character' ) || ! str_contains( $css, '.english-course-hero__corner-character' ) ) {
	fwrite( STDERR, "English courses corner character is missing.\n" );
	exit( 1 );
}

echo "English courses corner character is present.\n";
