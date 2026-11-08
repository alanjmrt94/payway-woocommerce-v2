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
 * Valida en servidor promoción, plan de cuotas y payment_method_id.
 *
 * @package wc-gateway-payway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Integrity validator for checkout payment data.
 */
class WC_Payway_Integrity_Validator {

	/**
	 * Valida y normaliza los datos del checkout contra promociones aplicables.
	 *
	 * @param array $posted_data Datos parseados del formulario.
	 * @return array|\WP_Error Datos normalizados o error.
	 */
	public static function validate_checkout_selection( array $posted_data ) {
		$rule_id      = isset( $posted_data['rule_id'] ) ? absint( $posted_data['rule_id'] ) : 0;
		$card_code    = isset( $posted_data['cc_type'] ) ? absint( $posted_data['cc_type'] ) : 0;
		$fee_to_send  = isset( $posted_data['installments'] ) ? absint( $posted_data['installments'] ) : 0;
		$token        = isset( $posted_data['token'] ) ? (string) $posted_data['token'] : '';

		if ( ! $token ) {
			return new WP_Error(
				WC_Payway_Errors::VALIDATION_TOKEN,
				WC_Payway_Errors::message( WC_Payway_Errors::VALIDATION_TOKEN )
			);
		}

		if ( ! $rule_id || ! $fee_to_send || ! $card_code ) {
			return new WP_Error(
				WC_Payway_Errors::VALIDATION_INSTALLMENTS,
				WC_Payway_Errors::message( WC_Payway_Errors::VALIDATION_INSTALLMENTS )
			);
		}

		$applicable = WC_Payway_Promotion_Factory::get_applicable_promotions();
		$matched    = null;

		foreach ( (array) $applicable as $promo ) {
			if ( (int) $promo->id !== $rule_id ) {
				continue;
			}
			if ( (int) $promo->card_code !== $card_code ) {
				continue;
			}
			$matched = $promo;
			break;
		}

		if ( ! $matched ) {
			return new WP_Error(
				WC_Payway_Errors::VALIDATION_PROMOTION,
				WC_Payway_Errors::message( WC_Payway_Errors::VALIDATION_PROMOTION )
			);
		}

		$plan = WC_Payway_Promotion_Factory::get_applied_fee_plan( $rule_id, $fee_to_send );
		if ( empty( $plan ) ) {
			return new WP_Error(
				WC_Payway_Errors::VALIDATION_FEE_PLAN,
				WC_Payway_Errors::message( WC_Payway_Errors::VALIDATION_FEE_PLAN )
			);
		}

		return array(
			'rule_id'      => $rule_id,
			'card_id'      => isset( $posted_data['card_id'] ) ? absint( $posted_data['card_id'] ) : (int) $matched->card_id,
			'installments' => $fee_to_send,
			'cc_type'      => $card_code,
			'token'        => $token,
			'bin'          => isset( $posted_data['bin'] ) ? preg_replace( '/\D/', '', (string) $posted_data['bin'] ) : '',
			'last_digits'  => isset( $posted_data['last_digits'] ) ? preg_replace( '/\D/', '', (string) $posted_data['last_digits'] ) : '',
			'door_number'  => isset( $posted_data['door_number'] ) ? absint( $posted_data['door_number'] ) : 0,
			'fee_plan'     => $plan,
			'promotion'    => $matched,
			'payment_method_id' => $card_code,
		);
	}
}
