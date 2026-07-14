<?php
/**
 * EAC Visibility Manager Class
 * Manage visibility and access control
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EAC_Visibility_Manager {
	public static function apply_restrictions() {
		// Check authentication on manage page
		if ( isset( $_GET['page'] ) && 'eac-manage' === $_GET['page'] ) {
			if ( ! EAC_Security::is_authenticated() ) {
				EAC_Security::render_password_page();
				exit;
			}
		}

		$settings = self::get_settings();
		
		// Hide sidebar menus
		self::hide_sidebar_menus( $settings );

		// Disable direct access to restricted pages
		self::protect_page_access( $settings );
	}

	public static function get_settings() {
		return get_option( 'eac_settings', array() );
	}

	public static function update_settings( $new_settings ) {
		return update_option( 'eac_settings', $new_settings );
	}

	private static function hide_sidebar_menus( $settings ) {
		global $menu, $submenu;

		if ( ! isset( $settings['sidebar_menus'] ) || ! is_array( $settings['sidebar_menus'] ) ) {
			return;
		}

		$restricted = $settings['sidebar_menus'];

		// Remove restricted menus
		if ( isset( $menu ) && is_array( $menu ) ) {
			foreach ( $menu as $key => $menu_item ) {
				if ( isset( $menu_item[2] ) && in_array( $menu_item[2], $restricted, true ) ) {
					// Don't hide Manage menu
					if ( 'eac-manage' !== $menu_item[2] ) {
						unset( $menu[ $key ] );
					}
				}
			}
		}

		// Remove restricted submenus
		if ( isset( $submenu ) && is_array( $submenu ) ) {
			foreach ( $submenu as $parent => $sub_items ) {
				foreach ( $sub_items as $key => $sub_item ) {
					if ( isset( $sub_item[2] ) && in_array( $sub_item[2], $restricted, true ) ) {
						unset( $submenu[ $parent ][ $key ] );
					}
				}
			}
		}
	}

	private static function protect_page_access( $settings ) {
		global $pagenow;

		$restricted_pages = isset( $settings['sidebar_menus'] ) ? $settings['sidebar_menus'] : array();

		if ( empty( $restricted_pages ) ) {
			return;
		}

		// Check current page - deny direct URL access to hidden pages
		if ( in_array( $pagenow, $restricted_pages, true ) ) {
			wp_die( 
				__( 'Access Denied. This page is restricted.', 'enterprise-access-control' ), 
				__( 'Access Denied', 'enterprise-access-control' ), 
				array( 'response' => 403 ) 
			);
		}

		// Additional check for URLs like admin.php?page=...
		if ( isset( $_GET['page'] ) && in_array( sanitize_text_field( $_GET['page'] ), $restricted_pages, true ) ) {
			wp_die( 
				__( 'Access Denied. This page is restricted.', 'enterprise-access-control' ), 
				__( 'Access Denied', 'enterprise-access-control' ), 
				array( 'response' => 403 ) 
			);
		}
	}

	public static function get_restricted_toolbar_items() {
		$settings = self::get_settings();
		return isset( $settings['toolbar_items'] ) ? $settings['toolbar_items'] : array();
	}

	public static function get_restricted_dashboard_widgets() {
		$settings = self::get_settings();
		return isset( $settings['dashboard_widgets'] ) ? $settings['dashboard_widgets'] : array();
	}
}
