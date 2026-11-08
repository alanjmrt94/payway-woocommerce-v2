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
 * Generates requests to send
 */
class WC_Payway_Request_Builder {

	/**
	 * WooCommerce Order
	 *
	 * @var WC_Order
	 */
	protected $order;

	/**
	 * Holds all processors responsible for building the request
	 *
	 * @see WC_Payway_Request_Processor_Interface
	 * @var array
	 */
	protected $processors;

	/**
	 * Holds relevant checkout form data values
	 *
	 * @var array
	 */
	protected $checkout_posted_data = array();

	/**
	 * Constructor
	 *
	 */
	public function __construct( ) {
		// initialize request processors
		$this->init_processors();
	}

	/**
	 * Includes required classes for request building
	 */
	private function init_processors() {
		$processors = array(
			'token-processor' 		=> new WC_Payway_Request_Token_Processor,
			'general-processor' 	=> new WC_Payway_Request_General_Processor,
			'customer-processor' 	=> new WC_Payway_Request_Customer_Processor,
			'payment-processor' 	=> new WC_Payway_Request_Payment_Processor,
			'order-processor' 		=> new WC_Payway_Request_Order_Processor,
			'subpayments-processor' => new WC_Payway_Request_SubPayments_Processor,
		);

		if ( wc_payway_config_is_cs_enabled() ) {
			$processors['cybersource-processor'] = new WC_Payway_Request_CyberSource_Processor;
		}

		$this->processors = $processors;
	}

	/**
	 * Returns the Checkout posted data associated during the Payment process
	 *
	 * @see WC_Payment_Gateway_Payway::process_payment()
	 *
	 * @return array
	 */
	public function get_checkout_form_data() {
		return $this->checkout_posted_data;
	}

	/**
	 *
	 * TODO: WC_Checkout::get_checkout_fields
	 * to add custom form fields into the list through:
	 * filter `woocommerce_checkout_fields`
	 *
	 * TODO: WC_Checkout::get_posted_data filter `woocommerce_checkout_posted_data` to capture posted data
	 * TODO: WC_Checkout::get_posted_data filter `woocommerce_process_checkout_field_{$key}` to capture one field at a time
	 * for manging custom fields
	 *
	 * TODO: WC_Checkout::process_checkout filter `woocommerce_checkout_order_processed`
	 *
	 * @param array $post_data
	 * @return $this
	 */
	public function set_checkout_form_data( $post_data ) {
		$rule_id      = 0;
		$card_id      = 0;
		$installments = 0;

		/**
		 * Installment dropdown option value
		 * structure: <rule_id>-<card_id>-<fee_to_send>
		 */
		if ( isset( $post_data['payway_gateway_cc_installments'] ) ) {
			$cc_installments = explode( '-', sanitize_text_field( (string) $post_data['payway_gateway_cc_installments'] ) );
			$rule_id         = isset( $cc_installments[0] ) ? absint( $cc_installments[0] ) : 0;
			$card_id         = isset( $cc_installments[1] ) ? absint( $cc_installments[1] ) : 0;
			$installments    = isset( $cc_installments[2] ) ? absint( $cc_installments[2] ) : 0;
		}

		$this->checkout_posted_data = array(
			'rule_id'      => $rule_id,
			'card_id'      => $card_id,
			'installments' => $installments,
			'token'        => isset( $post_data['payway_gateway_cc_token'] )
				? sanitize_text_field( (string) $post_data['payway_gateway_cc_token'] )
				: '',
			'bin'          => isset( $post_data['payway_gateway_cc_bin'] )
				? preg_replace( '/\D/', '', (string) $post_data['payway_gateway_cc_bin'] )
				: '',
			'last_digits'  => isset( $post_data['payway_gateway_cc_last_digits'] )
				? preg_replace( '/\D/', '', (string) $post_data['payway_gateway_cc_last_digits'] )
				: '',
			'cc_type'      => isset( $post_data['payway_gateway_cc_type'] )
				? absint( $post_data['payway_gateway_cc_type'] )
				: 0,
			'door_number'  => isset( $post_data['payway_gateway_cc_holder_door_number'] )
				? absint( $post_data['payway_gateway_cc_holder_door_number'] )
				: 0,
		);

		return $this;
	}

	/**
	 * Replaces checkout data with server-validated values.
	 *
	 * @param array $validated
	 * @return $this
	 */
	public function set_validated_checkout_data( array $validated ) {
		$this->checkout_posted_data = array_merge( $this->checkout_posted_data, $validated );
		return $this;
	}

	/**
	 * Returns the associated Order
	 *
	 * @return WC_Order|null
	 */
	public function get_order() {
		return $this->order;
	}

	/**
	 * Assigns the Order
	 *
	 * @param WC_Order $order
	 * @return void
	 */
	public function set_order( $order ) {
		$this->order = $order;

		return $this;
	}

	/**
	 * Executes all data processors
	 *
	 * @return array data to be sent to the Gateway
	 */
	public function process() {
		$result = array();

		do_action( 'wc_payway_request_builder_process_before', $this );

		foreach ( $this->processors as $name => $processor ) {
			$result = $this->merge(
				$result,
				$processor->process( $this->order, $this->checkout_posted_data )
			);
		}

		do_action( 'wc_payway_request_builder_process_after', $this );

		return $result;
	}

	/**
	 * Merge function for builders
	 *
	 * @param array $result
	 * @param array $builder
	 * @return array
	 */
	protected function merge(array $result, array $builder)
	{
		return array_replace_recursive($result, $builder);
	}
}
