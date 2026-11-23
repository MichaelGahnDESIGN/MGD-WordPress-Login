<?php
defined( 'ABSPATH' ) || exit;

/** Alle Einstellungen in EINER Option. Leer = Template-Standard. */
final class MGD_Login_Settings {
	const OPTION = 'mgd_login_settings';

	// Entwurf aus der Live-Vorschau, wird nie gespeichert
	private static ?array $override = null;

	public static function fonts(): array {
		return array(
			'system'  => array( 'System (modern)', 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", sans-serif' ),
			'rounded' => array( 'Rund & freundlich', 'ui-rounded, "SF Pro Rounded", "Hiragino Maru Gothic ProN", "Segoe UI", sans-serif' ),
			'serif'   => array( 'Serif (elegant)', 'Georgia, "Times New Roman", serif' ),
			'mono'    => array( 'Mono (Technik)', 'ui-monospace, SFMono-Regular, Menlo, Consolas, monospace' ),
		);
	}

	// Hintergrundbild Anzeige-Art: Name > Label, size, repeat
	public static function bg_modes(): array {
		return array(
			'cover'    => array( 'Ausschnitt (füllt alles, Rand wird abgeschnitten)', 'cover', 'no-repeat' ),
			'contain'  => array( 'Ganz zeigen (kleiner, ohne Abschneiden)', 'contain', 'no-repeat' ),
			'stretch'  => array( 'Strecken (über den ganzen Platz, verzerrt)', '100% 100%', 'no-repeat' ),
			'repeat'   => array( 'Wiederholen (Kachel-Muster)', 'auto', 'repeat' ),
			'original' => array( 'Originalgröße (einmal)', 'auto', 'no-repeat' ),
		);
	}

	// Hintergrundbild Ausrichtung: Name > Label, CSS-Position
	public static function bg_positions(): array {
		return array(
			'center'       => array( 'Mitte', 'center center' ),
			'top'          => array( 'Oben', 'center top' ),
			'bottom'       => array( 'Unten', 'center bottom' ),
			'left'         => array( 'Links', 'left center' ),
			'right'        => array( 'Rechts', 'right center' ),
			'top-left'     => array( 'Oben links', 'left top' ),
			'top-right'    => array( 'Oben rechts', 'right top' ),
			'bottom-left'  => array( 'Unten links', 'left bottom' ),
			'bottom-right' => array( 'Unten rechts', 'right bottom' ),
		);
	}

	public static function defaults(): array {
		return array(
			'template'     => 'aurora',
			'bg1'          => '',
			'bg2'          => '',
			'accent'       => '',
			'card'         => '',
			'text'         => '',
			'card_opacity' => '',
			'radius'       => '',
			'blur'         => '',
			'card_width'   => '',
			'overlay'      => '',
			'logo_id'      => 0,
			'logo_height'  => 80,
			'logo_mode'    => 'name',
			'bg_id'        => 0,
			'bg_mode'      => 'cover',
			'bg_pos'       => 'center',
			'font'         => 'system',
			'title'        => '',
			'subtitle'     => '',
			'footer'       => '',
			'hide_back'    => 0,
			'animation'    => 1,
			'speed'        => 100,
			'particles'    => '',
			'particle_amount' => 100,
			'light'        => '',
			'card_fx'      => '',
			'position'     => '',
			'custom_css'   => '',
			'delete_data'  => 0,
		);
	}

	public static function get(): array {
		$saved = self::$override ?? get_option( self::OPTION, array() );
		return array_merge( self::defaults(), is_array( $saved ) ? $saved : array() );
	}

	public static function set_override( array $raw ): void {
		self::$override = self::sanitize( $raw );
	}

	/** Zahl in Grenzen, leer bleibt leer (= Template-Standard) */
	private static function num( array $raw, string $key, int $min, int $max ): int|string {
		if ( ! isset( $raw[ $key ] ) || '' === trim( (string) $raw[ $key ] ) || ! is_numeric( $raw[ $key ] ) ) {
			return '';
		}
		return max( $min, min( $max, (int) $raw[ $key ] ) );
	}

	private static function image_id( mixed $value ): int {
		$id = absint( $value );
		return ( $id && wp_attachment_is_image( $id ) ) ? $id : 0;
	}

	public static function sanitize( mixed $raw ): array {
		$raw      = is_array( $raw ) ? $raw : array();
		$defaults = self::defaults();
		$out      = $defaults;

		$template        = sanitize_key( (string) ( $raw['template'] ?? '' ) );
		$out['template'] = MGD_Login_Templates::exists( $template ) ? $template : $defaults['template'];

		foreach ( array( 'bg1', 'bg2', 'accent', 'card', 'text' ) as $key ) {
			$out[ $key ] = (string) sanitize_hex_color( (string) ( $raw[ $key ] ?? '' ) );
		}

		$out['card_opacity'] = self::num( $raw, 'card_opacity', 10, 100 );
		$out['radius']       = self::num( $raw, 'radius', 0, 48 );
		$out['blur']         = self::num( $raw, 'blur', 0, 40 );
		$out['card_width']   = self::num( $raw, 'card_width', 300, 560 );
		$out['overlay']      = self::num( $raw, 'overlay', 0, 90 );

		$out['logo_id']     = self::image_id( $raw['logo_id'] ?? 0 );
		$out['bg_id']       = self::image_id( $raw['bg_id'] ?? 0 );
		$bg_mode            = (string) ( $raw['bg_mode'] ?? 'cover' );
		$out['bg_mode']     = isset( self::bg_modes()[ $bg_mode ] ) ? $bg_mode : 'cover';
		$bg_pos             = (string) ( $raw['bg_pos'] ?? 'center' );
		$out['bg_pos']      = isset( self::bg_positions()[ $bg_pos ] ) ? $bg_pos : 'center';
		$height             = self::num( $raw, 'logo_height', 24, 200 );
		$out['logo_height'] = '' === $height ? 80 : $height;
		$mode               = (string) ( $raw['logo_mode'] ?? 'name' );
		$out['logo_mode']   = in_array( $mode, array( 'name', 'wp', 'none' ), true ) ? $mode : 'name';

		$font        = (string) ( $raw['font'] ?? 'system' );
		$out['font'] = isset( self::fonts()[ $font ] ) ? $font : 'system';

		$out['title']    = sanitize_text_field( (string) ( $raw['title'] ?? '' ) );
		$out['subtitle'] = sanitize_text_field( (string) ( $raw['subtitle'] ?? '' ) );
		$out['footer']   = wp_kses(
			(string) ( $raw['footer'] ?? '' ),
			array(
				'a'      => array( 'href' => true, 'target' => true, 'rel' => true ),
				'strong' => array(),
				'em'     => array(),
				'br'     => array(),
			)
		);
		$out['footer']   = mb_substr( $out['footer'], 0, 400 );

		$out['hide_back']   = empty( $raw['hide_back'] ) ? 0 : 1;
		$out['animation']   = empty( $raw['animation'] ) ? 0 : 1;
		$out['delete_data'] = empty( $raw['delete_data'] ) ? 0 : 1;

		$speed        = self::num( $raw, 'speed', 25, 300 );
		$out['speed'] = '' === $speed ? 100 : $speed;

		// Effekte, leer = wie das Layout
		foreach ( MGD_Login_Templates::fx_choices() as $key => $choices ) {
			$val         = (string) ( $raw[ $key ] ?? '' );
			$out[ $key ] = isset( $choices[ $val ] ) ? $val : '';
		}
		$amount                 = self::num( $raw, 'particle_amount', 10, 200 );
		$out['particle_amount'] = '' === $amount ? 100 : $amount;

		$out['custom_css'] = self::clean_css( (string) ( $raw['custom_css'] ?? '' ) );
		return $out;
	}

	/** Eigenes CSS: keine Tags, kein Import, keine Skript-Tricks */
	public static function clean_css( string $css ): string {
		$css = wp_strip_all_tags( $css );
		$css = preg_replace( '/@import[^;]*;?/i', '', $css );
		$css = preg_replace( '/(expression\s*\(|javascript\s*:|behavior\s*:|-moz-binding)/i', '', (string) $css );
		return mb_substr( trim( (string) $css ), 0, 8000 );
	}
}
