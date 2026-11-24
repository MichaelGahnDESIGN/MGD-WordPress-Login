<?php
/**
 * Test Baukasten-Einbau ohne WordPress: php tests/kit.php
 * Prüft Update-Datei (UpdateInfo), dass alter Update-Code weg ist und Plugin + uninstall.php den Baukasten nutzen.
 */
$dir  = __DIR__ . '/../mgd-wordpress-login';
$fail = 0;
function check( string $name, bool $ok ): void {
	global $fail;
	echo ( $ok ? 'ok   ' : 'FEHLER ' ), $name, "\n";
	if ( ! $ok ) {
		$fail++;
	}
}

// Autoloader wie im Plugin
spl_autoload_register( static function ( string $class ) use ( $dir ): void {
	$prefix = 'Mgd\\WordPressLogin\\Kit\\';
	if ( str_starts_with( $class, $prefix ) ) {
		$file = $dir . '/includes/Kit/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
		if ( is_file( $file ) ) {
			require $file;
		}
	}
} );
use Mgd\WordPressLogin\Kit\Update\UpdateInfo;

// Update-Datei so wie update-simple-file.php sie baut
function json( array $o = array() ): string {
	$v = $o['version'] ?? '1.8.0';
	return (string) json_encode( array_merge( array(
		'schema'       => 1,
		'product'      => 'mgd-wordpress-login',
		'version'      => $v,
		'filename'     => "mgd-wordpress-login-$v.zip",
		'size'         => 120000,
		'sha256'       => str_repeat( 'a', 64 ),
		'root_dir'     => 'mgd-wordpress-login',
		'download_url' => "https://github.com/MichaelGahnDESIGN/MGD-WordPress-Login/releases/download/v$v/mgd-wordpress-login-$v.zip",
	), $o ), JSON_UNESCAPED_SLASHES );
}
check( 'Gültige Update-Datei wird angenommen', null !== UpdateInfo::parse( json(), 'mgd-wordpress-login' ) );
check( 'Falsches Produkt wird abgelehnt', null === UpdateInfo::parse( json( array( 'product' => 'mgd-andere' ) ), 'mgd-wordpress-login' ) );
check( 'Fremde Download-Adresse wird abgelehnt', null === UpdateInfo::parse( json( array( 'download_url' => 'https://evil.example/mgd-wordpress-login-1.8.0.zip' ) ), 'mgd-wordpress-login' ) );
check( 'Kaputte Prüfsumme wird abgelehnt', null === UpdateInfo::parse( json( array( 'sha256' => 'abc' ) ), 'mgd-wordpress-login' ) );

// alter Update-Code + alte Seiten sind raus
foreach ( array( 'includes/class-mgd-login-updater.php', 'includes/install-client', 'includes/class-mgd-plugin-admin-tools.php', 'includes/class-mgd-wordpress-plugin-catalog.php', 'includes/plugin-catalog', 'assets/plugin-catalog' ) as $old ) {
	check( "Alt entfernt: $old", ! file_exists( "$dir/$old" ) );
}
$main  = (string) file_get_contents( "$dir/mgd-wordpress-login.php" );
$admin = (string) file_get_contents( "$dir/includes/class-mgd-login-admin.php" );
$unin  = (string) file_get_contents( "$dir/uninstall.php" );
check( 'Plugin nutzt Setup::updater', str_contains( $main, 'Setup::updater(' ) );
check( 'Plugin räumt alte Reste auf (cleanupLegacy)', str_contains( $main, 'Setup::cleanupLegacy(' ) );
check( 'Kein eigener catalogActions-Aufruf mehr', ! str_contains( $admin, 'catalogActions' ) );
check( 'Einstellungen hängen unter MGD Tools (Setup::tools)', str_contains( $main, 'Setup::tools(' ) && str_contains( $main, "'page' => 'mgd-login-settings'" ) );
check( 'Kein eigenes Menü mehr', ! preg_match( '/add_(menu|submenu|options|management)_page\(/', $admin ) );
check( 'Kein alter Dashboard-Aufruf mehr', ! str_contains( $admin, 'Setup::dashboard(' ) );
check( 'Icons 128 und 256 liegen im Plugin', is_file( __DIR__ . '/../mgd-wordpress-login/assets/icon-128x128.png' ) && is_file( __DIR__ . '/../mgd-wordpress-login/assets/icon-256x256.png' ) );
check( 'Plugin-Kopf: Name MGD Login', str_contains( $main, 'Plugin Name: MGD Login' ) );
check( 'Farbfelder nutzen data-mgd-color, kein wp-color-picker', str_contains( $admin, 'data-mgd-color' ) && ! str_contains( $admin, 'wp-color-picker' ) );
check( 'uninstall.php meldet ab (Setup::uninstall)', str_contains( $unin, 'Setup::uninstall(' ) );
preg_match( '/Version:\s*([0-9.]+)/', $main, $h );
preg_match( "/'MGD_LOGIN_VERSION', '([0-9.]+)'/", $main, $c );
check( 'Version im Kopf = Konstante', isset( $h[1], $c[1] ) && $h[1] === $c[1] );

echo $fail ? "$fail Fehler\n" : "Alles gut\n";
exit( $fail ? 1 : 0 );
