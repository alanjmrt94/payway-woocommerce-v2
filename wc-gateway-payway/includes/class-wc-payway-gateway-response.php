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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles response
 */
class WC_Gateway_Payway_Response {

	const FRAUD_DETECTION_FIELD = 'fraud_detection';
	const FIELD_STATUS = 'status';
	const FIELD_DECISION = 'decision';
	const FIELD_REASON_CODE = 'reason_code';
	const FIELD_DESCRIPTION = 'description';
	const FIELD_REVIEW = 'review';

	protected $response;
	protected $status;
	protected $reason_code;

	/**
	 * The single instance of the class
	 *
	 * @var WC_Gateway_Payway_Response
	 */
	protected static $_instance = null;

	/**
     * @var WC_Payway_Response_Processor_Interface[]
     */
    private $handlers;

	public function __construct() {
		// $this->init_handlers();
	}

	/**
	 * Instance
	 *
	 * @static
	 * @return WC_Gateway_Payway_Response
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	// private function init_handlers() {
	// 	$processors = array(
	// 		'payment-processor' => new WC_Payway_Response_Payment_Processor,
	// 	);
	//
	// 	// if ( wc_payway_config_is_cs_enabled() ) {
	// 	// 	$processors['cybersource-processor'] = new WC_Payway_Response_CyberSource_Processor;
	// 	// }
	//
	// 	$this->handlers = $processors;
	// }

	/**
	 * Handles response
	 *
	 * @param array $data
	 * @return void
	 */
	// public function handle(array $data)
	// {
	// 	$response = array();
	//
	// 	foreach ($this->handlers as $handler) {
	// 		$handler->handle( $data, $response );
	// 	}
	//
	// 	$this->set_response( $response );
	// }

	/**
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * @return string
	 */
	public function get_decision() {
		return $this->decision;
	}

	/**
	 * @return int
	 */
	public function get_reason_code() {
		return $this->reason_code;
	}


}
