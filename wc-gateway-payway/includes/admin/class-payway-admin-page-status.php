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

use Automattic\Jetpack\Constants;

/**
 *
 */
class WC_Payway_Admin_Navigation_Page_Status {

	public function render() {
		$version   = Constants::get_constant( 'WC_VERSION' );
		wp_register_style(
			'woocommerce_admin_styles',
			WC()->plugin_url() . '/assets/css/admin.css',
			array(),
			$version
		);

		wp_enqueue_style( 'woocommerce_admin_styles' );

		return self::output();
	}

	/**
	* Handles output of the status page
	*/
	public static function output() {
		$plugin = array(
			'version' => wc_payway_get_version(),
			'sdk_version' => wc_payway_get_sdk_version(),
			'cb_version' => wc_payway_get_version_in_codebase(),
			'cb_sdk_version' => wc_payway_get_sdk_version_in_codebase()
		);

		include_once WC_PAYWAY_ABSPATH . 'includes/admin/html-payway-admin-page-status.php';
	}
}
