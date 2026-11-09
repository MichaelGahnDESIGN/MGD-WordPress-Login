<?php
defined( 'ABSPATH' ) || exit;

/** Startpunkt: hängt Frontend (Login-Seite) und Admin ein. */
final class MGD_Login_Plugin {
	public static function boot(): void {
		MGD_Login_Frontend::register();
		if ( is_admin() ) {
			( new MGD_Login_Admin() )->register();
		}
	}

	public static function activate(): void {
		// Nur anlegen wenn noch nichts da ist, bestehende Einstellungen bleiben
		if ( false === get_option( MGD_Login_Settings::OPTION, false ) ) {
			add_option( MGD_Login_Settings::OPTION, MGD_Login_Settings::defaults() );
		}
	}
}
