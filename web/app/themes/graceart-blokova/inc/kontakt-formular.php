<?php
/**
 * Kontaktný formulár na stránke Kontakt – skratka [graceart_kontakt_formular].
 *
 * Prenesené zo starej témy "graceart". Formulár sa odosiela cez admin-post.php,
 * správa príde e-mailom (wp_mail) na kontaktný e-mail a zákazník sa vráti
 * späť na stránku s potvrdením. Ochrana proti spamu: skryté pole pre roboty,
 * podpísaný čas zobrazenia formulára, limit správ z jednej IP adresy.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const GRACEART_KONTAKT_ACTION = 'graceart_kontakt';
const GRACEART_KONTAKT_NONCE  = 'graceart_kontakt_nonce';

/**
 * Kam chodia správy: kontaktný e-mail (graceart_contact_email), inak e-mail
 * odosielateľa WooCommerce, inak e-mail administrátora.
 */
function graceart_kontakt_email() {
	$candidates = array(
		(string) get_option( 'graceart_contact_email', '' ),
		(string) get_option( 'woocommerce_email_from_address', '' ),
		(string) get_option( 'admin_email', '' ),
	);
	foreach ( $candidates as $email ) {
		$email = sanitize_email( trim( $email ) );
		if ( '' !== $email && is_email( $email ) ) {
			return $email;
		}
	}
	return '';
}

function graceart_kontakt_redirect( $url, $status, $extra = array() ) {
	$args = array_merge( array( 'kontakt' => $status ), $extra );
	wp_safe_redirect( add_query_arg( $args, $url ) . '#kontakt-formular' );
	exit;
}

/**
 * Skryté pole s podpísaným časom zobrazenia formulára ("čas.podpis").
 */
function graceart_kontakt_timing_field() {
	$time = time();
	return '<input type="hidden" name="graceart_ts" value="' . esc_attr( $time . '.' . wp_hash( 'graceart_kontakt_' . $time ) ) . '">';
}

/**
 * Podpis musí sedieť a formulár musí byť zobrazený aspoň 3 sekundy a najviac 7 dní.
 */
function graceart_kontakt_timing_ok( $token ) {
	list( $time, $hash ) = array_pad( explode( '.', $token, 2 ), 2, '' );
	if ( ! ctype_digit( $time ) || ! hash_equals( wp_hash( 'graceart_kontakt_' . $time ), $hash ) ) {
		return false;
	}
	$age = time() - (int) $time;
	return $age >= 3 && $age <= WEEK_IN_SECONDS;
}

/**
 * Najviac 5 správ z jednej IP adresy za hodinu.
 */
function graceart_kontakt_rate_limited() {
	$key   = 'graceart_kontakt_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$count = (int) get_transient( $key );
	if ( $count >= 5 ) {
		return true;
	}
	set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	return false;
}

function graceart_kontakt_handle() {
	$redirect_to = isset( $_POST['redirect_to'] ) ? wp_unslash( $_POST['redirect_to'] ) : '';
	$redirect_to = wp_validate_redirect( esc_url_raw( (string) $redirect_to ), home_url( '/' ) );

	if ( ! isset( $_POST[ GRACEART_KONTAKT_NONCE ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ GRACEART_KONTAKT_NONCE ] ) ), GRACEART_KONTAKT_ACTION ) ) {
		graceart_kontakt_redirect( $redirect_to, 'chyba', array( 'dovod' => 'nonce' ) );
	}

	// Roboty vyplnia skryté pole, ľudia ho nevidia. Robotom "potvrdíme" odoslanie, aby to neskúšali inak.
	if ( ! empty( $_POST['graceart_website'] ) ) {
		graceart_kontakt_redirect( $redirect_to, 'odoslane' );
	}

	// Roboty odosielajú hneď (alebo opakujú starý formulár).
	if ( ! graceart_kontakt_timing_ok( sanitize_text_field( wp_unslash( $_POST['graceart_ts'] ?? '' ) ) ) ) {
		graceart_kontakt_redirect( $redirect_to, 'odoslane' );
	}

	$name    = sanitize_text_field( wp_unslash( $_POST['graceart_name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['graceart_email'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['graceart_message'] ?? '' ) );

	if ( '' === $name || '' === $message || ! is_email( $email ) ) {
		graceart_kontakt_redirect( $redirect_to, 'chyba', array( 'dovod' => 'polia' ) );
	}

	// Správy s veľa odkazmi sú takmer vždy spam.
	if ( preg_match_all( '~https?://|www\.~i', $name . ' ' . $message ) > 2 ) {
		graceart_kontakt_redirect( $redirect_to, 'odoslane' );
	}

	if ( graceart_kontakt_rate_limited() ) {
		graceart_kontakt_redirect( $redirect_to, 'chyba', array( 'dovod' => 'limit' ) );
	}

	$recipient = graceart_kontakt_email();
	if ( '' === $recipient ) {
		graceart_kontakt_redirect( $redirect_to, 'chyba', array( 'dovod' => 'prijemca' ) );
	}

	$sent = wp_mail(
		$recipient,
		sprintf( 'Správa z kontaktného formulára od %s', $name ),
		sprintf( "Meno: %s\nE-mail: %s\n\n%s\n", $name, $email, $message ),
		array(
			'Content-Type: text/plain; charset=UTF-8',
			sprintf( 'Reply-To: %s <%s>', $name, $email ),
		)
	);

	graceart_kontakt_redirect( $redirect_to, $sent ? 'odoslane' : 'chyba', $sent ? array() : array( 'dovod' => 'mail' ) );
}
add_action( 'admin_post_nopriv_' . GRACEART_KONTAKT_ACTION, 'graceart_kontakt_handle' );
add_action( 'admin_post_' . GRACEART_KONTAKT_ACTION, 'graceart_kontakt_handle' );

/**
 * Hláška nad formulárom po odoslaní.
 */
function graceart_kontakt_notice() {
	$status = isset( $_GET['kontakt'] ) ? sanitize_key( wp_unslash( $_GET['kontakt'] ) ) : '';
	if ( 'odoslane' === $status ) {
		return '<p class="graceart-contact-notice is-success" role="status">Ďakujem, správa bola odoslaná. Ozvem sa vám čo najskôr.</p>';
	}
	if ( 'chyba' !== $status ) {
		return '';
	}
	$reasons = array(
		'polia'    => 'Vyplňte, prosím, meno, platný e-mail a správu.',
		'limit'    => 'Odoslali ste príliš veľa správ. Skúste to, prosím, neskôr.',
		'prijemca' => 'Kontaktný e-mail nie je nastavený.',
	);
	$reason = isset( $_GET['dovod'] ) ? sanitize_key( wp_unslash( $_GET['dovod'] ) ) : '';
	$text   = $reasons[ $reason ] ?? 'Správu sa nepodarilo odoslať. Skúste to, prosím, znova alebo napíšte priamo na e-mail.';
	return '<p class="graceart-contact-notice is-error" role="alert">' . esc_html( $text ) . '</p>';
}

/**
 * [graceart_kontakt_formular] – formulár: meno, e-mail, správa.
 */
function graceart_kontakt_formular_shortcode() {
	$privacy = get_privacy_policy_url();
	if ( '' === $privacy ) {
		$privacy = home_url( '/ochrana-sukromia/' );
	}
	ob_start();
	?>
<div class="graceart-contact" id="kontakt-formular">
	<?php echo graceart_kontakt_notice(); // Text je ošetrený vo funkcii. ?>
	<form class="graceart-contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<input type="hidden" name="action" value="<?php echo esc_attr( GRACEART_KONTAKT_ACTION ); ?>">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink( get_queried_object_id() ) ); ?>">
		<?php wp_nonce_field( GRACEART_KONTAKT_ACTION, GRACEART_KONTAKT_NONCE ); ?>
		<?php echo graceart_kontakt_timing_field(); // Hodnota je ošetrená vo funkcii. ?>
		<div class="graceart-contact-row">
			<p><label for="graceart-name">Meno *</label><input type="text" id="graceart-name" name="graceart_name" autocomplete="name" required></p>
			<p><label for="graceart-email">E-mail *</label><input type="email" id="graceart-email" name="graceart_email" autocomplete="email" required></p>
		</div>
		<p><label for="graceart-message">Správa *</label><textarea id="graceart-message" name="graceart_message" rows="7" required></textarea></p>
		<?php /* Pole pre roboty: ľudia ho nevidia. */ ?>
		<div class="graceart-contact-hp" aria-hidden="true">
			<input type="text" name="graceart_website" tabindex="-1" autocomplete="off">
		</div>
		<p class="graceart-contact-privacy">Vaše meno a e-mail použijem len na odpoveď na vašu správu. Viac v <a href="<?php echo esc_url( $privacy ); ?>">Zásadách ochrany osobných údajov</a>.</p>
		<p><button type="submit" class="wp-element-button graceart-contact-submit">Odoslať správu</button></p>
	</form>
</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'graceart_kontakt_formular', 'graceart_kontakt_formular_shortcode' );
