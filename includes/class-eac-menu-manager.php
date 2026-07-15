<?php
/**
 * EAC Menu Manager - Manage menu visibility
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EAC_Menu_Manager {
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'hide_menus' ), 999 );
    }

    public static function hide_menus() {
        global $menu, $submenu;

        $hidden_menus = EAC_Settings::get_hidden_menus();

        if ( empty( $hidden_menus ) ) {
            return;
        }

        // Hide main menus
        if ( isset( $menu ) && is_array( $menu ) ) {
            foreach ( $menu as $key => $menu_item ) {
                if ( isset( $menu_item[2] ) && in_array( $menu_item[2], $hidden_menus, true ) ) {
                    unset( $menu[ $key ] );
                }
            }
        }

        // Hide submenus
        if ( isset( $submenu ) && is_array( $submenu ) ) {
            foreach ( $submenu as $parent => $sub_items ) {
                if ( is_array( $sub_items ) ) {
                    foreach ( $sub_items as $key => $sub_item ) {
                        if ( isset( $sub_item[2] ) && in_array( $sub_item[2], $hidden_menus, true ) ) {
                            unset( $submenu[ $parent ][ $key ] );
                        }
                    }
                }
            }
        }
    }

    public static function get_all_menus() {
        global $menu, $submenu;

        $all_menus = array();

        if ( isset( $menu ) && is_array( $menu ) ) {
            foreach ( $menu as $menu_item ) {
                if ( isset( $menu_item[2] ) ) {
                    $all_menus[ $menu_item[2] ] = array(
                        'title' => $menu_item[0],
                        'icon' => isset( $menu_item[6] ) ? $menu_item[6] : '',
                    );

                    // Get submenus
                    $menu_slug = $menu_item[2];
                    if ( isset( $submenu[ $menu_slug ] ) && is_array( $submenu[ $menu_slug ] ) ) {
                        foreach ( $submenu[ $menu_slug ] as $sub_item ) {
                            if ( isset( $sub_item[2] ) ) {
                                $all_menus[ $sub_item[2] ] = array(
                                    'title' => $sub_item[0],
                                    'parent' => $menu_slug,
                                );
                            }
                        }
                    }
                }
            }
        }

        return $all_menus;
    }
}
