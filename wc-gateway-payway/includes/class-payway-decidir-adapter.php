<?php
/**
 * Thin adapter around the Decidir/Payway PHP SDK connector.
 *
 * @package wc-gateway-payway
 * @license GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

/**
 * First step toward a dedicated Decidir boundary (namespaces later).
 */
class WC_Payway_Decidir_Adapter {

	/** @var \Decidir\Connector|null */
	private $connector;

	/**
	 * @param array  $credentials public_key / private_key
	 * @param string $environment sandbox|production
	 * @param string $api_url
	 */
	public function __construct( array $credentials, $environment, $api_url ) {
		if ( ! class_exists( '\Decidir\Connector' ) ) {
			$autoload = WC_PAYWAY_ABSPATH . 'decidir/vendor/autoload.php';
			if ( is_readable( $autoload ) ) {
				require_once $autoload;
			}
		}
		if ( class_exists( '\Decidir\Connector' ) ) {
			$this->connector = new \Decidir\Connector( $credentials, $environment, $api_url );
		}
	}

	/**
	 * @return \Decidir\Connector|null
	 */
	public function connector() {
		return $this->connector;
	}

	/**
	 * Success statuses accepted for paid orders (SEC-08).
	 *
	 * @return string[]
	 */
	public static function success_statuses() {
		/**
		 * Filters Decidir/Payway statuses treated as successful payments.
		 *
		 * @param string[] $statuses
		 */
		return apply_filters(
			'wc_payway_success_statuses',
			array( 'approved', 'accredited' )
		);
	}

	/**
	 * @param string $status
	 * @return bool
	 */
	public static function is_success_status( $status ) {
		return in_array( strtolower( (string) $status ), self::success_statuses(), true );
	}
}
