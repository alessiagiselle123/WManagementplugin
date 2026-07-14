<?php
/**
 * EAC Menu Detector Class
 * Automatically detect WordPress menus and settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EAC_Menu_Detector {
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function get_all_admin_menus() {
		global $menu, $submenu;

		$menus = array();

		if ( isset( $menu ) && is_array( $menu ) ) {
			foreach ( $menu as $menu_item ) {
				if ( isset( $menu_item[2] ) ) {
					$menus[ $menu_item[2] ] = array(
						'title' => $menu_item[0],
						'capability' => isset( $menu_item[1] ) ? $menu_item[1] : 'manage_options',
						'slug' => $menu_item[2],
						'submenu_items' => array(),
					);
				}
			}
		}

		if ( isset( $submenu ) && is_array( $submenu ) ) {
			foreach ( $submenu as $parent_slug => $sub_items ) {
				if ( isset( $menus[ $parent_slug ] ) ) {
					foreach ( $sub_items as $sub_item ) {
						$sub_slug = isset( $sub_item[2] ) ? $sub_item[2] : '';
						$menus[ $parent_slug ]['submenu_items'][ $sub_slug ] = array(
							'title' => isset( $sub_item[0] ) ? $sub_item[0] : '',
							'capability' => isset( $sub_item[1] ) ? $sub_item[1] : 'manage_options',
						);
					}
				}
			}
		}

		return $menus;
	}

	public static function get_toolbar_items() {
		global $wp_admin_bar;

		if ( ! is_admin_bar_showing() ) {
			return array();
		}

		$items = array();
		$toolbar_items = $wp_admin_bar->get_nodes();

		foreach ( $toolbar_items as $item ) {
			if ( isset( $item->id ) ) {
				$items[ $item->id ] = array(
					'title' => isset( $item->title ) ? $item->title : '',
					'parent' => isset( $item->parent ) ? $item->parent : '',
				);
			}
		}

		return $items;
	}

	public static function get_default_dashboard_widgets() {
		$widgets = array(
			'dashboard_right_now' => __( 'At a Glance' ),
			'dashboard_activity' => __( 'Activity' ),
			'dashboard_quick_press' => __( 'Quick Draft' ),
			'dashboard_primary' => __( 'WordPress News' ),
			'dashboard_secondary' => __( 'Other WordPress News' ),
		);

		return $widgets;
	}
}
