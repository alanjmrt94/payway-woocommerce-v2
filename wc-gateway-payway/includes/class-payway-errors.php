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
 * Catálogo de errores internos del plugin.
 *
 * @package wc-gateway-payway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Códigos y mensajes comprensibles (sin secretos).
 */
class WC_Payway_Errors {

	const CONFIG_INCOMPLETE         = 'PPC_CONFIG_001';
	const AUTH_CREDENTIALS          = 'PPC_AUTH_001';
	const CONNECTIVITY              = 'PPC_NET_001';
	const VALIDATION_TOKEN          = 'PPC_VAL_001';
	const VALIDATION_INSTALLMENTS   = 'PPC_VAL_002';
	const VALIDATION_PROMOTION      = 'PPC_VAL_003';
	const VALIDATION_FEE_PLAN       = 'PPC_VAL_004';
	const VALIDATION_PAYMENT_METHOD = 'PPC_VAL_005';
	const PROCESSING                = 'PPC_PAY_001';
	const ISSUER_REJECTED           = 'PPC_PAY_002';
	const PAYMENT_IN_PROGRESS       = 'PPC_PAY_003';
	const INVALID_CARD              = 'PPC_CARD_001';
	const CS_ADDRESS                = 'PPC_CS_001';
	const CS_CUSTOMER_DATA          = 'PPC_CS_002';
	const COMPATIBILITY             = 'PPC_COMPAT_001';
	const INTERNAL                  = 'PPC_INT_001';
	const GATEWAY_COMM              = 'PPC_API_001';

	/**
	 * Mensaje orientado al comercio/comprador.
	 *
	 * @param string $code
	 * @return string
	 */
	public static function message( $code ) {
		$messages = array(
			self::CONFIG_INCOMPLETE         => __( 'Payment method configuration is incomplete. Check credentials and promotions.', 'wc-gateway-payway' ),
			self::AUTH_CREDENTIALS          => __( 'Authentication error with Payway. Verify the keys for the active environment.', 'wc-gateway-payway' ),
			self::CONNECTIVITY              => __( 'Could not connect to the payment service. Try again later.', 'wc-gateway-payway' ),
			self::VALIDATION_TOKEN          => __( 'Card token was not received. Reload checkout and try again.', 'wc-gateway-payway' ),
			self::VALIDATION_INSTALLMENTS   => __( 'Select a valid bank, card, and installments.', 'wc-gateway-payway' ),
			self::VALIDATION_PROMOTION      => __( 'The selected promotion is not available. Refresh the page and choose installments again.', 'wc-gateway-payway' ),
			self::VALIDATION_FEE_PLAN       => __( 'The selected installment plan is not valid.', 'wc-gateway-payway' ),
			self::VALIDATION_PAYMENT_METHOD => __( 'The card identifier (payment_method_id) is not valid for this merchant.', 'wc-gateway-payway' ),
			self::PROCESSING                => __( 'An error occurred while processing the payment.', 'wc-gateway-payway' ),
			self::ISSUER_REJECTED           => __( 'The payment was declined by the issuer or the processor.', 'wc-gateway-payway' ),
			self::PAYMENT_IN_PROGRESS       => __( 'A payment is already being processed for this order. Please wait a moment and try again.', 'wc-gateway-payway' ),
			self::INVALID_CARD              => __( 'The card is invalid or not enabled for this merchant. Please try another card.', 'wc-gateway-payway' ),
			self::CS_ADDRESS                => __( 'We could not validate your billing or shipping address for fraud prevention. Please complete both addresses (street and number) and try again.', 'wc-gateway-payway' ),
			self::CS_CUSTOMER_DATA          => __( 'Some checkout details required for fraud prevention are missing or invalid. Please review your contact and address information and try again.', 'wc-gateway-payway' ),
			self::COMPATIBILITY             => __( 'Environment incompatibility detected. Check the plugin version matrix.', 'wc-gateway-payway' ),
			self::INTERNAL                  => __( 'Internal plugin error. Contact the site administrator.', 'wc-gateway-payway' ),
			self::GATEWAY_COMM              => __( 'Communication error with Payway.', 'wc-gateway-payway' ),
		);

		return isset( $messages[ $code ] )
			? $messages[ $code ]
			: __( 'Unknown error while processing the payment.', 'wc-gateway-payway' );
	}

	/**
	 * Mapea tipologías Decidir / HTTP a códigos internos.
	 *
	 * @param string|int $gateway_code
	 * @return string
	 */
	public static function from_gateway( $gateway_code ) {
		$map = array(
			'authentication_error'    => self::AUTH_CREDENTIALS,
			'invalid_request_error'   => self::VALIDATION_PAYMENT_METHOD,
			'malformed_request_error' => self::GATEWAY_COMM,
			'not_found_error'         => self::GATEWAY_COMM,
			'api_error'               => self::GATEWAY_COMM,
			'invalid_card'            => self::INVALID_CARD,
			'card_declined'           => self::ISSUER_REJECTED,
			'rejected'                => self::ISSUER_REJECTED,
			401                       => self::AUTH_CREDENTIALS,
			402                       => self::VALIDATION_PAYMENT_METHOD,
		);

		return isset( $map[ $gateway_code ] )
			? $map[ $gateway_code ]
			: self::PROCESSING;
	}

	/**
	 * Human-readable message from Decidir / SDK error payloads (for checkout UI).
	 *
	 * @param mixed $raw Error messages from the request layer (array|string).
	 * @return string
	 */
	public static function format_gateway_failure( $raw ) {
		if ( is_string( $raw ) && '' !== $raw ) {
			$decoded = json_decode( $raw, true );
			if ( JSON_ERROR_NONE === json_last_error() ) {
				$raw = $decoded;
			} else {
				return $raw;
			}
		}

		if ( ! is_array( $raw ) ) {
			return self::message( self::PROCESSING );
		}

		// Shape: status_details.error { type, reason... }
		if ( ! empty( $raw['type'] ) && is_string( $raw['type'] ) ) {
			return self::message( self::from_gateway( $raw['type'] ) );
		}

		// Shape: [ 'Gateway Error', '[{"code":"invalid_param","param":"fraud_detection..."}]' ]
		foreach ( $raw as $item ) {
			if ( is_array( $item ) && ! empty( $item['type'] ) && is_string( $item['type'] ) ) {
				return self::message( self::from_gateway( $item['type'] ) );
			}
			if ( ! is_string( $item ) ) {
				continue;
			}
			$maybe = json_decode( $item, true );
			if ( JSON_ERROR_NONE === json_last_error() && is_array( $maybe ) ) {
				$raw = $maybe;
				break;
			}
		}

		if ( is_array( $raw ) && ! empty( $raw['type'] ) && is_string( $raw['type'] ) ) {
			return self::message( self::from_gateway( $raw['type'] ) );
		}

		// Shape: validation_errors list
		if ( isset( $raw[0] ) && is_array( $raw[0] ) && isset( $raw[0]['param'] ) ) {
			$code = self::code_from_validation_params( $raw );
			return self::message( $code );
		}

		if ( ! empty( $raw['validation_errors'] ) && is_array( $raw['validation_errors'] ) ) {
			return self::format_gateway_failure( $raw['validation_errors'] );
		}

		return self::message( self::PROCESSING );
	}

	/**
	 * Maps Decidir validation param paths to shopper-safe error codes (no raw API paths).
	 *
	 * @param array $rows validation_errors rows
	 * @return string
	 */
	private static function code_from_validation_params( array $rows ) {
		$joined = '';
		foreach ( $rows as $row ) {
			if ( ! empty( $row['param'] ) ) {
				$joined .= ' ' . strtolower( (string) $row['param'] );
			}
		}

		if ( preg_match( '/fraud_detection.*(street|bill_to|ship_to|customer_in_site|postal|city|state|country)/', $joined ) ) {
			return self::CS_ADDRESS;
		}
		if ( false !== strpos( $joined, 'fraud_detection' ) ) {
			return self::CS_CUSTOMER_DATA;
		}

		return self::PROCESSING;
	}
}
