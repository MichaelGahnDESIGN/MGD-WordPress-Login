<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Nur löschen wenn es in den Einstellungen ausdrücklich angehakt war
$mgd_login_settings = get_option( 'mgd_login_settings' );
if ( is_array( $mgd_login_settings ) && ! empty( $mgd_login_settings['delete_data'] ) ) {
	delete_option( 'mgd_login_settings' );
}
