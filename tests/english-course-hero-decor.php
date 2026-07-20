<?php

declare(strict_types=1);

$theme = dirname( __DIR__ ) . '/wordpress/wp-content/themes/logika-theme';
$template = file_get_contents( $theme . '/template-parts/courses/english.php' ) ?: '';
$css = file_get_contents( $theme . '/assets/css/english-course.css' ) ?: '';

if ( ! is_file( $theme . '/assets/img/english-course/hero-decor.png' ) || ! str_contains( $template, 'hero-decor.png' ) || ! str_contains( $css, 'left:65%;width:min(110%,1500px)' ) ) {
	fwrite( STDERR, "English course hero decor is missing.\n" );
	exit( 1 );
}

echo "English course hero decor is present.\n";
