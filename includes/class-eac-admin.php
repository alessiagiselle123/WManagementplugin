<?php
/**
 * EAC Admin - Main admin interface
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EAC_Admin {
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
    }

    public static function add_admin_menu() {
        add_menu_page(
            'Enterprise Access Control',
            'Access Control',
            'manage_options',
            'eac-control',
            array( __CLASS__, 'render_page' ),
            'dashicons-shield',
            2
        );
    }

    public static function render_page() {
        // Check authentication
        if ( ! isset( $_SESSION ) ) {
            session_start();
        }

        if ( ! EAC_Settings::is_authenticated() ) {
            EAC_Security::render_password_page();
            return;
        }

        // Check for logout
        if ( isset( $_GET['eac_logout'] ) && sanitize_text_field( $_GET['eac_logout'] ) === '1' ) {
            EAC_Settings::logout();
            wp_redirect( admin_url( 'admin.php?page=eac-control' ) );
            exit;
        }

        // Handle form submission
        if ( isset( $_POST['eac_save_menus'] ) ) {
            check_admin_referer( 'eac_nonce' );
            
            $hidden_menus = isset( $_POST['hidden_menus'] ) ? array_map( 'sanitize_text_field', (array) $_POST['hidden_menus'] ) : array();
            EAC_Settings::set_hidden_menus( $hidden_menus );
            $saved = true;
        } else {
            $saved = false;
        }

        $all_menus = EAC_Menu_Manager::get_all_menus();
        $hidden_menus = EAC_Settings::get_hidden_menus();
        ?>
        <div class="wrap" style="background: #f1f1f1; padding: 20px; border-radius: 8px;">
            <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <div>
                        <h1 style="margin: 0; color: #333; font-size: 32px;">🛡️ Menu Visibility Control</h1>
                        <p style="color: #666; margin: 8px 0 0 0; font-size: 14px;">Select which menus to hide from WordPress dashboard</p>
                    </div>
                    <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=eac-control&eac_logout=1' ), 'eac_nonce' ); ?>" class="button" style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: 600;">
                        🚪 Logout
                    </a>
                </div>

                <?php if ( $saved ) : ?>
                    <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: 500;">
                        ✅ Settings saved successfully! Menus updated on WordPress dashboard.
                    </div>
                <?php endif; ?>

                <form method="POST" style="margin-bottom: 20px;">
                    <?php wp_nonce_field( 'eac_nonce' ); ?>
                    <input type="hidden" name="eac_save_menus" value="1">

                    <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #667eea;">
                        <h3 style="margin-top: 0; color: #333; font-size: 16px; margin-bottom: 15px;">📋 Available Menus - Check to HIDE</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
                            <?php if ( ! empty( $all_menus ) ) : ?>
                                <?php foreach ( $all_menus as $menu_slug => $menu_data ) : ?>
                                    <label style="display: flex; align-items: center; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e0e0e0; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#f9f9f9'; this.style.borderColor='#667eea';" onmouseout="this.style.background='white'; this.style.borderColor='#e0e0e0';">
                                        <input type="checkbox" name="hidden_menus[]" value="<?php echo esc_attr( $menu_slug ); ?>" <?php checked( in_array( $menu_slug, $hidden_menus ) ); ?> style="margin-right: 10px; width: 18px; height: 18px; cursor: pointer;">
                                        <span style="flex: 1; color: #333; font-weight: 500; font-size: 14px;"><?php echo esc_html( $menu_data['title'] ); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <p style="color: #999; grid-column: 1 / -1;">No menus found</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="button button-primary" style="background: #28a745; border-color: #28a745; padding: 12px 30px; font-size: 14px; font-weight: 600;">
                            💾 Save & Hide Selected Menus
                        </button>
                        
                        <button type="button" onclick="if(confirm('Show all menus?')) { document.querySelectorAll('input[name=\"hidden_menus[]\"]').forEach(cb => cb.checked = false); document.querySelector('form').submit(); }" class="button" style="background: #17a2b8; color: white; border: none; padding: 12px 30px; font-size: 14px; font-weight: 600; border-radius: 4px; cursor: pointer;">
                            👁️ Show All Menus
                        </button>
                    </div>
                </form>

                <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px; margin-top: 20px; font-size: 14px; color: #856404;">
                    <strong>📌 Note:</strong> After saving, exit this page and the hidden menus will disappear from your WordPress dashboard. Come back and enter password again to show them.
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize admin on admin_init
add_action( 'admin_init', array( 'EAC_Admin', 'init' ) );
