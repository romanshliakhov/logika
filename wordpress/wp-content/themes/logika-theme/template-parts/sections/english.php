<?php

declare(strict_types=1);

$page_id = get_option( 'page_on_front' );
$title = get_field( 'home_english_title', $page_id ) ?: 'Англійська мова в Logika';
$text = get_field( 'home_english_text', $page_id ) ?: 'Навчаємо говорити англійською впевнено з перших занять — через практику, живе спілкування та сучасні методики.';
$url = get_field( 'home_english_cta_url', $page_id ) ?: home_url( '/english-courses/' );
$levels = (array) get_field( 'home_english_levels', $page_id );
$levels = $levels ?: array(
	array( 'age' => '8-10 років', 'title' => 'Рівень A0', 'text' => 'Перші слова, фрази та знайомство з англійською', 'image' => 0 ),
	array( 'age' => '10-12 років', 'title' => 'Рівень A1', 'text' => 'Базове спілкування та щоденні теми', 'image' => 0 ),
);
$assets = get_template_directory_uri() . '/assets/img/english-courses/';
?>
<section class="english-section"><div class="container"><div class="english-section__wrapp"><div class="english-section__left"><div class="english-section__content"><div class="english-section__box"><h2 class="english-section__title"><?php echo esc_html( $title ); ?></h2></div><p class="english-section__text"><?php echo esc_html( $text ); ?></p><a href="<?php echo esc_url( $url ); ?>" class="english-section__link btn btn--yellow">Дізнатись більше</a></div></div><div class="english-section__right"><div class="english-section__slider"><div class="swiper-container"><ul class="swiper-wrapper"><?php foreach ( $levels as $level ) : ?><?php $image = ! empty( $level['image'] ) ? wp_get_attachment_image_url( $level['image'], 'medium' ) : $assets . 'A0.svg'; ?><li class="swiper-slide"><div class="english-level"><div class="english-level__ages"><?php echo esc_html( $level['age'] ?? '' ); ?></div><div class="english-level__image"><img width="283" height="258" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $level['title'] ?? '' ); ?>"></div><div class="english-level__info"><span class="h4"><?php echo esc_html( $level['title'] ?? '' ); ?></span><p><?php echo esc_html( $level['text'] ?? '' ); ?></p></div><a href="<?php echo esc_url( $url ); ?>" class="english-level__link btn btn--green">Обрати курс</a></div></li><?php endforeach; ?></ul></div></div></div></div></div></section>
