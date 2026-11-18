<?php
/**
 * Test Free-Lizenz Anbindung ohne WordPress: php tests/install.php
 * Prüft Client-Aufbau, Schalter-HTML und Schalter aus/an (ohne Netz).
 */
define( 'ABSPATH', __DIR__ );

$GLOBALS['opts']  = array();
$GLOBALS['hooks'] = array();
function get_option( $k, $d = false ) { return $GLOBALS['opts'][ $k ] ?? $d; }
function update_option( $k, $v, $a = null ) { $GLOBALS['opts'][ $k ] = $v; return true; }
function home_url() { return 'https://WooCommerce.Theme.Michael-Gahn.de/'; }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function wp_nonce_field( $a, $n, $r, $e ) { return '<input type="hidden" name="' . $n . '" value="x">'; }
function register_activation_hook( $f, $cb ) { $GLOBALS['hooks']['activate'] = $cb; }
function add_action( $h, $cb ) { $GLOBALS['hooks'][ $h ] = $cb; }

foreach ( array( 'Store', 'Transport', 'Response', 'CallbackStore', 'CurlTransport', 'InstallClient', 'Factory', 'Settings', 'Platform/WordPress' ) as $f ) {
	require __DIR__ . '/../mgd-wordpress-login/includes/install-client/' . $f . '.php';
}
use Mgd\WordPressLogin\Install\Platform\WordPress;
use Mgd\WordPressLogin\Install\Settings;

$fails = 0;
function check( string $name, bool $ok ): void {
	global $fails;
	echo ( $ok ? 'OK    ' : 'FEHLER' ) . ' ' . $name . "\n";
	$fails += $ok ? 0 : 1;
}

$c = WordPress::client( 'mgd-wordpress-login', 'wordpress', '1.5.0' );
check( 'Client wird gebaut (Produktname gültig)', null !== $c );
check( 'Falscher Produktname gibt null statt Fehler', null === WordPress::client( 'Kaputt Name!', 'wordpress', '1.5.0' ) );
check( 'Schalter ab Werk an', $c->isEnabled() );
$html = WordPress::settingsHtml( 'mgd-wordpress-login', 'wordpress', '1.5.0' );
check( 'Einstellungen zeigen Schalter angehakt', str_contains( $html, 'mgd_install_report_mgd_wordpress_login' ) && str_contains( $html, 'checked' ) );
check( 'Einstellungen zeigen freigegebenen Hinweistext', str_contains( $html, htmlspecialchars( Settings::HINT, ENT_QUOTES ) ) );
check( 'Einstellungen haben Nonce-Feld', str_contains( $html, '_mgd_install_nonce' ) );
$c->setEnabled( false );
check( 'Schalter aus wird gespeichert', ! WordPress::client( 'mgd-wordpress-login', 'wordpress', '1.5.0' )->isEnabled() );
check( 'Schalter aus: ping sendet nichts', false === $c->ping( true ) );
$c->setEnabled( true );
check( 'Schalter wieder an', WordPress::client( 'mgd-wordpress-login', 'wordpress', '1.5.0' )->isEnabled() );
check( 'Status ist eigene Option ohne Autoload', isset( $GLOBALS['opts']['mgd_install_mgd_wordpress_login'] ) );
WordPress::boot( __FILE__, 'mgd-wordpress-login', 'wordpress', '1.5.0', 'https://x/wp-admin/admin.php?page=mgd-login-settings' );
check( 'boot hängt sich an admin_init und admin_notices', isset( $GLOBALS['hooks']['admin_init'], $GLOBALS['hooks']['admin_notices'], $GLOBALS['hooks']['activate'] ) );

echo $fails ? "\n$fails Fehler\n" : "\nAlles gut\n";
exit( $fails ? 1 : 0 );
