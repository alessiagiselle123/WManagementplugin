<?php
/**
 * EAC Settings - Manage all plugin settings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EAC_Settings {
    const OPTION_NAME = 'eac_menu_settings';
    const PASSWORD_OPTION = 'eac_password';
    const SESSION_KEY = 'eac_authenticated';

    public static function activate() {
        // Initialize default settings
        if ( ! get_option( self::OPTION_NAME ) ) {
            update_option( self::OPTION_NAME, array( 'hidden_menus' => array() ) );
        }

        // Set default password
        if ( ! get_option( self::PASSWORD_OPTION ) ) {
            update_option( self::PASSWORD_OPTION, wp_hash_password( '12345' ) );
        }
    }

    public static function get_all_settings() {
        return get_option( self::OPTION_NAME, array( 'hidden_menus' => array() ) );
    }

    public static function get_hidden_menus() {
        $settings = self::get_all_settings();
        return isset( $settings['hidden_menus'] ) ? $settings['hidden_menus'] : array();
    }

    public static function set_hidden_menus( $hidden_menus ) {
        $settings = self::get_all_settings();
        $settings['hidden_menus'] = $hidden_menus;
        update_option( self::OPTION_NAME, $settings );
    }

    public static function is_authenticated() {
        return isset( $_SESSION[ self::SESSION_KEY ] ) && $_SESSION[ self::SESSION_KEY ] === true;
    }

    public static function set_authenticated( $authenticated = true ) {
        if ( ! isset( $_SESSION ) ) {
            session_start();
        }
        $_SESSION[ self::SESSION_KEY ] = $authenticated;
    }

    public static function logout() {
        if ( ! isset( $_SESSION ) ) {
            session_start();
        }
        unset( $_SESSION[ self::SESSION_KEY ] );
    }

    public static function verify_password( $password ) {
        $hashed = get_option( self::PASSWORD_OPTION );
        return wp_check_password( $password, $hashed );
    }

    public static function change_password( $new_password ) {
        $hashed = wp_hash_password( $new_password );
        return update_option( self::PASSWORD_OPTION, $hashed );
    }
}
