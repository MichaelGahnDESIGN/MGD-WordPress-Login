<?php
/**
 * Test Update-Weg ohne WordPress: php tests/updater.php
 * Richtige Prüfsumme = Update, falsche = abgelehnt, Server weg = still.
 */
define( 'ABSPATH', __DIR__ );

// Mini-WP: Transients, HTTP-Fake, WP_Error
$GLOBALS['transients'] = array();
$GLOBALS['http']       = null; // fn( url, args ) => Antwort-Array oder WP_Error
$GLOBALS['calls']      = array();
class WP_Error {
	public function __construct( public string $code = '', public string $message = '' ) {}
	public function get_error_message(): string { return $this->message; }
}
function is_wp_error( $x ) { return $x instanceof WP_Error; }
function plugin_basename( $f ) { return 'mgd-wordpress-login/mgd-wordpress-login.php'; }
function add_filter( ...$a ) {}
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function get_site_transient( $k ) { return $GLOBALS['transients'][ $k ] ?? false; }
function set_site_transient( $k, $v, $t = 0 ) { $GLOBALS['transients'][ $k ] = $v; return true; }
function delete_site_transient( $k ) { unset( $GLOBALS['transients'][ $k ] ); return true; }
function wp_tempnam( $n = '' ) { return tempnam( sys_get_temp_dir(), 'mgdt' ); }
function wp_delete_file( $f ) { if ( is_file( $f ) ) { unlink( $f ); } }
function wp_remote_retrieve_response_code( $r ) { return $r['code'] ?? 0; }
function wp_remote_retrieve_body( $r ) { return $r['body'] ?? ''; }
function wp_remote_retrieve_header( $r, $h ) { return $r['headers'][ $h ] ?? ''; }
function wp_safe_remote_get( $url, $args = array() ) {
	$GLOBALS['calls'][] = array( $url, $args );
	$r = ( $GLOBALS['http'] )( $url, $args );
	// stream wie WP: Body landet in der Datei
	if ( ! is_wp_error( $r ) && ! empty( $args['stream'] ) ) {
		$body = substr( $r['body'] ?? '', 0, $args['limit_response_size'] ?? PHP_INT_MAX );
		file_put_contents( $args['filename'], $body );
	}
	return $r;
}

require __DIR__ . '/../mgd-wordpress-login/includes/class-mgd-login-updater.php';
MGD_Login_Updater::register( 'x' );

$fails = 0;
function check( string $name, bool $ok ): void {
	global $fails;
	echo ( $ok ? 'OK    ' : 'FEHLER' ) . ' ' . $name . "\n";
	$fails += $ok ? 0 : 1;
}

$zip  = "PK-fake-zip-0.1.2\n" . str_repeat( 'x', 500 );
$file = 'mgd-wordpress-login/mgd-wordpress-login.php';
$pkg  = 'https://github.com/MichaelGahnDESIGN/MGD-WordPress-Login/releases/download/v0.1.2/mgd-wordpress-login-0.1.2.zip';
$cdn  = 'https://release-assets.githubusercontent.com/github-production-release-asset/1/abc';
function manifest( array $over = array() ): array {
	global $zip, $pkg;
	return $over + array( 'schema' => 1, 'product' => 'mgd-wordpress-login', 'version' => '0.1.2', 'filename' => 'mgd-wordpress-login-0.1.2.zip', 'size' => strlen( $zip ), 'sha256' => hash( 'sha256', $zip ), 'root_dir' => 'mgd-wordpress-login', 'download_url' => $pkg, 'changelog' => 'Neuer Update-Weg' );
}
// Server-Fake: Prüfdatei, GitHub mit 302, Asset-Host
function server( array $m, string $zip_body, string $redirect ): Closure {
	global $pkg;
	return function ( $url ) use ( $m, $zip_body, $redirect, $pkg ) {
		if ( MGD_Login_Updater::MANIFEST === $url ) { return array( 'code' => 200, 'body' => json_encode( $m ) ); }
		if ( $pkg === $url ) { return array( 'code' => 302, 'headers' => array( 'location' => $redirect ) ); }
		if ( $redirect === $url ) { return array( 'code' => 200, 'body' => $zip_body ); }
		return new WP_Error( 'http', 'unbekannt' );
	};
}
function reset_all(): void { $GLOBALS['transients'] = array(); $GLOBALS['calls'] = array(); }

// Prüfdatei
check( 'Gültige Prüfdatei wird angenommen', null !== MGD_Login_Updater::validate( manifest() ) );
check( 'Falsches Produkt wird abgelehnt', null === MGD_Login_Updater::validate( manifest( array( 'product' => 'mgd-andere' ) ) ) );
check( 'Fremde Download-Adresse wird abgelehnt', null === MGD_Login_Updater::validate( manifest( array( 'download_url' => 'https://evil.example/mgd-wordpress-login-0.1.2.zip' ) ) ) );
check( 'Download-Adresse mit anderer Version wird abgelehnt', null === MGD_Login_Updater::validate( manifest( array( 'download_url' => str_replace( 'v0.1.2', 'v0.1.1', $pkg ) ) ) ) );
check( 'Kaputte Prüfsumme wird abgelehnt', null === MGD_Login_Updater::validate( manifest( array( 'sha256' => 'abc' ) ) ) );
check( 'Ungültige Version wird abgelehnt', null === MGD_Login_Updater::validate( manifest( array( 'version' => '0.1.2-beta' ) ) ) );
check( 'Keine Weiterleitung auf fremde Hosts', ! MGD_Login_Updater::allowed_url( 'https://evil.example/x.zip' ) && ! MGD_Login_Updater::allowed_url( 'http://github.com/x.zip' ) && MGD_Login_Updater::allowed_url( $cdn ) );

// Angebot
reset_all();
$GLOBALS['http'] = server( manifest(), $zip, $cdn );
$offer = MGD_Login_Updater::offer( false, array(), $file );
check( 'Server da: Update 0.1.2 wird angeboten', is_array( $offer ) && '0.1.2' === $offer['version'] && $pkg === $offer['package'] );
check( 'Anderes Plugin bleibt unberührt', 'x' === MGD_Login_Updater::offer( 'x', array(), 'anderes/plugin.php' ) );

reset_all();
$GLOBALS['http'] = fn() => new WP_Error( 'http', 'timeout' );
check( 'Server weg: kein Angebot, kein Fehler', false === MGD_Login_Updater::offer( false, array(), $file ) );
MGD_Login_Updater::offer( false, array(), $file );
check( 'Server weg: zweiter Aufruf fragt nicht nochmal (still)', 1 === count( $GLOBALS['calls'] ) );

reset_all();
$GLOBALS['http'] = fn() => array( 'code' => 404, 'body' => 'nicht da' );
check( 'Prüfdatei fehlt (404): kein Angebot', false === MGD_Login_Updater::offer( false, array(), $file ) );

// Download
reset_all();
$GLOBALS['http'] = server( manifest(), $zip, $cdn );
$tmp = MGD_Login_Updater::download( false, $pkg, null, array( 'plugin' => $file ) );
check( 'Richtige Prüfsumme: ZIP wird freigegeben', is_string( $tmp ) && is_file( $tmp ) && file_get_contents( $tmp ) === $zip );
if ( is_string( $tmp ) ) { @unlink( $tmp ); }

reset_all();
$GLOBALS['http'] = server( manifest(), str_replace( 'x', 'y', $zip ), $cdn );
$before = glob( sys_get_temp_dir() . '/mgdt*' );
$res    = MGD_Login_Updater::download( false, $pkg, null, array( 'plugin' => $file ) );
check( 'Falsche Prüfsumme: Update abgelehnt', is_wp_error( $res ) );
check( 'Falsche Prüfsumme: Temp-Datei weg', count( glob( sys_get_temp_dir() . '/mgdt*' ) ) === count( $before ) );

reset_all();
$GLOBALS['http'] = server( manifest(), $zip . 'mehr', $cdn );
check( 'Zu große Datei: Update abgelehnt', is_wp_error( MGD_Login_Updater::download( false, $pkg, null, array( 'plugin' => $file ) ) ) );

reset_all();
$GLOBALS['http'] = server( manifest(), $zip, 'https://evil.example/zip' );
check( 'Weiterleitung auf fremden Host: abgelehnt', is_wp_error( MGD_Login_Updater::download( false, $pkg, null, array( 'plugin' => $file ) ) ) );

reset_all();
$GLOBALS['http'] = fn() => new WP_Error( 'http', 'timeout' );
check( 'Server weg beim Klick: nichts Ungeprüftes installiert', is_wp_error( MGD_Login_Updater::download( false, $pkg, null, array( 'plugin' => $file ) ) ) );

reset_all();
$GLOBALS['http'] = server( manifest(), $zip, $cdn );
check( 'Anderer Download für unser Plugin: abgelehnt', is_wp_error( MGD_Login_Updater::download( false, 'https://github.com/MichaelGahnDESIGN/MGD-WordPress-Login/archive/main.zip', null, array( 'plugin' => $file ) ) ) );
check( 'Fremdes Plugin: Download bleibt unberührt', false === MGD_Login_Updater::download( false, 'https://example.org/other.zip', null, array( 'plugin' => 'anderes/plugin.php' ) ) );
check( 'ZIP selbst hochladen bleibt möglich', false === MGD_Login_Updater::download( false, '/tmp/upload.zip', null, array( 'type' => 'plugin', 'action' => 'install' ) ) );

// Sonstiges
check( 'Keine automatischen Updates', false === MGD_Login_Updater::no_auto( true, (object) array( 'plugin' => $file ) ) && true === MGD_Login_Updater::no_auto( true, (object) array( 'plugin' => 'anderes/plugin.php' ) ) );
reset_all();
$GLOBALS['http'] = server( manifest(), $zip, $cdn );
$info = MGD_Login_Updater::info( false, 'plugin_information', (object) array( 'slug' => 'mgd-wordpress-login' ) );
check( 'Details zeigen neue Version und Changelog', is_object( $info ) && '0.1.2' === $info->version && str_contains( $info->sections['changelog'], 'Neuer Update-Weg' ) );

// Fälle aus dem Review
reset_all();
$GLOBALS['http'] = server( manifest(), $zip, $cdn );
$good = tempnam( sys_get_temp_dir(), 'mgdx' ); file_put_contents( $good, $zip );
check( 'Datei von anderem Filter mit richtiger Prüfsumme: ok', $good === MGD_Login_Updater::download( $good, $pkg, null, array( 'plugin' => $file ) ) );
$bad = tempnam( sys_get_temp_dir(), 'mgdx' ); file_put_contents( $bad, str_replace( 'x', 'y', $zip ) );
check( 'Datei von anderem Filter mit falscher Prüfsumme: abgelehnt', is_wp_error( MGD_Login_Updater::download( $bad, $pkg, null, array( 'plugin' => $file ) ) ) );
@unlink( $good ); @unlink( $bad );
check( 'Fehler von anderem Filter bleibt Fehler', is_wp_error( MGD_Login_Updater::download( new WP_Error( 'x', 'y' ), $pkg, null, array( 'plugin' => $file ) ) ) );

reset_all();
$GLOBALS['http'] = server( manifest(), $zip, $cdn );
$tmp = MGD_Login_Updater::download( false, $pkg, null, array() );
check( 'GitHub-Link ohne hook_extra wird trotzdem geprüft', is_string( $tmp ) && file_get_contents( $tmp ) === $zip );
if ( is_string( $tmp ) ) { @unlink( $tmp ); }

reset_all();
$GLOBALS['http'] = function ( $url ) use ( $pkg ) {
	if ( MGD_Login_Updater::MANIFEST === $url ) { return array( 'code' => 200, 'body' => json_encode( manifest() ) ); }
	return array( 'code' => 302, 'headers' => array( 'location' => $pkg ) ); // Endlosschleife
};
check( 'Mehr als 3 Weiterleitungen: abgelehnt', is_wp_error( MGD_Login_Updater::download( false, $pkg, null, array( 'plugin' => $file ) ) ) );

reset_all();
$GLOBALS['http'] = server( manifest(), $zip, '/relativ/zip' );
check( 'Relative Weiterleitung: abgelehnt', is_wp_error( MGD_Login_Updater::download( false, $pkg, null, array( 'plugin' => $file ) ) ) );

reset_all();
$GLOBALS['http'] = function ( $url ) use ( $pkg ) {
	if ( MGD_Login_Updater::MANIFEST === $url ) { return array( 'code' => 200, 'body' => json_encode( manifest() ) ); }
	return array( 'code' => 302, 'headers' => array( 'location' => array( 'https://github.com/a', 'https://github.com/b' ) ) );
};
check( 'Doppelter Location-Header: abgelehnt ohne Warnung', is_wp_error( MGD_Login_Updater::download( false, $pkg, null, array( 'plugin' => $file ) ) ) );

reset_all();
$GLOBALS['transients'][ MGD_Login_Updater::CACHE ] = array( 'version' => '9.9.9', 'download_url' => $pkg, 'sha256' => str_repeat( 'a', 64 ), 'size' => 1, 'changelog' => '' );
$GLOBALS['http'] = server( manifest(), $zip, $cdn );
$tmp = MGD_Login_Updater::download( false, $pkg, null, array( 'plugin' => $file ) );
check( 'Manipulierter Cache: beim Klick zählt die frische Prüfdatei', is_string( $tmp ) && file_get_contents( $tmp ) === $zip );
if ( is_string( $tmp ) ) { @unlink( $tmp ); }

echo $fails ? "\n{$fails} Fehler\n" : "\nAlles gut\n";
exit( $fails ? 1 : 0 );
