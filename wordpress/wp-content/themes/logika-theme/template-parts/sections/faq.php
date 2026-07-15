<?php

declare(strict_types=1);

$faq = new WP_Query(
	array(
		'post_type'      => 'faq_item',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'     => 'faq_is_active',
				'value'   => '1',
				'compare' => '=',
			),
		),
	)
);
?>
<section class="faq-section">
	<div class="container">
		<div class="faq-section__wrapp">
			<h2>Питання та відповіді</h2>
			<?php if ( $faq->have_posts() ) : ?>
				<ul class="accordion" data-default="1" data-single="true" data-breakpoint="576" data-accordion-init>
					<?php while ( $faq->have_posts() ) : $faq->the_post(); ?>
						<?php $id = 'faq-' . get_the_ID(); ?>
						<li class="accordion__item">
							<button class="accordion__btn h5" data-id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( get_field( 'faq_question' ) ?: get_the_title() ); ?></button>
							<div class="accordion__content" data-content="<?php echo esc_attr( $id ); ?>"><div class="editor"><?php echo wp_kses_post( get_field( 'faq_answer' ) ); ?></div></div>
						</li>
					<?php endwhile; ?>
				</ul>
			<?php else : ?>
				<p><?php esc_html_e( 'Відповіді на поширені запитання незабаром з’являться тут.', 'logika-theme' ); ?></p>
			<?php endif; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
