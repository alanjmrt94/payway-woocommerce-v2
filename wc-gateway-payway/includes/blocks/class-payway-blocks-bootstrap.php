<?php
/**
 * Payway Payment Community — gateway de pago para WooCommerce (fork independiente).
 * Copyright (C) 2026 Alan Martinez
 *
 * @package wc-gateway-payway
 * @license GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers Blocks payment method when the WC Blocks API is present.
 */
final class WC_Payway_Blocks_Bootstrap {

	/**
	 * @return void
	 */
	public static function init() {
		add_action( 'woocommerce_blocks_loaded', array( __CLASS__, 'on_blocks_loaded' ) );
	}

	/**
	 * @return void
	 */
	public static function on_blocks_loaded() {
		if ( ! class_exists( '\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			return;
		}

		require_once WC_PAYWAY_ABSPATH . 'includes/blocks/class-payway-blocks-payment-method.php';

		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			static function ( $payment_method_registry ) {
				if ( ! $payment_method_registry || ! method_exists( $payment_method_registry, 'register' ) ) {
					return;
				}
				$payment_method_registry->register( new WC_Payway_Blocks_Payment_Method() );
			}
		);
	}
}
