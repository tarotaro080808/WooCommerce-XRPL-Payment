<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that represents admin notices.
 *
 * @since 1.0.0
 */
class Woo_Xrpl_Payment_Admin_Notice {
  /**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

  /**
   * Advise if the plugin cannot be performed
   *
   * @since 1.0.0
   */
  public function admin_notices() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

    if ( ! function_exists( 'curl_init' ) ) {
      echo '<div class="error"><p>' . __( 'Stripe needs the CURL PHP extension.', Woo_Xrpl_Payment::$text_domain ) . '</p></div>';
    }

    if ( ! function_exists( 'json_decode' ) ) {
      echo '<div class="error"><p>' . __( 'Stripe needs the JSON PHP extension.', Woo_Xrpl_Payment::$text_domain ) . '</p></div>';
    }

    if ( ! function_exists( 'mb_detect_encoding' ) ) {
      echo '<div class="error"><p>' . __( 'Stripe needs the Multibyte String PHP extension.', Woo_Xrpl_Payment::$text_domain ) . '</p></div>';
    }

    $options = get_option('woocommerce_xrp_payment_settings');

    if ( ! $options['xrp_address'] ) {
      echo '<div class="error"><p>' . __( 'Please enter your recipient XRP Address.', Woo_Xrpl_Payment::$text_domain ) . '</p></div>';
    }
    
    if ( ! $options['api_key'] || ! $options['api_secret'] ) {
      echo '<div class="error"><p>' . __( 'Please enter the API keys (public and private) for XRPL Payment gateway.', Woo_Xrpl_Payment::$text_domain ) . '</p></div>';
    }    
  }
}
new Woo_Xrpl_Payment_Admin_Notice();
