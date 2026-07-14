<?php
/**
 * Plugin Name: Enterprise Access Control & Admin Protection
 * Plugin URI: https://example.com/enterprise-access-control
 * Description: Enterprise-level access control and admin protection plugin for WordPress. Control who sees what in WordPress admin panel.
 * Version: 1.0.0
 * Author: Your Company
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: enterprise-access-control
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'EAC_VERSION', '1.0.0' );
define( 'EAC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'EAC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load plugin files
require_once EAC_PLUGIN_DIR . 'includes/class-eac-core.php';
require_once EAC_PLUGIN_DIR . 'includes/class-eac-admin.php';
require_once EAC_PLUGIN_DIR . 'includes/class-eac-security.php';
require_once EAC_PLUGIN_DIR . 'includes/class-eac-menu-detector.php';
require_once EAC_PLUGIN_DIR . 'includes/class-eac-visibility-manager.php';

// Initialize the plugin
add_action( 'plugins_loaded', function() {
	EAC_Core::get_instance();
} );

// Activation hook
register_activation_hook( __FILE__, function() {
	EAC_Core::get_instance()->activate();
} );

// Deactivation hook
register_deactivation_hook( __FILE__, function() {
	EAC_Core::get_instance()->deactivate();
} );
