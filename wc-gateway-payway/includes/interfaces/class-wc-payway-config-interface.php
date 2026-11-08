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
 * Holds all Payway config field names within WooCommerce Payment Settings
 */
interface WC_Payway_Config_Interface {

	/**
	 * @var string
	 */
	const PAYMENT_CODE = 'payway_gateway';

	/**
	 * @var string
	 */
	const FIELD_ENABLED = 'enabled';

	/**
	 * @var string
	 */
	const FIELD_GATEWAY_TITLE = 'title';

	/**
	 * @var string
	 */
	const FIELD_GATEWAY_DESCRIPTION = 'description';

	/**
	 * @var string
	 */
	const FIELD_SANDBOX_MODE = 'sandbox_mode';

	/**
	 * @var string
	 */
	const FIELD_CYBERSOURCE_ENABLED = 'cybersource_enabled';

	/**
	 * @var string
	 */
	const FIELD_DEBUG_ENABLED = 'debug';

	/**
	 * @var string
	 */
	const FIELD_SANDBOX_SITE_ID = 'sandbox_site_id';

	/**
	 * @var string
	 */
	const FIELD_SANDBOX_PUBLIC_KEY = 'sandbox_public_key';

	/**
	 * @var string
	 */
	const FIELD_SANDBOX_PRIVATE_KEY = 'sandbox_private_key';

	/**
	 * @var string
	 */
	const FIELD_PRODUCTION_SITE_ID = 'production_site_id';

	/**
	 * @var string
	 */
	const FIELD_PRODUCTION_PUBLIC_KEY = 'production_public_key';

	/**
	 * @var string
	 */
	const FIELD_PRODUCTION_PRIVATE_KEY = 'production_private_key';
}
