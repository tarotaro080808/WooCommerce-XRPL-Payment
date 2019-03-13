<?php
/**
 * Main class
 *
 * @author tarotaro080808
 * @package WooCommerce XRPL Payment
 * @version 1.0.0
 */

if ( ! defined( 'WOO_XRPL_PAYMENT' ) ) {
	exit;
} // Exit if accessed directly

if( ! class_exists( 'Woo_Xrpl_Payment' ) ){
	/**
	 * WooCommerce XRPL Payment main class
	 *
	 * @since 1.0.0
	 */
	class Woo_Xrpl_Payment {
		/**
		 * Single instance of the class
		 *
		 * @var \Woo_Xrpl_Payment
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * XRP Payment gateway id
		 *
		 * @var string Id of specific gateway
		 * @since 1.0
		 */
		public static $gateway_id = 'xrp_payment';

		/**
		 * The gateway object
		 *
		 * @var Woo_Xrp_Payment_Gateway
		 * @since 1.0
		 */
		protected $gateway = null;

		/**
		 * Admin main class
		 *
		 * @var Woo_Xrp_Payment_Admin
		 */
		public $admin = null;

		/**
		 * Admin Notice main class
		 *
		 * @var Woo_Xrp_Payment_Admin_Notice
		 */
		public $admin_notice = null;

		/**
		 * Text Domain 
		 *
		 * @var string Name of text domain
		 * @since 1.0
		 */
		public static $text_domain = 'WooXrplPayment';

		/**
		 * Returns single instance of the class
		 *
		 * @return \Woo_Xrp_Payment
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self;
			}

			return self::$instance;
		}


		/**
		 * Constructor.
		 *
		 * @return \Woo_Xrp_Payment
		 * @since 1.0.0
		 */
		public function __construct() {
			if ( is_admin() ) {
				// admin includes ( But not currently using )
				include_once( 'class-woo-xrpl-payment-admin.php' );
				$this->admin = new Woo_Xrpl_Payment_Admin();

				// Notice display class for Admin
				include_once( 'class-woo-xrpl-payment-admin-notice.php' );
			}

			// add filter to append wallet as payment gateway
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_to_gateways' ) );
		}

		/**
		 * Adds XRP Payment Gateway to payment gateways available for woocommerce checkout
		 *
		 * @param $methods array Previously available gataways, to filter with the function
		 *
		 * @return array New list of available gateways
		 * @since 1.0.0
		 */
		public function add_to_gateways( $methods ) {
			self::$gateway_id = apply_filters( 'woo_xrp_payment_gateway_id', self::$gateway_id );

			include_once( 'class-woo-xrpl-payment-gateway.php' );
			$methods[] = 'Woo_Xrp_Payment_Gateway';
			return $methods;
		}

		/**
		 * Get the gateway object
		 *
		 * @return Woo_Xrp_Payment_Gateway
		 * @since 1.0.0
		 */
		public function get_gateway() {
			if ( ! is_a( $this->gateway, 'Woo_Xrp_Payment_Gateway' ) )  {
				$gateways = WC()->payment_gateways()->get_available_payment_gateways();

				if ( ! isset( $gateways[ self::$gateway_id ] ) ) {
					return false;
				}

				$this->gateway = $gateways[ self::$gateway_id ];
			}

			return $this->gateway;
		}
	}
}