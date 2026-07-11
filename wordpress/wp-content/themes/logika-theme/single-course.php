<?php

declare(strict_types=1);

get_header();

while ( have_posts() ) {
	the_post();
	echo Logika_Theme_Course_Page::render( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	get_template_part( 'template-parts/forms/lead', null, array( 'course_id' => get_the_ID() ) );
}

get_footer();
