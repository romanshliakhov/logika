<?php

declare(strict_types=1);

get_header();
?>
<main><?php
$camps = get_posts( array( 'post_type' => 'camp', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
foreach ( $camps as $camp ) {
	echo Logika_Theme_Camp_Page::render( $camp->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
?></main>
<?php
get_footer();
