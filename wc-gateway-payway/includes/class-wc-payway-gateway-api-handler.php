<?php
/**
 * Payway Payment Community — gateway de pago para WooCommerce (fork independiente).
 * Copyright (C) 2026 Alan Martinez
 *
 * Based on prior work © IURCO / Prisma SA where applicable.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <https://www.gnu.org/licenses/>.
 *
 * @package wc-gateway-payway
 * @license GPL-2.0-or-later
 */
/**
 * @author IURCO - Prisma SA
 */

defined( 'ABSPATH' ) || exit;

/**
 *
 */
class WC_Payway_Api_Handler {
	/**
	 * @var string
	 */
	protected $api_url;
	/**
	 * @var array
	 */
	protected $credentials;

	/**
	 * Environment type based if it's test mode or not
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * @var \Decidir\Connector
	 */
	protected $connector;

	public function __construct() {
		$this->init_settings();
		$this->setup_connector();
	}

	protected function init_settings() {
		$config = wc_payway_get_config_options();

		if ( ! $config ) {
			// no configuration saved yet
			wc_add_notice('no configuration saved yet');
			return;
		}

		if ( wc_payway_is_sandbox_enabled() ) {
			$type = WC_Payway_Rest_Interface::CONNECTOR_ENVIRONMENT_SANDBOX;
			$url = WC_Payway_Rest_Interface::SANDBOX_URL;
			$public = $config[WC_Payway_Config_Interface::FIELD_SANDBOX_PUBLIC_KEY];
			$private = $config[WC_Payway_Config_Interface::FIELD_SANDBOX_PRIVATE_KEY];
		} else {
			$type = WC_Payway_Rest_Interface::CONNECTOR_ENVIRONMENT_PRODUCTION;
			$url = WC_Payway_Rest_Interface::PRODUCTION_URL;
			$public = $config[WC_Payway_Config_Interface::FIELD_PRODUCTION_PUBLIC_KEY];
			$private = $config[WC_Payway_Config_Interface::FIELD_PRODUCTION_PRIVATE_KEY];
		}

		$this->type = $type;
		$this->api_url = $url;
		$this->credentials = array(
			WC_Payway_Rest_Interface::CONNECTOR_PUBLIC_KEY => $public,
			WC_Payway_Rest_Interface::CONNECTOR_PRIVATE_KEY => $private,
		);
	}

	protected function setup_connector() {
		include_once WC_PAYWAY_ABSPATH . '/decidir/vendor/autoload.php';

		$this->connector = new \Decidir\Connector(
			$this->credentials,
			$this->type,
			WC_Payway_Rest_Interface::CONNECTOR_DEVELOPER,
			WC_Payway_Rest_Interface::CONNECTOR_GROUPER,
			WC_Payway_Rest_Interface::CONNECTOR_SERVICE
		);
	}

	/**
	 * @param array $payment_data
	 * @return \Decidir\Payment\PaymentResponse
	 */
	public function post_payment( array $payment_data ) {
		return $this->connector
			->payment()
			->ExecutePayment( $payment_data );
	}
}
