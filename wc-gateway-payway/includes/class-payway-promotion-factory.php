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

defined( 'ABSPATH' ) || exit;

/**
 * Promotion factory class
 */
class WC_Payway_Promotion_Factory {

	/**
	 * The single instance of the class
	 *
	 * @var WC_Payway_Promotion_Factory
	 */
	protected static $_instance = null;

	/**
	 * Constructor function
	 */
	public function __construct() {
		// generates a filter where the page can call whenever a create action occurs
		// passing the data out through the filter
		add_filter( 'wc_payway_gateway_admin_promotion_create', array( $this, 'create_promotion' ) );
	}

	/**
	 * Returns a list of the required field names
	 *
	 * @return array[]
	 */
	public static function get_required_fields() {
		return array(
			array(
				'name' => 'rule_name',
				'label' => __( 'Name', 'wc-gateway-payway' )
			),
			array(
				'name' => 'bank_id',
				'label' => __( 'Bank', 'wc-gateway-payway' )
			),
			array(
				'name' => 'card_id',
				'label' => __( 'Card', 'wc-gateway-payway' )
			),
			array(
				'name' => 'from_date',
				'label' => __( 'From Date', 'wc-gateway-payway' )
			),
			array(
				'name' => 'applicable_days',
				'label' => __( 'Applicable Days', 'wc-gateway-payway' )
			),
		);
	}

	/**
	 * Instance
	 *
	 * @static
	 * @return WC_Payway_Promotion_Factory
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Concatenates WordPress table prefix for the given table name
	 *
	 * @param string $base_table_name Name of the database table
	 * @return string
	 */
	private static function get_table_name( $base_table_name ) {
		global $wpdb;
		return $wpdb->prefix . $base_table_name;
	}

	/**
	 * Returns Promotion database table name
	 *
	 * @return string
	 */
	private static function get_promotion_table_name() {
		return self::get_table_name( WC_Payway_Activator_Interface::TABLE_NAME_PROMOTIONS );
	}

	/**
	 * Returns Banks database table name
	 *
	 * @return string
	 */
	private static function get_banks_table_name() {
		return self::get_table_name( WC_Payway_Activator_Interface::TABLE_NAME_BANKS );
	}

	/**
	 * Returns Cards database table name
	 *
	 * @return string
	 */
	private static function get_cards_table_name() {
		return self::get_table_name( WC_Payway_Activator_Interface::TABLE_NAME_CARDS );
	}

	/**
	 * Retrieves all Promotions records
	 *
	 * @return (array[stdClass]|object|null) database query results
	 */
	public static function get_promotions() {
		global $wpdb;

		if ( class_exists( 'WC_Payway_Activator' ) ) {
			WC_Payway_Activator::ensure_core_schema();
		}

		$promotion_table = self::get_promotion_table_name();
		$bank_table = self::get_banks_table_name();
		$card_table = self::get_cards_table_name();

		$query = "SELECT promos.*,
			bank.name as 'bank_name',
			card.card_name,
			card.id_sps as 'card_code'
			FROM {$promotion_table} AS promos
			INNER JOIN {$bank_table} AS bank
				ON promos.bank_id = bank.id
			INNER JOIN {$card_table} AS card
				ON promos.card_id = card.id
		ORDER BY promos.rule_name ASC";

		return $wpdb->get_results(
			$query
		);
	}

	/**
	 * Transient key for applicable promotions (keyed by weekday).
	 *
	 * @param int|null $day
	 * @return string
	 */
	public static function get_applicable_promotions_cache_key( $day = null ) {
		if ( null === $day ) {
			$day = wc_payway_get_current_day_number();
		}
		return 'payway_applicable_promos_d' . (int) $day;
	}

	/**
	 * Drop cached applicable-promotion queries (all weekdays).
	 *
	 * @return void
	 */
	public static function invalidate_applicable_promotions_cache() {
		for ( $day = 0; $day <= 7; $day++ ) {
			delete_transient( self::get_applicable_promotions_cache_key( $day ) );
		}
	}

	/**
	 * Returns all promotions that should be used at Checkout.
	 * `applicable_days` is filtered in SQL via find_in_set.
	 *
	 * @return (array[stdClass]|object|null) database query results
	 */
	public static function get_applicable_promotions() {
		global $wpdb;

		if ( class_exists( 'WC_Payway_Activator' ) ) {
			WC_Payway_Activator::ensure_core_schema();
		}

		$current_day = wc_payway_get_current_day_number();
		$cache_key   = self::get_applicable_promotions_cache_key( $current_day );
		$skip_cache  = (bool) apply_filters( 'payway_skip_promo_cache', false );

		if ( ! $skip_cache ) {
			$cached = get_transient( $cache_key );
			if ( false !== $cached && is_array( $cached ) ) {
				return $cached;
			}
		}

		$promotion_table = self::get_promotion_table_name();
		$bank_table      = self::get_banks_table_name();
		$card_table      = self::get_cards_table_name();

		$query = "SELECT promos.*,
			bank.name as 'bank_name',
			card.card_name,
			card.id_sps as 'card_code'
			FROM {$promotion_table} AS promos
			INNER JOIN {$bank_table} AS bank
				ON promos.bank_id = bank.id
			INNER JOIN {$card_table} AS card
				ON promos.card_id = card.id
		WHERE
			promos.is_active = 1
			AND promos.from_date <= NOW()
			AND (
				promos.no_expiry = 1
				OR promos.to_date >= NOW()
			)
			AND find_in_set( {$current_day}, promos.applicable_days )
		ORDER BY promos.priority ASC,
			promos.bank_id ASC,
			promos.card_id ASC;
		";

		$results = $wpdb->get_results( $query );
		if ( ! is_array( $results ) ) {
			$results = array();
		}

		if ( ! $skip_cache ) {
			set_transient( $cache_key, $results, 5 * MINUTE_IN_SECONDS );
		}

		return $results;
	}

	/**
	 *
	 * @param array $value
	 * @return string
	 */
	public static function convert_days_for_save( $value ) {
		return $value ? implode( ',', $value ) : '';
	}

	/**
	 * @param array $value
	 * @return string
	 */
	public static function convert_fee_plans_for_save( $value ) {
		return $value ? wp_json_encode( $value ) : '';
	}

	/**
	 *
	 * @param string $value
	 * @param boolean $return_as_array whether to conver as array or return the default stdClass
	 * @return array
	 */
	public static function convert_fee_plans_for_display( $value, $return_as_array = false ) {
		return $value ? json_decode( $value, $return_as_array ) : '';
	}

	/**
	 * Returns a Promotion based on the given id
	 *
	 * @param int|string $promotion_id Promotion to be retrieved
	 * @return stdClass|null
	 */
	public static function get_promotion( $promotion_id ) {
		global $wpdb;

		if ( class_exists( 'WC_Payway_Activator' ) ) {
			WC_Payway_Activator::ensure_core_schema();
		}

		$query = $wpdb->prepare(
			"SELECT * FROM " . self::get_promotion_table_name() . " WHERE id = %d",
			$promotion_id
		);

		return $wpdb->get_row( $query );
	}

	/**
	 *
	 * @param array $data
	 * @return array
	 */
	public static function prepare_data_for_save( $data ) {
		return $data;
	}

	/**
	 *
	 * @param string $date
	 * @return string
	 */
	public static function get_promotion_datetime( $date ) {
		return $date;
	}

	/**
	 *
	 * @param array $data
	 * @return int|false id of the inserted record or false if fails
	 */
	public static function create_promotion( $data ) {
		global $wpdb;

		if ( class_exists( 'WC_Payway_Activator' ) ) {
			WC_Payway_Activator::ensure_core_schema();
		}

		$applicable_days = self::convert_days_for_save( $data['applicable_days'] );
		$fee_plans       = isset( $data['fee_plans'] )
			? self::convert_fee_plans_for_save( $data['fee_plans'] )
			: '';
		$no_expiry       = isset( $data['no_expiry'] ) ? (int) (bool) $data['no_expiry'] : 1;
		$from_date       = $data['from_date'];
		$to_date         = ! empty( $data['to_date'] ) ? $data['to_date'] : '2099-12-31 23:59:59';

		$values = array(
			'rule_name'       => $data['rule_name'],
			'card_id'         => (int) $data['card_id'],
			'bank_id'         => (int) $data['bank_id'],
			'from_date'       => $from_date,
			'to_date'         => $to_date,
			'priority'        => (int) $data['priority'],
			'is_active'       => (int) $data['is_active'],
			'no_expiry'       => $no_expiry,
			'applicable_days' => $applicable_days,
			'fee_plans'       => $fee_plans,
		);

		$wpdb->insert(
			self::get_promotion_table_name(),
			$values,
			array( '%s', '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%s' )
		);

		self::invalidate_applicable_promotions_cache();

		return $wpdb->insert_id;
	}

	/**
	 *
	 * @param array $data
	 * @return bool if successful, false otherwise
	 */
	public static function update_promotion( $data ) {
		global $wpdb;

		if ( class_exists( 'WC_Payway_Activator' ) ) {
			WC_Payway_Activator::ensure_core_schema();
		}

		$applicable_days = self::convert_days_for_save( $data['applicable_days'] );
		$fee_plans       = isset( $data['fee_plans'] )
			? self::convert_fee_plans_for_save( $data['fee_plans'] )
			: '';
		$no_expiry       = isset( $data['no_expiry'] ) ? (int) (bool) $data['no_expiry'] : 1;
		$from_date       = self::get_promotion_datetime( $data['from_date'] );
		$to_date         = ! empty( $data['to_date'] )
			? self::get_promotion_datetime( $data['to_date'] )
			: '2099-12-31 23:59:59';

		$values = array(
			'rule_name'       => $data['rule_name'],
			'card_id'         => (int) $data['card_id'],
			'bank_id'         => (int) $data['bank_id'],
			'from_date'       => $from_date,
			'to_date'         => $to_date,
			'priority'        => (int) $data['priority'],
			'is_active'       => (int) $data['is_active'],
			'no_expiry'       => $no_expiry,
			'applicable_days' => $applicable_days,
			'fee_plans'       => $fee_plans,
		);

		$update = $wpdb->update(
			self::get_promotion_table_name(),
			$values,
			array( 'id' => $data['id'] ),
			array( '%s', '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%s' ),
			array( '%d' )
		);

		if ( is_wp_error( $update ) ) {
			throw new Exception( $update->get_error_message() );
		}

		self::invalidate_applicable_promotions_cache();

		// When record exists but isn't updated (cause same data has been posted)
		// $wpdb->update() returns a zero integer, we need to normalize our response
		// to avoid showing an error when everything went OK but nothing gets saved
		return is_numeric( $update )
			? true
			: false;
	}

	/**
	 * Deletes the given record from the database
	 *
	 * @param int $record_id
	 * @return bool
	 */
	public static function delete_promotion( $record_id ) {
		global $wpdb;

		$deleted = (bool) $wpdb->delete(
			self::get_promotion_table_name(),
			array( 'id' => $record_id ),
			array( '%d' )
		);
		if ( $deleted ) {
			self::invalidate_applicable_promotions_cache();
		}

		return $deleted;
	}

	/**
	 * Amount of banks currently in the database
	 * (usually to display then in the admin grids)
	 * returns `null` if no var is found by `get_var`
	 *
	 * @return mixed|null
	 */
	public static function record_count() {
		global $wpdb;

		$query = "SELECT COUNT(*) FROM " . self::get_promotion_table_name();

		return $wpdb->get_var( $query );
	}

	/**
	 * Returns an array with days that current rule will be running
	 *
	 * data returned contains this format:
	 * [ ['value' => '0', 'label' => 'Sunday'], [...], ... ]
	 *
	 * @return array[]
	 */
	public static function get_weekdays_list() {
		global $wp_locale;

		$week_days = $wp_locale->weekday;
		$data = array();

		foreach ($week_days as $value => $label) {
			array_push(
				$data,
				array( 'value' => (string) $value, 'label' => $label )
			);
		}
		return $data;
	}

	/**
	 * Returns an array of strings with all the day ids selected for the current promotion
	 *
	 * @param stdClass $promotion
	 * @return string|string[]
	 */
	public static function get_promotion_days( $promotion ) {
		if ( ! $promotion || $promotion->applicable_days === '') {
			return array();
		}

		if (strpos($promotion->applicable_days, ',') ) {
			$data = explode( ',', $promotion->applicable_days );
		} else {
			$data = $promotion->applicable_days;
		}

		return $data;
	}

	/**
	 * Returns Fee Plan data from Promotion based on the selected `fee_to_send`
	 *
	 * This helper function retrieves the Fee Plan selected in the Installments dropdown
	 * during the Checkout Process. It's used to store the Fee Plan data
	 * in a custom WC_Order meta field for later visualization in WC_Order details
	 *
	 * @see WC_Payway_Meta_Interface::PROMOTION_APPLIED
	 *
	 * @param int $promotion_id
	 * @param int $fee_to_send
	 * @return array
	 */
	public static function get_applied_fee_plan( $promotion_id, $fee_to_send ) {
		$promotion = self::get_promotion( $promotion_id );

		$plans = $promotion
			? json_decode($promotion->fee_plans)
			: false;

		if ( !$plans || empty( $plans )) {
			return array();
		}

		$details = array();

		foreach ( $plans as $plan ) {
			if ( (int) $plan->fee_to_send === $fee_to_send) {
				$details = $plan;
				break;
			}
		}

		return $details;
	}
}
