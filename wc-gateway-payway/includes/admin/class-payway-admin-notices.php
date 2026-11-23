<?php
/**
 * Admin notices for Payway configuration gaps.
 *
 * @package wc-gateway-payway
 * @license GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

/**
 * Surfaces credential and catalog warnings on Payway and WC Payments screens.
 */
class WC_Payway_Admin_Notices {

	/**
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'render_admin_notices' ) );
	}

	/**
	 * @return bool
	 */
	public static function is_payway_admin_context() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		if ( false !== strpos( $screen->id, 'payway' ) ) {
			return true;
		}

		if ( 'dashboard' === $screen->id ) {
			return true;
		}

		if ( 'woocommerce_page_wc-settings' === $screen->id ) {
			$tab     = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
			$section = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : '';
			return 'checkout' === $tab && WC_Payway_Config_Interface::PAYMENT_CODE === $section;
		}

		return false;
	}

	/**
	 * @return array<int,array{type:string,message:string}>
	 */
	public static function collect_notices() {
		$notices = array();

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return $notices;
		}

		$payments_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . WC_Payway_Config_Interface::PAYMENT_CODE );
		$setup_url    = admin_url( 'admin.php?page=payway_admin_setup' );

		$sandbox_active = function_exists( 'wc_payway_is_sandbox_enabled' ) && wc_payway_is_sandbox_enabled();
		$enabled        = 'yes' === wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_ENABLED );

		if ( $sandbox_active ) {
			$has_keys = (bool) (
				wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_SANDBOX_PUBLIC_KEY )
				&& wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_SANDBOX_PRIVATE_KEY )
			);
			$creds_label = __( 'sandbox', 'wc-gateway-payway' );
		} else {
			$has_keys = (bool) (
				wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_PRODUCTION_PUBLIC_KEY )
				&& wc_payway_get_config_option( WC_Payway_Config_Interface::FIELD_PRODUCTION_PRIVATE_KEY )
			);
			$creds_label = __( 'production', 'wc-gateway-payway' );
		}

		if ( $enabled && ! $has_keys ) {
			$notices[] = array(
				'type'    => 'error',
				/* translators: 1: environment name 2: payments settings URL 3: setup URL */
				'message' => sprintf(
					wp_kses(
						__( 'Payway is enabled but %1$s API keys are missing. The payment method will not work until you configure them under <a href="%2$s">WooCommerce → Payments → Payway</a> or follow <a href="%3$s">Setup</a>.', 'wc-gateway-payway' ),
						array( 'a' => array( 'href' => array() ) )
					),
					esc_html( $creds_label ),
					esc_url( $payments_url ),
					esc_url( $setup_url )
				),
			);
		} elseif ( ! $has_keys ) {
			$notices[] = array(
				'type'    => 'warning',
				/* translators: 1: environment name 2: payments settings URL 3: setup URL */
				'message' => sprintf(
					wp_kses(
						__( 'Payway: %1$s API keys are missing for the active environment. Configure them under <a href="%2$s">WooCommerce → Payments → Payway</a> or follow <a href="%3$s">Setup</a>.', 'wc-gateway-payway' ),
						array( 'a' => array( 'href' => array() ) )
					),
					esc_html( $creds_label ),
					esc_url( $payments_url ),
					esc_url( $setup_url )
				),
			);
		}

		if ( $enabled && $has_keys ) {
			$storefront = function_exists( 'wc_payway_storefront_options' ) ? wc_payway_storefront_options() : array();
			$promotions = ! empty( $storefront['promotions'] );
			if ( ! $promotions ) {
				$notices[] = array(
					'type'    => 'error',
					/* translators: %s: Setup admin URL */
					'message' => sprintf(
						wp_kses(
							__( 'Payway is enabled and credentials are set, but no applicable promotions were found. The payment method stays hidden at checkout until you seed the catalog under <a href="%s">Setup</a>.', 'wc-gateway-payway' ),
							array( 'a' => array( 'href' => array() ) )
						),
						esc_url( $setup_url )
					),
				);
			}
		}

		if ( $enabled && class_exists( 'WC_Payway_Setup_Status' ) ) {
			foreach ( WC_Payway_Setup_Status::get_checks() as $check ) {
				if ( empty( $check['blocking'] ) || ! empty( $check['ok'] ) ) {
					continue;
				}
				$action = ! empty( $check['action_url'] )
					? ' <a href="' . esc_url( $check['action_url'] ) . '">' . esc_html( $check['action_label'] ?? __( 'Fix', 'wc-gateway-payway' ) ) . '</a>'
					: '';
				$notices[] = array(
					'type'    => 'error',
					'message' => wp_kses(
						esc_html( $check['label'] . ': ' . $check['message'] ) . $action,
						array( 'a' => array( 'href' => array() ) )
					),
				);
			}
		}

		return $notices;
	}

	/**
	 * @return void
	 */
	public static function render_admin_notices() {
		if ( ! self::is_payway_admin_context() ) {
			return;
		}

		foreach ( self::collect_notices() as $notice ) {
			printf(
				'<div class="notice notice-%1$s"><p>%2$s</p></div>',
				esc_attr( $notice['type'] ),
				wp_kses_post( $notice['message'] )
			);
		}
	}

	/**
	 * Prints the same notices inside the gateway settings panel (no screen guard).
	 *
	 * @return void
	 */
	public static function render_inline_for_gateway() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		foreach ( self::collect_notices() as $notice ) {
			printf(
				'<div class="notice notice-%1$s inline"><p>%2$s</p></div>',
				esc_attr( $notice['type'] ),
				wp_kses_post( $notice['message'] )
			);
		}
	}
}
