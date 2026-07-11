<?php

declare(strict_types=1);

get_header();

while ( have_posts() ) {
	the_post();
	$camp_id = get_the_ID();
	echo Logika_Theme_Camp_Page::render( $camp_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	if ( ! get_field( 'camp_end_date', $camp_id ) || strtotime( get_field( 'camp_end_date', $camp_id ) . ' 23:59:59' ) >= current_time( 'timestamp' ) ) {
		get_template_part( 'template-parts/forms/lead', null, array( 'camp_id' => $camp_id ) );
	}
}

get_footer();
