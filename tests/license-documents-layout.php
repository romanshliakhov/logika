<?php

declare(strict_types=1);

$css = file_get_contents( dirname( __DIR__ ) . '/wordpress/wp-content/themes/logika-theme/assets/css/style.css' ) ?: '';

if ( ! str_contains( $css, '.license-documents{display:grid;grid-template-columns:1fr;' ) || ! str_contains( $css, '.license-documents img{display:block;width:100%;height:auto;margin:0 auto}' ) ) {
	fwrite( STDERR, "License documents are not centered in one readable column.\n" );
	exit( 1 );
}

echo "License documents use a centered one-column layout.\n";
