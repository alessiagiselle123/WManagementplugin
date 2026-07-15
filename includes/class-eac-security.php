<?php
/**
 * EAC Security - Password protection and authentication
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EAC_Security {
    public static function render_password_page() {
        // Check if form was submitted
        if ( isset( $_POST['eac_password'] ) ) {
            $password = isset( $_POST['eac_password'] ) ? sanitize_text_field( $_POST['eac_password'] ) : '';
            
            if ( EAC_Settings::verify_password( $password ) ) {
                EAC_Settings::set_authenticated( true );
                wp_redirect( admin_url( 'admin.php?page=eac-control' ) );
                exit;
            } else {
                $error = 'Invalid password!';
            }
        }

        // Don't output anything - just render the password form
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Access Control - Enter Password</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .password-container {
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    padding: 40px;
                    width: 100%;
                    max-width: 400px;
                }
                .password-header {
                    text-align: center;
                    margin-bottom: 30px;
                }
                .password-header h1 {
                    font-size: 24px;
                    color: #333;
                    margin-bottom: 10px;
                }
                .password-header p {
                    color: #666;
                    font-size: 14px;
                }
                .password-form {
                    margin-bottom: 20px;
                }
                .form-group {
                    margin-bottom: 20px;
                }
                .form-group label {
                    display: block;
                    margin-bottom: 8px;
                    color: #333;
                    font-weight: 600;
                    font-size: 14px;
                }
                .form-group input {
                    width: 100%;
                    padding: 12px;
                    border: 1px solid #ddd;
                    border-radius: 6px;
                    font-size: 16px;
                    transition: border-color 0.3s;
                }
                .form-group input:focus {
                    outline: none;
                    border-color: #667eea;
                    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                }
                .error-message {
                    background-color: #fee;
                    border: 1px solid #fcc;
                    color: #c33;
                    padding: 12px;
                    border-radius: 6px;
                    margin-bottom: 20px;
                    font-size: 14px;
                }
                .submit-btn {
                    width: 100%;
                    padding: 12px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border: none;
                    border-radius: 6px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: transform 0.2s;
                }
                .submit-btn:hover {
                    transform: translateY(-2px);
                }
                .submit-btn:active {
                    transform: translateY(0);
                }
            </style>
        </head>
        <body>
            <div class="password-container">
                <div class="password-header">
                    <h1>🔐 Access Control</h1>
                    <p>Enter password to manage menu visibility</p>
                </div>

                <?php if ( isset( $error ) ) : ?>
                    <div class="error-message"><?php echo esc_html( $error ); ?></div>
                <?php endif; ?>

                <form method="POST" class="password-form">
                    <div class="form-group">
                        <label for="eac_password">Password:</label>
                        <input type="password" id="eac_password" name="eac_password" required autofocus>
                    </div>
                    <button type="submit" class="submit-btn">🔓 Unlock</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
