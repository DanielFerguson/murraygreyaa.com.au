<?php
/**
 * Cloudflare Turnstile integration for login and registration forms.
 *
 * @package Tailwind_ACF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Turnstile keys - replace with your actual keys from Cloudflare Dashboard.
if ( ! defined( 'TAILWIND_TURNSTILE_SITE_KEY' ) ) {
	define( 'TAILWIND_TURNSTILE_SITE_KEY', '' ); // Add your site key here.
}
if ( ! defined( 'TAILWIND_TURNSTILE_SECRET_KEY' ) ) {
	define( 'TAILWIND_TURNSTILE_SECRET_KEY', '' ); // Add your secret key here.
}

/**
 * Check if Turnstile is configured with valid keys.
 *
 * @return bool
 */
function tailwind_turnstile_is_configured() {
	return ! empty( TAILWIND_TURNSTILE_SITE_KEY ) && ! empty( TAILWIND_TURNSTILE_SECRET_KEY );
}

/**
 * Verify Turnstile token with Cloudflare.
 *
 * @param string $token The turnstile response token.
 * @return bool True if valid, false otherwise.
 */
function tailwind_turnstile_verify( $token ) {
	if ( empty( $token ) ) {
		return false;
	}

	$response = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		array(
			'body' => array(
				'secret'   => TAILWIND_TURNSTILE_SECRET_KEY,
				'response' => $token,
				'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$result = json_decode( wp_remote_retrieve_body( $response ), true );

	return ! empty( $result['success'] );
}

/**
 * Output Turnstile widget HTML.
 *
 * @param string $theme Widget theme: 'light', 'dark', or 'auto'.
 */
function tailwind_turnstile_widget( $theme = 'light' ) {
	if ( ! tailwind_turnstile_is_configured() ) {
		return;
	}
	?>
	<div class="cf-turnstile" data-sitekey="<?php echo esc_attr( TAILWIND_TURNSTILE_SITE_KEY ); ?>" data-theme="<?php echo esc_attr( $theme ); ?>"></div>
	<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
	<?php
}

/**
 * Add Turnstile widget to registration form.
 */
function tailwind_turnstile_register_form() {
	if ( ! tailwind_turnstile_is_configured() ) {
		return;
	}
	?>
	<p>
		<?php tailwind_turnstile_widget( 'light' ); ?>
	</p>
	<?php
}
add_action( 'register_form', 'tailwind_turnstile_register_form' );

/**
 * Validate Turnstile response on registration.
 *
 * @param WP_Error $errors              Registration errors.
 * @param string   $sanitized_user_login Sanitized username.
 * @param string   $user_email           User email.
 * @return WP_Error
 */
function tailwind_turnstile_validate_registration( $errors, $sanitized_user_login, $user_email ) {
	if ( ! tailwind_turnstile_is_configured() ) {
		return $errors;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Turnstile has its own verification.
	$token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';

	if ( empty( $token ) ) {
		$errors->add( 'turnstile_missing', __( '<strong>Error</strong>: Please complete the security check.', 'tailwind-acf' ) );
		return $errors;
	}

	if ( ! tailwind_turnstile_verify( $token ) ) {
		$errors->add( 'turnstile_failed', __( '<strong>Error</strong>: Security check failed. Please try again.', 'tailwind-acf' ) );
	}

	return $errors;
}
add_filter( 'registration_errors', 'tailwind_turnstile_validate_registration', 10, 3 );

/**
 * Add Turnstile widget to login form.
 */
function tailwind_turnstile_login_form() {
	if ( ! tailwind_turnstile_is_configured() ) {
		return;
	}
	?>
	<p>
		<?php tailwind_turnstile_widget( 'light' ); ?>
	</p>
	<?php
}
add_action( 'login_form', 'tailwind_turnstile_login_form' );

/**
 * Validate Turnstile response on login.
 *
 * @param WP_User|WP_Error $user     User object or error.
 * @param string           $password Password entered.
 * @return WP_User|WP_Error
 */
function tailwind_turnstile_validate_login( $user, $password ) {
	if ( ! tailwind_turnstile_is_configured() ) {
		return $user;
	}

	// Don't check if already errored.
	if ( is_wp_error( $user ) ) {
		return $user;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Turnstile has its own verification.
	$token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';

	if ( empty( $token ) ) {
		return new WP_Error( 'turnstile_missing', __( '<strong>Error</strong>: Please complete the security check.', 'tailwind-acf' ) );
	}

	if ( ! tailwind_turnstile_verify( $token ) ) {
		return new WP_Error( 'turnstile_failed', __( '<strong>Error</strong>: Security check failed. Please try again.', 'tailwind-acf' ) );
	}

	return $user;
}
add_filter( 'wp_authenticate_user', 'tailwind_turnstile_validate_login', 5, 2 );

/**
 * Add Turnstile widget to lost password form.
 */
function tailwind_turnstile_lostpassword_form() {
	if ( ! tailwind_turnstile_is_configured() ) {
		return;
	}
	?>
	<p>
		<?php tailwind_turnstile_widget( 'light' ); ?>
	</p>
	<?php
}
add_action( 'lostpassword_form', 'tailwind_turnstile_lostpassword_form' );

/**
 * Validate Turnstile response on lost password request.
 *
 * @param WP_Error $errors Error object.
 * @return WP_Error
 */
function tailwind_turnstile_validate_lostpassword( $errors ) {
	if ( ! tailwind_turnstile_is_configured() ) {
		return $errors;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Turnstile has its own verification.
	$token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';

	if ( empty( $token ) ) {
		$errors->add( 'turnstile_missing', __( '<strong>Error</strong>: Please complete the security check.', 'tailwind-acf' ) );
		return $errors;
	}

	if ( ! tailwind_turnstile_verify( $token ) ) {
		$errors->add( 'turnstile_failed', __( '<strong>Error</strong>: Security check failed. Please try again.', 'tailwind-acf' ) );
	}

	return $errors;
}
add_action( 'lostpassword_post', 'tailwind_turnstile_validate_lostpassword' );

