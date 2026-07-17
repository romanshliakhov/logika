<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$archive  = file_get_contents( get_template_directory() . '/archive-camp.php' ) ?: '';
$renderer = file_get_contents( get_template_directory() . '/src/CampArchive.php' ) ?: '';
$page     = file_get_contents( get_template_directory() . '/source-pages/camps.php' ) ?: '';
$functions = file_get_contents( get_template_directory() . '/functions.php' ) ?: '';

foreach ( array( 'Logika_Theme_Camp_Archive::render', "renderPage( 'camps' )", 'camp-highlights', 'camp-formats', 'camp-history', 'camp-booking' ) as $marker ) {
	if ( ! str_contains( $archive . $renderer . $page, $marker ) ) {
		fwrite( STDERR, "The full camps page is missing {$marker}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'camp-highlights__slider', 'camp-highlights__pattern', 'img/camps/camps1.svg', 'camp-formats__item-image' ) as $marker ) {
	if ( ! str_contains( $page, $marker ) ) {
		fwrite( STDERR, "The WordPress camps source is missing {$marker}.\n" );
		exit( 1 );
	}
}

foreach ( array( 'camp-booking', 'camp-formats', 'camp-highlights', 'camp-page-hero' ) as $section ) {
	if ( ! str_contains( $functions, "'{$section}'" ) ) {
		fwrite( STDERR, "The WordPress camps styles do not enqueue {$section}.\n" );
		exit( 1 );
	}
}

echo "Camps page keeps the full main HTML structure.\n";
