<?php
/**
 * EAC Core Class
 * Main plugin initialization and setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EAC_Core {
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init();
	}

	private function init() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 999 );
		add_action( 'admin_init', array( $this, 'init_admin' ) );
		add_action( 'wp_before_admin_bar_render', array( $this, 'filter_admin_bar' ) );
		add_filter( 'dashboard_glance_items', array( $this, 'filter_dashboard_widgets' ) );
		add_action( 'admin_head', array( $this, 'hide_restricted_elements' ) );
	}

	public function activate() {
		// Set default password
		if ( ! get_option( 'eac_password' ) ) {
			update_option( 'eac_password', wp_hash_password( '99999999999999999999' ) );
		}

		// Initialize all settings with default values (all visible)
		$this->initialize_default_settings();
	}

	public function deactivate() {
		// Cleanup if needed
	}

	private function initialize_default_settings() {
		$default_settings = array(
			'sidebar_menus' => array(),
			'toolbar_items' => array(),
			'dashboard_widgets' => array(),
		);

		if ( ! get_option( 'eac_settings' ) ) {
			update_option( 'eac_settings', $default_settings );
		}
	}

	public function add_admin_menu() {
		// Always add Manage menu - never hide it
		add_menu_page(
			'Manage Access Control',
			'Manage',
			'manage_options',
			'eac-manage',
			array( $this, 'render_manage_page' ),
			'dashicons-shield',
			2
		);
	}

	public function render_manage_page() {
		// Check password first
		if ( ! EAC_Security::is_authenticated() ) {
			EAC_Security::render_password_page();
			return;
		}

		// Render main management interface
		EAC_Admin::get_instance()->render_settings_page();
	}

	public function init_admin() {
		if ( is_admin() ) {
			EAC_Visibility_Manager::apply_restrictions();
		}
	}

	public function filter_admin_bar() {
		global $wp_admin_bar;
		
		if ( ! is_admin_bar_showing() || ! isset( $wp_admin_bar ) ) {
			return;
		}

		$settings = EAC_Visibility_Manager::get_settings();
		$restricted = isset( $settings['sidebar_menus'] ) ? $settings['sidebar_menus'] : array();
		
		// Remove restricted items from admin bar
		foreach ( $restricted as $item_id ) {
			$wp_admin_bar->remove_node( 'site-' . $item_id );
			$wp_admin_bar->remove_node( $item_id );
		}
	}

	public function filter_dashboard_widgets( $items ) {
		return $items;
	}

	public function hide_restricted_elements() {
		// Additional CSS hiding for any missed elements
	}
}
