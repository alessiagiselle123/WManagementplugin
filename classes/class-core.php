<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EAC_Core {

	private static $instance = null;

	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
	}

	private function __construct() {
		if ( ! isset( $_SESSION ) || session_status() === PHP_SESSION_NONE ) {
			@session_start();
		}

		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'apply_visibility' ) );
		add_action( 'wp_ajax_nopriv_eac_authenticate', array( $this, 'ajax_authenticate' ) );
		add_action( 'wp_ajax_eac_authenticate', array( $this, 'ajax_authenticate' ) );
		add_action( 'wp_ajax_eac_save_menus', array( $this, 'ajax_save_menus' ) );
		add_action( 'wp_ajax_nopriv_eac_save_menus', array( $this, 'ajax_save_menus' ) );
		add_action( 'admin_init', array( $this, 'handle_logout' ) );
	}

	public static function activate() {
		// Set default password
		if ( ! get_option( 'eac_password' ) ) {
			update_option( 'eac_password', wp_hash_password( EAC_Security::get_default_password() ) );
		}

		// Initialize empty hidden menus
		if ( ! get_option( 'eac_hidden_menus' ) ) {
			update_option( 'eac_hidden_menus', array() );
		}
	}

	public static function deactivate() {
		// Cleanup
	}

	public function add_menu() {
		add_menu_page(
			'Menu Visibility',
			'📋 Menu Manager',
			'manage_options',
			'eac-manage',
			array( 'EAC_Admin', 'render_page' ),
			'dashicons-visibility',
			2
		);
	}

	public function apply_visibility() {
		if ( is_admin() ) {
			EAC_Visibility::apply_visibility();
		}
	}

	public function handle_logout() {
		if ( isset( $_GET['eac_logout'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'eac_logout_nonce' ) ) {
			EAC_Security::logout();
			wp_redirect( admin_url( 'admin.php?page=eac-manage' ) );
			exit;
		}
	}

	public function ajax_authenticate() {
		$password = isset( $_POST['password'] ) ? sanitize_text_field( $_POST['password'] ) : '';

		if ( EAC_Security::authenticate( $password ) ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	public function ajax_save_menus() {
		$menus = isset( $_POST['menus'] ) ? json_decode( sanitize_text_field( $_POST['menus'] ), true ) : array();

		if ( ! is_array( $menus ) ) {
			$menus = array();
		}

		$menus = array_map( 'sanitize_text_field', $menus );

		if ( EAC_Visibility::set_hidden_menus( $menus ) ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}
}
