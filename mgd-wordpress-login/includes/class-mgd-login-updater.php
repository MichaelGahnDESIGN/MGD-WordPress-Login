<?php
defined( 'ABSPATH' ) || exit;

// Update-Weg wie bei den JTL-Plugins: Version + SHA-256 von theme.michael-gahn.de, ZIP aus dem GitHub-Release
final class MGD_Login_Updater {
	const MANIFEST   = 'https://theme.michael-gahn.de/plugin-updates/mgd-wordpress-login.json';
	const PRODUCT    = 'mgd-wordpress-login';
	const RELEASES   = 'https://github.com/MichaelGahnDESIGN/MGD-WordPress-Login/releases/download/';
	const CACHE      = 'mgd_login_update_manifest';
	const CACHE_OK   = 21600; // 6 Std
	const CACHE_FAIL = 1800;  // Server weg > 30 Min still sein
	const MAX_ZIP    = 20971520;
	const MAX_HOPS   = 3;
	// github.com leitet auf die Asset-Hosts weiter, mehr ist nicht erlaubt
	const HOSTS = array( 'github.com', 'objects.githubusercontent.com', 'release-assets.githubusercontent.com' );

	private static string $file = '';

	public static function register( string $plugin_file ): void {
		self::$file = plugin_basename( $plugin_file );
		// greift über "Update URI" im Plugin-Kopf, WordPress.org wird nie gefragt
		add_filter( 'update_plugins_theme.michael-gahn.de', array( self::class, 'offer' ), 10, 3 );
		// ganz spät, damit kein anderer Filter die geprüfte Datei noch tauscht
		add_filter( 'upgrader_pre_download', array( self::class, 'download' ), PHP_INT_MAX, 4 );
		add_filter( 'auto_update_plugin', array( self::class, 'no_auto' ), 99, 2 );
		add_filter( 'plugins_api', array( self::class, 'info' ), 20, 3 );
	}

	/** Update-Angebot für die WP-Liste, false = nichts anbieten */
	public static function offer( $update, $plugin_data, $plugin_file ) {
		if ( $plugin_file !== self::$file ) {
			return $update;
		}
		$m = self::manifest();
		if ( null === $m ) {
			return $update;
		}
		return array(
			'slug'         => self::PRODUCT,
			'version'      => $m['version'],
			'url'          => 'https://github.com/MichaelGahnDESIGN/MGD-WordPress-Login',
			'package'      => $m['download_url'],
			'requires'     => '6.5',
			'requires_php' => '8.1',
		);
	}

	/** ZIP selbst laden und gegen SHA-256 prüfen, sonst wird nix installiert */
	public static function download( $reply, $package, $upgrader = null, $hook_extra = array() ) {
		$package = (string) $package;
		$ours    = str_starts_with( $package, 'https://github.com/MichaelGahnDESIGN/MGD-WordPress-Login/' )
			|| ( is_array( $hook_extra ) && ( $hook_extra['plugin'] ?? '' ) === self::$file );
		if ( ! $ours || is_wp_error( $reply ) ) {
			return $reply;
		}
		// beim Klick frisch holen, nicht dem Cache vertrauen
		delete_site_transient( self::CACHE );
		$m = self::manifest();
		if ( null === $m || $package !== $m['download_url'] ) {
			return new WP_Error( 'mgd_login_update', 'Update abgebrochen: Die Prüfdaten von theme.michael-gahn.de fehlen oder passen nicht zum Download.' );
		}
		// anderes Plugin hat schon was geladen > trotzdem prüfen
		if ( false !== $reply ) {
			if ( is_string( $reply ) && self::verify_file( $reply, $m['size'], $m['sha256'] ) ) {
				return $reply;
			}
			return new WP_Error( 'mgd_login_update', 'Update abgebrochen: Die Prüfsumme des Downloads stimmt nicht. Es wurde nichts installiert.' );
		}
		$tmp = self::fetch_zip( $m['download_url'], $m['size'] );
		if ( is_wp_error( $tmp ) ) {
			return $tmp;
		}
		if ( ! self::verify_file( $tmp, $m['size'], $m['sha256'] ) ) {
			wp_delete_file( $tmp );
			return new WP_Error( 'mgd_login_update', 'Update abgebrochen: Die Prüfsumme des Downloads stimmt nicht. Es wurde nichts installiert.' );
		}
		return $tmp;
	}

	public static function no_auto( $update, $item ) {
		// Betreiber klickt selbst
		return ( is_object( $item ) && ( $item->plugin ?? '' ) === self::$file ) ? false : $update;
	}

	/** Version + Changelog für "Details anzeigen", Rest macht die MGD Admin-Tools Klasse */
	public static function info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || ! is_object( $args ) || ( $args->slug ?? '' ) !== self::PRODUCT ) {
			return $result;
		}
		$m = self::manifest();
		if ( null === $m ) {
			return $result;
		}
		$info          = ( is_object( $result ) && ! is_wp_error( $result ) ) ? $result : new stdClass();
		$info->version = $m['version'];
		if ( '' !== $m['changelog'] ) {
			$info->sections = array( 'changelog' => '<h3>' . esc_html( $m['version'] ) . '</h3><p>' . esc_html( $m['changelog'] ) . '</p>' ) + (array) ( $info->sections ?? array() );
		}
		return $info;
	}

	/** Prüfdatei holen (mit Cache), null wenn Server weg oder Datei ungültig */
	public static function manifest(): ?array {
		$cached = get_site_transient( self::CACHE );
		if ( is_array( $cached ) ) {
			return $cached ? $cached : null;
		}
		$res = wp_safe_remote_get( self::MANIFEST, array( 'timeout' => 8, 'redirection' => 0, 'limit_response_size' => 16384 ) );
		$m   = null;
		if ( ! is_wp_error( $res ) && 200 === (int) wp_remote_retrieve_response_code( $res ) ) {
			$m = self::validate( json_decode( (string) wp_remote_retrieve_body( $res ), true ) );
		}
		set_site_transient( self::CACHE, $m ?? array(), $m ? self::CACHE_OK : self::CACHE_FAIL );
		return $m;
	}

	/** Alles muss exakt passen, die Download-Adresse wird aus der Version gebaut und verglichen */
	public static function validate( $d ): ?array {
		if ( ! is_array( $d ) || 1 !== ( $d['schema'] ?? null ) || self::PRODUCT !== ( $d['product'] ?? null ) || self::PRODUCT !== ( $d['root_dir'] ?? null ) ) {
			return null;
		}
		$v = (string) ( $d['version'] ?? '' );
		if ( 1 !== preg_match( '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/D', $v ) ) {
			return null;
		}
		$name = self::PRODUCT . '-' . $v . '.zip';
		$url  = self::RELEASES . 'v' . $v . '/' . $name;
		$sha  = (string) ( $d['sha256'] ?? '' );
		$size = $d['size'] ?? 0;
		if ( ( $d['filename'] ?? '' ) !== $name || ( $d['download_url'] ?? '' ) !== $url
			|| 1 !== preg_match( '/^[a-f0-9]{64}$/D', $sha ) || ! is_int( $size ) || $size < 1 || $size > self::MAX_ZIP ) {
			return null;
		}
		$log = is_string( $d['changelog'] ?? null ) ? mb_substr( $d['changelog'], 0, 300 ) : '';
		return array( 'version' => $v, 'download_url' => $url, 'sha256' => $sha, 'size' => $size, 'changelog' => $log );
	}

	public static function allowed_url( string $url ): bool {
		$p = parse_url( $url );
		return is_array( $p ) && 'https' === ( $p['scheme'] ?? '' ) && in_array( $p['host'] ?? '', self::HOSTS, true )
			&& ! isset( $p['user'] ) && ! isset( $p['pass'] ) && 443 === ( $p['port'] ?? 443 );
	}

	public static function verify_file( string $path, int $size, string $sha ): bool {
		return is_file( $path ) && filesize( $path ) === $size && hash_equals( $sha, (string) hash_file( 'sha256', $path ) );
	}

	// Weiterleitungen selbst folgen, jeder Hop muss auf der Host-Liste stehen
	private static function fetch_zip( string $url, int $size ) {
		$tmp = wp_tempnam( 'mgd-login-update.zip' );
		if ( ! $tmp ) {
			return new WP_Error( 'mgd_login_update', 'Update abgebrochen: Keine temporäre Datei möglich.' );
		}
		for ( $hop = 0; $hop <= self::MAX_HOPS; $hop++ ) {
			if ( ! self::allowed_url( $url ) ) {
				break;
			}
			$res = wp_safe_remote_get( $url, array( 'timeout' => 300, 'redirection' => 0, 'stream' => true, 'filename' => $tmp, 'limit_response_size' => $size + 1 ) );
			if ( is_wp_error( $res ) ) {
				break;
			}
			$code = (int) wp_remote_retrieve_response_code( $res );
			if ( 200 === $code ) {
				return $tmp;
			}
			if ( ! in_array( $code, array( 301, 302, 303, 307, 308 ), true ) ) {
				break;
			}
			$url = wp_remote_retrieve_header( $res, 'location' );
			if ( ! is_string( $url ) ) {
				break; // doppelter Location Header > abbrechen
			}
		}
		wp_delete_file( $tmp );
		return new WP_Error( 'mgd_login_update', 'Update abgebrochen: Der Download von GitHub hat nicht geklappt oder kam von einer nicht erlaubten Adresse.' );
	}
}
