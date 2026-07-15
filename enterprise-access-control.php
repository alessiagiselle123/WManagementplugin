<?php
/*
Plugin Name: Enterprise Access Control
Plugin URI: https://example.com
Description: Complete menu visibility control with simple password protection
Version: 1.0.0
Author: Your Name
Author URI: https://example.com
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: enterprise-access-control
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'EAC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'EAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'EAC_PLUGIN_VERSION', '1.0.0' );

// Load all classes
require_once EAC_PLUGIN_PATH . 'includes/class-eac-settings.php';
require_once EAC_PLUGIN_PATH . 'includes/class-eac-menu-manager.php';
require_once EAC_PLUGIN_PATH . 'includes/class-eac-security.php';
require_once EAC_PLUGIN_PATH . 'includes/class-eac-admin.php';

// Plugin activation
register_activation_hook( __FILE__, array( 'EAC_Settings', 'activate' ) );

// Initialize plugin on admin_init
add_action( 'admin_init', array( 'EAC_Menu_Manager', 'init' ) );
