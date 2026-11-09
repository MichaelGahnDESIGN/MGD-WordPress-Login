<?php
defined( 'ABSPATH' ) || exit;

/** Alles was auf wp-login.php passiert: Styles, Hintergrund, Texte, Vorschau. */
final class MGD_Login_Frontend {
	private static array $s = array();
	private static string $tpl = 'aurora';

	public static function register(): void {
		add_action( 'login_init', array( self::class, 'preview' ), 1 );
		add_action( 'login_init', array( self::class, 'load' ), 2 );
		add_action( 'login_enqueue_scripts', array( self::class, 'enqueue' ), 20 );
		add_filter( 'login_body_class', array( self::class, 'body_class' ) );
		add_filter( 'login_headertext', array( self::class, 'header_text' ) );
		add_filter( 'login_headerurl', array( self::class, 'header_url' ) );
		add_filter( 'login_message', array( self::class, 'message' ) );
		add_action( 'login_header', array( self::class, 'background' ) );
		add_action( 'login_footer', array( self::class, 'footer' ) );
	}

	public static function load(): void {
		self::$s   = MGD_Login_Settings::get();
		self::$tpl = MGD_Login_Templates::usable( (string) self::$s['template'] );
	}

	/** Live-Vorschau für das Backend: nur Admin mit gültigem Nonce, zeigt ein Fake-Formular */
	public static function preview(): void {
		if ( ! isset( $_GET['mgd_login_preview'] ) ) {
			return;
		}
		$nonce = isset( $_POST['mgd_preview_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['mgd_preview_nonce'] ) ) : '';
		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $nonce, 'mgd_login_preview' ) ) {
			wp_die( 'Vorschau nicht erlaubt.', '', array( 'response' => 403 ) );
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- wird in sanitize() geprüft
		$draft = isset( $_POST['mgd_login_settings'] ) && is_array( $_POST['mgd_login_settings'] ) ? wp_unslash( $_POST['mgd_login_settings'] ) : array();
		MGD_Login_Settings::set_override( $draft );
		self::load();

		// Kern-Texte aus WordPress, damit die Vorschau in jeder Sprache stimmt
		// phpcs:disable WordPress.WP.I18n.TextDomainMismatch,WordPress.WP.I18n.MissingArgDomain
		login_header( __( 'Log In' ), '', null );
		?>
		<form name="loginform" id="loginform" action="#" method="post" onsubmit="return false;">
			<p><label for="user_login"><?php echo esc_html__( 'Username or Email Address' ); ?></label>
			<input type="text" name="log" id="user_login" class="input" value="" size="20" autocomplete="off"></p>
			<div class="user-pass-wrap"><label for="user_pass"><?php echo esc_html__( 'Password' ); ?></label>
				<div class="wp-pwd"><input type="password" name="pwd" id="user_pass" class="input password-input" value="" size="20" autocomplete="off">
				<button type="button" class="button button-secondary wp-hide-pw hide-if-no-js" aria-label="<?php echo esc_attr__( 'Show password' ); ?>"><span class="dashicons dashicons-visibility" aria-hidden="true"></span></button></div></div>
			<p class="forgetmenot"><input name="rememberme" type="checkbox" id="rememberme" value="forever"> <label for="rememberme"><?php echo esc_html__( 'Remember Me' ); ?></label></p>
			<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php echo esc_attr__( 'Log In' ); ?>"></p>
		</form>
		<p id="nav"><a href="#" onclick="return false;"><?php echo esc_html__( 'Lost your password?' ); ?></a></p>
		<?php
		login_footer( 'user_login' );
		// phpcs:enable
		exit;
	}

	public static function enqueue(): void {
		if ( empty( self::$s ) ) {
			self::load();
		}
		$tpl = MGD_Login_Templates::get( self::$tpl );
		wp_enqueue_style( 'mgd-login-base', MGD_LOGIN_URL . 'assets/login-base.css', array( 'login' ), MGD_LOGIN_VERSION );
		wp_enqueue_style( 'mgd-login-tpl-' . self::$tpl, $tpl['css'], array( 'mgd-login-base' ), $tpl['ver'] );
		if ( ! empty( $tpl['js'] ) && ! empty( self::$s['animation'] ) ) {
			wp_enqueue_script( 'mgd-login-tpl-' . self::$tpl, $tpl['js'], array(), $tpl['ver'], true );
		}
		$css = self::variables( $tpl );
		if ( '' !== self::$s['custom_css'] ) {
			$css .= "\n" . MGD_Login_Settings::clean_css( self::$s['custom_css'] );
		}
		wp_add_inline_style( 'mgd-login-tpl-' . self::$tpl, str_ireplace( '</style', '', $css ) );
	}

	/** Template-Standard + eigene Werte als CSS-Variablen */
	private static function variables( array $tpl ): string {
		$s = self::$s;
		$v = $tpl['vars'];
		foreach ( array( 'bg1', 'bg2', 'accent', 'card', 'text', 'card_opacity', 'radius', 'blur', 'card_width', 'overlay' ) as $key ) {
			if ( '' !== $s[ $key ] ) {
				$v[ $key ] = $s[ $key ];
			}
		}
		$fonts = MGD_Login_Settings::fonts();
		$font  = $fonts[ $s['font'] ][1] ?? $fonts['system'][1];
		$logo  = $s['logo_id'] ? wp_get_attachment_image_url( (int) $s['logo_id'], 'full' ) : '';
		$bg    = $s['bg_id'] ? wp_get_attachment_image_url( (int) $s['bg_id'], 'full' ) : '';
		$url   = static fn( string $u ): string => 'url("' . str_replace( array( '"', '\\', "\n", "\r" ), array( '%22', '%5C', '', '' ), esc_url_raw( $u ) ) . '")';

		$decl = array(
			'--mgd-bg1'        => $v['bg1'],
			'--mgd-bg2'        => $v['bg2'],
			'--mgd-accent'     => $v['accent'],
			'--mgd-card'       => $v['card'],
			'--mgd-text'       => $v['text'],
			'--mgd-btn-text'   => self::contrast( (string) $v['accent'] ),
			'--mgd-card-pct'   => (int) $v['card_opacity'] . '%',
			'--mgd-radius'     => (int) $v['radius'] . 'px',
			'--mgd-blur'       => (int) $v['blur'] . 'px',
			'--mgd-card-width' => (int) $v['card_width'] . 'px',
			'--mgd-overlay'    => (int) $v['overlay'] . '%',
			'--mgd-speed'      => (string) round( (int) $s['speed'] / 100, 2 ),
			'--mgd-logo-h'     => (int) $s['logo_height'] . 'px',
			'--mgd-font'       => $font,
			'--mgd-logo'       => $logo ? $url( $logo ) : 'none',
			'--mgd-bg-image'   => $bg ? $url( $bg ) : 'none',
		);
		$out = '';
		foreach ( $decl as $name => $value ) {
			$out .= $name . ':' . $value . ';';
		}
		return 'html body.login.mgd-login{' . $out . '}';
	}

	/** Schwarz oder Weiß, je nachdem was auf der Farbe besser lesbar ist */
	public static function contrast( string $hex ): string {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
			return '#ffffff';
		}
		$lin = static function ( int $c ): float {
			$c /= 255;
			return $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
		};
		$l = 0.2126 * $lin( hexdec( substr( $hex, 0, 2 ) ) ) + 0.7152 * $lin( hexdec( substr( $hex, 2, 2 ) ) ) + 0.0722 * $lin( hexdec( substr( $hex, 4, 2 ) ) );
		return $l > 0.36 ? '#10141a' : '#ffffff';
	}

	public static function body_class( array $classes ): array {
		$classes[] = 'mgd-login';
		$classes[] = 'mgd-t-' . self::$tpl;
		if ( empty( self::$s['animation'] ) ) {
			$classes[] = 'mgd-no-anim';
		}
		if ( self::$s['bg_id'] ) {
			$classes[] = 'mgd-has-bg';
		}
		$classes[] = self::$s['logo_id'] ? 'mgd-logo-image' : 'mgd-logo-' . self::$s['logo_mode'];
		if ( self::$s['hide_back'] ) {
			$classes[] = 'mgd-hide-back';
		}
		return $classes;
	}

	// Ohne eigenes Logo steht der Seitenname als Text
	public static function header_text( string $text ): string {
		return ( self::$s['logo_id'] || 'name' !== self::$s['logo_mode'] ) ? $text : get_bloginfo( 'name' );
	}

	public static function header_url( string $url ): string {
		return home_url( '/' );
	}

	public static function message( string $message ): string {
		$title = self::$s['title'];
		$sub   = self::$s['subtitle'];
		if ( '' === $title && '' === $sub ) {
			return $message;
		}
		$html = '<div class="mgd-login-welcome">';
		if ( '' !== $title ) {
			$html .= '<h2>' . esc_html( $title ) . '</h2>';
		}
		if ( '' !== $sub ) {
			$html .= '<p>' . esc_html( $sub ) . '</p>';
		}
		return $html . '</div>' . $message;
	}

	/** Hintergrund-Ebene mit Animationsformen, kommt direkt nach <body> */
	public static function background(): void {
		echo '<div class="mgd-login-bg" aria-hidden="true"><span></span><span></span><span></span>';
		echo '<div class="mgd-login-hero"><strong>' . esc_html( get_bloginfo( 'name' ) ) . '</strong>';
		if ( '' !== self::$s['subtitle'] ) {
			echo '<em>' . esc_html( self::$s['subtitle'] ) . '</em>';
		}
		echo '</div></div>';
	}

	public static function footer(): void {
		if ( '' === self::$s['footer'] ) {
			return;
		}
		echo '<div class="mgd-login-footer">' . wp_kses_post( self::$s['footer'] ) . '</div>';
	}
}
