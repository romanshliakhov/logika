<?php

declare(strict_types=1);

$root     = dirname(__DIR__);
$archive  = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_camp_archive.json' );
$camp     = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/acf-json/group_logika_camp.json' );
$renderer = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/src/PageContent.php' );
$modal    = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/template-parts/components/camp-modal.php' );
$page     = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/source-pages/camps.php' );
$camp_page = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/source-pages/camp.php' );
$gallery_css = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/gallery-section.css' );
$main_js = (string) file_get_contents( $root . '/wordpress/wp-content/themes/logika-theme/assets/js/main.js' );
$errors   = array();

foreach ( array( 'camp_archive_formats', 'camp_card_dates', 'camp_hero_images', 'camp_details', 'camp_gallery' ) as $field ) {
	if ( ! str_contains( $archive . $camp, '"name": "' . $field . '"' ) ) {
		$errors[] = "Tilda summer camps require {$field} in ACF.";
	}
}

foreach ( array( "get_field( 'camp_archive_formats'", 'camp_card_dates', 'camp_card_description', 'get_permalink( $camp_id )' ) as $marker ) {
	if ( ! str_contains( $modal, $marker ) ) {
		$errors[] = "Camp modal is missing {$marker}.";
	}
}

foreach ( array( 'isTildaCamp', 'applyCampDetailGalleries', 'applyCampExtraSections' ) as $method ) {
	if ( ! str_contains( $renderer, 'function ' . $method ) ) {
		$errors[] = "Camp renderer is missing {$method}.";
	}
}

if ( ! str_contains( $camp_page, 'class="camp-extra"' ) ) {
	$errors[] = 'Camp source must retain camp-extra for non-Tilda camps.';
}

if ( ! str_contains( $gallery_css, 'grid-template-columns: repeat(4, minmax(0, 1fr))' ) || str_contains( $main_js, 'gallerySectionSlider' ) ) {
	$errors[] = 'Galleries must use the shared responsive grid instead of Swiper.';
}

foreach ( array( 'camp-formats__item-season', 'Літо', 'Осінь', 'Зима', 'Весна', 'data-path="camps"' ) as $marker ) {
	if ( ! str_contains( $page, $marker ) ) {
		$errors[] = "Camp season selector is missing {$marker}.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

require $root . '/wordpress/wp-load.php';

$slugs = array(
	'greece-2026'      => '5–14.07 · 10 днів',
	'emily-resort-2026' => '20–27.06 · 8 днів',
	'carpathians-2026' => '27.06–06.07 · 21.07–30.07',
	'city-camps-2026'  => 'Дати — у менеджера',
);
$ids   = array();
foreach ( $slugs as $slug => $dates ) {
	$post = get_page_by_path( $slug, OBJECT, 'camp' );
	if ( ! $post || ! get_field( 'camp_card_image', $post->ID ) || ! get_field( 'camp_gallery', $post->ID ) ) {
		$errors[] = "Tilda camp {$slug} is missing imported editable media.";
		continue;
	}
	if ( $dates !== (string) get_field( 'camp_card_dates', $post->ID ) ) {
		$errors[] = "Tilda camp {$slug} must use its compact modal date label.";
	}
	$output = Logika_Theme_Page_Content::apply( $camp_page, 'camp', $post->ID );
	if ( ! str_contains( $output, 'href="#lead-form" data-logika-camp-booking data-logika-camp-id="' . $post->ID . '"' ) ) {
		$errors[] = "Tilda camp {$slug} detail CTAs must open the shared lead modal with camp context.";
	}
	$hero   = array_values( array_filter( array_map( 'absint', (array) get_field( 'camp_hero_images', $post->ID ) ) ) );
	$facts  = array_values( array_filter( (array) get_field( 'camp_hero_facts', $post->ID ), 'is_array' ) );
	$activities = array_values( array_filter( (array) get_field( 'camp_activities', $post->ID ), 'is_array' ) );
	$details = array_values( array_filter( (array) get_field( 'camp_details', $post->ID ), 'is_array' ) );
	$gallery = array_values( array_filter( array_map( 'absint', (array) get_field( 'camp_gallery', $post->ID ) ) ) );
	$faq = array_values( array_filter( array_map( 'absint', (array) get_field( 'camp_related_faq', $post->ID ) ) ) );
	if ( 6 !== count( $facts ) ) {
		$errors[] = "Tilda camp {$slug} must have six editable hero facts.";
	}
	foreach ( $activities as $activity ) {
		if ( empty( $activity['title'] ) || ! str_contains( $output, (string) $activity['title'] ) ) {
			$errors[] = "Tilda camp {$slug} must render its editable activity names.";
		}
	}
	if ( 3 !== count( $faq ) ) {
		$errors[] = "Tilda camp {$slug} must have three editable camp FAQ items.";
	}
	foreach ( $faq as $faq_id ) {
		if ( ! get_field( 'faq_question', $faq_id ) || ! get_field( 'faq_answer', $faq_id ) || ! str_contains( $output, (string) get_field( 'faq_question', $faq_id ) ) ) {
			$errors[] = "Tilda camp {$slug} must render its editable FAQ items.";
		}
	}
	if ( array_filter( $gallery, static fn( int $image ): bool => ! in_array( get_post_mime_type( $image ), array( 'image/jpeg', 'image/webp' ), true ) ) ) {
		$errors[] = "Tilda camp {$slug} gallery must not include Tilda decorations.";
	}
	if ( 'greece-2026' === $slug && 13 !== count( $gallery ) ) {
		$errors[] = 'Greece camp gallery must include only the 13 imported photos.';
	}
	$detail_gallery = array_values( array_filter( array_map( 'absint', (array) ( $details[0]['gallery'] ?? array() ) ) ) );
	$urls = array_filter( array(
		$hero ? wp_get_attachment_image_url( $hero[0], 'large' ) : false,
		$detail_gallery ? wp_get_attachment_image_url( $detail_gallery[0], 'large' ) : false,
		$gallery ? wp_get_attachment_image_url( $gallery[0], 'large' ) : false,
	) );
	if ( str_contains( $output, 'class="camp-extra"' ) || ! str_contains( $output, '10 днів цікавої програми' ) || ! str_contains( $output, (string) get_field( 'camp_hero_text', $post->ID ) ) ) {
		$errors[] = "Tilda camp {$slug} must use the base body and its hero text.";
	}
	foreach ( $urls as $url ) {
		if ( ! str_contains( $output, $url ) ) {
			$errors[] = "Tilda camp {$slug} is missing a retained gallery image.";
		}
	}
	$ids[] = (int) $post->ID;
}

if ( $ids !== array_values( array_map( 'intval', (array) get_field( 'camp_archive_formats', 'camp_archive' ) ) ) ) {
	$errors[] = 'Camp archive must keep the four Tilda formats in source order.';
}

$legacy = get_page_by_path( 'winter-camp', OBJECT, 'camp' );
if ( ! $legacy || ! str_contains( Logika_Theme_Page_Content::apply( $camp_page, 'camp', $legacy->ID ), '<h2 class="trips-section__title">Виїзні екскурсії</h2>' ) ) {
	$errors[] = 'Non-Tilda camps must keep their existing ACF body rendering.';
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Tilda summer camp formats contract is valid.\n";
