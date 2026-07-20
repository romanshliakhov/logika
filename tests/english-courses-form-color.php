<?php

declare(strict_types=1);

$css = file_get_contents( dirname( __DIR__ ) . '/wordpress/wp-content/themes/logika-theme/assets/css/style.css' ) ?: '';

if ( ! str_contains( $css, '.page-id-988 .banner-section__form{background-color:#374c92}' ) || ! str_contains( $css, '.page-id-988 .banner-section__info h4{color:#374c92}' ) ) {
	fwrite( STDERR, "English courses hero does not use Dust Blue.\n" );
	exit( 1 );
}

echo "English courses form uses Dust Blue.\n";
