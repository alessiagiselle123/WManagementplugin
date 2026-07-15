<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EAC_Admin {

	public static function render_page() {
		if ( ! EAC_Security::is_authenticated() ) {
			EAC_Security::render_login_page();
			return;
		}

		$hidden_menus = EAC_Visibility::get_hidden_menus();
		$all_menus = EAC_Visibility::get_all_menus();
		?>
		<div class="wrap">
			<h1>📋 Menu Visibility Manager</h1>

			<div style="display: flex; gap: 20px; margin-top: 30px;">
				<!-- SHOW Section -->
				<div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
					<h2 style="color: #28a745; margin-bottom: 15px;">✅ SHOW - Visible Menus</h2>
					<p style="color: #666; margin-bottom: 20px; font-size: 14px;">Check items you want to SHOW in sidebar:</p>

					<form id="showForm">
						<?php foreach ( $all_menus as $menu ) : ?>
							<div style="padding: 10px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center;">
								<input type="checkbox" 
									id="show-<?php echo esc_attr( $menu['slug'] ); ?>"
									value="<?php echo esc_attr( $menu['slug'] ); ?>"
									<?php checked( ! in_array( $menu['slug'], $hidden_menus ) ); ?>
									style="width: 20px; height: 20px; cursor: pointer; margin-right: 10px;">
								<label for="show-<?php echo esc_attr( $menu['slug'] ); ?>" style="cursor: pointer; flex: 1; margin: 0;">
									<?php echo esc_html( $menu['title'] ); ?>
								</label>
							</div>
						<?php endforeach; ?>

						<div style="margin-top: 20px; display: flex; gap: 10px;">
							<button type="button" class="button button-primary" onclick="selectAll('showForm')">✓ Select All</button>
							<button type="button" class="button" onclick="deselectAll('showForm')">✕ Deselect All</button>
							<button type="submit" class="button button-primary" style="background: #28a745; border-color: #28a745;">💾 Save SHOW Settings</button>
						</div>
					</form>
				</div>

				<!-- HIDE Section -->
				<div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
					<h2 style="color: #dc3545; margin-bottom: 15px;">❌ HIDE - Hidden Menus</h2>
					<p style="color: #666; margin-bottom: 20px; font-size: 14px;">Check items you want to HIDE from sidebar:</p>

					<form id="hideForm">
						<?php foreach ( $all_menus as $menu ) : ?>
							<div style="padding: 10px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center;">
								<input type="checkbox" 
									id="hide-<?php echo esc_attr( $menu['slug'] ); ?>"
									value="<?php echo esc_attr( $menu['slug'] ); ?>"
									<?php checked( in_array( $menu['slug'], $hidden_menus ) ); ?>
									style="width: 20px; height: 20px; cursor: pointer; margin-right: 10px;">
								<label for="hide-<?php echo esc_attr( $menu['slug'] ); ?>" style="cursor: pointer; flex: 1; margin: 0;">
									<?php echo esc_html( $menu['title'] ); ?>
								</label>
							</div>
						<?php endforeach; ?>

						<div style="margin-top: 20px; display: flex; gap: 10px;">
							<button type="button" class="button button-primary" onclick="selectAll('hideForm')" style="background: #dc3545; border-color: #dc3545;">✓ Select All</button>
							<button type="button" class="button" onclick="deselectAll('hideForm')">✕ Deselect All</button>
							<button type="submit" class="button button-primary" style="background: #dc3545; border-color: #dc3545;">💾 Save HIDE Settings</button>
						</div>
					</form>
				</div>
			</div>

			<!-- Logout Button -->
			<div style="margin-top: 30px;">
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'eac_logout', 'true' ), 'eac_logout_nonce' ) ); ?>" class="button">🔓 Logout</a>
			</div>
		</div>

		<script>
			function selectAll(formId) {
				document.getElementById(formId).querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
			}

			function deselectAll(formId) {
				document.getElementById(formId).querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
			}

			document.getElementById('showForm').addEventListener('submit', function(e) {
				e.preventDefault();
				const checked = Array.from(document.querySelectorAll('#showForm input[type="checkbox"]:checked')).map(cb => cb.value);
				const all = Array.from(document.querySelectorAll('#showForm input[type="checkbox"]')).map(cb => cb.value);
				const hidden = all.filter(item => !checked.includes(item));
				saveMenus(hidden);
			});

			document.getElementById('hideForm').addEventListener('submit', function(e) {
				e.preventDefault();
				const hidden = Array.from(document.querySelectorAll('#hideForm input[type="checkbox"]:checked')).map(cb => cb.value);
				saveMenus(hidden);
			});

			function saveMenus(hiddenMenus) {
				var xhr = new XMLHttpRequest();
				xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

				xhr.onload = function() {
					var response = JSON.parse(xhr.responseText);
					if (response.success) {
						alert('✅ Settings saved successfully!');
						window.location.reload();
					} else {
						alert('❌ Error saving settings');
					}
				};

				xhr.send('action=eac_save_menus&menus=' + encodeURIComponent(JSON.stringify(hiddenMenus)));
			}
		</script>

		<style>
			.button {
				padding: 8px 16px;
				border-radius: 4px;
				cursor: pointer;
				text-decoration: none;
				display: inline-block;
			}
			.button:hover {
				opacity: 0.9;
			}
		</style>
		<?php
	}
}
