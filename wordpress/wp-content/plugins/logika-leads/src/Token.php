<?php

declare(strict_types=1);

final class Logika_Leads_Form_Tokens {
	private const TTL = 900;

	public static function issue( string $form_id ): string {
		$token = bin2hex( random_bytes( 32 ) );
		set_transient( self::key( $token ), array( 'form_id' => $form_id, 'fingerprint' => self::fingerprint() ), self::TTL );

		return $token;
	}

	public static function verify( string $token, string $form_id ): bool {
		$data = get_transient( self::key( $token ) );

		return is_array( $data )
			&& hash_equals( (string) ( $data['form_id'] ?? '' ), $form_id );
	}

	private static function key( string $token ): string {
		return 'logika_lead_token_' . hash( 'sha256', $token );
	}

	private static function fingerprint(): string {
		return hash( 'sha256', (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) . '|' . (string) ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
	}
}
