<?php
if ( ! defined( 'WOO_XRPL_PAYMENT' ) ) {
	exit;
} // Exit if accessed directly

class Woo_Xrp_Payment_Gateway extends WC_Payment_Gateway {

  /**
   * Webhook main class
   *
   * @var Woo_Xrpl_Payment_Webhook
   */
  public $webhook = null;

  public $instance = '';

  public $admin_notices = array();
  public $xrp_address = '';

  public $callback_name = 'woocommerce_api_woo_xrp_payment_gateway';

  /**
   * Constructor for the gateway.
   */
  public function __construct() {
    $this->id                 = Woo_Xrpl_Payment::$gateway_id;
    $this->icon               = WOO_XRPL_PAYMENT_URL . '/assets/images/xrp_logo.png';
    $this->has_fields         = false;
    $this->method_title       = __( 'XRP', Woo_Xrpl_Payment::$text_domain );
    $this->method_description = __( 'Payment via XRP Ledger', Woo_Xrpl_Payment::$text_domain );

    $this->instance           = preg_replace( '/http(s)?:\/\//', '', site_url() );

    // Load the settings.
    $this->init_form_fields();
    $this->init_settings();

    // Get setting values
    foreach ( $this->settings as $key => $val ) $this->$key = $val;

    // Define user set variables
    $this->title           = $this->get_option( 'title' );
    $this->description     = $this->get_option( 'description' );
    $this->instructions    = $this->get_option( 'instructions', $this->description );

    $this->xrp_address     = $this->get_option( 'xrp_address' );
    $this->api_key         = $this->get_option( 'api_key' );
    $this->api_secret      = $this->get_option( 'api_secret' );

    $this->webhook_id      = $this->get_option( 'webhook_id' );
    $this->subscription_id = $this->get_option( 'subscription_id' );

    $this->webhook = $this->woo_xrpl_payment_webhook();

    // Reference: https://docs.woocommerce.com/document/wc_api-the-woocommerce-api-callback/
    add_action( 'woocommerce_api_woo_xrp_payment_gateway', array( $this, 'webhook_callback' ) );


    // Actions
    add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
    add_action( 'woocommerce_view_order', array( $this, 'thankyou_page' ), 10 );

    // Customer Emails
    add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

  }

  /**
   * Initialise Gateway Settings Form Fields
   */
  public function init_form_fields() {
    $this->form_fields = array(
      'enabled' => array(
        'title'   => __( 'Enable/Disable', Woo_Xrpl_Payment::$text_domain ),
        'type'    => 'checkbox',
        'label'   => __( 'Enable XRPL Payment', Woo_Xrpl_Payment::$text_domain ),
        'default' => 'no'
      ),
      'title' => array(
        'title'       => __( 'タイトル', Woo_Xrpl_Payment::$text_domain ),
        'type'        => 'text',
        'description' => __( 'Title', Woo_Xrpl_Payment::$text_domain ),
        'default'     => __( 'XRP', Woo_Xrpl_Payment::$text_domain ),
        'desc_tip'    => true,
      ),
      'description' => array(
        'title'       => __( 'Description', Woo_Xrpl_Payment::$text_domain ),
        'type'        => 'textarea',
        'description' => __( 'Payment method description that the customer will see on your website.', Woo_Xrpl_Payment::$text_domain ),
        'default'     => __( 'Pay with XRP', Woo_Xrpl_Payment::$text_domain ),
        'desc_tip'    => true,
      ),
      'instructions' => array(
        'title'       => __( 'Instructions', Woo_Xrpl_Payment::$text_domain ),
        'type'        => 'textarea',
        'description' => __( 'Instructions that will be added to the thank you page.', Woo_Xrpl_Payment::$text_domain ),
        'default'     => '',
        'desc_tip'    => true,
      ),
      'address'          => array(
        'title'       => __( 'XRP Address', Woo_Xrpl_Payment::$text_domain ),
        'type'        => 'title',
        'description' => __( 'Enter the activated XRP address.', Woo_Xrpl_Payment::$text_domain ),
      ),
      'xrp_address' => array(
        'title'       => __( 'Your XRP Address', Woo_Xrpl_Payment::$text_domain ),
        'type'        => 'text',
        'description' => __( 'Write your xrpl address(rxxx...)', Woo_Xrpl_Payment::$text_domain ),
        'default'     => '',
        'desc_tip'    => true,
      ),
      'keys'          => array(
        'title'       => __( 'API Keys', Woo_Xrpl_Payment::$text_domain ),
        'type'        => 'title',
        'description' => sprintf( __( 'You can get it in <a href="%s" target="_blank">xrpayments.co</a>', Woo_Xrpl_Payment::$text_domain ), 'https://webhook.xrpayments.co/' ),
      ),
      'api_key' => array(
        'title'       => __( 'API Key', Woo_Xrpl_Payment::$text_domain ),
        'type'        => 'text',
        'description' => __( 'Get from xrpayment.co', Woo_Xrpl_Payment::$text_domain ),
        'default'     => '',
        'desc_tip'    => true,
      ),
      'api_secret' => array(
        'title'       => __( 'API Secret', Woo_Xrpl_Payment::$text_domain ),
        'type'        => 'text',
        'description' => __( 'Get from xrpayment.co', Woo_Xrpl_Payment::$text_domain ),
        'default'     => '',
        'desc_tip'    => true,
      ),

    );
  }

  /**
   * Unique access to instance of Woo_Xrpl_Payment_Webhook class
   *
   * @return \Woo_Xrpl_Payment_Webhook
   * @since 1.0.0
   */
  public function woo_xrpl_payment_webhook() {
    // Load required classes and functions
    include_once( 'class-woo-xrpl-payment-webhook.php' );
    $options = get_option('woocommerce_xrp_payment_settings');
    $this->webhook = new Woo_Xrpl_Payment_Webhook( $options['api_key'], $options['api_secret'] );
  }


  public function webhook_callback() {
    $this->webhook->callback();
  }

  
  public function admin_options() {
    ?>
    <h2><?php _e('XRP Payment Setting Status','woocommerce'); ?></h2>
    <div style="background:#fff; padding: 0 10px; margin: 0 0 20px;">
      <table class="form-table">
        <tbody>
          <tr>
            <th>Status</th>
            <td>
              <?php
              if ( $this->webhook_id ) {
                echo '<b><span style="color: #1bbf1b;">OK</span></b>';
              } else {
                echo '<b><span style="color: #f00;">NG</span></b>';
              }
              ?>
            </td>
          </tr>
          <?php if ( $this->webhook_id ) { ?>
          <tr>
            <th>Webhook Id</th>
            <td><?php echo $this->webhook_id; ?></td>
          </tr>
          <?php } ?>
          <?php if ( $this->subscription_id ) { ?>
          <tr>
            <th>Subscription Id</th>
            <td><?php echo $this->subscription_id; ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <hr>
    <?php
    parent::admin_options();
  }
  
  public function check_webhook() {
    if ( empty( $this->api_key ) || empty( $this->api_secret ) ) {
      return false;
    }

    if ( ( $webhook = $this->webhook->get_webhooks() ) !== false ) {
      return true;
    }

    if ( $this->webhook_id === $webhook['id'] ) {
      return true;
    }

    if ( ( $webhook_id = $this->webhook->add_webhook() ) === false ) {
      return false;
    }
    $this->update_option( 'webhook_id', $webhook_id );

    return true;
  }

  public function check_subscription() {
    if ( empty( $this->api_key ) || empty( $this->api_secret ) ) {
      return false;
    }

    if ( ( $subscription = $this->webhook->get_subscriptions() ) !== false ) {
      return true;
    }

    if ( $this->subscription_id === $subscription['id'] ) {
      return true;
    }

    if ( ( $subscription_id = $this->webhook->add_subscription( $this->xrp_address ) ) === false ) {
      return false;
    }

    $this->update_option( 'subscription_id', $subscription_id );

    return true;
  }

  /**
   * Setting for Webhook of xrpayments.co
   */
  public function process_admin_options( ) {
    parent::process_admin_options();

    $this->woo_xrpl_payment_webhook();

    // Manage webhook
    if ( ! $this->check_webhook() ) {
      return false;
    }

    // Manage subscription
    $this->check_subscription();

  }

  /**
   * Output for the order received page.
   */
  public function thankyou_page( $order_id ) {
    echo '<h2 class="woocommerce-column__title">' . __( 'Payment Information', Woo_Xrpl_Payment::$text_domain ) . '</h2>';
    if ( $this->instructions ) {
      echo wpautop( wptexturize( wp_kses_post( $this->instructions ) ) );
    }

  }

  /**
   * Add content to the WC emails.
   *
   * @access public
   * @param WC_Order $order
   * @param bool $sent_to_admin
   * @param bool $plain_text
   * @return void
   */
  public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
    $payment_method = version_compare( WC_VERSION, '2.7', '<' ) ? $order->payment_method : $order->get_payment_method();
    if ( ! $sent_to_admin && $this->id == $payment_method && ('on-hold' === $order->status || 'pending' === $order->status ) ) {
      if ( $this->instructions ) {
        echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
      }
      $order_id = version_compare( WC_VERSION, '2.7', '<' ) ? $order->id : $order->get_id();
      $this->xrp_payment_details( $order_id );
    }
  }

  /**
   * Get bank details and place into a list format
   */
  private function xrp_payment_details( $order_id = '' ) {
    if ( empty( $this->account_details ) ) {
      return;
    }
  }

  /**
   * Process the payment and return the result
   *
   * @param int $order_id
   * @return array
   */
  public function process_payment( $order_id ) {
    global $woocommerce;
    $order = new WC_Order( $order_id );

    // Mark as on-hold (we're awaiting the payment)
    $order->update_status( 'on-hold', __( 'XRP', Woo_Xrpl_Payment::$text_domain ) );

    // Reduce stock levels
    $order->reduce_order_stock();

    // Remove cart
    WC()->cart->empty_cart();

    // Return thankyou redirect
    return array(
      'result' 	=> 'success',
      'redirect'	=> $this->get_return_url( $order )
    );
  }
}
