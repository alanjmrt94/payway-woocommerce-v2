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
 * Interface to hold static values used during request initialization
 * and Connector instantiation, which currently PHP SDK does not exposes
 *
 * @see \Decidir\RESTClient
 */
interface WC_Payway_Rest_Interface {

	/**
	 * @var string
	 */
	const PRODUCTION_URL = 'https://live.decidir.com/api/v2';

	/**
	 * @var string
	 */
	const SANDBOX_URL = 'https://developers.decidir.com/api/v2';

	/**
	 * RestClient fields used to hold public key
	 * @see \Decidir\RESTClient::setKey()
	 * @var string
	 */
	const CONNECTOR_PUBLIC_KEY = 'public_key';

	/**
	 * RestClient fields used to hold private key
	 * @see \Decidir\RESTClient::setKey()
	 * @var string
	 */
	const CONNECTOR_PRIVATE_KEY = 'private_key';

	/**
	 * Value name to indicate Sandbox env
	 * used in SDK during Connector creation
	 *
	 * @see \Decidir\RESTClient::__construct()
	 * @var string
	 */
	const CONNECTOR_ENVIRONMENT_SANDBOX = 'test';

	/**
	 * Value name to indicate Production env
	 * used in SDK during Connector creation
	 *
	 * @see \Decidir\RESTClient::__construct()
	 * @var string
	 */
	const CONNECTOR_ENVIRONMENT_PRODUCTION = 'prod';

	/**
	 * Status codes to consider a request to be successful
	 *
	 * @var int[]
	 */
	const SUCCESS_STATUS_CODES = [200, 201, 204];


    /**
     * Value to set transaction platform origin service
     *
     * @var string
     */
    const CONNECTOR_SERVICE = 'SDK-PHP-IURCO-WOOCOMMERCE';

    /**
     * Value to set transaction platform origin developer
     *
     * @var string
     */
    const CONNECTOR_DEVELOPER = 'IURCO - Prisma SA';

    /**
     * Value to set transaction platform origin grouper
     *
     * @var string
     */
    const CONNECTOR_GROUPER = 'wc-gateway-payway';
}
