<?php
/*
Plugin Name: WooCommerce XRPL Payment
Plugin URI: 
Description: Plugin description
Version: 1.0.0
Author: tarotaro080808
Author URI: https://github.com/tarotaro080808
License: GPL2
Text Domain: WooXrplPayment
Domain Path: /languages
*/

if( !defined( 'ABSPATH' ) ){
	exit;
}

if ( !function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if( !defined( 'WOO_XRPL_PAYMENT' ) ){
  define( 'WOO_XRPL_PAYMENT', 'WOO_XRPL_PAYMENT' );
}

if( !defined( 'WOO_XRPL_PAYMENT_VERSION' ) ){
  define( 'WOO_XRPL_PAYMENT_VERSION', '1.0' );
}

if( !defined( 'WOO_XRPL_PAYMENT_BASENAME' ) ){
  define( 'WOO_XRPL_PAYMENT_BASENAME', plugin_basename( WOO_XRPL_PAYMENT ) );
}

if( !defined( 'WOO_XRPL_PAYMENT_NAME' ) ){
  define( 'WOO_XRPL_PAYMENT_NAME', trim( dirname( WOO_XRPL_PAYMENT_BASENAME ), '/' ) );
}

if ( ! defined( 'WOO_XRPL_PAYMENT_URL' ) ) {
	define( 'WOO_XRPL_PAYMENT_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'WOO_XRPL_PAYMENT_DIR' ) ) {
	define( 'WOO_XRPL_PAYMENT_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WOO_XRPL_PAYMENT_INC' ) ) {
	define( 'WOO_XRPL_PAYMENT_INC', WOO_XRPL_PAYMENT_DIR . 'includes/' );
}


if ( ! function_exists( 'Woo_Xrpl_Payment' ) ) {
	/**
	 * Unique access to instance of Woo_Xrpl_Payment class
	 *
	 * @return \Woo_Xrpl_Payment
	 * @since 1.0.0
	 */
	function Woo_Xrpl_Payment() {
		// Load required classes and functions
		require_once( WOO_XRPL_PAYMENT_INC . 'class-woo-xrpl-payment.php' );

		return Woo_Xrpl_Payment::get_instance();
	}
}

if ( ! function_exists( 'woo_xrpl_payment_constructor' ) ) {
	function woo_xrpl_payment_constructor() {
    load_plugin_textdomain( 'WooXrplPayment', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    
		Woo_Xrpl_Payment();
	}
}
add_action( 'plugins_loaded', 'woo_xrpl_payment_constructor' );

