<?php

declare(strict_types=1);

get_header();

while ( have_posts() ) :
	the_post();
	$city_id = get_the_ID();
	?>
	<main>
		<section class="banner-section"><div class="container"><div class="banner-section__wrapp"><div class="banner-section__info"><h1><?php the_title(); ?></h1><?php if ( get_field( 'city_intro' ) ) : ?><p><?php echo esc_html( get_field( 'city_intro' ) ); ?></p><?php endif; ?></div></div></div></section>
		<?php echo Logika_Theme_City_Page::render_branches( $city_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php echo Logika_Theme_City_Page::render_related( $city_id, 'city_related_courses', 'Курси у місті' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php echo Logika_Theme_City_Page::render_related( $city_id, 'city_related_camps', 'Табори у місті' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php echo Logika_Theme_City_Page::render_reviews( $city_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php echo Logika_Theme_City_Page::render_faq( $city_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<section class="cta-section"><div class="container"><?php get_template_part( 'template-parts/forms/lead', null, array( 'city_id' => $city_id ) ); ?></div></section>
		<?php if ( get_field( 'city_seo_text' ) ) : ?><section class="media-section"><div class="container"><div class="editor"><?php echo wp_kses_post( get_field( 'city_seo_text' ) ); ?></div></div></section><?php endif; ?>
	</main>
	<?php
endwhile;

get_footer();
