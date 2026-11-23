<?php
/**
 * Admin service: Decidir PaymentList for the active or selected environment.
 *
 * @package wc-gateway-payway
 * @license GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wraps SDK PaymentList without logging private keys.
 */
class WC_Payway_Payment_List {

	const ENV_SANDBOX     = 'sandbox';
	const ENV_PRODUCTION  = 'production';
	const DEFAULT_SIZE    = 20;
	const MAX_PAGE_SIZE   = 50;

	/**
	 * @param string $environment sandbox|production
	 * @return bool
	 */
	public static function environment_has_credentials( $environment ) {
		if ( self::ENV_SANDBOX === $environment ) {
			return (bool) (
				wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_SANDBOX_PUBLIC_KEY )
				&& wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_SANDBOX_PRIVATE_KEY )
			);
		}

		return (bool) (
			wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_PRODUCTION_PUBLIC_KEY )
			&& wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_PRODUCTION_PRIVATE_KEY )
		);
	}

	/**
	 * @return string sandbox|production
	 */
	public static function default_environment() {
		return function_exists( 'wc_payway_is_sandbox_enabled' ) && wc_payway_is_sandbox_enabled()
			? self::ENV_SANDBOX
			: self::ENV_PRODUCTION;
	}

	/**
	 * @param string $environment sandbox|production
	 * @param int    $page        zero-based page index
	 * @param int    $page_size
	 * @return array{success:bool,results:array,has_more:bool,limit:int,offset:int,error_code:int,error_message:string}
	 */
	public static function fetch_payments( $environment, $page = 0, $page_size = self::DEFAULT_SIZE ) {
		$page      = max( 0, (int) $page );
		$page_size = max( 1, min( self::MAX_PAGE_SIZE, (int) $page_size ) );
		$offset    = $page * $page_size;

		$empty = array(
			'success'       => false,
			'results'       => array(),
			'has_more'      => false,
			'limit'         => $page_size,
			'offset'        => $offset,
			'error_code'    => 0,
			'error_message' => '',
		);

		if ( ! self::environment_has_credentials( $environment ) ) {
			$empty['error_message'] = __( 'Configure API keys for this environment under WooCommerce → Payments.', 'wc-gateway-payway' );
			return $empty;
		}

		try {
			$connector = self::build_connector( $environment );
			$data      = array(
				'offset'   => $offset,
				'pageSize' => $page_size,
			);
			$response = $connector->payment()->PaymentList( $data );

			// PaymentsListResponse exposes fields via Data\Response::__call;
			// method_exists( 'getResults' ) is false — prefer getDataField().
			$data_field = method_exists( $response, 'getDataField' )
				? $response->getDataField()
				: array();
			if ( ! is_array( $data_field ) ) {
				$data_field = array();
			}

			$results = array();
			if ( isset( $data_field['results'] ) && is_array( $data_field['results'] ) ) {
				$results = $data_field['results'];
			} else {
				// Fallback for SDK __call getters (do not use method_exists).
				try {
					$via_call = $response->getResults();
					if ( is_array( $via_call ) ) {
						$results = $via_call;
					}
				} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				}
			}

			$has_more = ! empty( $data_field['hasMore'] ) || ! empty( $data_field['has_more'] );
			$limit       = isset( $data_field['limit'] ) ? (int) $data_field['limit'] : $page_size;
			$resp_offset = isset( $data_field['offset'] ) ? (int) $data_field['offset'] : $offset;

			return array(
				'success'       => true,
				'results'       => $results,
				'has_more'      => (bool) $has_more,
				'limit'         => $limit,
				'offset'        => $resp_offset,
				'error_code'    => 0,
				'error_message' => '',
			);
		} catch ( \Decidir\Exception\SdkException $e ) {
			return self::error_result( $empty, $e );
		} catch ( \Exception $e ) {
			return self::error_result( $empty, $e );
		}
	}

	/**
	 * @param array     $base
	 * @param \Exception $e
	 * @return array
	 */
	private static function error_result( array $base, $e ) {
		$code = (int) $e->getCode();
		$base['error_code']    = $code;
		$base['error_message'] = self::human_error_message( $code, $e );

		if ( class_exists( 'WC_Payway_Logger', false ) ) {
			WC_Payway_Logger::error(
				sprintf(
					'PaymentList failed (HTTP %d): %s',
					$code,
					$e->getMessage()
				)
			);
		}

		return $base;
	}

	/**
	 * @param int        $code
	 * @param \Exception $e
	 * @return string
	 */
	private static function human_error_message( $code, $e ) {
		if ( 401 === $code ) {
			return __( 'Decidir returned 401 Unauthorized: check that the private key matches the selected environment and is active.', 'wc-gateway-payway' );
		}
		if ( 403 === $code ) {
			return __( 'Decidir returned 403 Forbidden: this private key is not allowed to list payments for the merchant.', 'wc-gateway-payway' );
		}

		return $e->getMessage()
			? $e->getMessage()
			: __( 'Could not load payments from Decidir.', 'wc-gateway-payway' );
	}

	/**
	 * @param string $environment sandbox|production
	 * @return \Decidir\Connector
	 */
	private static function build_connector( $environment ) {
		require_once WC_PAYWAY_ABSPATH . 'includes/interfaces/class-wc-payway-rest-interface.php';
		include_once WC_PAYWAY_ABSPATH . 'decidir/vendor/autoload.php';

		if ( self::ENV_SANDBOX === $environment ) {
			$type    = WC_Payway_Rest_Interface::CONNECTOR_ENVIRONMENT_SANDBOX;
			$public  = wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_SANDBOX_PUBLIC_KEY );
			$private = wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_SANDBOX_PRIVATE_KEY );
		} else {
			$type    = WC_Payway_Rest_Interface::CONNECTOR_ENVIRONMENT_PRODUCTION;
			$public  = wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_PRODUCTION_PUBLIC_KEY );
			$private = wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_PRODUCTION_PRIVATE_KEY );
		}

		$credentials = array(
			WC_Payway_Rest_Interface::CONNECTOR_PUBLIC_KEY  => $public,
			WC_Payway_Rest_Interface::CONNECTOR_PRIVATE_KEY => $private,
		);

		return new \Decidir\Connector(
			$credentials,
			$type,
			WC_Payway_Rest_Interface::CONNECTOR_DEVELOPER,
			WC_Payway_Rest_Interface::CONNECTOR_GROUPER,
			WC_Payway_Rest_Interface::CONNECTOR_SERVICE
		);
	}

	/**
	 * @param mixed $payment_row API row (array or object)
	 * @param string $field
	 * @return mixed
	 */
	public static function payment_field( $payment_row, $field ) {
		if ( is_array( $payment_row ) && array_key_exists( $field, $payment_row ) ) {
			return $payment_row[ $field ];
		}
		if ( is_object( $payment_row ) ) {
			$underscore_getter = 'get' . str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $field ) ) );
			if ( method_exists( $payment_row, $underscore_getter ) ) {
				return $payment_row->$underscore_getter();
			}
			if ( isset( $payment_row->$field ) ) {
				return $payment_row->$field;
			}
		}
		return null;
	}

	/**
	 * @param string|int $transaction_id Payway payment id
	 * @param string     $site_transaction_id Merchant reference
	 * @return int Order ID or 0
	 */
	public static function find_wc_order_id( $transaction_id, $site_transaction_id ) {
		$meta_transaction = WC_Payway_Meta::get_full_meta_name( WC_Payway_Meta_Interface::TRANSACTION_ID );
		$meta_site        = WC_Payway_Meta::get_full_meta_name( WC_Payway_Meta_Interface::SITE_TRANSACTION_ID );

		if ( $transaction_id !== '' && $transaction_id !== null ) {
			$orders = wc_get_orders(
				array(
					'limit'      => 1,
					'meta_key'   => $meta_transaction,
					'meta_value' => (string) $transaction_id,
					'return'     => 'ids',
				)
			);
			if ( ! empty( $orders[0] ) ) {
				return (int) $orders[0];
			}
		}

		if ( $site_transaction_id !== '' && $site_transaction_id !== null ) {
			$orders = wc_get_orders(
				array(
					'limit'      => 1,
					'meta_key'   => $meta_site,
					'meta_value' => (string) $site_transaction_id,
					'return'     => 'ids',
				)
			);
			if ( ! empty( $orders[0] ) ) {
				return (int) $orders[0];
			}
		}

		return 0;
	}
}