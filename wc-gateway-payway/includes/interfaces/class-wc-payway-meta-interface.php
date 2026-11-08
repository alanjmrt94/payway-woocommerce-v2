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

/**
 * Interface that holds custom meta field names
 * with Transaction and Promotion data applied to the WC Order
 */
interface WC_Payway_Meta_Interface
{
	/**
	 * Prefix for all Plugin's Meta fields
	 *
	 * @var string
	 */
	const PREFIX = '_payway_';

	/**
	 * @var string
	 */
	const TRANSACTION_ID = 'transaction_id';

	/**
	 * Meta field that holds the transaction id value from the Merchant
	 *
	 * @var string
	 */
	const SITE_TRANSACTION_ID = 'site_transaction_id';

	/**
	 * @var string
	 */
	const PAYMENT_DATA = 'payment_data';

	/**
	 * Holds the entire SDK response data of a transaction
	 *
	 * @var string
	 */
	 const FULL_RESPONSE = 'full_response';

	/**
	 * Holds relevant Promotion data selected during Checkout process
	 *
	 * @var string
	 */
	const PROMOTION_APPLIED = 'promotion_applied';
}
