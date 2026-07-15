<?php

declare(strict_types=1);

namespace Logika\Core;

final class AcfJson {
	public static function register(): void {
		add_filter( 'acf/settings/save_json', array( self::class, 'savePath' ) );
		add_filter( 'acf/settings/load_json', array( self::class, 'loadPaths' ) );
	}

	public static function savePath( string $path ): string {
		return LOGIKA_CORE_PATH . 'acf-json';
	}

	/**
	 * @param string[] $paths Existing ACF Local JSON directories.
	 * @return string[]
	 */
	public static function loadPaths( array $paths ): array {
		$paths[] = LOGIKA_CORE_PATH . 'acf-json';

		return array_unique( $paths );
	}
}
