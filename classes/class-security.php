<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EAC_Security {

	public static function get_default_password() {
		return '123456';
	}

	public static function is_authenticated() {
		if ( ! isset( $_SESSION ) || session_status() === PHP_SESSION_NONE ) {
			@session_start();
		}

		return isset( $_SESSION['eac_authenticated'] ) && $_SESSION['eac_authenticated'] === true;
	}

	public static function authenticate( $password ) {
		if ( ! isset( $_SESSION ) || session_status() === PHP_SESSION_NONE ) {
			@session_start();
		}

		$stored_password = get_option( 'eac_password', wp_hash_password( self::get_default_password() ) );
		
		if ( wp_check_password( $password, $stored_password ) ) {
			$_SESSION['eac_authenticated'] = true;
			return true;
		}

		return false;
	}

	public static function logout() {
		if ( ! isset( $_SESSION ) || session_status() === PHP_SESSION_NONE ) {
			@session_start();
		}
		unset( $_SESSION['eac_authenticated'] );
	}

	public static function change_password( $new_password ) {
		$hashed = wp_hash_password( $new_password );
		return update_option( 'eac_password', $hashed );
	}

	public static function render_login_page() {
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Access Control - Authenticate</title>
			<style>
				* { margin: 0; padding: 0; box-sizing: border-box; }
				body {
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
					background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
					min-height: 100vh;
					display: flex;
					align-items: center;
					justify-content: center;
				}
				.login-container {
					background: white;
					padding: 40px;
					border-radius: 8px;
					box-shadow: 0 10px 40px rgba(0,0,0,0.2);
					width: 100%;
					max-width: 400px;
				}
				.login-container h1 {
					text-align: center;
					color: #333;
					margin-bottom: 10px;
					font-size: 28px;
				}
				.login-container p {
					text-align: center;
					color: #666;
					margin-bottom: 30px;
					font-size: 14px;
				}
				.error-message {
					background: #f8d7da;
					border: 1px solid #f5c6cb;
					color: #721c24;
					padding: 12px;
					border-radius: 4px;
					margin-bottom: 20px;
					display: none;
				}
				.error-message.show { display: block; }
				.form-group {
					margin-bottom: 20px;
				}
				.form-group label {
					display: block;
					margin-bottom: 8px;
					color: #333;
					font-weight: 600;
				}
				.form-group input {
					width: 100%;
					padding: 12px;
					border: 1px solid #ddd;
					border-radius: 4px;
					font-size: 14px;
					transition: border-color 0.3s;
				}
				.form-group input:focus {
					outline: none;
					border-color: #667eea;
				}
				.btn-login {
					width: 100%;
					padding: 12px;
					background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
					color: white;
					border: none;
					border-radius: 4px;
					font-size: 16px;
					font-weight: 600;
					cursor: pointer;
					transition: transform 0.2s, box-shadow 0.2s;
				}
				.btn-login:hover {
					transform: translateY(-2px);
					box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
				}
				.btn-login:active {
					transform: translateY(0);
				}
				.default-password {
					text-align: center;
					margin-top: 20px;
					font-size: 12px;
					color: #999;
				}
				.default-password code {
					background: #f5f5f5;
					padding: 4px 8px;
					border-radius: 3px;
					font-family: monospace;
				}
			</style>
		</head>
		<body>
			<div class="login-container">
				<h1>🔐 Access Control</h1>
				<p>Enter password to manage menu visibility</p>

				<div class="error-message" id="errorMsg">Invalid password</div>

				<form id="loginForm" method="POST">
					<div class="form-group">
						<label for="password">Password:</label>
						<input type="password" id="password" name="password" required autofocus>
					</div>
					<button type="submit" class="btn-login">Unlock Access</button>
				</form>

				<div class="default-password">
					Default: <code>123456</code>
				</div>
			</div>

			<script>
				document.getElementById('loginForm').addEventListener('submit', function(e) {
					e.preventDefault();
					const password = document.getElementById('password').value;
					const errorMsg = document.getElementById('errorMsg');

					var xhr = new XMLHttpRequest();
					xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
					xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

					xhr.onload = function() {
						var response = JSON.parse(xhr.responseText);
						if (response.success) {
							window.location.reload();
						} else {
							errorMsg.classList.add('show');
							document.getElementById('password').value = '';
							setTimeout(() => errorMsg.classList.remove('show'), 3000);
						}
					};

					xhr.send('action=eac_authenticate&password=' + encodeURIComponent(password));
				});
			</script>
		</body>
		</html>
		<?php
		exit;
	}
}
