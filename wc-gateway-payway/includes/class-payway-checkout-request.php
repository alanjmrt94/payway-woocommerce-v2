<?php
/**
 * Payway Payment Community — gateway de pago para WooCommerce (fork independiente).
 * Copyright (C) 2026 Alan Martinez
 *
 * Based on prior work © IURCO / Prisma SA where applicable.
 *
 * @package wc-gateway-payway
 * @license GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

/**
 * Normalizes checkout payment fields from classic POST or Blocks Store API payment_data.
 */
class WC_Payway_Checkout_Request {

	/**
	 * Raw request bag (POST keys used by the gateway form).
	 *
	 * @param array|null $source Optional override (tests / Store API).
	 * @return array<string,mixed>
	 */
	public static function get_raw_payment_fields( $source = null ) {
		$bag = is_array( $source ) ? $source : self::collect_request_bag();

		$keys = array(
			'payway_gateway_cc_token',
			'payway_gateway_cc_bin',
			'payway_gateway_cc_last_digits',
			'payway_gateway_cc_type',
			'payway_gateway_cc_installments',
			'payway_gateway_cc_holder_door_number',
			'payway_gateway_cc_bank',
		);

		$out = array();
		foreach ( $keys as $key ) {
			if ( isset( $bag[ $key ] ) ) {
				$out[ $key ] = $bag[ $key ];
			}
		}

		return $out;
	}

	/**
	 * Shape expected by Integrity_Validator / Request_Builder.
	 *
	 * @param array|null $source
	 * @return array{token:string,bin:string,last_digits:string,cc_type:int,door_number:int,rule_id:int,card_id:int,installments:int}
	 */
	public static function get_validated_shape( $source = null ) {
		$raw = self::get_raw_payment_fields( $source );

		$posted = array(
			'token'        => isset( $raw['payway_gateway_cc_token'] ) ? sanitize_text_field( (string) $raw['payway_gateway_cc_token'] ) : '',
			'bin'          => isset( $raw['payway_gateway_cc_bin'] ) ? sanitize_text_field( (string) $raw['payway_gateway_cc_bin'] ) : '',
			'last_digits'  => isset( $raw['payway_gateway_cc_last_digits'] ) ? sanitize_text_field( (string) $raw['payway_gateway_cc_last_digits'] ) : '',
			'cc_type'      => isset( $raw['payway_gateway_cc_type'] ) ? absint( $raw['payway_gateway_cc_type'] ) : 0,
			'door_number'  => isset( $raw['payway_gateway_cc_holder_door_number'] ) ? absint( $raw['payway_gateway_cc_holder_door_number'] ) : 0,
			'rule_id'      => 0,
			'card_id'      => 0,
			'installments' => 0,
		);

		if ( ! empty( $raw['payway_gateway_cc_installments'] ) ) {
			$parts = explode( '-', sanitize_text_field( (string) $raw['payway_gateway_cc_installments'] ) );
			$posted['rule_id']      = isset( $parts[0] ) ? absint( $parts[0] ) : 0;
			$posted['card_id']      = isset( $parts[1] ) ? absint( $parts[1] ) : 0;
			$posted['installments'] = isset( $parts[2] ) ? absint( $parts[2] ) : 0;
		}

		return $posted;
	}

	/**
	 * Merge classic $_POST with Blocks payment_data (list of {key,value} or assoc).
	 *
	 * @return array<string,mixed>
	 */
	private static function collect_request_bag() {
		$bag = isset( $_POST ) && is_array( $_POST ) ? wp_unslash( $_POST ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( isset( $bag['payment_data'] ) ) {
			$bag = array_merge( $bag, self::flatten_payment_data( $bag['payment_data'] ) );
		}

		/**
		 * Allow Store API / tests to inject payment fields.
		 *
		 * @param array $bag
		 */
		return (array) apply_filters( 'wc_payway_checkout_request_bag', $bag );
	}

	/**
	 * @param mixed $payment_data
	 * @return array<string,mixed>
	 */
	private static function flatten_payment_data( $payment_data ) {
		$flat = array();

		if ( ! is_array( $payment_data ) ) {
			return $flat;
		}

		$is_list = array_keys( $payment_data ) === range( 0, count( $payment_data ) - 1 );
		if ( $is_list ) {
			foreach ( $payment_data as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$key = isset( $row['key'] ) ? (string) $row['key'] : '';
				if ( '' === $key ) {
					continue;
				}
				$flat[ $key ] = isset( $row['value'] ) ? $row['value'] : '';
			}
			return $flat;
		}

		foreach ( $payment_data as $key => $value ) {
			$flat[ (string) $key ] = $value;
		}

		return $flat;
	}

	/**
	 * Whether Blocks payment-method API is available on this install.
	 *
	 * @return bool
	 */
	public static function blocks_integration_available() {
		return class_exists( '\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' );
	}
}
