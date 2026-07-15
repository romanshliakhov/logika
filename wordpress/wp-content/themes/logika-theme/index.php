<?php

declare(strict_types=1);

get_header();
logika_theme_render_source_page( is_singular( 'post' ) ? 'article' : 'media-center' );
get_footer();
