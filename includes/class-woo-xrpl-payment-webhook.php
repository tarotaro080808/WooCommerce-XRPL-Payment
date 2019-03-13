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

if( ! class_exists( 'Woo_Xrpl_Payment_Webhook' ) ){
	/**
	 * WooCommerce XRPL Payment main class
	 *
	 * @since 1.0.0
	 */
	class Woo_Xrpl_Payment_Webhook {

		public $endpoint = 'https://webhook.xrpayments.co';
		
		private $api_key = '';
		
		private $api_secret = '';

		/**
		 * Constructor
		 */
		public function __construct( $api_key = '', $api_secret = '' ) {
			$this->api_key = $api_key;
			$this->api_secret = $api_secret;
		}
		

		/**
		 * Get Registered Webhooks
		 */
		public function get_webhooks() {
			$path = $this->endpoint . '/api/v1/webhooks';
			$args = array(
				'method' => 'GET',
				'headers' => array(
					'x-api-key' => $this->api_key,
					'x-api-secret' => $this->api_secret
				)
			);
			$response = wp_remote_get( $path, $args );
			if ( ! is_wp_error( $response ) ) {
				$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
				if( count( $response_body['webhooks'] ) > 0 ) {
					return $response_body['webhooks'][0];
				}
			}
			return false;
		}



		/**
		 * Add webhook url.
		 */
		public function add_webhook() {
			if ( !$this->api_key || !$this->api_secret ) {
				return false;
			}
			$path = $this->endpoint . '/api/v1/webhooks';
			$args = array(
				'method' => 'POST',
				'headers' => array(
					'Content-Type: application/json; charset=utf-8',
					'x-api-key' => $this->api_key,
					'x-api-secret' => $this->api_secret
				),
				'body' => array(
					"url" => WC()->api_request_url( 'Woo_Xrp_Payment_Gateway' )
				)
			);
			$response = wp_remote_request( $path, $args );
			if ( ! is_wp_error( $response ) ) {
				$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( $response_body['success'] === true ) {
					return $response_body['webhook_id'];
				}
			}
			return false;
		}

		public function get_subscriptions() {
			$path = $this->endpoint . '/api/v1/subscriptions';
			$args = array(
				'method' => 'GET',
				'headers' => array(
					'x-api-key' => $this->api_key,
					'x-api-secret' => $this->api_secret
				)
			);
			$response = wp_remote_get( $path, $args );
			if ( ! is_wp_error( $response ) ) {
				$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
				if( count( $response_body['subscriptions'] ) > 0 ) {
					return $response_body['subscriptions'][0];
				}
			}
			return false;
		}


		public function add_subscription( $address = '' ) {
			if ( !$this->api_key || !$this->api_secret ) {
				return false;
			}

			if ( $address === false ) {
				return false;
			}

			$path = $this->endpoint . '/api/v1/subscriptions';
			$args = array(
				'method' => 'POST',
				'headers' => array(
					'Content-Type: application/json; charset=utf-8',
					'x-api-key' => $this->api_key,
					'x-api-secret' => $this->api_secret
				),
				'body' => array(
					"address" => $address
				)
			);
			$response = wp_remote_request( $path, $args );
			if ( ! is_wp_error( $response ) ) {
				$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( $response_body['success'] === true ) {
					return $response_body['subscription_id'];
				}
			}

			return false;
		}


		/**
		 * Delete webhook url ( optional )
		 */
		public function delete_webhook( $id = '' ) {
			if ( !$this->api_key || !$this->api_secret ) {
				return false;
			}
			
			if ( $id == '' ) {
				return false;
			}

			$path = $this->endpoint . '/api/v1/webhooks';
			$args = array(
				'method' => 'DELETE',
				'headers' => array(
					'Content-Type: application/json; charset=utf-8',
					'x-api-key' => $this->api_key,
					'x-api-secret' => $this->api_secret
				),
				'body' => array(
					"webhook_id" => $id
				)
			);
			$response = wp_remote_request( $path, $args );
			if ( ! is_wp_error( $response ) ) {
				$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( $response_body['success'] === true ) {
					return true;
				}
			}
			return false;
		}

		public function callback($post) {
		}
  }
}
