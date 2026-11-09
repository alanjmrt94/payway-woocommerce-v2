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

class WC_Payway_Request_Order_Processor implements WC_Payway_Request_Processor_Interface {

	/**
	 * @var string
	 */
	const AMOUNT = 'amount';

	/**
     * @var string
     */
    const CURRENCY = 'currency';

	/**
     * @var string
     */
    const ESTABLISHMENT_NAME = 'establishment_name';

	/**
     * @var string
     */
	const SITE_TRANSACTION_ID = 'site_transaction_id';

	/**
	 *
	 * @param WC_Order $order
	 * @param array $checkout_posted_data
	 * @return array
	 */
	public function process( $order, $checkout_posted_data ) {
		return $this->get_data( $order );
	}

	/**
	 *
	 * @param WC_Order
	 * @return array
	 */
	private function get_data( $order ) {
		return array(
			self::AMOUNT => $order->get_total(),
			self::CURRENCY => $order->get_currency(),
			self::ESTABLISHMENT_NAME => $this->get_establishment_name(),
			self::SITE_TRANSACTION_ID => $this->get_site_transaction_id( $order )
		);
	}

	/**
	 *
	 * @param WC_Order $order
	 * @return int
	 */
	private function get_amount( $order ) {
		return $order->get_total();
	}

	/**
	 * Returns the WordPress blog site title
	 *
	 * @return string
	 */
	private function get_establishment_name()
	{
		return substr( get_bloginfo('name'), 0, 25);
	}

	/**
	 * Stable site transaction id for Decidir dedupe across retries of the same order.
	 * Reuses a previously stored value when present; otherwise persists `payway-{order_id}`.
	 *
	 * @param WC_Order $order
	 * @return string
	 */
	private function get_site_transaction_id( $order ) {
		$order_id = (int) $order->get_id();
		$existing = WC_Payway_Meta::get_order_site_transaction_id( $order_id, true );
		if ( $existing ) {
			return (string) $existing;
		}

		$stid = 'payway-' . $order_id;
		WC_Payway_Meta::set_order_site_transaction_id( $order_id, $stid );

		return $stid;
	}
}
