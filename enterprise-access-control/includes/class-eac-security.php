<?php
/**
 * EAC Security Class
 * Password protection and security management
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EAC_Security {
	const SESSION_KEY = 'eac_authenticated';
	const NONCE_ACTION = 'eac_password_verification';

	public static function is_authenticated() {
		if ( ! is_admin() ) {
			return false;
		}

		// Allow super admin without password on manage page
		if ( current_user_can( 'manage_options' ) && isset( $_GET['page'] ) && 'eac-manage' === $_GET['page'] ) {
			if ( isset( $_SESSION[ self::SESSION_KEY ] ) && $_SESSION[ self::SESSION_KEY ] ) {
				return true;
			}

			// Check if password is being submitted
			if ( isset( $_POST['eac_password_submit'] ) ) {
				return self::verify_password();
			}

			return false;
		}

		return isset( $_SESSION[ self::SESSION_KEY ] ) && $_SESSION[ self::SESSION_KEY ];
	}

	public static function verify_password() {
		if ( ! isset( $_POST['eac_password_nonce'] ) || ! wp_verify_nonce( $_POST['eac_password_nonce'], self::NONCE_ACTION ) ) {
			return false;
		}

		$submitted_password = isset( $_POST['eac_password'] ) ? sanitize_text_field( $_POST['eac_password'] ) : '';
		$stored_password = get_option( 'eac_password' );

		if ( wp_check_password( $submitted_password, $stored_password ) ) {
			if ( ! isset( $_SESSION ) ) {
				@session_start();
			}
			$_SESSION[ self::SESSION_KEY ] = true;
			return true;
		}

		return false;
	}

	public static function render_password_page() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0" />
			<title><?php esc_html_e( 'Access Control - Password Required', 'enterprise-access-control' ); ?></title>
			<style>
				* {
					margin: 0;
					padding: 0;
					box-sizing: border-box;
				}
				body {
					background-color: #f1f1f1;
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
					display: flex;
					align-items: center;
					justify-content: center;
					min-height: 100vh;
					padding: 20px;
				}
				.eac-password-container {
					background: #fff;
					border: 1px solid #ddd;
					border-radius: 5px;
					box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
					max-width: 400px;
					width: 100%;
					padding: 40px;
					text-align: center;
				}
				.eac-password-header {
					margin-bottom: 30px;
				}
				.eac-password-header h1 {
					color: #444;
					font-size: 24px;
					margin-bottom: 10px;
					font-weight: 400;
				}
				.eac-password-header p {
					color: #777;
					font-size: 14px;
				}
				.eac-password-form {
					margin-top: 25px;
				}
				.eac-form-group {
					margin-bottom: 20px;
					text-align: left;
				}
				.eac-form-group label {
					display: block;
					margin-bottom: 8px;
					color: #333;
					font-weight: 500;
					font-size: 14px;
				}
				.eac-form-group input[type="password"] {
					width: 100%;
					padding: 10px 12px;
					border: 1px solid #ddd;
					border-radius: 4px;
					font-size: 14px;
					transition: border-color 0.2s;
				}
				.eac-form-group input[type="password"]:focus {
					outline: none;
					border-color: #0073aa;
					box-shadow: 0 0 0 1px #0073aa;
				}
				.eac-form-group button {
					width: 100%;
					padding: 10px;
					background-color: #0073aa;
					color: #fff;
					border: none;
					border-radius: 4px;
					font-size: 14px;
					font-weight: 500;
					cursor: pointer;
					transition: background-color 0.2s;
				}
				.eac-form-group button:hover {
					background-color: #005a87;
				}
				.eac-form-group button:active {
					background-color: #004a6f;
				}
				.eac-error-message {
					background-color: #fee;
					border: 1px solid #fcc;
					color: #c33;
					padding: 12px;
					border-radius: 4px;
					margin-bottom: 20px;
					font-size: 14px;
					display: none;
				}
				.eac-error-message.show {
					display: block;
				}
			</style>
		</head>
		<body>
			<div class="eac-password-container">
				<div class="eac-password-header">
					<h1><?php esc_html_e( 'Access Control', 'enterprise-access-control' ); ?></h1>
					<p><?php esc_html_e( 'Password required to access settings', 'enterprise-access-control' ); ?></p>
				</div>

				<?php if ( isset( $_POST['eac_password_submit'] ) && ! isset( $_SESSION['eac_authenticated'] ) ) : ?>
					<div class="eac-error-message show">
						<?php esc_html_e( 'Invalid password. Please try again.', 'enterprise-access-control' ); ?>
					</div>
				<?php endif; ?>

				<form method="POST" class="eac-password-form">
					<div class="eac-form-group">
						<label for="eac_password"><?php esc_html_e( 'Enter Password:', 'enterprise-access-control' ); ?></label>
						<input type="password" id="eac_password" name="eac_password" required autofocus />
						<?php wp_nonce_field( self::NONCE_ACTION, 'eac_password_nonce' ); ?>
					</div>
					<div class="eac-form-group">
						<button type="submit" name="eac_password_submit"><?php esc_html_e( 'Unlock Settings', 'enterprise-access-control' ); ?></button>
					</div>
				</form>
			</div>
		</body>
		</html>
		<?php
		exit;
	}

	public static function change_password( $new_password ) {
		$hashed_password = wp_hash_password( $new_password );
		update_option( 'eac_password', $hashed_password );
		return true;
	}
}
