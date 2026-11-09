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
 * WC_Payment_Gateway_Payway Class.
 */
class WC_Payment_Gateway_Payway extends WC_Payment_Gateway {

	const PAYMENT_CODE = WC_Payway_Config_Interface::PAYMENT_CODE;

	/**
	 * Whether or not logging is enabled
	 *
	 * @var bool
	 */
	public static $log_enabled = false;

	/**
	 * Logger instance
	 *
	 * @var WC_Logger
	 */
	public static $log = false;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id					= self::PAYMENT_CODE;
		$this->has_fields			= true;
		$this->icon					= apply_filters(
			'woocommerce_payway_icon',
			plugins_url('assets/images/card-logos.png', WC_PAYWAY_PLUGIN_FILE)
		);
		$this->order_button_text	= __( 'Pay with Payway', 'wc-gateway-payway' );
		$this->method_title			= __( 'Payway Payment Community', 'wc-gateway-payway' );
		$this->method_description	= __( 'Fork independiente para WooCommerce. No es un producto oficial de Payway.', 'wc-gateway-payway' );
		$this->supports				= array( 'products' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define Checkout Form variables.
		$this->title          = $this->get_option( 'title' );
		$this->description    = $this->get_option( 'description' );

		if ( ! $this->is_valid_for_use() ) {
			// TODO: add an admin notice
			$this->enabled = 'no';
		}

		// listens admin options gets saved for this Payment Gateway
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array( $this, 'process_admin_options' )
		);

		// implement custom success content
		if ( 'yes' === $this->enabled ) {
			add_filter(
				'woocommerce_thankyou_order_received_text',
				array( $this, 'order_received_text' ),
				10,
				2
			);
		}

		// After processing Checkout Payment form
		// check if we need to add a fee into the Order before sending it to the gateway
		add_action(
			'wc_payway_request_builder_process_before',
			array( $this, 'order_process_before_gateway' )
		);
	}

	/**
	 * Captures the Order and creates fees into the Order
	 *
	 * @param WC_Payway_Request_Builder $builder
	 */
	public function order_process_before_gateway( $builder ) {
		$data = $builder->get_checkout_form_data();

		// Prefer validated fee_plan from Integrity Validator when present.
		$plan = isset( $data['fee_plan'] ) && ! empty( $data['fee_plan'] )
			? $data['fee_plan']
			: false;

		if ( ! $plan
			&& ! empty( $data['rule_id'] )
			&& ! empty( $data['installments'] )
		) {
			$plan = WC_Payway_Promotion_Factory::get_applied_fee_plan(
				(int) $data['rule_id'],
				(int) $data['installments']
			);
		}

		if ( $plan && $builder->get_order() ) {
			$order = $builder->get_order();
			$total = (float) $order->get_total();

			$coefficient = is_object( $plan )
				? (float) $plan->coefficient
				: (float) ( $plan['coefficient'] ?? 1 );
			$new_total   = $total * $coefficient;
			$charge      = $new_total - $total;

			if ( $charge > 0 ) {
				$fee = new WC_Order_Item_Fee();
				$fee->set_name( __( 'Financing Cost', 'wc-gateway-payway' ) );
				$fee->set_amount( $charge );
				$fee->set_total( $charge );

				$order->add_item( $fee );
				$order->calculate_totals();
				$order->save();
			}
		}
	}

	/**
	 * Return whether or not this gateway still requires setup to function.
	 *
	 * When this gateway is toggled on via AJAX, if this returns true a
	 * redirect will occur to the settings page instead.
	 *
	 * @return bool
	 */
	public function needs_setup() {
		$env = wc_payway_is_sandbox_enabled() ? 'sandbox' : 'production';
		if ( 'sandbox' === $env ) {
			return ! (
				wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_SANDBOX_SITE_ID )
				&& wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_SANDBOX_PUBLIC_KEY )
				&& wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_SANDBOX_PRIVATE_KEY )
			);
		}

		return ! (
			wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_PRODUCTION_SITE_ID )
			&& wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_PRODUCTION_PUBLIC_KEY )
			&& wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_PRODUCTION_PRIVATE_KEY )
		);
	}

	/**
	 * Initialise Admin settings form fields
	 */
	public function init_form_fields()
	{
		$this->form_fields = include WC_PAYWAY_ABSPATH . 'includes/wc-payway-admin-settings.php';
	}

	/**
	 * Check if this gateway is available based on WordPress and WooCommerce configuration
	 * - checks if site's currency
	 *
	 * @return bool
	 */
	public function is_valid_for_use() {
		$currency_supported = in_array(
			get_woocommerce_currency(),
			apply_filters(
				'woocommerce_payway_supported_currencies',
				array( 'ARS', 'USD' )
			),
			true
		);

		return (bool) $currency_supported;
	}

	public function is_available() {
		$is_available = parent::is_available();

		return (bool) $is_available && $this->is_config_ready_for_checkout();
	}

	/**
	 * Ensure all conditions are meet to render the form within the Checkout

	 * @return bool
	 */
	public function is_config_ready_for_checkout() {
		// Ensure we have Configured Promotions
		// otherwise, there's nothing to render in the Checkout
		$data = wc_payway_storefront_options();
		$promotions = !empty( $data['promotions'] )
			? true
			: false;

		return (bool) $promotions;
	}

	/**
	 * Admin Panel Options.
	 */
	public function admin_options() {
		if ( $this->is_valid_for_use() ) {
			parent::admin_options();
		} else {
			?>
			<div class="inline error">
				<p>
					<strong><?php esc_html_e( 'Gateway disabled', 'woocommerce' ); ?></strong>: <?php esc_html_e( 'Payway Gateway does not support your store currency.', 'wc-gateway-payway' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Builds Checkout Payment Form
	 *
	 * @return void
	 */
	public function payment_fields() {
		if ( ! is_checkout() || ! $this->is_config_ready_for_checkout() ) {
			return;
		}

		$this->enqueueStorefrontScripts();

		// retrieve all config options for the Checkout Form
		$config_options = wc_payway_storefront_options();
		// adds the cart total for installment calculation
		$config_options['cart_total'] = $this->get_order_total();

		wp_localize_script(
			'woocommerce-gateway-payway-sdk',
			'wc_gateway_payway_params',
			$config_options
		);

		require_once WC_PAYWAY_ABSPATH . 'template/html-payway-checkout-form.php';
	}

	/**
	 *
	 * @return void
	 */
	public function validate_fields() {
		$posted     = WC_Payway_Checkout_Request::get_validated_shape();
		$validation = WC_Payway_Integrity_Validator::validate_checkout_selection( $posted );
		if ( is_wp_error( $validation ) ) {
			wc_add_notice( $validation->get_error_message(), 'error' );
			return false;
		}

		return true;
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		Payway()->init_payway_request_includes();

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return array(
				'result'   => 'failure',
				'refresh'  => true,
				'messages' => WC_Payway_Errors::message( WC_Payway_Errors::INTERNAL ),
			);
		}

		// Idempotencia: si ya hay transaction_id, no reprocesar.
		$existing_tx = WC_Payway_Meta::get_order_transaction_id( $order_id, true );
		if ( $existing_tx && $order->is_paid() ) {
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}

		try {
			$builder = new WC_Payway_Request_Builder();
			$builder->set_checkout_form_data( WC_Payway_Checkout_Request::get_raw_payment_fields() );

			$validated = WC_Payway_Integrity_Validator::validate_checkout_selection(
				$builder->get_checkout_form_data()
			);
			if ( is_wp_error( $validated ) ) {
				wc_add_notice( $validated->get_error_message(), 'error' );
				$order->update_status(
					'failed',
					$validated->get_error_message()
				);
				return array(
					'result'   => 'failure',
					'refresh'  => true,
					'messages' => $validated->get_error_message(),
				);
			}

			// Sobrescribe datos del builder con valores validados en servidor.
			$builder->set_validated_checkout_data( $validated );
			$request_data = $builder->set_order( $order )->process();

			// Forzar payment_method_id desde tarjeta validada (mitiga #71).
			if ( isset( $validated['payment_method_id'] ) ) {
				$request_data['payment_method_id'] = (int) $validated['payment_method_id'];
			}

			$this->update_order_meta_promotion( $order, $validated );

			$request = new WC_Payway_Request();
			$request->pay( $request_data );

			if ( $request->get_success() ) {
				$result_payload = $request->get_result();
				$status         = is_array( $result_payload ) ? ( $result_payload['status'] ?? '' ) : '';

				if ( $status && ! in_array( $status, array( 'approved', 'accredited' ), true ) ) {
					throw new Exception( WC_Payway_Errors::message( WC_Payway_Errors::ISSUER_REJECTED ) );
				}

				$order->payment_complete( $request->get_transaction_id() );
				$this->update_order_meta( $result_payload, $order );
				$this->add_success_note_to_order( $order );

				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
			}

			$error_raw  = $request->get_error_messages();
			$error_text = WC_Payway_Errors::format_gateway_failure( $error_raw );
			wc_add_notice(
				__( 'Payment error: ', 'wc-gateway-payway' ) . $error_text,
				'error'
			);
			$this->add_failure_note_to_order( $order, is_string( $error_raw ) ? $error_raw : wp_json_encode( $error_raw ) );
			$order->update_status(
				'failed',
				sprintf(
					/* translators: %s: gateway error */
					__( 'Pago fallido en la pasarela: %s', 'wc-gateway-payway' ),
					$error_text
				)
			);

			// Blocks Store API clears notices and shows a generic message unless we throw.
			if ( $this->is_store_api_request() ) {
				throw new Exception( $error_text );
			}

			return array(
				'result'   => 'failure',
				'refresh'  => true,
				'messages' => $error_text,
			);

		} catch ( Exception $e ) {
			wc_payway_get_logger()->error( $e->getMessage() );
			$friendly = WC_Payway_Errors::format_gateway_failure( $e->getMessage() );
			if ( $friendly === WC_Payway_Errors::message( WC_Payway_Errors::PROCESSING ) && $e->getMessage() ) {
				$friendly = $e->getMessage();
			}
			wc_add_notice(
				__( 'Payment error: ', 'wc-gateway-payway' ) . $friendly,
				'error'
			);
			$this->add_failure_note_to_order( $order, $e->getMessage() );
			$order->update_status(
				'failed',
				sprintf(
					/* translators: %s: exception message */
					__( 'Pago fallido: %s', 'wc-gateway-payway' ),
					$friendly
				)
			);

			if ( $this->is_store_api_request() ) {
				throw new Exception( $friendly );
			}

			return array(
				'result'   => 'failure',
				'refresh'  => true,
				'messages' => $friendly,
			);
		}
	}

	/**
	 * Blocks checkout (Store API) clears WC notices; failures must throw to reach the shopper.
	 *
	 * @return bool
	 */
	private function is_store_api_request() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}
		if ( function_exists( 'WC' ) && WC()->is_rest_api_request() ) {
			return true;
		}
		return false;
	}

	/**
	 * Adds a success message whether using Cybersource or not
	 *
	 * @param WC_Order $order
	 * @param string $error_message
	 */
	private function add_failure_note_to_order( $order, $error_message = '' ) {
		$note = __('Order couldn\'t be processed through Payway Payment Gateway', 'wc-gateway-payway');

		if ( wc_payway_config_is_cs_enabled() ) {
			$note .= __(' using Cybersource', 'wc-gateway-payway');
		}

		if ( $error_message ) {
			$note .= __(': ' . $error_message, 'wc-gateway-payway');
		}

		$order->add_order_note( $note );
	}

	/**
	 * Adds a success message whether using Cybersource or not
	 *
	 * @param WC_Order $order
	 */
	private function add_success_note_to_order( $order ) {
		$note = __('Order successfully processed through Payway Payment Gateway', 'wc-gateway-payway');

		if ( wc_payway_config_is_cs_enabled() ) {
			$note .= __(' using Cybersource', 'wc-gateway-payway');
		}

		$order->add_order_note( $note );
	}

	/**
	 * Update the Promotion applied meta field with the selected Fee Plan
	 *
	 * @param WC_Order $order
	 * @param array $posted_data
	 * @return bool Whether process finished OK
	 */
	public function update_order_meta_promotion( $order, $posted_data ) {
		$order_id = $order->get_id();

		/**
		 * Retrieves the `fee_to_send` value
		 * from the Installment dropdown's selected option
		 * within the Checkout Payment form
		 *
		 * @see WC_Payway_Request_Builder::set_checkout_form_data()
		 * @var int
		 */
		$fee_to_send = isset( $posted_data['installments'] )
			? $posted_data['installments']
			: false;
		$promotion_id = isset( $posted_data['rule_id'] )
			? $posted_data['rule_id']
			: false;

		// we can't proceed if we don't have both
		if ( !$promotion_id || !$fee_to_send ) {
			return false;
		}

		// Now retrieve the Fee Plan data
		$fee_details = WC_Payway_Promotion_Factory::get_applied_fee_plan(
			$promotion_id,
			$fee_to_send
		);

		// convert the record into an array()
		// to make it easy access to the data within the Order Detail view
		if ( gettype($fee_details) === 'object' ) {
			$fee_details = json_decode(json_encode($fee_details), true);
		}

		// Update the Order meta data with the selected Plan
		WC_Payway_Meta::set_order_promotion( $order_id, $fee_details );
	}

	/**
	 * Saves order metadata for further display through Order View
	 *
	 * @param array $result
	 * @param WC_Order $order
	 */
	public function update_order_meta( $result, $order ) {
		$order_id = $order->get_id();

		// Saves full response from Gateway
		WC_Payway_Meta::set_order_transaction_response( $order_id, $result );
		// Saves transaction id data
		WC_Payway_Meta::set_order_transaction_id( $order_id, $result['id'] );
		// Saves Site Transaction Id data
		WC_Payway_Meta::set_order_site_transaction_id( $order_id, $result['site_transaction_id'] );

		$payment_data = array();
		$payment_data['payment_method_id'] = $result['payment_method_id'] ?? 'no data in response';
		$payment_data['credit_card_type'] = $result['card_brand'] ?? 'no data in response';
		$payment_data['installments'] = $result['installments'] ?? 'no data in response';
		$payment_data['status'] = $result['status'] ?? 'no data in response';

		// Builds general status data details
		if ( isset($result['status_details']) ) {
			$details = $result['status_details'];
			$payment_data['status_details'] = array(
				'ticket' => $details['ticket'],
				'card_authorization_code' => $details['card_authorization_code'],
				'address_validation_code' => $details['address_validation_code']
			);
		}

		// Builds Cybersource data
		if ( isset($result['fraud_detection']) && isset($result['fraud_detection']['status']) ) {
			$fraud = $result['fraud_detection']['status'];
			$payment_data['cybersource'] = array(
				'decision' => $fraud['decision'],
				'description' => $fraud['description']
			);
		}

		// Save data into the Order meta field
		WC_Payway_Meta::set_order_payment_data( $order_id, $payment_data );
	}

	/**
	 * Register scripts for frontend
	 *
	 * @return void
	 */
	private function enqueueStorefrontScripts() {
		$version = defined( 'WC_PAYWAY_PLUGIN_VERSION' ) ? WC_PAYWAY_PLUGIN_VERSION : '1.1.0';

		wp_register_script(
			'woocommerce-gateway-payway-sdk',
			'https://live.decidir.com/static/v2.5/decidir.js',
			array(),
			'2.5',
			false
		);
		wp_enqueue_script( 'woocommerce-gateway-payway-sdk' );

		wp_register_script(
			'woocommerce-accounting',
			WC()->plugin_url() . '/assets/js/accounting/accounting.min.js',
			array( 'jquery' ),
			'0.4.2'
		);

		wp_localize_script(
			'woocommerce-accounting',
			'wc_gateway_payway_accounting_format',
			array(
				'precision' => wc_get_price_decimals(),
				'symbol'    => get_woocommerce_currency_symbol(),
				'decimal'   => esc_attr( wc_get_price_decimal_separator() ),
				'thousand'  => esc_attr( wc_get_price_thousand_separator() ),
				'format'    => esc_attr(
					str_replace(
						array( '%1$s', '%2$s' ),
						array( '%s', '%v' ),
						get_woocommerce_price_format()
					)
				),
			)
		);

		wp_register_script(
			'woocommerce-gateway-payway-checkout-core',
			plugins_url( 'assets/js/frontend/payway-checkout-core.js', WC_PAYWAY_PLUGIN_FILE ),
			array( 'jquery', 'woocommerce-gateway-payway-sdk' ),
			$version,
			true
		);

		wp_register_script(
			'woocommerce-gateway-payway-classic-checkout',
			plugins_url( 'assets/js/frontend/payway-classic-checkout.js', WC_PAYWAY_PLUGIN_FILE ),
			array(
				'jquery',
				'wc-checkout',
				'woocommerce-gateway-payway-sdk',
				'woocommerce-gateway-payway-checkout-core',
				'woocommerce-accounting',
			),
			$version,
			true
		);

		wp_register_style(
			'woocommerce-gateway-payway-styles',
			plugins_url( 'assets/css/style.css', WC_PAYWAY_PLUGIN_FILE ),
			array(),
			$version
		);
		wp_enqueue_style( 'woocommerce-gateway-payway-styles' );

		wp_enqueue_script( 'woocommerce-accounting' );
		wp_enqueue_script( 'woocommerce-gateway-payway-checkout-core' );
		wp_enqueue_script( 'woocommerce-gateway-payway-classic-checkout' );
	}

	/**
	 * Load admin scripts.
	 */
	public function admin_scripts() {
		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( 'woocommerce_page_wc-settings' !== $screen_id ) {
			return;
		}

		// wp_enqueue_script(
		// 	'woocommerce_gateway_payway_admin_scripts',
		// 	__DIR__ . '/includes/admin/assets/js/payway-admin.js',
		// 	array(),
		// 	$version,
		// 	true
		// );
	}

	/**
	 * Custom order received text.
	 *
	 * @param string   $text Default text.
	 * @param WC_Order $order Order data.
	 * @return string
	 */
	public function order_received_text( $text, $order ) {
		if ( $order && $this->id === $order->get_payment_method() ) {
			return esc_html__( 'Thank you for your payment. Your transaction has been completed.', 'wc-gateway-payway' );
		}

		return $text;
	}
}
