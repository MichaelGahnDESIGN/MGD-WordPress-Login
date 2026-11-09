<?php
defined( 'ABSPATH' ) || exit;

/** Einstellungsseite mit Template-Galerie und Live-Vorschau. */
final class MGD_Login_Admin {
	const SLUG = 'mgd-login-settings';

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		( new MGD_WordPress_Plugin_Catalog_V4() )->register( self::SLUG );
	}

	public function menu(): void {
		add_menu_page( 'MGD WordPress Login', 'MGD Login', 'manage_options', self::SLUG, array( $this, 'page' ), 'dashicons-lock', 81 );
	}

	public function settings(): void {
		register_setting(
			'mgd_login',
			MGD_Login_Settings::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( 'MGD_Login_Settings', 'sanitize' ),
				'default'           => MGD_Login_Settings::defaults(),
			)
		);
	}

	public function assets( string $hook ): void {
		if ( 'toplevel_page_' . self::SLUG !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'mgd-login-admin', MGD_LOGIN_URL . 'assets/admin.css', array(), MGD_LOGIN_VERSION );
		wp_enqueue_script( 'mgd-login-admin', MGD_LOGIN_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), MGD_LOGIN_VERSION, true );

		$defaults = array();
		foreach ( MGD_Login_Templates::all() as $id => $tpl ) {
			$defaults[ $id ] = $tpl['vars'];
		}
		wp_localize_script(
			'mgd-login-admin',
			'MGDLogin',
			array(
				'previewUrl' => add_query_arg( 'mgd_login_preview', '1', wp_login_url() ),
				'defaults'   => $defaults,
			)
		);
	}

	// Kleine Helfer fürs Formular
	private function name( string $key ): string {
		return MGD_Login_Settings::OPTION . '[' . $key . ']';
	}

	private function color( array $s, string $key, string $label ): void {
		echo '<p class="mgd-la-field"><label for="mgd-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label>';
		echo '<input type="text" class="mgd-la-color" id="mgd-' . esc_attr( $key ) . '" name="' . esc_attr( $this->name( $key ) ) . '" value="' . esc_attr( (string) $s[ $key ] ) . '" data-key="' . esc_attr( $key ) . '" maxlength="7"></p>';
	}

	private function number( array $s, string $key, string $label, int $min, int $max, string $unit, string $hint = '' ): void {
		echo '<p class="mgd-la-field"><label for="mgd-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label>';
		echo '<span class="mgd-la-num"><input type="number" id="mgd-' . esc_attr( $key ) . '" name="' . esc_attr( $this->name( $key ) ) . '" value="' . esc_attr( (string) $s[ $key ] ) . '" min="' . (int) $min . '" max="' . (int) $max . '" placeholder="Standard" data-key="' . esc_attr( $key ) . '"> ' . esc_html( $unit ) . '</span>';
		if ( '' !== $hint ) {
			echo '<span class="description">' . esc_html( $hint ) . '</span>';
		}
		echo '</p>';
	}

	private function media( array $s, string $key, string $label, string $button ): void {
		$id  = (int) $s[ $key ];
		$img = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
		echo '<div class="mgd-la-media" data-media="' . esc_attr( $key ) . '"><span class="mgd-la-media__label">' . esc_html( $label ) . '</span>';
		echo '<div class="mgd-la-media__box">' . ( $img ? '<img src="' . esc_url( $img ) . '" alt="">' : '<span>Kein Bild</span>' ) . '</div>';
		echo '<input type="hidden" name="' . esc_attr( $this->name( $key ) ) . '" value="' . esc_attr( (string) $id ) . '">';
		echo '<button type="button" class="button" data-pick>' . esc_html( $button ) . '</button> <button type="button" class="button-link" data-clear' . ( $id ? '' : ' hidden' ) . '>Entfernen</button></div>';
	}

	public function page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Nicht berechtigt.', '', array( 'response' => 403 ) );
		}
		$s        = MGD_Login_Settings::get();
		$all      = MGD_Login_Templates::all();
		$unlocked = MGD_Login_Templates::pro_unlocked();
		$current  = isset( $all[ $s['template'] ] ) ? $s['template'] : 'aurora';
		?>
		<div class="wrap mgd-la">
			<header class="mgd-la-hero">
				<div>
					<p class="mgd-la-kicker">MGD · WORDPRESS</p>
					<h1>MGD WordPress Login</h1>
					<p>Gib deiner Anmeldeseite ein Gesicht. Template wählen, Farben und Logo anpassen, rechts live ansehen.</p>
				</div>
				<div class="mgd-la-hero__actions">
					<a class="button button-primary" href="<?php echo esc_url( wp_login_url() ); ?>" target="_blank" rel="noopener">Login-Seite öffnen <span class="screen-reader-text">(neuer Tab)</span></a>
					<a class="button" href="<?php echo esc_url( MGD_Login_Templates::SHOP_URL ); ?>" target="_blank" rel="noopener">Mehr Templates <span class="screen-reader-text">(neuer Tab)</span></a>
				</div>
			</header>
			<hr class="wp-header-end">
			<?php settings_errors(); ?>

			<form id="mgd-la-form" method="post" action="options.php">
				<?php settings_fields( 'mgd_login' ); ?>
				<input type="hidden" name="mgd_preview_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mgd_login_preview' ) ); ?>">

				<div class="mgd-la-grid">
					<div class="mgd-la-main">

						<section class="mgd-la-box">
							<h2><span>1</span> Template wählen</h2>
							<p class="description">Klick auf eine Karte. Die Vorschau rechts ändert sich sofort. Gespeichert wird erst mit dem Button unten.</p>
							<div class="mgd-la-tpls" role="radiogroup" aria-label="Templates">
								<?php foreach ( $all as $id => $tpl ) :
									$locked = 'pro' === ( $tpl['tier'] ?? 'free' ) && ! $unlocked;
									$v      = $tpl['vars'];
									?>
									<label class="mgd-la-tpl<?php echo $locked ? ' is-locked' : ''; ?>">
										<input type="radio" name="<?php echo esc_attr( $this->name( 'template' ) ); ?>" value="<?php echo esc_attr( $id ); ?>" <?php checked( $current, $id ); ?> <?php disabled( $locked ); ?>>
										<span class="mgd-la-tpl__thumb" style="--b1:<?php echo esc_attr( $v['bg1'] ); ?>;--b2:<?php echo esc_attr( $v['bg2'] ); ?>;--ac:<?php echo esc_attr( $v['accent'] ); ?>;--cd:<?php echo esc_attr( $v['card'] ); ?>"><i><b></b><b></b><b class="btn"></b></i></span>
										<span class="mgd-la-tpl__name"><?php echo esc_html( $tpl['name'] ); ?><?php echo 'pro' === ( $tpl['tier'] ?? 'free' ) ? ' <em>PRO</em>' : ''; ?></span>
										<span class="mgd-la-tpl__desc"><?php echo esc_html( $tpl['desc'] ); ?></span>
										<span class="mgd-la-tpl__tags"><?php foreach ( $tpl['tags'] as $tag ) { echo '<span>' . esc_html( $tag ) . '</span>'; } ?></span>
									</label>
								<?php endforeach; ?>
								<?php foreach ( MGD_Login_Templates::teasers() as $tid => $teaser ) : ?>
									<a class="mgd-la-tpl is-teaser" href="<?php echo esc_url( MGD_Login_Templates::SHOP_URL ); ?>" target="_blank" rel="noopener">
										<span class="mgd-la-tpl__thumb" style="--b1:<?php echo esc_attr( $teaser['colors'][0] ); ?>;--b2:<?php echo esc_attr( $teaser['colors'][1] ); ?>;--ac:<?php echo esc_attr( $teaser['colors'][2] ); ?>;--cd:#ffffff"><i><b></b><b></b><b class="btn"></b></i><span class="mgd-la-lock">PRO · bald</span></span>
										<span class="mgd-la-tpl__name"><?php echo esc_html( $teaser['name'] ); ?> <em>PRO</em></span>
										<span class="mgd-la-tpl__desc"><?php echo esc_html( $teaser['desc'] ); ?></span>
										<span class="mgd-la-tpl__tags"><span>Kostenpflichtig</span><span>theme.michael-gahn.de</span></span>
									</a>
								<?php endforeach; ?>
							</div>
						</section>

						<section class="mgd-la-box">
							<h2><span>2</span> Farben</h2>
							<p class="description">Feld leer lassen = Farbe des Templates. Mit „Leeren“ im Farbwähler gehst du zurück.</p>
							<div class="mgd-la-cols">
								<?php
								$this->color( $s, 'accent', 'Akzentfarbe (Button, Fokus)' );
								$this->color( $s, 'bg1', 'Hintergrund Farbe 1' );
								$this->color( $s, 'bg2', 'Hintergrund Farbe 2' );
								$this->color( $s, 'card', 'Farbe der Login-Karte' );
								$this->color( $s, 'text', 'Textfarbe' );
								?>
							</div>
							<p id="mgd-la-contrast" class="mgd-la-warn" role="status" hidden>⚠️ Text und Karte haben wenig Kontrast. Das ist schwer lesbar. Wähle eine hellere oder dunklere Textfarbe.</p>
						</section>

						<section class="mgd-la-box">
							<h2><span>3</span> Logo und Hintergrundbild</h2>
							<div class="mgd-la-cols">
								<?php $this->media( $s, 'logo_id', 'Logo', 'Logo wählen' ); ?>
								<?php $this->media( $s, 'bg_id', 'Hintergrundbild (optional)', 'Bild wählen' ); ?>
							</div>
							<div class="mgd-la-cols">
								<?php $this->number( $s, 'logo_height', 'Logo-Höhe', 24, 200, 'px' ); ?>
								<p class="mgd-la-field"><label for="mgd-logo_mode">Ohne Logo zeigen</label>
									<select id="mgd-logo_mode" name="<?php echo esc_attr( $this->name( 'logo_mode' ) ); ?>">
										<option value="name" <?php selected( $s['logo_mode'], 'name' ); ?>>Namen der Website</option>
										<option value="wp" <?php selected( $s['logo_mode'], 'wp' ); ?>>WordPress-Logo</option>
										<option value="none" <?php selected( $s['logo_mode'], 'none' ); ?>>Nichts</option>
									</select></p>
								<?php $this->number( $s, 'overlay', 'Abdunklung über dem Bild', 0, 90, '%', 'Hilft, wenn das Bild zu hell oder unruhig ist.' ); ?>
							</div>
						</section>

						<section class="mgd-la-box">
							<h2><span>4</span> Karte und Schrift</h2>
							<div class="mgd-la-cols">
								<?php
								$this->number( $s, 'radius', 'Eckenrundung', 0, 48, 'px' );
								$this->number( $s, 'card_opacity', 'Deckkraft der Karte', 10, 100, '%', '100 = ganz fest, weniger = durchsichtig.' );
								$this->number( $s, 'blur', 'Glas-Unschärfe', 0, 40, 'px' );
								$this->number( $s, 'card_width', 'Breite der Karte', 300, 560, 'px' );
								?>
								<p class="mgd-la-field"><label for="mgd-font">Schriftart</label>
									<select id="mgd-font" name="<?php echo esc_attr( $this->name( 'font' ) ); ?>">
										<?php foreach ( MGD_Login_Settings::fonts() as $fid => $font ) { echo '<option value="' . esc_attr( $fid ) . '" ' . selected( $s['font'], $fid, false ) . '>' . esc_html( $font[0] ) . '</option>'; } ?>
									</select>
									<span class="description">Nur Schriften, die schon auf dem Gerät sind. Kein Download, kein Datenschutz-Problem.</span></p>
							</div>
						</section>

						<section class="mgd-la-box">
							<h2><span>5</span> Texte</h2>
							<p class="mgd-la-field"><label for="mgd-title">Überschrift über dem Formular</label>
								<input type="text" class="large-text" id="mgd-title" name="<?php echo esc_attr( $this->name( 'title' ) ); ?>" value="<?php echo esc_attr( $s['title'] ); ?>" placeholder="z. B. Willkommen zurück" maxlength="80"></p>
							<p class="mgd-la-field"><label for="mgd-subtitle">Text darunter</label>
								<input type="text" class="large-text" id="mgd-subtitle" name="<?php echo esc_attr( $this->name( 'subtitle' ) ); ?>" value="<?php echo esc_attr( $s['subtitle'] ); ?>" placeholder="z. B. Melde dich an, um loszulegen" maxlength="140"></p>
							<p class="mgd-la-field"><label for="mgd-footer">Fußzeile (Links, fett und kursiv erlaubt)</label>
								<textarea class="large-text" rows="2" id="mgd-footer" name="<?php echo esc_attr( $this->name( 'footer' ) ); ?>" maxlength="400" placeholder="z. B. &lt;a href=&quot;/datenschutz&quot;&gt;Datenschutz&lt;/a&gt;"><?php echo esc_textarea( $s['footer'] ); ?></textarea></p>
							<p><label><input type="checkbox" name="<?php echo esc_attr( $this->name( 'hide_back' ) ); ?>" value="1" <?php checked( $s['hide_back'], 1 ); ?>> Link „Zurück zur Website“ ausblenden</label></p>
						</section>

						<section class="mgd-la-box">
							<h2><span>6</span> Animation</h2>
							<p><label><input type="checkbox" name="<?php echo esc_attr( $this->name( 'animation' ) ); ?>" value="1" <?php checked( $s['animation'], 1 ); ?>> Animationen einschalten</label></p>
							<?php $this->number( $s, 'speed', 'Tempo', 25, 300, '%', '100 = normal, 50 = halb so schnell, 200 = doppelt so schnell.' ); ?>
							<p class="description">⚠️ Wer in seinem Gerät „Bewegung reduzieren“ eingestellt hat, sieht automatisch keine Animation. Das ist Absicht.</p>
						</section>

						<section class="mgd-la-box">
							<h2><span>7</span> Für Fortgeschrittene</h2>
							<p class="mgd-la-field"><label for="mgd-custom_css">Eigenes CSS für die Login-Seite</label>
								<textarea class="large-text code" rows="6" id="mgd-custom_css" name="<?php echo esc_attr( $this->name( 'custom_css' ) ); ?>" spellcheck="false" placeholder="body.login #login h1 a { }"><?php echo esc_textarea( $s['custom_css'] ); ?></textarea>
								<span class="description">Kein HTML, kein @import. Wird nach dem Template geladen.</span></p>
							<p><label><input type="checkbox" name="<?php echo esc_attr( $this->name( 'delete_data' ) ); ?>" value="1" <?php checked( $s['delete_data'], 1 ); ?>> Einstellungen beim Löschen des Plugins entfernen</label></p>
						</section>

						<p class="mgd-la-save">
							<?php submit_button( 'Änderungen speichern', 'primary large', 'mgd_save', false ); ?>
							<button type="button" class="button button-large" id="mgd-la-reset">Farben und Maße auf Template-Standard</button>
						</p>
					</div>

					<aside class="mgd-la-side" aria-label="Live-Vorschau">
						<div class="mgd-la-side__bar">
							<strong>Live-Vorschau</strong>
							<span class="mgd-la-devices" role="group" aria-label="Vorschau-Größe">
								<button type="button" class="button is-active" data-w="100%" aria-pressed="true">Desktop</button>
								<button type="button" class="button" data-w="768px" aria-pressed="false">Tablet</button>
								<button type="button" class="button" data-w="390px" aria-pressed="false">Handy</button>
							</span>
						</div>
						<div class="mgd-la-frame"><iframe name="mgd-la-preview" title="Vorschau der Login-Seite" loading="lazy"></iframe></div>
						<p class="description">Die Vorschau zeigt ein Beispiel-Formular. Anmelden geht hier nicht.</p>
					</aside>
				</div>
			</form>
		</div>
		<?php
	}
}
