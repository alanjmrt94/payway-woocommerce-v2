<?php
/**
 * Admin: Recent payments (Decidir PaymentList).
 *
 * @package wc-gateway-payway
 * @license GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders Payway → Recent Payments.
 */
class WC_Payway_Admin_Navigation_Page_Payments {

	/**
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( WC_Payway_Admin_Sanitize::required_capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wc-gateway-payway' ) );
		}

		wp_enqueue_style(
			'wc-payway-admin',
			plugins_url( 'assets/css/style-admin.css', WC_PAYWAY_PLUGIN_FILE ),
			array(),
			function_exists( 'wc_payway_asset_version' ) ? wc_payway_asset_version() : '0.0.0'
		);

		$environment = isset( $_GET['payway_env'] ) ? sanitize_key( wp_unslash( $_GET['payway_env'] ) ) : '';
		if ( ! in_array( $environment, array( WC_Payway_Payment_List::ENV_SANDBOX, WC_Payway_Payment_List::ENV_PRODUCTION ), true ) ) {
			$environment = WC_Payway_Payment_List::default_environment();
		}

		$page = isset( $_GET['payway_page'] ) ? max( 0, (int) $_GET['payway_page'] ) : 0;
		$size = isset( $_GET['payway_size'] ) ? max( 1, min( WC_Payway_Payment_List::MAX_PAGE_SIZE, (int) $_GET['payway_size'] ) ) : WC_Payway_Payment_List::DEFAULT_SIZE;

		$list_result = array(
			'success'       => false,
			'results'       => array(),
			'has_more'      => false,
			'limit'         => $size,
			'offset'        => 0,
			'error_code'    => 0,
			'error_message' => '',
		);

		if ( WC_Payway_Payment_List::environment_has_credentials( $environment ) ) {
			$list_result = WC_Payway_Payment_List::fetch_payments( $environment, $page, $size );
		}

		$payments_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . WC_Payway_Config_Interface::PAYMENT_CODE );

		include WC_PAYWAY_ABSPATH . 'includes/admin/html-payway-admin-page-payments.php';
	}
}