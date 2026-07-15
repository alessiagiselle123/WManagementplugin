<?php
/**
 * Plugin Name: Enterprise Access Control
 * Plugin URI: https://github.com/alessiagiselle123/WManagementplugin
 * Description: Simple WordPress menu access control - Hide/Show admin menus with password protection
 * Version: 1.0.0
 * Author: Alessia Giselle
 * License: GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EAC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load all classes
require_once EAC_PLUGIN_DIR . 'classes/class-security.php';
require_once EAC_PLUGIN_DIR . 'classes/class-visibility.php';
require_once EAC_PLUGIN_DIR . 'classes/class-admin.php';
require_once EAC_PLUGIN_DIR . 'classes/class-core.php';

// Initialize plugin
EAC_Core::init();

// Activation hook
register_activation_hook( __FILE__, array( 'EAC_Core', 'activate' ) );

// Deactivation hook
register_deactivation_hook( __FILE__, array( 'EAC_Core', 'deactivate' ) );
