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
		// fx = Effekt-Standard je Layout, im Backend einzeln überschreibbar
		$base = array(
			'aurora'    => array(
				'name' => 'Nordlicht', 'tags' => array( 'Dunkel', 'Sterne', 'Glanz' ),
				'desc' => 'Tanzende Polarlicht-Bänder, funkelnde Sterne und eine Milchglas-Karte mit Glanz.',
				'vars' => array( 'bg1' => '#050816', 'bg2' => '#0f1a3d', 'accent' => '#5ef2c2', 'card' => '#0d1430', 'text' => '#eefcff', 'card_opacity' => 58, 'radius' => 22, 'blur' => 22, 'card_width' => 400, 'overlay' => 0 ),
				'fx'   => array( 'particles' => 'stars', 'light' => 'off', 'card_fx' => 'shine', 'position' => 'center' ),
			),
			'glass'     => array(
				'name' => 'Milchglas', 'tags' => array( 'Hell', '3D', 'Blasen' ),
				'desc' => 'Glänzende 3D-Kugeln und Seifenblasen hinter einer hellen Glasscheibe, die sich zur Maus neigt.',
				'vars' => array( 'bg1' => '#dfe8ff', 'bg2' => '#fbe3ff', 'accent' => '#5146ff', 'card' => '#ffffff', 'text' => '#191d3a', 'card_opacity' => 55, 'radius' => 28, 'blur' => 26, 'card_width' => 400, 'overlay' => 0 ),
				'fx'   => array( 'particles' => 'bubbles', 'light' => 'off', 'card_fx' => 'tilt', 'position' => 'center' ),
			),
			'neon'      => array(
				'name' => 'Neon Grid', 'tags' => array( 'Dunkel', 'Retro', 'Leuchtrand' ),
				'desc' => 'Synthwave-Sonne über einem laufenden Raster. Ein Lichtstreifen kreist um die Karte.',
				'vars' => array( 'bg1' => '#07041a', 'bg2' => '#2a0845', 'accent' => '#ff3fd2', 'card' => '#0d0821', 'text' => '#fff0fb', 'card_opacity' => 80, 'radius' => 12, 'blur' => 10, 'card_width' => 400, 'overlay' => 0 ),
				'fx'   => array( 'particles' => 'none', 'light' => 'off', 'card_fx' => 'glow', 'position' => 'center' ),
			),
			'minimal'   => array(
				'name' => 'Minimal Clean', 'tags' => array( 'Hell', 'Ruhig', 'Lichtkegel' ),
				'desc' => 'Ruhiges Punktraster, ein weicher Lichtkegel folgt der Maus. Karte links, viel Luft.',
				'vars' => array( 'bg1' => '#f4f6f9', 'bg2' => '#e7ecf3', 'accent' => '#1f5d50', 'card' => '#ffffff', 'text' => '#1b1f23', 'card_opacity' => 100, 'radius' => 14, 'blur' => 0, 'card_width' => 380, 'overlay' => 0 ),
				'fx'   => array( 'particles' => 'none', 'light' => 'on', 'card_fx' => 'none', 'position' => 'left' ),
			),
			'split'     => array(
				'name' => 'Split Screen', 'tags' => array( 'Zweigeteilt', 'Marke', 'Verlauf' ),
				'desc' => 'Links eine große Markenfläche mit fließendem Farbverlauf und Glühwürmchen, rechts das Formular.',
				'vars' => array( 'bg1' => '#0f3d36', 'bg2' => '#2f8f74', 'accent' => '#1f6f5c', 'card' => '#ffffff', 'text' => '#17201e', 'card_opacity' => 100, 'radius' => 0, 'blur' => 0, 'card_width' => 380, 'overlay' => 0 ),
				'fx'   => array( 'particles' => 'fireflies', 'light' => 'off', 'card_fx' => 'none', 'position' => 'center' ),
			),
			'particles' => array(
				'name' => 'Partikel-Netz', 'tags' => array( 'Dunkel', 'Interaktiv', 'Netz' ),
				'desc' => 'Ein lebendiges Punktenetz, das der Maus folgt. Dazu ein Lichtkegel und eine Karte in 3D.',
				'vars' => array( 'bg1' => '#040913', 'bg2' => '#0c1d3d', 'accent' => '#4cc9f0', 'card' => '#0b1424', 'text' => '#eaf6ff', 'card_opacity' => 78, 'radius' => 16, 'blur' => 14, 'card_width' => 400, 'overlay' => 0 ),
				'fx'   => array( 'particles' => 'network', 'light' => 'on', 'card_fx' => 'tilt', 'position' => 'center' ),
			),
			'sunset'    => array(
				'name' => 'Sunset', 'tags' => array( 'Warm', 'Glühwürmchen', 'Glanz' ),
				'desc' => 'Fließender Abendhimmel, eine atmende Sonne über Hügeln und warme Glühwürmchen.',
				'vars' => array( 'bg1' => '#ff8a5c', 'bg2' => '#5b2bc4', 'accent' => '#ffb347', 'card' => '#2a0f3d', 'text' => '#fff6ee', 'card_opacity' => 56, 'radius' => 22, 'blur' => 20, 'card_width' => 400, 'overlay' => 0 ),
				'fx'   => array( 'particles' => 'fireflies', 'light' => 'off', 'card_fx' => 'shine', 'position' => 'center' ),
			),
			'cosmos'    => array(
				'name' => 'Kosmos', 'tags' => array( 'Dunkel', 'Warp', 'Seitenpanel' ),
				'desc' => 'Flug durch ein Sternenfeld, ein leuchtender Planet und das Formular in einem Glas-Panel rechts.',
				'vars' => array( 'bg1' => '#02030a', 'bg2' => '#140b33', 'accent' => '#8f7bff', 'card' => '#0b0a1f', 'text' => '#f1efff', 'card_opacity' => 30, 'radius' => 18, 'blur' => 24, 'card_width' => 380, 'overlay' => 0 ),
				'fx'   => array( 'particles' => 'warp', 'light' => 'off', 'card_fx' => 'none', 'position' => 'right' ),
			),
		);

		$templates = array();
		foreach ( $base as $id => $tpl ) {
			$templates[ $id ] = $tpl + array(
				'tier' => 'free',
				'css'  => MGD_LOGIN_URL . 'assets/templates/' . $id . '.css',
				'js'   => '',
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
				$templates[ $id ] = $tpl + array( 'tags' => array(), 'desc' => '', 'tier' => 'pro', 'js' => '', 'ver' => MGD_LOGIN_VERSION, 'fx' => array() );
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

	/** Effekt-Auswahl fürs Backend, '' = wie das Layout */
	public static function fx_choices(): array {
		return array(
			'particles' => array( 'none' => 'Keine', 'stars' => 'Sterne', 'snow' => 'Schnee', 'fireflies' => 'Glühwürmchen', 'bubbles' => 'Seifenblasen', 'network' => 'Punktenetz', 'warp' => 'Sternenflug', 'confetti' => 'Konfetti' ),
			'light'     => array( 'on' => 'An', 'off' => 'Aus' ),
			'card_fx'   => array( 'none' => 'Keiner', 'tilt' => '3D-Neigung zur Maus', 'glow' => 'Kreisender Leuchtrand', 'shine' => 'Glanz-Streifen' ),
			'position'  => array( 'center' => 'Mitte', 'left' => 'Links', 'right' => 'Rechts' ),
		);
	}

	/** Effekte vom Layout + eigene Auswahl (leer = Layout) */
	public static function fx( string $id, array $s ): array {
		$tpl = self::get( $id );
		$fx  = array_merge( array( 'particles' => 'none', 'light' => 'off', 'card_fx' => 'none', 'position' => 'center' ), $tpl['fx'] ?? array() );
		foreach ( self::fx_choices() as $key => $choices ) {
			$mine = (string) ( $s[ $key ] ?? '' );
			if ( isset( $choices[ $mine ] ) ) {
				$fx[ $key ] = $mine;
			}
			if ( ! isset( $choices[ $fx[ $key ] ] ) ) {
				$fx[ $key ] = array_key_first( $choices );
			}
		}
		return $fx;
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
