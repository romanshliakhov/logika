<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$page = file_get_contents( get_template_directory() . '/source-pages/about.php' ) ?: '';
$css  = file_get_contents( get_template_directory() . '/assets/css/blocks/sections/about.css' ) ?: '';
$errors = array();

foreach ( array( 'about-stats', 'about-directions', 'about-outcomes', 'about-history', 'school-map', 'cta-section', 'faq-section' ) as $section ) {
	if ( ! str_contains( $page, $section ) ) {
		$errors[] = "The full about page is missing {$section}.";
	}
}

if ( 6 !== substr_count( $page, 'class="media-section__card media-section__card--' ) ) {
	$errors[] = 'About page is missing the six Why Logika cards.';
}

if ( str_contains( $css, '.about-outcomes h2 { width: auto; font-size: inherit;' ) || str_contains( $css, '.about-history__content h2 { color: inherit; font-size: inherit;' ) ) {
	$errors[] = 'About headings must keep their scoped typography.';
}

if ( ! str_contains( $page, 'img/about/anniversary-full.png' ) || ! is_file( get_template_directory() . '/assets/img/about/anniversary-full.png' ) ) {
	$errors[] = 'About history must use its tracked image asset.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "About page keeps the full main HTML structure.\n";
