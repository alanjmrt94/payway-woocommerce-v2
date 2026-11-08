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
 * Capa de compatibilidad (detección de capacidades).
 *
 * @package wc-gateway-payway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Feature detection helpers.
 */
class WC_Payway_Compatibility {

	/**
	 * Whether HPOS (custom order tables) is enabled.
	 *
	 * @return bool
	 */
	public static function is_hpos_enabled() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
			return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
		}
		return false;
	}

	/**
	 * Resolve WC_Order from id.
	 *
	 * @param int|WC_Order $order_or_id
	 * @return WC_Order|false
	 */
	public static function get_order( $order_or_id ) {
		if ( $order_or_id instanceof WC_Order ) {
			return $order_or_id;
		}
		return wc_get_order( absint( $order_or_id ) );
	}
}
