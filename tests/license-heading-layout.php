<?php

declare(strict_types=1);

$page_content = file_get_contents( dirname( __DIR__ ) . '/wordpress/wp-content/themes/logika-theme/src/PageContent.php' ) ?: '';

if ( ! str_contains( $page_content, "'litsenziia' === \$source ? '<h2 class=\"license-title\">'" ) ) {
	fwrite( STDERR, "License heading is not styled by the rendered legal-page path.\n" );
	exit( 1 );
}

echo "License heading receives its page-specific class in rendered markup.\n";
