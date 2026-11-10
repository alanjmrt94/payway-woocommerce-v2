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

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * WooCommerce Blocks payment method type for Payway.
 */
final class WC_Payway_Blocks_Payment_Method extends AbstractPaymentMethodType {

	/**
	 * Must match WC_Payment_Gateway_Payway::$id.
	 *
	 * @var string
	 */
	protected $name = 'payway_gateway';

	/**
	 * @var WC_Payment_Gateway_Payway|null
	 */
	private $gateway = null;

	/**
	 * {@inheritdoc}
	 */
	public function initialize() {
		$options        = function_exists( 'wc_payway_get_config_options' )
			? wc_payway_get_config_options()
			: get_option( 'woocommerce_payway_gateway_settings', array() );
		$this->settings = is_array( $options ) ? $options : array();
		$gateways       = WC()->payment_gateways()->payment_gateways();
		if ( isset( $gateways[ $this->name ] ) && $gateways[ $this->name ] instanceof WC_Payment_Gateway_Payway ) {
			$this->gateway = $gateways[ $this->name ];
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_active() {
		if ( ! $this->gateway ) {
			return false;
		}
		return $this->gateway->is_available();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_payment_method_script_handles() {
		$version = function_exists( 'wc_payway_asset_version' ) ? wc_payway_asset_version() : '0.0.0';

		wp_register_script(
			'woocommerce-gateway-payway-sdk',
			'https://live.decidir.com/static/v2.5/decidir.js',
			array(),
			'2.5',
			true
		);

		wp_register_script(
			'woocommerce-gateway-payway-checkout-core',
			plugins_url( 'assets/js/frontend/payway-checkout-core.js', WC_PAYWAY_PLUGIN_FILE ),
			array( 'jquery', 'woocommerce-gateway-payway-sdk' ),
			$version,
			true
		);

		wp_register_script(
			'wc-payway-blocks-payment-method',
			plugins_url( 'assets/js/blocks/payway-blocks-payment-method.js', WC_PAYWAY_PLUGIN_FILE ),
			array(
				'wc-blocks-registry',
				'wc-settings',
				'wp-element',
				'wp-html-entities',
				'wp-i18n',
				'jquery',
				'woocommerce-gateway-payway-checkout-core',
				'woocommerce-gateway-payway-sdk',
			),
			$version,
			true
		);

		wp_enqueue_style(
			'woocommerce-gateway-payway-styles',
			plugins_url( 'assets/css/style.css', WC_PAYWAY_PLUGIN_FILE ),
			array(),
			$version
		);

		return array( 'wc-payway-blocks-payment-method' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_payment_method_data() {
		$options = function_exists( 'wc_payway_storefront_options' )
			? wc_payway_storefront_options()
			: array();

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$cart_total = 0;
		if ( function_exists( 'WC' ) && WC()->cart ) {
			$cart_total = (float) WC()->cart->get_total( 'edit' );
		}

		$options['cart_total'] = $cart_total;
		$options['title']      = $this->gateway ? $this->gateway->get_title() : ( $this->get_setting( 'title' ) );
		$options['description'] = $this->gateway ? $this->gateway->get_description() : ( $this->get_setting( 'description' ) );
		$options['supports']    = $this->get_supported_features();
		$options['i18n']        = isset( $options['i18n'] ) && is_array( $options['i18n'] )
			? $options['i18n']
			: array( 'please_select' => __( 'Please select...', 'wc-gateway-payway' ) );

		$options['accounting'] = array(
			'precision' => function_exists( 'wc_get_price_decimals' ) ? wc_get_price_decimals() : 2,
			'symbol'    => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$',
			'decimal'   => function_exists( 'wc_get_price_decimal_separator' ) ? wc_get_price_decimal_separator() : '.',
			'thousand'  => function_exists( 'wc_get_price_thousand_separator' ) ? wc_get_price_thousand_separator() : ',',
			'format'    => function_exists( 'get_woocommerce_price_format' )
				? str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() )
				: '%s%v',
		);

		$cs = isset( $options['cybersource_enabled'] ) ? $options['cybersource_enabled'] : false;
		$options['disableCybersource'] = ! ( true === $cs || 'yes' === $cs || 1 === $cs || '1' === $cs );

		return $options;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_supported_features() {
		if ( $this->gateway && is_array( $this->gateway->supports ) ) {
			return $this->gateway->supports;
		}
		return array( 'products' );
	}
}
