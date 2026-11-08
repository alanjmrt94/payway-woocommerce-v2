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

class WC_Payway_Request_Customer_Processor implements WC_Payway_Request_Processor_Interface {

	/**
     * @var string
     */
    const CUSTOMER = 'customer';

    /**
     * @var string
     */
    const ID = 'id';

    /**
     * @var string
     */
    const EMAIL = 'email';

    /**
     * @var string
     */
    const IP_ADDRESS = 'ip_address';

	/**
	 *
	 * @param WC_Order $order
	 * @param array $checkout_posted_data
	 * @return array
	 */
	public function process( $order, $checkout_posted_data ) {
		return array(
			self::CUSTOMER => $this->get_data( $order )
		);
	}

	/**
	 * @param WC_Order $order
	 * @return array
	 */
	private function get_data( $order )
	{
		$customerId = $order->get_customer_id() > 0
			? $order->get_customer_id()
			: $order->get_billing_first_name() . '_' . $order->get_billing_last_name();

		return array(
			self::ID => (string) $customerId,
			self::EMAIL => $order->get_billing_email(),
			self::IP_ADDRESS => $order->get_customer_ip_address(),
		);
	}
}
