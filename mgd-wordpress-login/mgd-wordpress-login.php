<?php
/**
 * Plugin Name: MGD WordPress Login
 * Plugin URI: https://github.com/MichaelGahnDESIGN/MGD-WordPress-Login
 * Description: Ein richtig schöner Login mit Templates, Animationen und eigenen Farben, Logo und Hintergrund. Ohne externe Dienste.
 * Version: 0.1.5
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

define( 'MGD_LOGIN_VERSION', '0.1.5' );
define( 'MGD_LOGIN_FILE', __FILE__ );
define( 'MGD_LOGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MGD_LOGIN_URL', plugin_dir_url( __FILE__ ) );

require_once MGD_LOGIN_DIR . 'includes/class-mgd-login-settings.php';
require_once MGD_LOGIN_DIR . 'includes/class-mgd-login-templates.php';
require_once MGD_LOGIN_DIR . 'includes/class-mgd-login-frontend.php';
require_once MGD_LOGIN_DIR . 'includes/class-mgd-wordpress-plugin-catalog.php';
require_once MGD_LOGIN_DIR . 'includes/class-mgd-login-admin.php';
require_once MGD_LOGIN_DIR . 'includes/class-mgd-login-plugin.php';
require_once MGD_LOGIN_DIR . 'includes/class-mgd-login-updater.php';

register_activation_hook( __FILE__, array( 'MGD_Login_Plugin', 'activate' ) );

MGD_Login_Plugin::boot();

// Updates nur über theme.michael-gahn.de mit SHA-256 Prüfung, auch im Cron
MGD_Login_Updater::register( __FILE__ );

// Plugin-Links + Details wie bei den anderen MGD Plugins, ohne 'package' > keine GitHub-Updates mehr
require_once __DIR__ . '/includes/class-mgd-plugin-admin-tools.php';
\MGD_Plugin_Admin_Tools_20261023::register([
    'file' => __FILE__,
    'repo' => 'MGD-WordPress-Login',
    'slug' => 'mgd-wordpress-login',
    'name' => 'MGD WordPress Login',
    'description' => 'Ein richtig schöner Login mit Templates, Animationen und eigenen Farben, Logo und Hintergrund.',
    'version' => '0.1.5',
    'settings' => 'admin.php?page=mgd-login-settings',
    'requires' => '6.5',
    'php' => '8.1',
    'caches' => [MGD_Login_Updater::CACHE],
]);
