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
 * Validate order cybersource interface
 *
 * Class WC_Payway_Cybersource_Validator_Interface
 * @see https://decidirv2.api-docs.io/1.0/prevencion-de-fraude-by-cybersource/flujo-de-una-transaccion-con-cybersource
 */
interface WC_Payway_Cybersource_Validator_Interface
{
    /**
     * @var string
     */
    const DECISION_GREEN = 'green';

    /**
     * @var string
     */
    const DECISION_RED = 'red';

    /**
     * @var string
     */
    const DECISION_YELLOW = 'yellow';

    /**
     * @var string
     */
    const DECISION_BLUE = 'BLUE';

    /**
     * @var string
     */
    const DECISION_BLACK = 'black';

    /**
     * @var string
     */
    const CYBERSOURCE_ERROR = 'cybersource_error';
}
