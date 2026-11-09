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
 * Core helpers — Payway Payment Community
 *
 * @package wc-gateway-payway
 */

defined( 'ABSPATH' ) || exit;

include_once WC_PAYWAY_ABSPATH . 'includes/interfaces/class-wc-payway-config-interface.php';
include_once WC_PAYWAY_ABSPATH . 'includes/interfaces/class-wc-payway-rest-interface.php';

/**
 * Returns the main instance of PaywayWC.
 *
 * @return PaywayWC
 */
function Payway() {
	return PaywayWC::instance();
}

/**
 * Retrieves the Payment code used by WooCommerce.
 *
 * @return string
 */
function wc_payway_get_payment_code() {
	return WC_Payway_Config_Interface::PAYMENT_CODE;
}

/**
 * @return WC_Payway_Logger
 */
function wc_payway_get_logger() {
	return WC_Payway_Logger::instance();
}

/**
 * Whether plugin is configured to work in test mode.
 *
 * @return bool
 */
function wc_payway_is_sandbox_enabled() {
	return 'yes' === wc_payway_get_config_option(
		WC_Payway_Config_Interface::FIELD_SANDBOX_MODE
	);
}

/**
 * Whether Cybersource is enabled.
 *
 * @return bool
 */
function wc_payway_config_is_cs_enabled() {
	return 'yes' === wc_payway_get_config_option(
		WC_Payway_Config_Interface::FIELD_CYBERSOURCE_ENABLED
	);
}

/**
 * Returns credentials for the specified environment.
 * Never include private_key unless explicitly requested (backend only).
 *
 * @param bool $sandbox Whether to return sandbox credentials.
 * @param bool $include_private_key Whether to include private key.
 * @return array
 */
function wc_payway_get_credentials( $sandbox = true, $include_private_key = false ) {
	$data    = array();
	$options = wc_payway_get_config_options();
	if ( ! is_array( $options ) ) {
		$options = array();
	}

	if ( $sandbox ) {
		$data['creds']['site_id']    = $options[ WC_Payway_Config_Interface::FIELD_SANDBOX_SITE_ID ] ?? '';
		$data['creds']['public_key'] = $options[ WC_Payway_Config_Interface::FIELD_SANDBOX_PUBLIC_KEY ] ?? '';

		if ( $include_private_key ) {
			$data['creds']['private_key'] = $options[ WC_Payway_Config_Interface::FIELD_SANDBOX_PRIVATE_KEY ] ?? '';
		}
	} else {
		$data['creds']['site_id']    = $options[ WC_Payway_Config_Interface::FIELD_PRODUCTION_SITE_ID ] ?? '';
		$data['creds']['public_key'] = $options[ WC_Payway_Config_Interface::FIELD_PRODUCTION_PUBLIC_KEY ] ?? '';

		if ( $include_private_key ) {
			$data['creds']['private_key'] = $options[ WC_Payway_Config_Interface::FIELD_PRODUCTION_PRIVATE_KEY ] ?? '';
		}
	}

	return $data;
}

/**
 * Sandbox credentials. Private key never included by default.
 *
 * @param bool $include_private_key whether to include private key.
 * @return array
 */
function wc_payway_get_sandbox_credentials( $include_private_key = false ) {
	return wc_payway_get_credentials( true, $include_private_key );
}

/**
 * Production credentials. Private key never included by default (SEC-01).
 *
 * @param bool $include_private_key whether to include private key.
 * @return array
 */
function wc_payway_get_production_credentials( $include_private_key = false ) {
	return wc_payway_get_credentials( false, $include_private_key );
}

/**
 * Builds checkout promotions payload for the storefront.
 *
 * @return array
 */
function wc_payway_checkout_promotions() {
	$promotions = WC_Payway_Promotion_Factory::get_applicable_promotions();
	$banks      = $cards = $plans = array();

	if ( empty( $promotions ) ) {
		return array();
	}

	foreach ( $promotions as $promo ) {
		$banks[ $promo->bank_id ]                          = array(
			'name'  => $promo->bank_name,
			'value' => $promo->bank_id,
		);
		$cards[ $promo->bank_id ][ $promo->card_code ]     = array(
			'name'  => $promo->card_name,
			'value' => $promo->card_code,
		);

		if ( $promo->fee_plans ) {
			$list = json_decode( $promo->fee_plans );

			foreach ( (array) $list as $plan ) {
				$plans[ $promo->bank_id ][ $promo->card_code ][] = array(
					'fee_period'  => $plan->fee_period,
					'fee_to_send' => $plan->fee_to_send,
					'coefficient' => $plan->coefficient,
					'tea'         => $plan->tea,
					'cft'         => $plan->cft,
					'rule_id'     => $promo->id,
					'rule_name'   => $promo->rule_name,
				);
			}
		}
	}

	return array(
		'banks' => $banks,
		'cards' => $cards,
		'plans' => $plans,
	);
}

/**
 * Storefront config for JS. Never exposes private_key.
 *
 * @param bool $encode_as_json
 * @return array|string
 */
function wc_payway_storefront_options( $encode_as_json = false ) {
	$config_options = wc_payway_get_config_options();
	if ( ! is_array( $config_options ) ) {
		$config_options = array();
	}

	if ( wc_payway_is_sandbox_enabled() ) {
		$options  = wc_payway_get_sandbox_credentials( false );
		$url      = WC_Payway_Rest_Interface::SANDBOX_URL;
		$sandbox  = true;
	} else {
		$options  = wc_payway_get_production_credentials( false );
		$url      = WC_Payway_Rest_Interface::PRODUCTION_URL;
		$sandbox  = false;
	}

	$options[ WC_Payway_Config_Interface::FIELD_CYBERSOURCE_ENABLED ] = (
		isset( $config_options[ WC_Payway_Config_Interface::FIELD_CYBERSOURCE_ENABLED ] )
		&& 'yes' === $config_options[ WC_Payway_Config_Interface::FIELD_CYBERSOURCE_ENABLED ]
	);

	$options['endpoint_url']     = $url;
	$options['sandbox_enabled']  = $sandbox;
	$options['promotions']       = wc_payway_checkout_promotions();
	$options['i18n']             = array(
		'please_select' => __( 'Please select...', 'wc-gateway-payway' ),
	);

	// Defensa en profundidad: nunca filtrar private_key al frontend.
	if ( isset( $options['creds']['private_key'] ) ) {
		unset( $options['creds']['private_key'] );
	}

	return ! $encode_as_json
		? $options
		: wp_json_encode( $options );
}

/**
 * Current day number 0 (Sunday) through 6 (Saturday).
 *
 * @return string
 */
function wc_payway_get_current_day_number() {
	return date( 'w' ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
}
