<?php
/**
 * Payway Payment Community — gateway de pago para WooCommerce (fork independiente).
 * Copyright (C) 2026 Alan Martinez
 *
 * Based on prior work © IURCO / Prisma SA where applicable.
 *
 * @package wc-gateway-payway
 * @license GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sanitize helpers for Payway admin CRUD forms.
 */
class WC_Payway_Admin_Sanitize {

	/**
	 * Capability required for Payway admin screens and mutations.
	 *
	 * @return string
	 */
	public static function required_capability() {
		return 'manage_woocommerce';
	}

	/**
	 * Die unless the current user can manage Payway admin.
	 *
	 * @return void
	 */
	public static function require_manage_cap() {
		if ( ! current_user_can( self::required_capability() ) ) {
			wp_die( esc_html__( 'You do not have permission for this action.', 'wc-gateway-payway' ) );
		}
	}

	/**
	 * @param array $post Raw $_POST.
	 * @return array{name:string}
	 */
	public static function bank_from_post( $post ) {
		$post = is_array( $post ) ? $post : array();

		return array(
			'name' => isset( $post['name'] ) ? sanitize_text_field( wp_unslash( $post['name'] ) ) : '',
		);
	}

	/**
	 * @param array $post Raw $_POST.
	 * @return array{bank_id:int,name:string}
	 */
	public static function bank_update_from_post( $post ) {
		$post = is_array( $post ) ? $post : array();
		$data = self::bank_from_post( $post );
		$data['bank_id'] = isset( $post['bank_id'] ) ? absint( $post['bank_id'] ) : 0;

		return $data;
	}

	/**
	 * @param array $post Raw $_POST.
	 * @return array{card_name:string,id_sps:string,id_nps:string}
	 */
	public static function card_from_post( $post ) {
		$post = is_array( $post ) ? $post : array();

		return array(
			'card_name' => isset( $post['card_name'] ) ? sanitize_text_field( wp_unslash( $post['card_name'] ) ) : '',
			'id_sps'    => isset( $post['id_sps'] ) ? sanitize_text_field( wp_unslash( $post['id_sps'] ) ) : '',
			'id_nps'    => isset( $post['id_nps'] ) ? sanitize_text_field( wp_unslash( $post['id_nps'] ) ) : '',
		);
	}

	/**
	 * @param array $post Raw $_POST.
	 * @return array{card_id:int,card_name:string,id_sps:string,id_nps:string}
	 */
	public static function card_update_from_post( $post ) {
		$post = is_array( $post ) ? $post : array();
		$data = self::card_from_post( $post );
		$data['card_id'] = isset( $post['card_id'] ) ? absint( $post['card_id'] ) : 0;

		return $data;
	}

	/**
	 * Sanitize fee plan rows from promotions admin.
	 *
	 * @param mixed $raw_plans
	 * @return array<int,array{fee_period:string,coefficient:string,tea:string,cft:string,fee_to_send:string}>
	 */
	public static function fee_plans_from_post( $raw_plans ) {
		$plans = array();
		if ( ! is_array( $raw_plans ) ) {
			return $plans;
		}

		foreach ( $raw_plans as $plan ) {
			if ( ! is_array( $plan ) ) {
				continue;
			}
			$plans[] = array(
				'fee_period'  => isset( $plan['fee_period'] ) ? sanitize_text_field( wp_unslash( $plan['fee_period'] ) ) : '',
				'coefficient' => isset( $plan['coefficient'] ) ? sanitize_text_field( wp_unslash( $plan['coefficient'] ) ) : '',
				'tea'         => isset( $plan['tea'] ) ? sanitize_text_field( wp_unslash( $plan['tea'] ) ) : '',
				'cft'         => isset( $plan['cft'] ) ? sanitize_text_field( wp_unslash( $plan['cft'] ) ) : '',
				'fee_to_send' => isset( $plan['fee_to_send'] ) ? sanitize_text_field( wp_unslash( $plan['fee_to_send'] ) ) : '',
			);
		}

		return $plans;
	}

	/**
	 * @param array $post Raw $_POST.
	 * @return array
	 */
	public static function promotion_from_post( $post ) {
		$post = is_array( $post ) ? $post : array();

		$days = array();
		if ( isset( $post['applicable_days'] ) && is_array( $post['applicable_days'] ) ) {
			foreach ( $post['applicable_days'] as $day ) {
				// absint( 'x' ) === 0; reject non-numeric before casting.
				if ( ! is_numeric( $day ) ) {
					continue;
				}
				$day = (int) $day;
				if ( $day >= 0 && $day <= 6 ) {
					$days[] = (string) $day;
				}
			}
			$days = array_values( array_unique( $days ) );
		}

		$data = array(
			'rule_name'       => isset( $post['rule_name'] ) ? sanitize_text_field( wp_unslash( $post['rule_name'] ) ) : '',
			'bank_id'         => isset( $post['bank_id'] ) ? absint( $post['bank_id'] ) : 0,
			'card_id'         => isset( $post['card_id'] ) ? absint( $post['card_id'] ) : 0,
			'from_date'       => isset( $post['from_date'] ) ? sanitize_text_field( wp_unslash( $post['from_date'] ) ) : '',
			'to_date'         => isset( $post['to_date'] ) ? sanitize_text_field( wp_unslash( $post['to_date'] ) ) : '',
			'priority'        => isset( $post['priority'] ) ? absint( $post['priority'] ) : 0,
			'is_active'       => isset( $post['is_active'] ) ? absint( $post['is_active'] ) : 0,
			'no_expiry'       => ! empty( $post['no_expiry'] ) ? 1 : 0,
			'applicable_days' => $days,
			'fee_plans'       => self::fee_plans_from_post( isset( $post['fee_plans'] ) ? $post['fee_plans'] : array() ),
		);

		if ( isset( $post['promotion_id'] ) ) {
			$data['promotion_id'] = absint( $post['promotion_id'] );
		}

		return $data;
	}
}
