<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Nur löschen wenn es in den Einstellungen ausdrücklich angehakt war
$mgd_login_settings = get_option( 'mgd_login_settings' );
if ( is_array( $mgd_login_settings ) && ! empty( $mgd_login_settings['delete_data'] ) ) {
	delete_option( 'mgd_login_settings' );
}

// Bei theme.michael-gahn.de abmelden + Baukasten-Daten weg, eigene uninstall.php > Baukasten selbst laden
spl_autoload_register( static function ( string $class ): void {
	$prefix = 'Mgd\\WordPressLogin\\Kit\\';
	if ( str_starts_with( $class, $prefix ) ) {
		$file = __DIR__ . '/includes/Kit/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
		if ( is_file( $file ) ) {
			require $file;
		}
	}
} );
\Mgd\WordPressLogin\Kit\Platform\WordPress\Setup::uninstall( 'mgd-wordpress-login', 'wordpress' );
