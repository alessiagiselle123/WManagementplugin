<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EAC_Visibility {

	public static function get_hidden_menus() {
		return get_option( 'eac_hidden_menus', array() );
	}

	public static function set_hidden_menus( $menus ) {
		return update_option( 'eac_hidden_menus', $menus );
	}

	public static function apply_visibility() {
		global $menu, $submenu;

		$hidden = self::get_hidden_menus();

		if ( empty( $hidden ) ) {
			return;
		}

		// Hide top-level menus
		if ( isset( $menu ) && is_array( $menu ) ) {
			foreach ( $menu as $key => $item ) {
				if ( isset( $item[2] ) && in_array( $item[2], $hidden, true ) ) {
					unset( $menu[ $key ] );
				}
			}
		}

		// Hide submenus
		if ( isset( $submenu ) && is_array( $submenu ) ) {
			foreach ( $submenu as $parent => $items ) {
				foreach ( $items as $key => $item ) {
					if ( isset( $item[2] ) && in_array( $item[2], $hidden, true ) ) {
						unset( $submenu[ $parent ][ $key ] );
					}
				}
			}
		}

		// Block direct access to hidden pages
		self::protect_direct_access();
	}

	private static function protect_direct_access() {
		global $pagenow;

		$hidden = self::get_hidden_menus();

		// Check if current page is hidden
		if ( in_array( $pagenow, $hidden, true ) ) {
			wp_die( 'Access Denied: This page is hidden.', 'Access Denied', array( 'response' => 403 ) );
		}

		// Check for admin.php?page=xxx
		if ( isset( $_GET['page'] ) && in_array( sanitize_text_field( $_GET['page'] ), $hidden, true ) ) {
			wp_die( 'Access Denied: This page is hidden.', 'Access Denied', array( 'response' => 403 ) );
		}
	}

	public static function get_all_menus() {
		global $menu, $submenu;

		$menus = array();

		if ( isset( $menu ) && is_array( $menu ) ) {
			foreach ( $menu as $item ) {
				if ( isset( $item[2] ) && ! empty( $item[2] ) && $item[2] !== 'separator' && $item[2] !== 'eac-manage' ) {
					$menus[] = array(
						'slug' => $item[2],
						'title' => $item[0],
					);
				}
			}
		}

		return $menus;
	}
}
