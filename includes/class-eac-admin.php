<?php
/**
 * EAC Admin Class
 * Admin interface and settings management
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EAC_Admin {
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_eac_update_settings', array( $this, 'ajax_update_settings' ) );
		add_action( 'wp_ajax_eac_change_password', array( $this, 'ajax_change_password' ) );
	}

	public function render_settings_page() {
		$menus = EAC_Menu_Detector::get_all_admin_menus();
		$settings = EAC_Visibility_Manager::get_settings();
		$restricted = isset( $settings['sidebar_menus'] ) ? $settings['sidebar_menus'] : array();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Enterprise Access Control Settings', 'enterprise-access-control' ); ?></h1>
			
			<nav class="nav-tab-wrapper">
				<a href="#" class="nav-tab nav-tab-active" data-tab="sidebar"><?php esc_html_e( 'Sidebar Menus', 'enterprise-access-control' ); ?></a>
				<a href="#" class="nav-tab" data-tab="toolbar"><?php esc_html_e( 'Toolbar Items', 'enterprise-access-control' ); ?></a>
				<a href="#" class="nav-tab" data-tab="security"><?php esc_html_e( 'Security', 'enterprise-access-control' ); ?></a>
			</nav>

			<div class="eac-settings-container" style="background: #fff; border: 1px solid #ccc; border-top: none; padding: 20px;">
				<?php $this->render_sidebar_menus_tab( $menus, $restricted ); ?>
				<?php $this->render_toolbar_tab(); ?>
				<?php $this->render_security_tab(); ?>
			</div>
		</div>

		<style>
			.eac-tab-content { display: none; }
			.eac-tab-content.active { display: block; }
			.eac-checkbox-list { list-style: none; margin: 0; padding: 0; }
			.eac-checkbox-list li { padding: 8px 0; border-bottom: 1px solid #f1f1f1; }
			.eac-checkbox-list li:last-child { border-bottom: none; }
			.eac-checkbox-list input[type="checkbox"] { margin-right: 8px; cursor: pointer; }
			.eac-checkbox-list label { cursor: pointer; display: inline-block; margin: 0; padding: 4px 0; }
			.eac-button-group { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
			.eac-button { background-color: #0073aa; border: 1px solid #0073aa; color: #fff; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; transition: background-color 0.2s; margin-right: 10px; border-style: solid; font-weight: 500; }
			.eac-button:hover { background-color: #005a87; }
			.eac-button:active { background-color: #004a6f; }
			.eac-button.secondary { background-color: #e0e0e0; color: #333; border-color: #e0e0e0; }
			.eac-button.secondary:hover { background-color: #d0d0d0; }
			.eac-status-message { padding: 12px; border-radius: 4px; margin-bottom: 20px; display: none; } 
			.eac-status-message.success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; display: block !important; }
			.eac-status-message.error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; display: block !important; }
			.eac-bulk-actions { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
		</style>

		<script>
			var eacNonce = '<?php echo wp_create_nonce( 'eac_nonce' ); ?>';
			
			document.addEventListener('DOMContentLoaded', function() {
				console.log('EAC Settings page loaded');
				
				// Tab navigation
				document.querySelectorAll('.nav-tab').forEach(tab => {
					tab.addEventListener('click', function(e) {
						e.preventDefault();
						const tabName = this.getAttribute('data-tab');
						console.log('Switching to tab:', tabName);
						
						document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('nav-tab-active'));
						document.querySelectorAll('.eac-tab-content').forEach(t => t.classList.remove('active'));
						
						this.classList.add('nav-tab-active');
						document.getElementById('eac-tab-' + tabName).classList.add('active');
					});
				});

				// Select All / Unselect All
				document.querySelectorAll('.eac-select-all').forEach(btn => {
					btn.addEventListener('click', function(e) {
						e.preventDefault();
						const container = this.closest('.eac-tab-content');
						const checkboxes = container.querySelectorAll('input[type="checkbox"]');
						checkboxes.forEach(cb => cb.checked = true);
						console.log('All items hidden');
					});
				});

				document.querySelectorAll('.eac-unselect-all').forEach(btn => {
					btn.addEventListener('click', function(e) {
						e.preventDefault();
						const container = this.closest('.eac-tab-content');
						const checkboxes = container.querySelectorAll('input[type="checkbox"]');
						checkboxes.forEach(cb => cb.checked = false);
						console.log('All items shown');
					});
				});

				// Save Settings
				document.querySelectorAll('.eac-save-settings').forEach(btn => {
					btn.addEventListener('click', function(e) {
						e.preventDefault();
						console.log('Save button clicked');
						eacSaveSettings(this);
					});
				});
			});

			function eacSaveSettings(btn) {
				console.log('eacSaveSettings function called');
				const container = btn.closest('.eac-tab-content');
				const checkboxes = container.querySelectorAll('input[type="checkbox"]');
				
				const restricted = [];
				checkboxes.forEach(cb => {
					if (cb.checked) {
						restricted.push(cb.value);
					}
				});

				console.log('Restricted items:', restricted);
				console.log('Nonce:', eacNonce);

				const formData = new FormData();
				formData.append('action', 'eac_update_settings');
				formData.append('nonce', eacNonce);
				formData.append('restricted', JSON.stringify(restricted));

				console.log('Sending AJAX request...');

				fetch(ajaxurl, {
					method: 'POST',
					body: formData
				})
				.then(response => {
					console.log('Response status:', response.status);
					return response.json();
				})
				.then(data => {
					console.log('AJAX Response:', data);
					const msgDiv = container.querySelector('.eac-status-message');
					
					if (data.success) {
						msgDiv.textContent = '✓ Settings saved successfully!';
						msgDiv.classList.remove('error');
						msgDiv.classList.add('success');
						console.log('Success message shown');
					} else {
						msgDiv.textContent = data.data || 'Error saving settings';
						msgDiv.classList.remove('success');
						msgDiv.classList.add('error');
						console.log('Error message shown');
					}
					msgDiv.style.display = 'block';
					setTimeout(() => {
						msgDiv.style.display = 'none';
					}, 5000);
				})
				.catch(error => {
					console.error('AJAX Error:', error);
					const msgDiv = container.querySelector('.eac-status-message');
					msgDiv.textContent = 'Error: ' + error.message;
					msgDiv.classList.remove('success');
					msgDiv.classList.add('error');
					msgDiv.style.display = 'block';
				});
			}
		</script>
		<?php
	}

	private function render_sidebar_menus_tab( $menus, $restricted ) {
		?>
		<div id="eac-tab-sidebar" class="eac-tab-content active" data-tab="sidebar">
			<div class="eac-status-message"></div>
			
			<div class="eac-bulk-actions">
				<button class="eac-button secondary eac-select-all"><?php esc_html_e( 'Hide All', 'enterprise-access-control' ); ?></button>
				<button class="eac-button secondary eac-unselect-all"><?php esc_html_e( 'Show All', 'enterprise-access-control' ); ?></button>
			</div>

			<h3><?php esc_html_e( 'Sidebar Menus', 'enterprise-access-control' ); ?></h3>
			<p style="color: #666; margin-bottom: 15px;"><?php esc_html_e( 'Check items to HIDE them from sidebar', 'enterprise-access-control' ); ?></p>
			<ul class="eac-checkbox-list">
				<?php if ( ! empty( $menus ) ) : ?>
					<?php foreach ( $menus as $slug => $menu ) : ?>
						<li>
							<input type="checkbox" id="menu-<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $restricted ) ); ?> />
							<label for="menu-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $menu['title'] ); ?></label>
						</li>
						<?php if ( ! empty( $menu['submenu_items'] ) ) : ?>
							<?php foreach ( $menu['submenu_items'] as $sub_slug => $sub_menu ) : ?>
								<li style="margin-left: 20px;">
									<input type="checkbox" id="menu-<?php echo esc_attr( $sub_slug ); ?>" value="<?php echo esc_attr( $sub_slug ); ?>" <?php checked( in_array( $sub_slug, $restricted ) ); ?> />
									<label for="menu-<?php echo esc_attr( $sub_slug ); ?>"><?php echo esc_html( $sub_menu['title'] ); ?></label>
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php else : ?>
					<li><p><?php esc_html_e( 'No menus found', 'enterprise-access-control' ); ?></p></li>
				<?php endif; ?>
			</ul>

			<div class="eac-button-group">
				<button class="eac-button eac-save-settings"><?php esc_html_e( 'Save Settings', 'enterprise-access-control' ); ?></button>
			</div>
		</div>
		<?php
	}

	private function render_toolbar_tab() {
		?>
		<div id="eac-tab-toolbar" class="eac-tab-content" data-tab="toolbar">
			<div class="eac-status-message"></div>
			<p><?php esc_html_e( 'Toolbar control - Coming in future updates', 'enterprise-access-control' ); ?></p>
		</div>
		<?php
	}

	private function render_security_tab() {
		?>
		<div id="eac-tab-security" class="eac-tab-content" data-tab="security">
			<div class="eac-status-message"></div>
			
			<h3><?php esc_html_e( 'Change Master Password', 'enterprise-access-control' ); ?></h3>
			<p><strong><?php esc_html_e( 'Current Password:', 'enterprise-access-control' ); ?></strong> <code style="background: #f1f1f1; padding: 5px 10px; border-radius: 3px;">99999999999999999999</code></p>
			
			<div style="max-width: 400px;">
				<label style="display: block; margin-bottom: 10px; font-weight: 500;"><?php esc_html_e( 'New Password:', 'enterprise-access-control' ); ?></label>
				<input type="password" id="eac-new-password" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px;" />
				
				<label style="display: block; margin-bottom: 10px; font-weight: 500;"><?php esc_html_e( 'Confirm Password:', 'enterprise-access-control' ); ?></label>
				<input type="password" id="eac-confirm-password" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;" />

				<button class="eac-button" id="eac-change-password-btn"><?php esc_html_e( 'Update Password', 'enterprise-access-control' ); ?></button>
			</div>

			<script>
				document.getElementById('eac-change-password-btn').addEventListener('click', function() {
					const newPassword = document.getElementById('eac-new-password').value;
					const confirmPassword = document.getElementById('eac-confirm-password').value;
					const msgDiv = document.querySelector('#eac-tab-security .eac-status-message');

					if (!newPassword) {
						msgDiv.textContent = '<?php esc_html_e( 'Please enter a new password', 'enterprise-access-control' ); ?>';
						msgDiv.classList.remove('success');
						msgDiv.classList.add('error');
						msgDiv.style.display = 'block';
						return;
					}

					if (newPassword !== confirmPassword) {
						msgDiv.textContent = '<?php esc_html_e( 'Passwords do not match', 'enterprise-access-control' ); ?>';
						msgDiv.classList.remove('success');
						msgDiv.classList.add('error');
						msgDiv.style.display = 'block';
						return;
					}

					if (newPassword.length < 8) {
						msgDiv.textContent = '<?php esc_html_e( 'Password must be at least 8 characters', 'enterprise-access-control' ); ?>';
						msgDiv.classList.remove('success');
						msgDiv.classList.add('error');
						msgDiv.style.display = 'block';
						return;
					}

					const formData = new FormData();
					formData.append('action', 'eac_change_password');
					formData.append('nonce', eacNonce);
					formData.append('password', newPassword);

					fetch(ajaxurl, {
						method: 'POST',
						body: formData
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							msgDiv.textContent = '✓ Password updated successfully!';
							msgDiv.classList.remove('error');
							msgDiv.classList.add('success');
							document.getElementById('eac-new-password').value = '';
							document.getElementById('eac-confirm-password').value = '';
						} else {
							msgDiv.textContent = data.data || '<?php esc_html_e( 'Error updating password', 'enterprise-access-control' ); ?>';
							msgDiv.classList.remove('success');
							msgDiv.classList.add('error');
						}
						msgDiv.style.display = 'block';
					});
				});
			</script>
		</div>
		<?php
	}

	public function ajax_update_settings() {
		check_ajax_referer( 'eac_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$restricted = isset( $_POST['restricted'] ) ? json_decode( sanitize_text_field( $_POST['restricted'] ), true ) : array();

		if ( ! is_array( $restricted ) ) {
			$restricted = array();
		}

		$settings = EAC_Visibility_Manager::get_settings();
		$settings['sidebar_menus'] = array_map( 'sanitize_text_field', $restricted );

		if ( EAC_Visibility_Manager::update_settings( $settings ) ) {
			wp_send_json_success( array( 'message' => 'Settings updated successfully' ) );
		} else {
			wp_send_json_error( 'Failed to update settings' );
		}
	}

	public function ajax_change_password() {
		check_ajax_referer( 'eac_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$password = isset( $_POST['password'] ) ? sanitize_text_field( $_POST['password'] ) : '';

		if ( empty( $password ) ) {
			wp_send_json_error( 'Password is required' );
		}

		if ( EAC_Security::change_password( $password ) ) {
			wp_send_json_success( array( 'message' => 'Password updated successfully' ) );
		} else {
			wp_send_json_error( 'Failed to update password' );
		}
	}
}

EAC_Admin::get_instance();
