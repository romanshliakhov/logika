<?php

declare(strict_types=1);

$root     = dirname(__DIR__);
$page     = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/source-pages/index.php' );
$styles   = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/media-section.css' );
$functions = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/functions.php' );
$background = $root . '/wordpress/wp-content/themes/logika-theme/assets/img/media-center/why-logika/background.png';
$shared_pages = array(
	(string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/source-pages/about.php' ),
	(string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/source-pages/media-center.php' ),
);
$errors   = array();

foreach ( array( 'why-logika-section', 'Єдина платформа – єдина якість', 'свої проєкти' ) as $marker ) {
	if ( ! str_contains( $page, $marker ) ) {
		$errors[] = "Why Logika markup is missing {$marker}.";
	}
}

foreach ( array( 'aspect-ratio: 345/307', 'border-radius: 30px', '.why-logika-section {', 'padding-top: clamp(16px, 1.563vw, 24px)', '.why-logika-section .media-section__wrapp {', 'gap: clamp(110px, 9.375vw, 135px)', '.media-section__card--platform .media-section__card-img', 'width: 78.893%', 'top: -43.721%', '@media (max-width: 576px)', 'gap: 128px' ) as $marker ) {
	if ( ! str_contains( $styles, $marker ) ) {
		$errors[] = "Why Logika styles are missing {$marker}.";
	}
}

if ( ! file_exists( $background ) || ! str_contains( $styles, 'background.png' ) ) {
	$errors[] = 'Why Logika background decoration is missing.';
}

foreach ( $shared_pages as $shared_page ) {
	if ( ! str_contains( $shared_page, 'media-section why-logika-section' ) ) {
		$errors[] = 'Why Logika section is not shared outside the homepage.';
	}
}

foreach ( array( "is_page( array( 'about', 'media-center' ) )", 'logika-home-media-center' ) as $marker ) {
	if ( ! str_contains( $functions, $marker ) ) {
		$errors[] = "Why Logika stylesheet is not shared: {$marker}.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Why Logika cards keep their Figma-sized layout and decoration.\n";
