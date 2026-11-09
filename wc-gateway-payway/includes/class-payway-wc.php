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

final class PaywayWC {
	/**
	 * The single instance of the class
	 *
	 * @var PaywayWC
	 */
	protected static $instance = null;

	/**
	 * @var Payway_WC_Promotion_Factory
	 */
	public $promotion = null;

	/**
	 * @var Payway_WC_Bank_Factory
	 */
	public $bank = null;

	/**
	 * @var Payway_WC_Card_Factory
	 */
	public $card = null;

	/**
	 *
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		if ( is_admin() ) {
			add_action('init', array($this, 'init_admin_menu'));
		}
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'wc-gateway-payway' ), '0.1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'wc-gateway-payway' ), '0.1.0' );
	}

	/**
	 * Main Payway Instance.
	 *
	 * @static
	 * @return PaywayWC - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	* Adds the Payment Gateway into WooCommerce gateways' pool
	* @see WC filter `woocommerce_payment_gateways`
	*/
	public function payway_add_gateway_class( $gateways ) {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		if ( ! class_exists( 'WC_Payment_Gateway_Payway' ) ) {
			require_once dirname( WC_PAYWAY_PLUGIN_FILE ) . '/wc-payment-gateway-payway.php';
		}

		$gateways[] = 'WC_Payment_Gateway_Payway';
		return $gateways;
	}

	/**
	 * Defines global constants used across the entire plugin
	 */
	private function define_constants() {
		$upload_dir = wp_upload_dir( null, false );

		$this->define( 'WC_PAYWAY_PLUGIN_VERSION', wc_payway_get_version() );
		$this->define( 'WC_PAYWAY_PLUGIN_SDK_VERSION', wc_payway_get_sdk_version() );
		$this->define( 'WC_PAYWAY_PLUGIN_VERSION_CODEBASE', wc_payway_get_version_in_codebase() );
		$this->define( 'WC_PAYWAY_PLUGIN_SDK_VERSION_CODEBASE', wc_payway_get_sdk_version_in_codebase() );
		$this->define( 'WC_PAYWAY_ABSPATH', dirname( WC_PAYWAY_PLUGIN_FILE ) . '/' );
	}

	public function init_admin_menu() {
		// Admin menu initialization
		new WC_Payway_Admin_Menu;
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Includes required classes
	 */
	private function includes() {
		require_once WC_PAYWAY_ABSPATH . 'includes/interfaces/class-wc-payway-config-interface.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/interfaces/class-wc-payway-meta-interface.php';

		require_once WC_PAYWAY_ABSPATH . 'includes/class-payway-compatibility.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/class-payway-errors.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/class-payway-logger.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/class-payway-activator.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/class-payway-core-functions.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/class-payway-bank-factory.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/class-payway-card-factory.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/class-payway-promotion-factory.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/class-payway-integrity-validator.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/class-payway-meta.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/class-payway-default-catalog.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/class-payway-setup-status.php';

		if ( is_admin() ) {
			require_once WC_PAYWAY_ABSPATH . 'includes/admin/class-payway-admin-menu.php';
		}
	}

	/**
	 * Process all required files to execute a Payment Request against Payway SDK
	 *
	 * @return void
	 */
	public function init_payway_request_includes() {
		// Interfaces
		require_once WC_PAYWAY_ABSPATH . 'includes/interfaces/class-wc-payway-config-interface.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/interfaces/class-wc-payway-rest-interface.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/interfaces/class-wc-payway-request-processor-interface.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/interfaces/class-wc-payway-cybersource-validator-interface.php';

		// Request and builder classes
		require_once WC_PAYWAY_ABSPATH . 'includes/class-wc-payway-gateway-request-builder.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/class-wc-payway-gateway-request.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/class-wc-payway-gateway-api-handler.php';

		// Request Processors
		require_once WC_PAYWAY_ABSPATH . 'includes/processors/class-wc-payway-request-token.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/processors/class-wc-payway-request-general.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/processors/class-wc-payway-request-customer.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/processors/class-wc-payway-request-order.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/processors/class-wc-payway-request-payment.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/processors/class-wc-payway-request-cybersource.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/processors/class-wc-payway-request-subpayments.php';
	}

	/**
	 * Loads languages for this plugin
	 */
	public function load_localization_files() {
		load_plugin_textdomain(
			'wc-gateway-payway',
			false,
			dirname( plugin_basename( WC_PAYWAY_PLUGIN_FILE ) ) . '/i18n/languages'
		);
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		// Carga temprano para i18n en checkout (#33/#43).
		add_action( 'init', array( $this, 'load_localization_files' ), 0 );
		add_action( 'before_wc_payway_gateway_init', array( $this, 'load_localization_files' ) );

		// adds Payway into the Payment Gateways list
		add_filter( 'woocommerce_payment_gateways', array( $this, 'payway_add_gateway_class'));

		// initialize dependencies
		add_action( 'init', array( $this, 'init' ), 0 );
		// Schema upgrade temprano (admin + storefront): promos query necesita no_expiry.
		add_action( 'plugins_loaded', array( 'WC_Payway_Activator', 'maybe_upgrade' ), 20 );
		add_action( 'init', array( 'WC_Payway_Activator', 'maybe_upgrade' ), 1 );

		// expose custom meta box within Order Details
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'render_order_payment_details') );
	}

	/**
	 * TODO: this is currently required, cause they register the custom filters.
	 * refactor to a better architecture.
	 *
	 * @see self::init_hooks()
	 * @see wc_payway_gateway_admin_xxx_create
	 * @see wc_payway_gateway_admin_xxx_update
	 */
	public function init() {
		do_action( 'before_wc_payway_gateway_init' );

		$this->bank = new WC_Payway_Bank_Factory();
		$this->card = new WC_Payway_Card_Factory();
		$this->promotion = new WC_Payway_Promotion_Factory();
	}

	/**
	 * Renders custom information within the WooCommerce Order view screen
	 * TODO: convert into a meta box and move to it's own class
	 *
	 * @param WC_Order $order
	 * @return string
	 */
	public function render_order_payment_details( $order ) {
		// Ensure the current Order was payed through Payway
		if ( wc_payway_get_payment_code() !== $order->get_payment_method() ) {
			return;
		}

		$order_id = $order->get_id();
		$payway_transaction_id = WC_Payway_Meta::get_order_transaction_id( $order_id, true );
		$payway_site_transaction_id = WC_Payway_Meta::get_order_site_transaction_id( $order_id, true );
		$payment_data = WC_Payway_Meta::get_order_payment_data( $order_id, true );
		$promo = WC_Payway_Meta::get_order_promotion( $order_id, true );
		?>
			<h3><?php esc_html_e( 'Payway Payment Information', 'wc-gateway-payway' ); ?></h3>
			<?php if ( $payway_transaction_id ) : ?>
			<div>
				<strong><?php esc_html_e( 'Trans. ID', 'wc-gateway-payway' ); ?>:</strong>
				<span><?php echo esc_html( (string) $payway_transaction_id ); ?></span>
			</div>
			<?php endif; ?>
			<?php if ( $payway_site_transaction_id ) : ?>
			<div>
				<strong><?php esc_html_e( 'Site Trans. ID', 'wc-gateway-payway' ); ?>:</strong>
				<span><?php echo esc_html( (string) $payway_site_transaction_id ); ?></span>
			</div>
			<?php endif; ?>
			<?php if ( ! empty( $payment_data ) && isset( $payment_data['status_details'] ) ) : ?>
			<?php $status = $payment_data['status_details']; ?>
			<div>
				<strong><?php esc_html_e( 'Ticket', 'wc-gateway-payway' ); ?>:</strong>
				<span><?php echo esc_html( (string) ( $status['ticket'] ?? '' ) ); ?></span>
			</div>
			<div>
				<strong><?php esc_html_e( 'CC Auth Code', 'wc-gateway-payway' ); ?>:</strong>
				<span><?php echo esc_html( (string) ( $status['card_authorization_code'] ?? '' ) ); ?></span>
			</div>
			<div>
				<strong><?php esc_html_e( 'CC Address Code', 'wc-gateway-payway' ); ?>:</strong>
				<span><?php echo esc_html( (string) ( $status['address_validation_code'] ?? '' ) ); ?></span>
			</div>
			<?php endif; ?>
			<?php if ( ! empty( $payment_data ) && isset( $payment_data['cybersource'] ) ) : ?>
			<?php $cybersource = $payment_data['cybersource']; ?>
			<div>
				<strong><?php esc_html_e( 'CS Decision', 'wc-gateway-payway' ); ?>:</strong>
				<span><?php echo esc_html( (string) ( $cybersource['decision'] ?? '' ) ); ?></span>
			</div>
			<?php endif; ?>
			<?php if ( ! empty( $promo ) && is_array( $promo ) ) : ?>
			<div>
				<h4><?php esc_html_e( 'Fee Plans configuration', 'wc-gateway-payway' ); ?></h4>
			</div>
			<div>
				<strong><?php esc_html_e( 'Period', 'wc-gateway-payway' ); ?>:</strong>
				<span><?php echo esc_html( (string) ( $promo['fee_period'] ?? '' ) ); ?></span>
			</div>
			<div>
				<strong><?php esc_html_e( 'Coeficient', 'wc-gateway-payway' ); ?>:</strong>
				<span><?php echo esc_html( (string) ( $promo['coefficient'] ?? '' ) ); ?></span>
			</div>
			<div>
				<strong><?php esc_html_e( 'TEA', 'wc-gateway-payway' ); ?>:</strong>
				<span><?php echo esc_html( (string) ( $promo['tea'] ?? '' ) ); ?></span>
			</div>
			<div>
				<strong><?php esc_html_e( 'CFT', 'wc-gateway-payway' ); ?>:</strong>
				<span><?php echo esc_html( (string) ( $promo['cft'] ?? '' ) ); ?></span>
			</div>
			<div>
				<strong><?php esc_html_e( 'Fee to send', 'wc-gateway-payway' ); ?>:</strong>
				<span><?php echo esc_html( (string) ( $promo['fee_to_send'] ?? '' ) ); ?></span>
			</div>
			<?php endif; ?>
		<?php
	}
}
