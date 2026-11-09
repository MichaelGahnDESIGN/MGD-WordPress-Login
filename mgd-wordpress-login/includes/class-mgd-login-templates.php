<?php
defined( 'ABSPATH' ) || exit;

/**
 * Template-Register. Free Templates sind fest eingebaut.
 * Pro Templates kommen später per Filter 'mgd_login_templates' (eigenes Add-on)
 * und werden erst mit Filter 'mgd_login_pro_unlocked' freigeschaltet.
 */
final class MGD_Login_Templates {
	const SHOP_URL = 'https://theme.michael-gahn.de';

	public static function all(): array {
		$base = array(
			'aurora'    => array(
				'name' => 'Aurora', 'tags' => array( 'Dunkel', 'Animiert' ),
				'desc' => 'Leuchtende Farbwolken schweben hinter einer Milchglas-Karte.',
				'vars' => array( 'bg1' => '#0a0d1f', 'bg2' => '#1a1147', 'accent' => '#8b6cff', 'card' => '#12162e', 'text' => '#f4f5ff', 'card_opacity' => 62, 'radius' => 20, 'blur' => 22, 'card_width' => 400, 'overlay' => 0 ),
			),
			'glass'     => array(
				'name' => 'Milchglas', 'tags' => array( 'Hell', 'Animiert' ),
				'desc' => 'Bunte Kugeln treiben hinter einer hellen Glasscheibe.',
				'vars' => array( 'bg1' => '#dbe7ff', 'bg2' => '#fbe0ff', 'accent' => '#4f5dff', 'card' => '#ffffff', 'text' => '#1b2140', 'card_opacity' => 58, 'radius' => 26, 'blur' => 26, 'card_width' => 400, 'overlay' => 0 ),
			),
			'neon'      => array(
				'name' => 'Neon Grid', 'tags' => array( 'Dunkel', 'Animiert', 'Auffällig' ),
				'desc' => 'Retro-Raster im Dauerlauf und eine pulsierende Neon-Karte.',
				'vars' => array( 'bg1' => '#06050f', 'bg2' => '#16062e', 'accent' => '#19f0ff', 'card' => '#0b0a1c', 'text' => '#eafcff', 'card_opacity' => 78, 'radius' => 10, 'blur' => 10, 'card_width' => 400, 'overlay' => 0 ),
			),
			'minimal'   => array(
				'name' => 'Minimal Clean', 'tags' => array( 'Hell', 'Ruhig' ),
				'desc' => 'Aufgeräumt, weiches Licht, dezente Bewegung. Passt zu jeder Marke.',
				'vars' => array( 'bg1' => '#f3f5f8', 'bg2' => '#e6ebf2', 'accent' => '#1f5d50', 'card' => '#ffffff', 'text' => '#1b1f23', 'card_opacity' => 100, 'radius' => 14, 'blur' => 0, 'card_width' => 400, 'overlay' => 0 ),
			),
			'split'     => array(
				'name' => 'Split Screen', 'tags' => array( 'Zweigeteilt', 'Animiert' ),
				'desc' => 'Links Markenfläche mit Bewegung, rechts das Formular.',
				'vars' => array( 'bg1' => '#143d37', 'bg2' => '#2c7a68', 'accent' => '#1f6f5c', 'card' => '#ffffff', 'text' => '#17201e', 'card_opacity' => 100, 'radius' => 0, 'blur' => 0, 'card_width' => 380, 'overlay' => 0 ),
			),
			'particles' => array(
				'name' => 'Partikel-Netz', 'tags' => array( 'Dunkel', 'Interaktiv' ),
				'desc' => 'Ein lebendiges Punktenetz, das auf die Maus reagiert.',
				'vars' => array( 'bg1' => '#050a14', 'bg2' => '#0d1b38', 'accent' => '#4cc9f0', 'card' => '#0c1424', 'text' => '#eaf6ff', 'card_opacity' => 80, 'radius' => 16, 'blur' => 14, 'card_width' => 400, 'overlay' => 0 ),
			),
			'sunset'    => array(
				'name' => 'Sunset', 'tags' => array( 'Warm', 'Animiert' ),
				'desc' => 'Ein fließender Farbverlauf mit einer langsam atmenden Sonne.',
				'vars' => array( 'bg1' => '#ff8a5c', 'bg2' => '#6a2fd6', 'accent' => '#ffb347', 'card' => '#2a0f3d', 'text' => '#fff6ee', 'card_opacity' => 58, 'radius' => 22, 'blur' => 20, 'card_width' => 400, 'overlay' => 0 ),
			),
		);

		$templates = array();
		foreach ( $base as $id => $tpl ) {
			$templates[ $id ] = $tpl + array(
				'tier' => 'free',
				'css'  => MGD_LOGIN_URL . 'assets/templates/' . $id . '.css',
				'js'   => 'particles' === $id ? MGD_LOGIN_URL . 'assets/js/particles.js' : '',
				'ver'  => MGD_LOGIN_VERSION,
			);
		}

		// Add-on darf eigene Templates ergänzen. Nur gültige Einträge bleiben.
		$extra = apply_filters( 'mgd_login_templates', array() );
		if ( is_array( $extra ) ) {
			foreach ( $extra as $id => $tpl ) {
				$id = sanitize_key( (string) $id );
				if ( '' === $id || isset( $templates[ $id ] ) || ! is_array( $tpl ) || empty( $tpl['name'] ) || empty( $tpl['vars'] ) || empty( $tpl['css'] ) ) {
					continue;
				}
				$templates[ $id ] = $tpl + array( 'tags' => array(), 'desc' => '', 'tier' => 'pro', 'js' => '', 'ver' => MGD_LOGIN_VERSION );
			}
		}
		return $templates;
	}

	/** Pro Vorschau-Karten, noch nicht wählbar */
	public static function teasers(): array {
		$have = self::all();
		$all  = array(
			'waves'    => array( 'name' => 'Wellen 3D', 'desc' => 'Tiefe Wellen mit Licht-Reflexen.', 'colors' => array( '#052a4d', '#0e7490', '#67e8f9' ) ),
			'terminal' => array( 'name' => 'Retro Terminal', 'desc' => 'Grüner Bildschirm mit Tipp-Effekt.', 'colors' => array( '#020a04', '#063b12', '#3cff7a' ) ),
			'parallax' => array( 'name' => 'Parallax Skyline', 'desc' => 'Mehrere Ebenen, die der Maus folgen.', 'colors' => array( '#1b1033', '#5a2a82', '#ff9ec7' ) ),
		);
		return array_diff_key( $all, $have );
	}

	public static function exists( string $id ): bool {
		return isset( self::all()[ $id ] );
	}

	public static function pro_unlocked(): bool {
		return (bool) apply_filters( 'mgd_login_pro_unlocked', false );
	}

	/** Pro nur wenn freigeschaltet, sonst zurück auf Aurora */
	public static function usable( string $id ): string {
		$all = self::all();
		if ( ! isset( $all[ $id ] ) ) {
			return 'aurora';
		}
		if ( 'pro' === ( $all[ $id ]['tier'] ?? 'free' ) && ! self::pro_unlocked() ) {
			return 'aurora';
		}
		return $id;
	}

	public static function get( string $id ): array {
		$all = self::all();
		return $all[ self::usable( $id ) ];
	}
}
