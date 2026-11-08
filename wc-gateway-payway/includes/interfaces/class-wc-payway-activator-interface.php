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
 * Holds Payway database configuration
 */
interface WC_Payway_Activator_Interface {

	/**
	 * @var string
	 */
	const WC_PAYWAY_VERSION = 'payway_gateway_version';

	/**
	 * Plugin version
	 *
	 * When updating, this must be also updated in the `index.php` file metadata
	 * otherwise, WordPress won't detect the version update
	 *
	 * @see wc-gateway-payway/index.php
	 * @var string
	 */
	const WC_PAYWAY_VERSION_VALUE = '1.0.0';

	/**
	 * @var string
	 */
	const WC_PAYWAY_SDK_VERSION = 'payway_gateway_sdk_version';

	/**
	 * Vendored Decidir SDK version (composer.lock).
	 *
	 * @var string
	 */
	const WC_PAYWAY_SDK_VERSION_VALUE = '1.4.0';

	/**
	 * @var string
	 */
	const TABLE_NAME_BANKS = 'prisma_payway_banks';

	/**
	 * @var string
	 */
	const TABLE_NAME_CARDS = 'prisma_payway_cards';

	/**
	 * @var string
	 */
	const TABLE_NAME_PROMOTIONS = 'prisma_payway_promotions';

	/**
	 * Config option name that holds the plugin version
	 *
	 * @var string
	 */
	const CONFIG_OPTION_GATEWAY_VERSION_NAME = 'payway_gateway_version';

	/**
	 * Config option name that holds the Payway SDK version
	 *
	 * @var string
	 */
	const CONFIG_OPTION_SDK_VERSION_NAME = 'payway_gateway_sdk_version';
}
