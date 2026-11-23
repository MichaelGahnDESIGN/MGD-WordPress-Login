<?php
/**
 * Plugin Name: MGD WordPress Login
 * Plugin URI: https://github.com/MichaelGahnDESIGN/MGD-WordPress-Login
 * Description: Ein richtig schöner Login mit Templates, Animationen und eigenen Farben, Logo und Hintergrund. Ohne externe Dienste.
 * Version: 1.8.1
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: Michael Gahn DESIGN
 * Author URI: https://michael-gahn.de
 * Text Domain: mgd-wordpress-login
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://theme.michael-gahn.de/plugin-updates/mgd-wordpress-login.json
 */

defined( 'ABSPATH' ) || exit;

define( 'MGD_LOGIN_VERSION', '1.8.1' );
define( 'MGD_LOGIN_FILE', __FILE__ );
define( 'MGD_LOGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MGD_LOGIN_URL', plugin_dir_url( __FILE__ ) );

require_once MGD_LOGIN_DIR . 'includes/class-mgd-login-settings.php';
require_once MGD_LOGIN_DIR . 'includes/class-mgd-login-templates.php';
require_once MGD_LOGIN_DIR . 'includes/class-mgd-login-frontend.php';
require_once MGD_LOGIN_DIR . 'includes/class-mgd-login-admin.php';
require_once MGD_LOGIN_DIR . 'includes/class-mgd-login-plugin.php';

register_activation_hook( __FILE__, array( 'MGD_Login_Plugin', 'activate' ) );

MGD_Login_Plugin::boot();

// Baukasten aus MichaThemeV5-Pro > includes/Kit (bin/build-plugin-kit.php), nicht von Hand ändern
spl_autoload_register( static function ( string $class ): void {
	$prefix = 'Mgd\\WordPressLogin\\Kit\\';
	if ( str_starts_with( $class, $prefix ) ) {
		$file = MGD_LOGIN_DIR . 'includes/Kit/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
		if ( is_file( $file ) ) {
			require $file;
		}
	}
} );

// Updater vom Baukasten, startet auch den Install-Melder + Installieren-Knopf bei Weitere Plugins
MGD_Login_Plugin::$updater = \Mgd\WordPressLogin\Kit\Platform\WordPress\Setup::updater( __FILE__, 'mgd-wordpress-login', 'wordpress', MGD_LOGIN_VERSION, 'MGD WordPress Login', admin_url( 'admin.php?page=mgd-login-plugins' ) );
// Reste vom alten Updater + Katalog wegräumen, 1x pro Version
\Mgd\WordPressLogin\Kit\Platform\WordPress\Setup::cleanupLegacy( 'mgd-wordpress-login', MGD_LOGIN_VERSION, array( 'mgd_login_update_manifest', 'mgd_admin_release_*', 'mgd_wordpress_catalog_v3', 'mgd_catalog_v3_release_*' ) );

// Link Einstellungen in der Plugin-Liste
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), static function ( array $links ): array {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=mgd-login-settings' ) ) . '">Einstellungen</a>' );
	return $links;
} );
