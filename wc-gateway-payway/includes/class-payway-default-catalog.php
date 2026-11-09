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

defined( 'ABSPATH' ) || exit;

/**
 * Catálogo fijo Decidir y seed de banco / tarjetas / promociones por defecto.
 */
class WC_Payway_Default_Catalog {

	const DEFAULT_BANK_NAME = 'Todos';
	const DEFAULT_PROMO_PREFIX = 'Default — ';

	/**
	 * Plan type: credit (1/3/6/9/12/18) or single (1).
	 */
	const PLAN_CREDIT = 'credit';
	const PLAN_SINGLE = 'single';

	/**
	 * MiPyME installment codes sent to Decidir (period → fee_to_send).
	 * Requires MiPyME enabled in Mi Payway and an eligible merchant MCC.
	 */
	const MIPYME_FEE_TO_SEND = array(
		3 => 13,
		6 => 16,
	);

	/**
	 * @return array<int,array{name:string,id_sps:int,id_nps:int,plan_type:string,note?:string}>
	 */
	public static function get_cards_catalog() {
		return array(
			array(
				'name'      => 'Visa Crédito',
				'id_sps'    => 1,
				'id_nps'    => 1,
				'plan_type' => self::PLAN_CREDIT,
			),
			array(
				'name'      => 'Visa Débito',
				'id_sps'    => 31,
				'id_nps'    => 31,
				'plan_type' => self::PLAN_SINGLE,
			),
			array(
				'name'      => 'Visa Prepaga',
				'id_sps'    => 114,
				'id_nps'    => 114,
				'plan_type' => self::PLAN_SINGLE,
			),
			array(
				'name'      => 'MasterCard Crédito',
				'id_sps'    => 104,
				'id_nps'    => 104,
				'plan_type' => self::PLAN_CREDIT,
			),
			array(
				'name'      => 'MasterCard Débito',
				'id_sps'    => 105,
				'id_nps'    => 105,
				'plan_type' => self::PLAN_SINGLE,
			),
			array(
				'name'      => 'MasterCard Prepaga',
				'id_sps'    => 116,
				'id_nps'    => 116,
				'plan_type' => self::PLAN_SINGLE,
			),
			array(
				'name'      => 'Maestro',
				'id_sps'    => 106,
				'id_nps'    => 106,
				'plan_type' => self::PLAN_SINGLE,
			),
			array(
				'name'      => 'Cabal Crédito',
				'id_sps'    => 63,
				'id_nps'    => 63,
				'plan_type' => self::PLAN_CREDIT,
			),
			array(
				'name'      => 'Cabal Débito',
				'id_sps'    => 108,
				'id_nps'    => 108,
				'plan_type' => self::PLAN_SINGLE,
			),
			array(
				'name'      => 'AMEX',
				'id_sps'    => 111,
				'id_nps'    => 111,
				'plan_type' => self::PLAN_CREDIT,
			),
			array(
				'name'      => 'Amex Pura',
				'id_sps'    => 65,
				'id_nps'    => 65,
				'plan_type' => self::PLAN_CREDIT,
				'note'      => __( 'Usually applies to Decidir 2.0, not Ventas Online.', 'wc-gateway-payway' ),
			),
			array(
				'name'      => 'Discover',
				'id_sps'    => 139,
				'id_nps'    => 139,
				'plan_type' => self::PLAN_CREDIT,
			),
			array(
				'name'      => 'Naranja',
				'id_sps'    => 24,
				'id_nps'    => 24,
				'plan_type' => self::PLAN_CREDIT,
			),
			array(
				'name'      => 'Tarjeta Sol',
				'id_sps'    => 143,
				'id_nps'    => 143,
				'plan_type' => self::PLAN_CREDIT,
			),
		);
	}

	/**
	 * Credit installment periods used by defaults / setup assistant.
	 *
	 * @return int[]
	 */
	public static function get_credit_periods() {
		return array( 1, 3, 6, 9, 12, 18 );
	}

	/**
	 * Human-readable credit plan list for admin UI.
	 *
	 * @return string
	 */
	public static function get_credit_periods_label() {
		return implode( ' / ', self::get_credit_periods() );
	}

	/**
	 * Sanitizes credit periods coming from the setup assistant checkboxes.
	 *
	 * @param mixed $raw
	 * @param bool  $fallback_all When true and nothing valid is provided, return the full credit set.
	 * @return int[]
	 */
	public static function sanitize_credit_periods( $raw, $fallback_all = true ) {
		$allowed = self::get_credit_periods();
		if ( ! is_array( $raw ) ) {
			return $fallback_all ? $allowed : array();
		}

		$periods = array();
		foreach ( $raw as $value ) {
			$period = absint( $value );
			if ( in_array( $period, $allowed, true ) ) {
				$periods[] = $period;
			}
		}

		$periods = array_values( array_unique( $periods ) );
		sort( $periods, SORT_NUMERIC );

		if ( $periods ) {
			return $periods;
		}

		return $fallback_all ? $allowed : array();
	}

	/**
	 * Resolves fee_to_send for a visible installment period.
	 *
	 * @param int  $period  Installments shown to the shopper (fee_period).
	 * @param bool $mipyme  When true, maps 3→13 and 6→16.
	 * @return int
	 */
	public static function resolve_fee_to_send( $period, $mipyme = false ) {
		$period = (int) $period;
		if ( $mipyme && isset( self::MIPYME_FEE_TO_SEND[ $period ] ) ) {
			return (int) self::MIPYME_FEE_TO_SEND[ $period ];
		}
		return $period;
	}

	/**
	 * Whether a fee plan uses MiPyME codes (3→13 / 6→16).
	 *
	 * @param int $fee_period
	 * @param int $fee_to_send
	 * @return bool
	 */
	public static function is_mipyme_fee_plan( $fee_period, $fee_to_send ) {
		$fee_period  = (int) $fee_period;
		$fee_to_send = (int) $fee_to_send;
		return isset( self::MIPYME_FEE_TO_SEND[ $fee_period ] )
			&& self::MIPYME_FEE_TO_SEND[ $fee_period ] === $fee_to_send;
	}

	/**
	 * @param string     $plan_type
	 * @param bool       $mipyme    Apply MiPyME fee_to_send mapping for periods 3 and 6.
	 * @param int[]|null $periods   Credit periods to include; debit/prepaid always use [1].
	 * @return array<int,array{fee_period:string,coefficient:string,tea:string,cft:string,fee_to_send:string}>
	 */
	public static function get_default_fee_plans( $plan_type, $mipyme = false, $periods = null ) {
		if ( self::PLAN_SINGLE === $plan_type ) {
			$periods = array( 1 );
		} else {
			$periods = self::sanitize_credit_periods( $periods );
		}

		$plans = array();
		foreach ( $periods as $n ) {
			$plans[] = array(
				'fee_period'  => (string) $n,
				'coefficient' => '1',
				'tea'         => '0',
				'cft'         => '0',
				'fee_to_send' => (string) self::resolve_fee_to_send( $n, $mipyme ),
			);
		}

		return $plans;
	}

	/**
	 * @param int $id_sps
	 * @return array|null
	 */
	public static function get_catalog_card_by_sps( $id_sps ) {
		foreach ( self::get_cards_catalog() as $card ) {
			if ( (int) $card['id_sps'] === (int) $id_sps ) {
				return $card;
			}
		}
		return null;
	}

	/**
	 * Ensures bank "Todos" exists.
	 *
	 * @return int bank id
	 */
	public static function ensure_default_bank() {
		$existing = self::find_bank_by_name( self::DEFAULT_BANK_NAME );
		if ( $existing ) {
			return (int) $existing->id;
		}

		$id = WC_Payway_Bank_Factory::create_bank(
			array( 'name' => self::DEFAULT_BANK_NAME )
		);

		return (int) $id;
	}

	/**
	 * @param string $name
	 * @return stdClass|null
	 */
	public static function find_bank_by_name( $name ) {
		global $wpdb;
		$table = $wpdb->prefix . WC_Payway_Activator_Interface::TABLE_NAME_BANKS;
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE name = %s LIMIT 1", $name )
		);
	}

	/**
	 * @param int $id_sps
	 * @return stdClass|null
	 */
	public static function find_card_by_sps( $id_sps ) {
		global $wpdb;
		$table = $wpdb->prefix . WC_Payway_Activator_Interface::TABLE_NAME_CARDS;
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id_sps = %d LIMIT 1", (int) $id_sps )
		);
	}

	/**
	 * Inserts selected catalog cards that are not already present (by id_sps).
	 *
	 * @param int[] $id_sps_list
	 * @return array{created:int[],skipped:int[],errors:string[]}
	 */
	public static function add_cards( array $id_sps_list ) {
		$result = array(
			'created' => array(),
			'skipped' => array(),
			'errors'  => array(),
		);

		foreach ( $id_sps_list as $id_sps ) {
			$id_sps = (int) $id_sps;
			$meta   = self::get_catalog_card_by_sps( $id_sps );
			if ( ! $meta ) {
				$result['errors'][] = sprintf(
					/* translators: %d: id_sps */
					__( 'SPS ID %d is not in the catalog.', 'wc-gateway-payway' ),
					$id_sps
				);
				continue;
			}

			if ( self::find_card_by_sps( $id_sps ) ) {
				$result['skipped'][] = $id_sps;
				continue;
			}

			$insert_id = WC_Payway_Card_Factory::create_card(
				array(
					'card_name' => $meta['name'],
					'id_sps'    => $meta['id_sps'],
					'id_nps'    => $meta['id_nps'],
				)
			);

			if ( $insert_id ) {
				$result['created'][] = $id_sps;
			} else {
				$result['errors'][] = sprintf(
					/* translators: %s: card name */
					__( 'Could not create card %s.', 'wc-gateway-payway' ),
					$meta['name']
				);
			}
		}

		return $result;
	}

	/**
	 * Creates default promotions for the given SPS ids (cards must exist).
	 *
	 * @param int[]      $id_sps_list
	 * @param bool       $mipyme      When true, credit plans send MiPyME codes for 3 and 6 installments.
	 * @param int[]|null $periods     Credit periods to include in each promotion.
	 * @return array{created:string[],skipped:string[],errors:string[]}
	 */
	public static function create_default_promotions( array $id_sps_list, $mipyme = false, $periods = null ) {
		$result = array(
			'created' => array(),
			'skipped' => array(),
			'errors'  => array(),
		);

		$bank_id = self::ensure_default_bank();
		if ( ! $bank_id ) {
			$result['errors'][] = __( 'Could not create bank Todos.', 'wc-gateway-payway' );
			return $result;
		}

		$days    = array( '0', '1', '2', '3', '4', '5', '6' );
		$now     = current_time( 'mysql' );
		$mipyme  = (bool) $mipyme;
		$periods = self::sanitize_credit_periods( $periods );

		foreach ( $id_sps_list as $id_sps ) {
			$id_sps = (int) $id_sps;
			$meta   = self::get_catalog_card_by_sps( $id_sps );
			$card   = self::find_card_by_sps( $id_sps );

			if ( ! $meta || ! $card ) {
				$result['errors'][] = sprintf(
					/* translators: %d: id_sps */
					__( 'Card with SPS ID %d is missing. Add it before creating the promotion.', 'wc-gateway-payway' ),
					$id_sps
				);
				continue;
			}

			$rule_name = self::DEFAULT_PROMO_PREFIX . $meta['name'];
			if ( self::find_default_promotion( (int) $bank_id, (int) $card->id, $rule_name ) ) {
				$result['skipped'][] = $rule_name;
				continue;
			}

			$promo_id = WC_Payway_Promotion_Factory::create_promotion(
				array(
					'rule_name'       => $rule_name,
					'card_id'         => (int) $card->id,
					'bank_id'         => $bank_id,
					'from_date'       => $now,
					'to_date'         => '2099-12-31 23:59:59',
					'priority'        => 10,
					'is_active'       => 1,
					'no_expiry'       => 1,
					'applicable_days' => $days,
					'fee_plans'       => self::get_default_fee_plans( $meta['plan_type'], $mipyme, $periods ),
				)
			);

			if ( $promo_id ) {
				$result['created'][] = $rule_name;
			} else {
				$result['errors'][] = sprintf(
					/* translators: %s: promotion name */
					__( 'Could not create promotion %s.', 'wc-gateway-payway' ),
					$rule_name
				);
			}
		}

		return $result;
	}

	/**
	 * @param int    $bank_id
	 * @param int    $card_id
	 * @param string $rule_name
	 * @return stdClass|null
	 */
	public static function find_default_promotion( $bank_id, $card_id, $rule_name ) {
		global $wpdb;
		$table = $wpdb->prefix . WC_Payway_Activator_Interface::TABLE_NAME_PROMOTIONS;
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE bank_id = %d AND card_id = %d AND rule_name = %s LIMIT 1",
				$bank_id,
				$card_id,
				$rule_name
			)
		);
	}
}
