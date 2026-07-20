<?php

declare(strict_types=1);

$css = file_get_contents( dirname( __DIR__ ) . '/wordpress/wp-content/themes/logika-theme/assets/css/adaptive.css' ) ?: '';

if ( ! str_contains( $css, '.article-section__headings{position:sticky;top:calc(var(--header-height) + 20px)}' ) ) {
	fwrite( STDERR, "Article sidebar does not clear the sticky header.\n" );
	exit( 1 );
}

echo "Article sidebar clears the sticky header.\n";
