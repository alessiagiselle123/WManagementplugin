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
		add_action( 'wp_ajax_nopriv_eac_update_settings', array( $this, 'ajax_update_settings' ) );
		add_action( 'wp_ajax_eac_update_settings', array( $this, 'ajax_update_settings' ) );
		add_action( 'wp_ajax_nopriv_eac_change_password', array( $this, 'ajax_change_password' ) );
		add_action( 'wp_ajax_eac_change_password', array( $this, 'ajax_change_password' ) );
	}

	public function render_settings_page() {
		$menus = EAC_Menu_Detector::get_all_admin_menus();
		$settings = EAC_Visibility_Manager::get_settings();
		$restricted = isset( $settings['sidebar_menus'] ) ? $settings['sidebar_menus'] : array();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Enterprise Access Control Settings', 'enterprise-access-control' ); ?></h1>
			<p style="color: #666; font-size: 14px;">Only selected items will be visible in WordPress sidebar. All other items will be hidden.</p>
			
			<nav class="nav-tab-wrapper">
				<a href="#" class="nav-tab nav-tab-active" data-tab="show-menu"><?php esc_html_e( 'SHOW - Visible Items', 'enterprise-access-control' ); ?></a>
				<a href="#" class="nav-tab" data-tab="hide-menu"><?php esc_html_e( 'HIDE - Hidden Items', 'enterprise-access-control' ); ?></a>
				<a href="#" class="nav-tab" data-tab="security"><?php esc_html_e( 'Security', 'enterprise-access-control' ); ?></a>
			</nav>

			<div class="eac-settings-container" style="background: #fff; border: 1px solid #ccc; border-top: none; padding: 20px;">
				<?php $this->render_show_menu_tab( $menus, $restricted ); ?>
				<?php $this->render_hide_menu_tab( $menus, $restricted ); ?>
				<?php $this->render_security_tab(); ?>
			</div>
		</div>

		<style>
			.eac-tab-content { display: none; }
			.eac-tab-content.active { display: block; }
			.eac-checkbox-list { list-style: none; margin: 0; padding: 0; }
			.eac-checkbox-list li { padding: 10px; border-bottom: 1px solid #f1f1f1; margin: 0; }
			.eac-checkbox-list li:last-child { border-bottom: none; }
			.eac-checkbox-list li:hover { background: #f9f9f9; }
			.eac-checkbox-list input[type="checkbox"] { margin-right: 10px; cursor: pointer; width: 18px; height: 18px; }
			.eac-checkbox-list label { cursor: pointer; display: inline-block; margin: 0; padding: 0; font-weight: 500; color: #333; }
			.eac-button-group { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
			.eac-button { background-color: #0073aa; border: 1px solid #0073aa; color: #fff; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 14px; transition: background-color 0.2s; margin-right: 10px; border-style: solid; font-weight: 600; }
			.eac-button:hover { background-color: #005a87; }
			.eac-button:active { background-color: #004a6f; }
			.eac-status-message { padding: 15px; border-radius: 4px; margin-bottom: 20px; display: none; font-weight: 500; } 
			.eac-status-message.success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; display: block !important; }
			.eac-status-message.error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; display: block !important; }
			.eac-info-box { background: #e7f3ff; border-left: 4px solid #0073aa; padding: 12px; margin-bottom: 15px; border-radius: 3px; }
			.eac-info-box p { margin: 0; font-size: 14px; color: #0073aa; }
		</style>

		<script>
			var eacNonce = '<?php echo wp_create_nonce( 'eac_nonce' ); ?>';
			
			document.addEventListener('DOMContentLoaded', function() {
				console.log('EAC Settings Loaded');
				
				// Tab navigation
				document.querySelectorAll('.nav-tab').forEach(tab => {
					tab.addEventListener('click', function(e) {
						e.preventDefault();
						const tabName = this.getAttribute('data-tab');
						document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('nav-tab-active'));
						document.querySelectorAll('.eac-tab-content').forEach(t => t.classList.remove('active'));
						this.classList.add('nav-tab-active');
						document.getElementById('eac-tab-' + tabName).classList.add('active');
					});
				});

				// Select All buttons
				document.querySelectorAll('.eac-select-all').forEach(btn => {
					btn.addEventListener('click', function(e) {
						e.preventDefault();
						const container = this.closest('.eac-tab-content');
						const checkboxes = container.querySelectorAll('input[type="checkbox"]');
						checkboxes.forEach(cb => cb.checked = true);
					});
				});

				// Unselect All buttons
				document.querySelectorAll('.eac-unselect-all').forEach(btn => {
					btn.addEventListener('click', function(e) {
						e.preventDefault();
						const container = this.closest('.eac-tab-content');
						const checkboxes = container.querySelectorAll('input[type="checkbox"]');
						checkboxes.forEach(cb => cb.checked = false);
					});
				});

				// Save buttons
				document.querySelectorAll('.eac-save-settings').forEach(btn => {
					btn.addEventListener('click', function(e) {
						e.preventDefault();
						eacSaveSettings(this);
					});
				});
			});

			function eacSaveSettings(btn) {
				const container = btn.closest('.eac-tab-content');
				const tabId = container.id;
				const allCheckboxes = document.querySelectorAll('.eac-tab-content input[type="checkbox"]');
				
				const showItems = [];
				const hideItems = [];
				
				// Get show items (checked in show-menu tab)
				document.querySelectorAll('#eac-tab-show-menu input[type="checkbox"]:checked').forEach(cb => {
					showItems.push(cb.value);
				});
				
				// Get hide items (checked in hide-menu tab)
				document.querySelectorAll('#eac-tab-hide-menu input[type="checkbox"]:checked').forEach(cb => {
					hideItems.push(cb.value);
				});

				console.log('Show Items:', showItems);
				console.log('Hide Items:', hideItems);

				const formData = new FormData();
				formData.append('action', 'eac_update_settings');
				formData.append('nonce', eacNonce);
				formData.append('show_items', JSON.stringify(showItems));
				formData.append('hide_items', JSON.stringify(hideItems));

				fetch(ajaxurl, {
					method: 'POST',
					body: formData
				})
				.then(response => response.json())
				.then(data => {
					const msgDiv = container.querySelector('.eac-status-message');
					
					if (data.success) {
						msgDiv.textContent = '✓ Settings saved successfully!';
						msgDiv.classList.remove('error');
						msgDiv.classList.add('success');
						console.log('Success');
					} else {
						msgDiv.textContent = data.data || 'Error saving settings';
						msgDiv.classList.remove('success');
						msgDiv.classList.add('error');
					}
					msgDiv.style.display = 'block';
					setTimeout(() => {
						msgDiv.style.display = 'none';
					}, 4000);
				})
				.catch(error => {
					console.error('Error:', error);
					const msgDiv = container.querySelector('.eac-status-message');
					msgDiv.textContent = 'Error: ' + error.message;
					msgDiv.classList.add('error');
					msgDiv.style.display = 'block';
				});
			}
		</script>
		<?php
	}

	private function render_show_menu_tab( $menus, $restricted ) {
		?>
		<div id="eac-tab-show-menu" class="eac-tab-content active" data-tab="show-menu">
			<div class="eac-status-message"></div>
			
			<div class="eac-info-box">
				<p>✓ Check all items that should be VISIBLE in sidebar. Only checked items will appear.</p>
			</div>

			<div style="margin-bottom: 15px;">
				<button class="eac-button eac-select-all" style="background-color: #28a745; border-color: #28a745;"><?php esc_html_e( 'Select All (Show Everything)', 'enterprise-access-control' ); ?></button>
				<button class="eac-button eac-unselect-all" style="background-color: #dc3545; border-color: #dc3545;"><?php esc_html_e( 'Unselect All (Hide Everything)', 'enterprise-access-control' ); ?></button>
			</div>

			<h3><?php esc_html_e( 'Visible Menu Items', 'enterprise-access-control' ); ?></h3>
			<ul class="eac-checkbox-list">
				<?php if ( ! empty( $menus ) ) : ?>
					<?php foreach ( $menus as $slug => $menu ) : ?>
						<li>
							<input type="checkbox" id="show-menu-<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $slug ); ?>" <?php checked( ! in_array( $slug, $restricted ) ); ?> />
							<label for="show-menu-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $menu['title'] ); ?></label>
						</li>
						<?php if ( ! empty( $menu['submenu_items'] ) ) : ?>
							<?php foreach ( $menu['submenu_items'] as $sub_slug => $sub_menu ) : ?>
								<li style="padding-left: 40px;">
									<input type="checkbox" id="show-menu-<?php echo esc_attr( $sub_slug ); ?>" value="<?php echo esc_attr( $sub_slug ); ?>" <?php checked( ! in_array( $sub_slug, $restricted ) ); ?> />
									<label for="show-menu-<?php echo esc_attr( $sub_slug ); ?>"><?php echo esc_html( $sub_menu['title'] ); ?></label>
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php else : ?>
					<li><p><?php esc_html_e( 'No menus found', 'enterprise-access-control' ); ?></p></li>
				<?php endif; ?>
			</ul>

			<div class="eac-button-group">
				<button class="eac-button eac-save-settings"><?php esc_html_e( 'Save Visibility Settings', 'enterprise-access-control' ); ?></button>
			</div>
		</div>
		<?php
	}

	private function render_hide_menu_tab( $menus, $restricted ) {
		?>
		<div id="eac-tab-hide-menu" class="eac-tab-content" data-tab="hide-menu">
			<div class="eac-status-message"></div>
			
			<div class="eac-info-box">
				<p>✗ Check all items that should be HIDDEN from sidebar. These items won't be visible anywhere.</p>
			</div>

			<div style="margin-bottom: 15px;">
				<button class="eac-button eac-select-all" style="background-color: #dc3545; border-color: #dc3545;"><?php esc_html_e( 'Select All (Hide Everything)', 'enterprise-access-control' ); ?></button>
				<button class="eac-button eac-unselect-all" style="background-color: #28a745; border-color: #28a745;"><?php esc_html_e( 'Unselect All (Show Everything)', 'enterprise-access-control' ); ?></button>
			</div>

			<h3><?php esc_html_e( 'Hidden Menu Items', 'enterprise-access-control' ); ?></h3>
			<ul class="eac-checkbox-list">
				<?php if ( ! empty( $menus ) ) : ?>
					<?php foreach ( $menus as $slug => $menu ) : ?>
						<li>
							<input type="checkbox" id="hide-menu-<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $restricted ) ); ?> />
							<label for="hide-menu-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $menu['title'] ); ?></label>
						</li>
						<?php if ( ! empty( $menu['submenu_items'] ) ) : ?>
							<?php foreach ( $menu['submenu_items'] as $sub_slug => $sub_menu ) : ?>
								<li style="padding-left: 40px;">
									<input type="checkbox" id="hide-menu-<?php echo esc_attr( $sub_slug ); ?>" value="<?php echo esc_attr( $sub_slug ); ?>" <?php checked( in_array( $sub_slug, $restricted ) ); ?> />
									<label for="hide-menu-<?php echo esc_attr( $sub_slug ); ?>"><?php echo esc_html( $sub_menu['title'] ); ?></label>
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php else : ?>
					<li><p><?php esc_html_e( 'No menus found', 'enterprise-access-control' ); ?></p></li>
				<?php endif; ?>
			</ul>

			<div class="eac-button-group">
				<button class="eac-button eac-save-settings"><?php esc_html_e( 'Save Visibility Settings', 'enterprise-access-control' ); ?></button>
			</div>
		</div>
		<?php
	}

	private function render_security_tab() {
		?>
		<div id="eac-tab-security" class="eac-tab-content" data-tab="security">
			<div class="eac-status-message"></div>
			
			<h3><?php esc_html_e( 'Change Master Password', 'enterprise-access-control' ); ?></h3>
			<p><strong><?php esc_html_e( 'Current Password:', 'enterprise-access-control' ); ?></strong> <code style="background: #f1f1f1; padding: 8px 12px; border-radius: 3px; font-family: monospace;">99999999999999999999</code></p>
			
			<div style="max-width: 400px; margin-top: 20px;">
				<label style="display: block; margin-bottom: 10px; font-weight: 600;"><?php esc_html_e( 'New Password:', 'enterprise-access-control' ); ?></label>
				<input type="password" id="eac-new-password" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px; font-size: 14px;" />
				
				<label style="display: block; margin-bottom: 10px; font-weight: 600;"><?php esc_html_e( 'Confirm Password:', 'enterprise-access-control' ); ?></label>
				<input type="password" id="eac-confirm-password" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px; font-size: 14px;" />

				<button class="eac-button" id="eac-change-password-btn"><?php esc_html_e( 'Update Password', 'enterprise-access-control' ); ?></button>
			</div>

			<script>
				document.getElementById('eac-change-password-btn').addEventListener('click', function() {
					const newPassword = document.getElementById('eac-new-password').value;
					const confirmPassword = document.getElementById('eac-confirm-password').value;
					const msgDiv = document.querySelector('#eac-tab-security .eac-status-message');

					if (!newPassword) {
						msgDiv.textContent = '✗ Please enter a new password';
						msgDiv.classList.remove('success');
						msgDiv.classList.add('error');
						msgDiv.style.display = 'block';
						return;
					}

					if (newPassword !== confirmPassword) {
						msgDiv.textContent = '✗ Passwords do not match';
						msgDiv.classList.remove('success');
						msgDiv.classList.add('error');
						msgDiv.style.display = 'block';
						return;
					}

					if (newPassword.length < 8) {
						msgDiv.textContent = '✗ Password must be at least 8 characters';
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
							msgDiv.textContent = data.data || '✗ Error updating password';
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

		$show_items = isset( $_POST['show_items'] ) ? json_decode( sanitize_text_field( $_POST['show_items'] ), true ) : array();
		$hide_items = isset( $_POST['hide_items'] ) ? json_decode( sanitize_text_field( $_POST['hide_items'] ), true ) : array();

		if ( ! is_array( $show_items ) ) {
			$show_items = array();
		}
		if ( ! is_array( $hide_items ) ) {
			$hide_items = array();
		}

		$settings = EAC_Visibility_Manager::get_settings();
		$settings['sidebar_menus'] = array_map( 'sanitize_text_field', $hide_items );

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
