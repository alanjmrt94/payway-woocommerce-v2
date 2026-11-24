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

if ( class_exists( 'WC_Payway_Admin_Menu' )) {
	return;
}

class WC_Payway_Admin_Menu {

	/**
	 * @var WC_Payway_Admin_Navigation_Page_Setup|null
	 */
	private $setup = null;

	/**
	 * Holds an instance of `WC_Payway_Admin_Navigation_Page_Banks`
	 * @var WC_Payway_Admin_Navigation_Page_Banks|null
	 */
	private $banks = null;

	/**
	 * Holds an instance of `WC_Payway_Admin_Navigation_Page_Cards`
	 * @var WC_Payway_Admin_Navigation_Page_Cards|null
	 */
	private $cards = null;

	/**
	 * Holds an instance of `WC_Payway_Admin_Navigation_Page_Promotions`
	 * @var WC_Payway_Admin_Navigation_Page_Promotions|null
	 */
	private $promotions = null;

	/**
	 * @var WC_Payway_Admin_Navigation_Page_Payments|null
	 */
	private $payments = null;

	/**
	 * Holds an instance of `WC_Payway_Admin_Navigation_Page_Status`
	 * @var WC_Payway_Admin_Navigation_Page_Status|null
	 */
	private $status = null;

	/**
	 *
	 */
	public function __construct() {
		$this->process_navigation_includes();
		WC_Payway_Admin_Notices::init();

		add_action( 'admin_menu', array( $this, 'register_pages' ) );
	}

	/**
	 * Includes admin pages
	 */
	public static function process_navigation_includes() {
		require_once WC_PAYWAY_ABSPATH . 'includes/admin/class-payway-admin-sanitize.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/admin/class-payway-admin-page-setup.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/admin/class-payway-admin-page-banks.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/admin/class-payway-admin-page-cards.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/admin/class-payway-admin-page-promotions.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/admin/class-payway-admin-notices.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/admin/class-payway-payment-list.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/admin/class-payway-admin-page-payments.php';
		require_once WC_PAYWAY_ABSPATH . 'includes/admin/class-payway-admin-page-status.php';
	}

	/**
	 * Renders Instalación page
	 */
	public function render_setup_page() {
		return $this->setup->render();
	}

	/**
	 * Renders the Bank Page
	 */
	public function render_banks_page()
	{
		return $this->banks->render();
	}

	/**
	 * Renders the Cards Page
	 */
	public function render_cards_page()
	{
		return $this->cards->render();
	}

	/**
	 * Renders the Cards Page
	 */
	public function render_promotions_page()
	{
		return $this->promotions->render();
	}

	/**
	 * Renders Recent Payments page.
	 */
	public function render_payments_page() {
		return $this->payments->render();
	}

	/**
	 * Renders the Cards Page
	 */
	public function render_status_page()
	{
		return $this->status->render();
	}

	/**
	 * Initializes menu items and page renderers
	 */
	public function register_pages() {
		$this->setup = new WC_Payway_Admin_Navigation_Page_Setup();
		$this->banks = new WC_Payway_Admin_Navigation_Page_Banks();
		$this->cards = new WC_Payway_Admin_Navigation_Page_Cards();
		$this->promotions = new WC_Payway_Admin_Navigation_Page_Promotions();
		$this->payments   = new WC_Payway_Admin_Navigation_Page_Payments();
		$this->status     = new WC_Payway_Admin_Navigation_Page_Status();

		// Ícono Community (ruta "w"); no usar el isotipo oficial de Payway.
		$main_icon = $this->get_menu_icon_data_uri();

		$cap = class_exists( 'WC_Payway_Admin_Sanitize', false )
			? WC_Payway_Admin_Sanitize::required_capability()
			: 'manage_woocommerce';

		add_menu_page(
			__( 'Payway', 'wc-gateway-payway' ),
			__( 'Payway', 'wc-gateway-payway' ),
			$cap,
			'payway_admin_dashboard',
			array( $this, 'render_setup_page' ),
			$main_icon,
			58
		);

		add_submenu_page(
			'payway_admin_dashboard',
			__( 'Setup', 'wc-gateway-payway' ),
			__( 'Setup', 'wc-gateway-payway' ),
			$cap,
			'payway_admin_setup',
			array( $this, 'render_setup_page' )
		);

		add_submenu_page(
			'payway_admin_dashboard',
			__( 'Manage Banks', 'wc-gateway-payway' ),
			__( 'Manage Banks', 'wc-gateway-payway' ),
			$cap,
			'payway_admin_banks',
			array( $this, 'render_banks_page' )
		);

		add_submenu_page(
			'payway_admin_dashboard',
			__( 'Manage Cards', 'wc-gateway-payway' ),
			__( 'Manage Cards', 'wc-gateway-payway' ),
			$cap,
			'payway_admin_cards',
			array( $this, 'render_cards_page' )
		);

		add_submenu_page(
			'payway_admin_dashboard',
			__( 'Manage Promotions', 'wc-gateway-payway' ),
			__( 'Manage Promotions', 'wc-gateway-payway' ),
			$cap,
			'payway_admin_promotions',
			array( $this, 'render_promotions_page' )
		);

		add_submenu_page(
			'payway_admin_dashboard',
			__( 'Recent Payments', 'wc-gateway-payway' ),
			__( 'Recent Payments', 'wc-gateway-payway' ),
			$cap,
			'payway_admin_payments',
			array( $this, 'render_payments_page' )
		);

		add_submenu_page(
			'payway_admin_dashboard',
			__( 'Status', 'wc-gateway-payway' ),
			__( 'Status', 'wc-gateway-payway' ),
			$cap,
			'payway_admin_status',
			array( $this, 'render_status_page' )
		);

		// Removes the auto submenu that mirrors the top-level slug.
		remove_submenu_page( 'payway_admin_dashboard', 'payway_admin_dashboard' );

		return apply_filters( 'payway_admin_navigation_sections', array() );
	}

	/**
	 * Data-URI del isotipo Community para el menú admin (SVG base64).
	 *
	 * @return string
	 */
	private function get_menu_icon_data_uri() {
		$svg_path = dirname( WC_PAYWAY_PLUGIN_FILE ) . '/assets/images/brand/isotipo-chico.svg';
		if ( is_readable( $svg_path ) ) {
			$svg = file_get_contents( $svg_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( is_string( $svg ) && '' !== $svg ) {
				return 'data:image/svg+xml;base64,' . base64_encode( $svg );
			}
		}

		return 'dashicons-cart';
	}
}
