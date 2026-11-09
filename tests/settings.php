<?php
/**
 * Schneller Test ohne WordPress-Installation: php tests/settings.php
 * Prüft Bereinigung der Einstellungen und Kontrastfarbe.
 */
define( 'ABSPATH', __DIR__ );
define( 'MGD_LOGIN_URL', 'http://x/' );
define( 'MGD_LOGIN_VERSION', 'test' );

// Minimale WordPress-Ersatzfunktionen
function sanitize_key( $k ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', (string) $k ) ); }
function sanitize_hex_color( $c ) { return preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', (string) $c ) ? $c : ''; }
function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function wp_strip_all_tags( $s ) { return trim( strip_tags( (string) $s ) ); }
function wp_kses( $s, $a ) { return strip_tags( (string) $s, '<a><strong><em><br>' ); }
function absint( $n ) { return abs( (int) $n ); }
function wp_attachment_is_image( $id ) { return 7 === (int) $id; }
function apply_filters( $t, $v ) { return $v; }

require __DIR__ . '/../mgd-wordpress-login/includes/class-mgd-login-settings.php';
require __DIR__ . '/../mgd-wordpress-login/includes/class-mgd-login-templates.php';
require __DIR__ . '/../mgd-wordpress-login/includes/class-mgd-login-frontend.php';

$fails = 0;
function check( string $name, bool $ok ): void {
	global $fails;
	echo ( $ok ? 'OK    ' : 'FEHLER' ) . ' ' . $name . "\n";
	$fails += $ok ? 0 : 1;
}

$s = MGD_Login_Settings::sanitize( array(
	'template' => 'gibts-nicht', 'accent' => 'red;}</style>', 'bg1' => '#abc', 'radius' => '999', 'blur' => '-5',
	'card_opacity' => '', 'logo_id' => '7', 'bg_id' => '8', 'font' => 'comic', 'speed' => 'abc',
	'custom_css' => 'a{color:red} @import url(x); </style><script>alert(1)</script> b{width:expression(1)}',
	'title' => '<b>Hi</b>', 'animation' => '1',
) );
check( 'Unbekanntes Template fällt auf Aurora zurück', 'aurora' === $s['template'] );
check( 'Ungültige Farbe wird leer', '' === $s['accent'] );
check( 'Kurze Hex-Farbe bleibt', '#abc' === $s['bg1'] );
check( 'Radius wird auf 48 begrenzt', 48 === $s['radius'] );
check( 'Blur wird auf 0 begrenzt', 0 === $s['blur'] );
check( 'Leere Deckkraft bleibt leer', '' === $s['card_opacity'] );
check( 'Echtes Bild-ID bleibt, falsche wird 0', 7 === $s['logo_id'] && 0 === $s['bg_id'] );
check( 'Unbekannte Schrift wird System', 'system' === $s['font'] );
check( 'Ungültiges Tempo wird 100', 100 === $s['speed'] );
check( 'CSS ohne Tags, Import und expression', ! preg_match( '/<|@import|expression/i', $s['custom_css'] ) );
check( 'Titel ohne HTML', 'Hi' === $s['title'] );
check( 'Animation bleibt an', 1 === $s['animation'] && 0 === $s['hide_back'] );
check( 'Kontrast: heller Akzent bekommt dunklen Text', '#10141a' === MGD_Login_Frontend::contrast( '#ffd166' ) );
check( 'Kontrast: dunkler Akzent bekommt weißen Text', '#ffffff' === MGD_Login_Frontend::contrast( '#1f5d50' ) );
check( 'Pro-Template ohne Freischaltung nicht nutzbar', 'aurora' === MGD_Login_Templates::usable( 'aurora' ) );

exit( $fails ? 1 : 0 );
