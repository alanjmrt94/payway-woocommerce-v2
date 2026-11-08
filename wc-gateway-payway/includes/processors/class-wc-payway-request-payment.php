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

class WC_Payway_Request_Payment_Processor implements WC_Payway_Request_Processor_Interface {

	/**
     * @var string
     */
    const BIN = 'bin';

    /**
     * @var string
     */
    const PAYMENT_METHOD_ID = 'payment_method_id';

    /**
     * @var string
     */
    const PAYMENT_TYPE = 'payment_type';

    /**
     * @var string
     */
    const INSTALLMENTS = 'installments';

	/**
	 *
	 * @param WC_Order $order
	 * @param array $checkout_posted_data
	 * @return array
	 */
	public function process( $order, $checkout_posted_data ) {
		return $this->get_data( $order, $checkout_posted_data );
	}

	/**
	 * @param WC_Order $order
	 * @param array $checkout_posted_data
	 * @return array
	 */
	private function get_data( $order, $checkout_posted_data )
	{
		$data = array(
			self::INSTALLMENTS => $this->get_installments( $checkout_posted_data ),
			self::PAYMENT_METHOD_ID => $this->get_payment_method_id( $checkout_posted_data ),
			self::PAYMENT_TYPE => $this->get_payment_type( $order )
		);

		$cc_bin = $this->get_bin( $checkout_posted_data );
		if ( $cc_bin ) {
			$data[self::BIN] = $cc_bin;
		}

		return $data;
	}

	/**
	 *
	 * @param array $data
	 * @return int
	 */
	private function get_installments( $data ) {
		return (int) $data['installments'];
	}

	/**
	 *
	 * @param array $data
	 * @return int
	 */
	private function get_payment_method_id( $data ) {
		if ( isset( $data['payment_method_id'] ) && $data['payment_method_id'] ) {
			return (int) $data['payment_method_id'];
		}
		return (int) ( $data['cc_type'] ?? 0 );
	}

	/**
	 *
	 * @param WC_Order $order
	 * @return string
	 */
	private function get_payment_type( $order ) {
		return 'single';
	}

	/**
	 *
	 * @param array $data
	 * @return null|string
	 */
	private function get_bin( $data ) {
		return (string) $data['bin'];
	}
}
