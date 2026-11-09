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

defined( 'ABSPATH' ) || exit;

/**
 * Checklist compartido: Instalación + Status (alineado al manual operativo).
 */
class WC_Payway_Setup_Status {

	/**
	 * @return array<int,array{id:string,ok:bool,label:string,message:string,action_url:string,action_label:string,blocking:bool,group:string,proposed?:string}>
	 */
	public static function get_checks() {
		$payments_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=payway_gateway' );
		$currency_url = admin_url( 'admin.php?page=wc-settings&tab=general' );
		$setup_url    = admin_url( 'admin.php?page=payway_admin_setup' );
		$banks_url    = admin_url( 'admin.php?page=payway_admin_banks' );
		$cards_url    = admin_url( 'admin.php?page=payway_admin_cards' );
		$promos_url   = admin_url( 'admin.php?page=payway_admin_promotions' );
		$pages_url    = admin_url( 'admin.php?page=wc-settings&tab=advanced' );
		$plugins_url  = admin_url( 'plugins.php' );

		$wc_active = class_exists( 'WooCommerce' );

		$enabled = 'yes' === wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_ENABLED );

		$sandbox = function_exists( 'wc_payway_is_sandbox_enabled' ) && wc_payway_is_sandbox_enabled();
		if ( $sandbox ) {
			$has_creds = (bool) (
				wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_SANDBOX_SITE_ID )
				&& wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_SANDBOX_PUBLIC_KEY )
				&& wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_SANDBOX_PRIVATE_KEY )
			);
			$creds_label = __( 'Sandbox credentials', 'wc-gateway-payway' );
		} else {
			$has_creds = (bool) (
				wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_PRODUCTION_SITE_ID )
				&& wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_PRODUCTION_PUBLIC_KEY )
				&& wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_PRODUCTION_PRIVATE_KEY )
			);
			$creds_label = __( 'Production credentials', 'wc-gateway-payway' );
		}

		$title       = (string) wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_GATEWAY_TITLE );
		$description = (string) wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_GATEWAY_DESCRIPTION );
		$cs_enabled  = 'yes' === wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_CYBERSOURCE_ENABLED );
		$debug_on    = function_exists( 'wc_payway_is_debug_enabled' ) && wc_payway_is_debug_enabled();

		$currency           = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '';
		$currency_supported = in_array( $currency, array( 'ARS', 'USD' ), true );
		$decimals           = (int) get_option( 'woocommerce_price_num_decimals', 2 );
		$decimals_ok        = 2 === $decimals;

		$banks_count = (int) WC_Payway_Bank_Factory::record_count();
		$cards_count = (int) WC_Payway_Card_Factory::record_count();

		$promotions_data = function_exists( 'wc_payway_checkout_promotions' )
			? wc_payway_checkout_promotions()
			: array();
		$has_applicable  = ! empty( $promotions_data['plans'] );

		$promo_with_plans = self::has_promotion_with_fee_plans();
		$blocks_checkout  = self::is_checkout_using_blocks();
		$blocks_supported = class_exists( 'WC_Payway_Checkout_Request' )
			? WC_Payway_Checkout_Request::blocks_integration_available()
			: class_exists( '\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' );
		$checkout_ok      = ! $blocks_checkout || $blocks_supported;
		$schema_ok        = self::promotions_schema_ready();
		$billing_ui_ok    = self::checkout_has_billing_address_ui();
		$shipping_ui_ok   = self::checkout_has_shipping_address_ui();
		$cards_without_promo = self::cards_missing_applicable_promotion();
		$cards_promo_ok      = empty( $cards_without_promo );

		$default_title = __( 'Checkout Payway', 'wc-gateway-payway' );
		$default_desc  = __( 'Pay with Payway using your card right from the store.', 'wc-gateway-payway' );

		return array(
			array(
				'id'           => 'woocommerce',
				'ok'           => $wc_active,
				'label'        => __( 'WooCommerce active', 'wc-gateway-payway' ),
				'message'      => $wc_active
					? __( 'WooCommerce is installed and active.', 'wc-gateway-payway' )
					: __( 'WooCommerce is required. Install and activate it.', 'wc-gateway-payway' ),
				'action_url'   => $plugins_url,
				'action_label' => __( 'View plugins', 'wc-gateway-payway' ),
				'blocking'     => true,
				'group'        => 'blocking',
			),
			array(
				'id'           => 'gateway_enabled',
				'ok'           => $enabled,
				'label'        => __( 'Payment method enabled', 'wc-gateway-payway' ),
				'message'      => $enabled
					? __( 'Payway is enabled under WooCommerce → Payments.', 'wc-gateway-payway' )
					: __( 'Status → Enabled is missing. The manual requires it for the method to appear.', 'wc-gateway-payway' ),
				'action_url'   => $payments_url,
				'action_label' => __( 'Configure in WooCommerce', 'wc-gateway-payway' ),
				'blocking'     => true,
				'group'        => 'blocking',
				'proposed'     => __( 'Suggested: Enabled = yes (use “Apply recommended settings”).', 'wc-gateway-payway' ),
			),
			array(
				'id'           => 'credentials',
				'ok'           => $has_creds,
				'label'        => $creds_label,
				'message'      => $has_creds
					? __( 'Site Id, Public Key and Private Key for the active environment are set.', 'wc-gateway-payway' )
					: __( 'Site Id / Public / Private for the active environment are missing. This plugin does not invent keys.', 'wc-gateway-payway' ),
				'action_url'   => $payments_url,
				'action_label' => __( 'Enter keys', 'wc-gateway-payway' ),
				'blocking'     => true,
				'group'        => 'blocking',
				'proposed'     => __( 'Request them from your account manager / portal. For CI tests you can use ci/.env.apikeys.', 'wc-gateway-payway' ),
			),
			array(
				'id'           => 'currency',
				'ok'           => $currency_supported,
				'label'        => __( 'Store currency', 'wc-gateway-payway' ),
				'message'      => $currency_supported
					? sprintf(
						/* translators: %s: currency code */
						__( 'Current currency: %s.', 'wc-gateway-payway' ),
						$currency
					)
					: sprintf(
						/* translators: %s: currency code */
						__( 'Currency %s is not supported. Use ARS or USD.', 'wc-gateway-payway' ),
						$currency ? $currency : '—'
					),
				'action_url'   => $currency_url,
				'action_label' => __( 'General settings', 'wc-gateway-payway' ),
				'blocking'     => true,
				'group'        => 'blocking',
				'proposed'     => __( 'Suggested: ARS (or USD if you sell in dollars).', 'wc-gateway-payway' ),
			),
			array(
				'id'           => 'decimals',
				'ok'           => $decimals_ok,
				'label'        => __( 'Currency decimals = 2', 'wc-gateway-payway' ),
				'message'      => $decimals_ok
					? __( 'Number of decimals = 2 (required by the manual).', 'wc-gateway-payway' )
					: sprintf(
						/* translators: %d: decimals */
						__( 'Current decimals: %d. The manual requires exactly 2.', 'wc-gateway-payway' ),
						$decimals
					),
				'action_url'   => $currency_url,
				'action_label' => __( 'Currency options', 'wc-gateway-payway' ),
				'blocking'     => true,
				'group'        => 'blocking',
				'proposed'     => __( 'Suggested: woocommerce_price_num_decimals = 2.', 'wc-gateway-payway' ),
			),
			array(
				'id'           => 'banks',
				'ok'           => $banks_count > 0,
				'label'        => __( 'Banks', 'wc-gateway-payway' ),
				'message'      => $banks_count > 0
					? sprintf(
						/* translators: %d: count */
						__( 'There are %d configured bank(s).', 'wc-gateway-payway' ),
						$banks_count
					)
					: __( 'At least one bank is required (e.g. “Todos”).', 'wc-gateway-payway' ),
				'action_url'   => $banks_count > 0 ? $banks_url : $setup_url,
				'action_label' => $banks_count > 0
					? __( 'View banks', 'wc-gateway-payway' )
					: __( 'Go to Setup', 'wc-gateway-payway' ),
				'blocking'     => true,
				'group'        => 'blocking',
				'proposed'     => __( 'Suggested: bank “Todos”.', 'wc-gateway-payway' ),
			),
			array(
				'id'           => 'cards',
				'ok'           => $cards_count > 0,
				'label'        => __( 'Cards', 'wc-gateway-payway' ),
				'message'      => $cards_count > 0
					? sprintf(
						/* translators: %d: count */
						__( 'There are %d configured card(s).', 'wc-gateway-payway' ),
						$cards_count
					)
					: __( 'At least one card with a valid id_sps is required.', 'wc-gateway-payway' ),
				'action_url'   => $cards_count > 0 ? $cards_url : $setup_url,
				'action_label' => $cards_count > 0
					? __( 'View cards', 'wc-gateway-payway' )
					: __( 'Go to Setup', 'wc-gateway-payway' ),
				'blocking'     => true,
				'group'        => 'blocking',
				'proposed'     => __( 'Suggested: Decidir catalog (Visa 1, etc.) from Setup.', 'wc-gateway-payway' ),
			),
			array(
				'id'           => 'promo_plans',
				'ok'           => $promo_with_plans,
				'label'        => __( 'Promotion with installment plans', 'wc-gateway-payway' ),
				'message'      => $promo_with_plans
					? __( 'There is at least one promotion with fee_plans loaded.', 'wc-gateway-payway' )
					: __( 'Promotions must include plans (period / coefficient / value to send). Debit = 1 installment.', 'wc-gateway-payway' ),
				'action_url'   => $promo_with_plans ? $promos_url : $setup_url,
				'action_label' => $promo_with_plans
					? __( 'View promotions', 'wc-gateway-payway' )
					: __( 'Create defaults', 'wc-gateway-payway' ),
				'blocking'     => true,
				'group'        => 'blocking',
				'proposed'     => __( 'Suggested: credit 1/3/6/9/12/18; debit/prepaid 1 installment; no expiry. Optional MiPyME: 3→13 / 6→16 when enabled in Mi Payway and eligible MCC.', 'wc-gateway-payway' ),
			),
			array(
				'id'           => 'promotions',
				'ok'           => $has_applicable,
				'label'        => __( 'Promotion applicable today', 'wc-gateway-payway' ),
				'message'      => $has_applicable
					? __( 'There is at least one active promotion applicable for checkout.', 'wc-gateway-payway' )
					: __( 'Without an applicable promotion (active, days, no expiry or valid dates) the method does not appear.', 'wc-gateway-payway' ),
				'action_url'   => $has_applicable ? $promos_url : $setup_url,
				'action_label' => $has_applicable
					? __( 'View promotions', 'wc-gateway-payway' )
					: __( 'Create defaults', 'wc-gateway-payway' ),
				'blocking'     => true,
				'group'        => 'blocking',
				'proposed'     => __( 'Suggested: “No expiry” + every day of the week.', 'wc-gateway-payway' ),
			),
			array(
				'id'           => 'cards_without_promo',
				'ok'           => $cards_promo_ok,
				'label'        => __( 'Cards covered by an applicable promotion', 'wc-gateway-payway' ),
				'message'      => $cards_promo_ok
					? (
						$cards_count > 0
							? __( 'Every configured card has an active promotion with installment plans applicable today.', 'wc-gateway-payway' )
							: __( 'No cards configured yet.', 'wc-gateway-payway' )
					)
					: sprintf(
						/* translators: %s: comma-separated card names */
						__( 'These cards will not appear at checkout because they have no applicable promotion (or no fee plans): %s', 'wc-gateway-payway' ),
						implode( ', ', $cards_without_promo )
					),
				'action_url'   => $setup_url,
				'action_label' => __( 'Create promotions', 'wc-gateway-payway' ),
				'blocking'     => false,
				'group'        => 'recommended',
				'proposed'     => __( 'Use Setup → Create promotions for selected cards, or add a promotion under Payway → Promotions.', 'wc-gateway-payway' ),
				'symptom'      => $cards_promo_ok
					? ''
					: __( 'Some cards never show in the bank/card dropdown even though they exist under Payway → Cards.', 'wc-gateway-payway' ),
			),
			array(
				'id'           => 'checkout_surface',
				'ok'           => $checkout_ok,
				'label'        => __( 'Checkout surface (classic or Blocks)', 'wc-gateway-payway' ),
				'message'      => self::checkout_surface_message( $blocks_checkout, $blocks_supported ),
				'action_url'   => $pages_url,
				'action_label' => __( 'WooCommerce pages', 'wc-gateway-payway' ),
				'blocking'     => true,
				'group'        => 'blocking',
				'proposed'     => $checkout_ok
					? __( 'Classic shortcode and Blocks checkout are both supported when the Blocks payment API is present.', 'wc-gateway-payway' )
					: __( 'This WooCommerce version has no Blocks payment API. Use the shortcode [woocommerce_checkout], or upgrade WooCommerce.', 'wc-gateway-payway' ),
				'symptom'      => $checkout_ok
					? ''
					: __( 'There are no payment methods available. This may be an error on our side…', 'wc-gateway-payway' ),
			),
			array(
				'id'           => 'schema',
				'ok'           => $schema_ok,
				'label'        => __( 'Core schema (tables + no_expiry)', 'wc-gateway-payway' ),
				'message'      => $schema_ok
					? __( 'Banks/cards/promos tables and no_expiry column are OK (self-heal enabled).', 'wc-gateway-payway' )
					: __( 'Incomplete schema. Auto-heal runs on each request; reload Setup.', 'wc-gateway-payway' ),
				'action_url'   => $setup_url,
				'action_label' => __( 'Retry', 'wc-gateway-payway' ),
				'blocking'     => true,
				'group'        => 'blocking',
			),
			array(
				'id'           => 'title',
				'ok'           => '' !== trim( $title ),
				'label'        => __( 'Checkout title', 'wc-gateway-payway' ),
				'message'      => '' !== trim( $title )
					? sprintf(
						/* translators: %s: title */
						__( 'Title: %s', 'wc-gateway-payway' ),
						$title
					)
					: __( 'Checkout title is missing.', 'wc-gateway-payway' ),
				'action_url'   => $payments_url,
				'action_label' => __( 'Edit title', 'wc-gateway-payway' ),
				'blocking'     => false,
				'group'        => 'recommended',
				'proposed'     => sprintf(
					/* translators: %s: default title */
					__( 'Suggested: “%s”.', 'wc-gateway-payway' ),
					$default_title
				),
			),
			array(
				'id'           => 'description',
				'ok'           => '' !== trim( $description ),
				'label'        => __( 'Checkout description', 'wc-gateway-payway' ),
				'message'      => '' !== trim( $description )
					? __( 'Description is set.', 'wc-gateway-payway' )
					: __( 'No description (recommended).', 'wc-gateway-payway' ),
				'action_url'   => $payments_url,
				'action_label' => __( 'Edit description', 'wc-gateway-payway' ),
				'blocking'     => false,
				'group'        => 'recommended',
				'proposed'     => sprintf(
					/* translators: %s: default description */
					__( 'Suggested: “%s”.', 'wc-gateway-payway' ),
					$default_desc
				),
			),
			array(
				'id'           => 'sandbox_mode',
				'ok'           => true,
				'label'        => __( 'Sandbox mode', 'wc-gateway-payway' ),
				'message'      => $sandbox
					? __( 'Sandbox is on (test environment).', 'wc-gateway-payway' )
					: __( 'Sandbox is off: production keys are used.', 'wc-gateway-payway' ),
				'action_url'   => $payments_url,
				'action_label' => __( 'Change environment', 'wc-gateway-payway' ),
				'blocking'     => false,
				'group'        => 'recommended',
				'proposed'     => __( 'Suggested for testing: Sandbox = yes. For live production: disable Sandbox and load production keys.', 'wc-gateway-payway' ),
			),
			array(
				'id'           => 'cybersource',
				'ok'           => $cs_enabled,
				'label'        => __( 'Cybersource', 'wc-gateway-payway' ),
				'message'      => $cs_enabled
					? __( 'Cybersource enabled (manual / merchant onboarding default).', 'wc-gateway-payway' )
					: __( 'Cybersource disabled. The manual enables it by default; turn it off only if the merchant does not use it.', 'wc-gateway-payway' ),
				'action_url'   => $payments_url,
				'action_label' => __( 'Configure CS', 'wc-gateway-payway' ),
				'blocking'     => false,
				'group'        => 'recommended',
				'proposed'     => __( 'Suggested: Cybersource = yes.', 'wc-gateway-payway' ),
			),
			array(
				'id'           => 'cs_billing_address',
				'ok'           => ! $cs_enabled || $billing_ui_ok,
				'label'        => __( 'Cybersource: billing address on checkout', 'wc-gateway-payway' ),
				'message'      => ! $cs_enabled
					? __( 'Skipped (Cybersource is off).', 'wc-gateway-payway' )
					: (
						$billing_ui_ok
							? __( 'Billing address step/fields are present on checkout.', 'wc-gateway-payway' )
							: __( 'Cybersource requires a visible billing address step on checkout.', 'wc-gateway-payway' )
					),
				'action_url'   => $pages_url,
				'action_label' => __( 'WooCommerce pages', 'wc-gateway-payway' ),
				'blocking'     => $cs_enabled,
				'group'        => $cs_enabled ? 'blocking' : 'recommended',
				'proposed'     => __( 'Blocks: keep the Billing address block. Classic: do not remove billing checkout fields.', 'wc-gateway-payway' ),
				'symptom'      => $cs_enabled && ! $billing_ui_ok
					? __( 'Payment may fail fraud-prevention address validation.', 'wc-gateway-payway' )
					: '',
			),
			array(
				'id'           => 'cs_shipping_address',
				'ok'           => ! $cs_enabled || $shipping_ui_ok,
				'label'        => __( 'Cybersource: shipping address on checkout', 'wc-gateway-payway' ),
				'message'      => ! $cs_enabled
					? __( 'Skipped (Cybersource is off).', 'wc-gateway-payway' )
					: (
						$shipping_ui_ok
							? __( 'Shipping address step/fields are present on checkout.', 'wc-gateway-payway' )
							: __( 'Cybersource requires a visible shipping address step on checkout.', 'wc-gateway-payway' )
					),
				'action_url'   => $pages_url,
				'action_label' => __( 'WooCommerce pages', 'wc-gateway-payway' ),
				'blocking'     => $cs_enabled,
				'group'        => $cs_enabled ? 'blocking' : 'recommended',
				'proposed'     => __( 'Blocks: keep the Shipping address block. Classic: avoid “Force shipping to customer billing address” / hiding shipping fields when Cybersource is on.', 'wc-gateway-payway' ),
				'symptom'      => $cs_enabled && ! $shipping_ui_ok
					? __( 'Payment may fail fraud-prevention address validation.', 'wc-gateway-payway' )
					: '',
			),
			array(
				'id'           => 'debug',
				'ok'           => $debug_on || ! $sandbox,
				'label'        => __( 'Debug Mode', 'wc-gateway-payway' ),
				'message'      => $debug_on
					? __( 'Debug is on (logs under WooCommerce → Status → Logs).', 'wc-gateway-payway' )
					: (
						$sandbox
							? __( 'Debug is off in Sandbox: enable it for troubleshooting.', 'wc-gateway-payway' )
							: __( 'Debug is off (acceptable in production if there are no incidents).', 'wc-gateway-payway' )
					),
				'action_url'   => $payments_url,
				'action_label' => __( 'Configure debug', 'wc-gateway-payway' ),
				'blocking'     => false,
				'group'        => 'recommended',
				'proposed'     => __( 'Suggested for testing: Debug = yes.', 'wc-gateway-payway' ),
			),
		);
	}

	/**
	 * Applies WooCommerce + gateway settings recommended by the manual.
	 * Never invents API keys.
	 *
	 * @return array{changed:string[],skipped:string[]}
	 */
	public static function apply_recommended_settings() {
		$changed = array();
		$skipped = array();

		$decimals = (int) get_option( 'woocommerce_price_num_decimals', 2 );
		if ( 2 !== $decimals ) {
			update_option( 'woocommerce_price_num_decimals', '2' );
			$changed[] = 'woocommerce_price_num_decimals=2';
		} else {
			$skipped[] = 'decimals';
		}

		$opts = wc_payway_get_config_options();
		if ( ! is_array( $opts ) ) {
			$opts = array();
		}

		$defaults = array(
			WC_Payway_Config_Interface::FIELD_ENABLED             => 'yes',
			WC_Payway_Config_Interface::FIELD_SANDBOX_MODE         => 'yes',
			WC_Payway_Config_Interface::FIELD_CYBERSOURCE_ENABLED  => 'yes',
			WC_Payway_Config_Interface::FIELD_DEBUG_ENABLED        => 'yes',
			WC_Payway_Config_Interface::FIELD_GATEWAY_TITLE        => __( 'Checkout Payway', 'wc-gateway-payway' ),
			WC_Payway_Config_Interface::FIELD_GATEWAY_DESCRIPTION  => __( 'Pay with Payway using your card right from the store.', 'wc-gateway-payway' ),
		);

		foreach ( $defaults as $key => $value ) {
			$current = isset( $opts[ $key ] ) ? $opts[ $key ] : null;
			$empty   = ( null === $current || '' === $current );
			$force   = in_array(
				$key,
				array(
					WC_Payway_Config_Interface::FIELD_ENABLED,
					WC_Payway_Config_Interface::FIELD_SANDBOX_MODE,
					WC_Payway_Config_Interface::FIELD_CYBERSOURCE_ENABLED,
					WC_Payway_Config_Interface::FIELD_DEBUG_ENABLED,
				),
				true
			);

			if ( $force && $current !== $value ) {
				$opts[ $key ] = $value;
				$changed[]    = $key . '=' . $value;
			} elseif ( $empty ) {
				$opts[ $key ] = $value;
				$changed[]    = $key . '=' . $value;
			} else {
				$skipped[] = $key;
			}
		}

		update_option( 'woocommerce_payway_gateway_settings', $opts );

		return array(
			'changed' => $changed,
			'skipped' => $skipped,
		);
	}

	/**
	 * @return array<int,array{id:string,ok:bool,label:string,message:string,action_url:string,action_label:string,blocking:bool,group:string,proposed?:string}>
	 */
	public static function get_blocking_failures() {
		$failures = array();
		foreach ( self::get_checks() as $check ) {
			if ( ! empty( $check['blocking'] ) && empty( $check['ok'] ) ) {
				$failures[] = $check;
			}
		}
		return $failures;
	}

	/**
	 * @return bool
	 */
	public static function is_ready_for_checkout() {
		return empty( self::get_blocking_failures() );
	}

	/**
	 * Whether checkout exposes a billing address step/fields (required by Cybersource).
	 *
	 * @return bool
	 */
	private static function checkout_has_billing_address_ui() {
		if ( self::is_checkout_using_blocks() ) {
			return self::blocks_checkout_has_address_step( 'woocommerce/checkout-billing-address-block' );
		}

		return self::classic_checkout_has_address_fields( 'billing' );
	}

	/**
	 * Whether checkout exposes a shipping address step/fields (required by Cybersource).
	 *
	 * @return bool
	 */
	private static function checkout_has_shipping_address_ui() {
		if ( self::is_checkout_using_blocks() ) {
			return self::blocks_checkout_has_address_step( 'woocommerce/checkout-shipping-address-block' );
		}

		// Classic: “Force shipping to the customer billing address” hides the shipping form.
		if ( 'billing_only' === get_option( 'woocommerce_ship_to_destination' ) ) {
			return false;
		}

		return self::classic_checkout_has_address_fields( 'shipping' );
	}

	/**
	 * Blocks: default Checkout template includes both address steps; customized trees must keep them.
	 *
	 * @param string $block_name e.g. woocommerce/checkout-billing-address-block
	 * @return bool
	 */
	private static function blocks_checkout_has_address_step( $block_name ) {
		$content = self::get_checkout_page_content();
		if ( '' === $content || false === strpos( $content, 'woocommerce/checkout' ) ) {
			return false;
		}

		$customized = (bool) preg_match(
			'/woocommerce\/checkout-(billing-address|shipping-address|contact-information|payment|shipping-method|order-summary)/',
			$content
		);

		if ( ! $customized ) {
			// Bare Checkout block → WooCommerce default inner template (includes billing + shipping).
			return true;
		}

		return false !== strpos( $content, $block_name );
	}

	/**
	 * Classic checkout fields presence (best-effort in admin).
	 *
	 * @param string $fieldset billing|shipping
	 * @return bool
	 */
	private static function classic_checkout_has_address_fields( $fieldset ) {
		$key = ( 'shipping' === $fieldset ) ? 'shipping_address_1' : 'billing_address_1';

		$checkout = ( function_exists( 'WC' ) && WC() ) ? WC()->checkout() : null;
		if ( $checkout && is_callable( array( $checkout, 'get_checkout_fields' ) ) ) {
			$fields = $checkout->get_checkout_fields( $fieldset );
			if ( is_array( $fields ) ) {
				return ! empty( $fields[ $key ] );
			}
		}

		// Without a live checkout instance, assume classic templates still show the fields.
		return true;
	}

	/**
	 * @return string
	 */
	private static function get_checkout_page_content() {
		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return '';
		}
		$page_id = wc_get_page_id( 'checkout' );
		if ( $page_id <= 0 ) {
			return '';
		}
		return (string) get_post_field( 'post_content', $page_id );
	}

	/**
	 * @param bool $blocks_checkout
	 * @param bool $blocks_supported
	 * @return string
	 */
	private static function checkout_surface_message( $blocks_checkout, $blocks_supported ) {
		if ( $blocks_checkout && $blocks_supported ) {
			return __( 'Checkout uses WooCommerce Blocks and Payway Blocks integration is available.', 'wc-gateway-payway' );
		}
		if ( $blocks_checkout && ! $blocks_supported ) {
			return __( 'Checkout uses WooCommerce Blocks, but this WooCommerce build has no payment-method Blocks API. Shoppers may see “There are no payment methods available…”.', 'wc-gateway-payway' );
		}
		if ( ! $blocks_checkout && $blocks_supported ) {
			return __( 'Classic checkout detected. Blocks integration is also available if you switch the page to the Checkout block.', 'wc-gateway-payway' );
		}
		return __( 'Classic checkout detected (shortcode or equivalent).', 'wc-gateway-payway' );
	}

	/**
	 * @return bool
	 */
	public static function is_checkout_using_blocks() {
		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return false;
		}

		$page_id = wc_get_page_id( 'checkout' );
		if ( $page_id <= 0 ) {
			return false;
		}

		$content = (string) get_post_field( 'post_content', $page_id );
		if ( $content && false !== strpos( $content, 'woocommerce/checkout' ) ) {
			return true;
		}

		if ( function_exists( 'has_block' ) && has_block( 'woocommerce/checkout', $page_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	private static function has_promotion_with_fee_plans() {
		$promos = WC_Payway_Promotion_Factory::get_promotions();
		if ( empty( $promos ) ) {
			return false;
		}
		foreach ( $promos as $promo ) {
			if ( ! empty( $promo->fee_plans ) && '[]' !== $promo->fee_plans ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Configured cards that will not appear at checkout (no applicable promo with fee_plans today).
	 *
	 * @return string[] Labels like "Visa Crédito (SPS 1)"
	 */
	private static function cards_missing_applicable_promotion() {
		$cards = WC_Payway_Card_Factory::get_cards();
		if ( empty( $cards ) ) {
			return array();
		}

		$applicable  = WC_Payway_Promotion_Factory::get_applicable_promotions();
		$covered_ids = array();

		foreach ( (array) $applicable as $promo ) {
			if ( empty( $promo->fee_plans ) || '[]' === $promo->fee_plans ) {
				continue;
			}
			$covered_ids[ (int) $promo->card_id ] = true;
		}

		$missing = array();
		foreach ( $cards as $card ) {
			if ( ! empty( $covered_ids[ (int) $card->id ] ) ) {
				continue;
			}
			$missing[] = sprintf(
				'%s (SPS %s)',
				(string) $card->card_name,
				(string) $card->id_sps
			);
		}

		return $missing;
	}

	/**
	 * @return bool
	 */
	private static function promotions_schema_ready() {
		if ( ! class_exists( 'WC_Payway_Activator' ) ) {
			return false;
		}
		WC_Payway_Activator::ensure_core_schema();
		return WC_Payway_Activator::core_tables_exist()
			&& WC_Payway_Activator::promotions_has_no_expiry_column();
	}
}
