<?php
/**
 * Plugin Name: MGD Login
 * Plugin URI: https://theme.michael-gahn.de/plugins/plugin-login-wordpress
 * Description: Ein richtig schöner Login mit Templates, Animationen und eigenen Farben, Logo und Hintergrund.
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

// Updater + MGD Tools Menü vom Baukasten
use Mgd\WordPressLogin\Kit\Platform\WordPress\Setup;
use Mgd\WordPressLogin\Kit\Platform\WordPress\Tools;

MGD_Login_Plugin::$updater = Setup::updater( __FILE__, 'mgd-wordpress-login', 'wordpress', MGD_LOGIN_VERSION, 'MGD Login', Tools::url() );
// Reste vom alten Updater + Katalog wegräumen, 1x pro Version
Setup::cleanupLegacy( 'mgd-wordpress-login', MGD_LOGIN_VERSION, array( 'mgd_login_update_manifest', 'mgd_admin_release_*', 'mgd_wordpress_catalog_v3', 'mgd_catalog_v3_release_*' ) );

// Einstellungen unter MGD Tools - Seiten-Kennung bleibt mgd-login-settings
Setup::tools( __FILE__, 'mgd-wordpress-login', 'wordpress', MGD_LOGIN_VERSION, 'MGD Login', MGD_Login_Plugin::$updater, array(
	'slug'     => 'plugin-login-wordpress',
	'repo'     => 'MGD-WordPress-Login',
	'settings' => array( 'page' => 'mgd-login-settings', 'title' => 'Login', 'cap' => 'manage_options', 'render' => static function (): void { MGD_Login_Plugin::$admin?->page(); } ),
	'license'  => false,
) );
