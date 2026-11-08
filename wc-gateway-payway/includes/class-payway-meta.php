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
 * Order meta HPOS-compatible.
 *
 * @package wc-gateway-payway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta data helper using WC CRUD when possible.
 */
class WC_Payway_Meta implements WC_Payway_Meta_Interface {

	/** @var self|null */
	protected static $instance = null;

	/**
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @return string
	 */
	private static function get_prefix_meta_name() {
		return static::PREFIX;
	}

	/**
	 * @param string $meta
	 * @return string
	 */
	public static function get_full_meta_name( $meta ) {
		return self::get_prefix_meta_name() . $meta;
	}

	/**
	 * @param int|WC_Order $order_or_id
	 * @return WC_Order|false
	 */
	private static function resolve_order( $order_or_id ) {
		return WC_Payway_Compatibility::get_order( $order_or_id );
	}

	/**
	 * @param int|WC_Order $order_or_id
	 * @param string       $key
	 * @param bool         $single
	 * @return mixed
	 */
	private static function get_meta( $order_or_id, $key, $single = true ) {
		$order = self::resolve_order( $order_or_id );
		if ( ! $order ) {
			return $single ? '' : array();
		}
		return $order->get_meta( self::get_full_meta_name( $key ), $single );
	}

	/**
	 * @param int|WC_Order $order_or_id
	 * @param string       $key
	 * @param mixed        $value
	 * @return void
	 */
	private static function set_meta( $order_or_id, $key, $value ) {
		$order = self::resolve_order( $order_or_id );
		if ( ! $order ) {
			return;
		}
		$order->update_meta_data( self::get_full_meta_name( $key ), $value );
		$order->save();
	}

	/**
	 * @param int  $order_id
	 * @param bool $single
	 * @return mixed
	 */
	public static function get_order_transaction_response( $order_id, $single = false ) {
		return self::get_meta( $order_id, self::FULL_RESPONSE, $single );
	}

	/**
	 * Stores a redacted subset of the gateway response.
	 *
	 * @param int   $order_id
	 * @param array $response
	 */
	public static function set_order_transaction_response( $order_id, $response ) {
		self::set_meta( $order_id, self::FULL_RESPONSE, self::sanitize_response_for_storage( $response ) );
	}

	/**
	 * @param mixed $response
	 * @return array
	 */
	public static function sanitize_response_for_storage( $response ) {
		if ( ! is_array( $response ) ) {
			return array();
		}

		$allowed = array(
			'id',
			'site_transaction_id',
			'payment_method_id',
			'card_brand',
			'installments',
			'status',
			'status_details',
			'fraud_detection',
			'amount',
			'currency',
			'date',
		);

		$safe = array();
		foreach ( $allowed as $key ) {
			if ( isset( $response[ $key ] ) ) {
				$safe[ $key ] = $response[ $key ];
			}
		}

		return $safe;
	}

	/**
	 * @param int  $order_id
	 * @param bool $single
	 * @return mixed
	 */
	public static function get_order_transaction_id( $order_id, $single = false ) {
		return self::get_meta( $order_id, self::TRANSACTION_ID, $single );
	}

	/**
	 * @param int    $order_id
	 * @param string $transaction_id
	 */
	public static function set_order_transaction_id( $order_id, $transaction_id ) {
		self::set_meta( $order_id, self::TRANSACTION_ID, $transaction_id );
	}

	/**
	 * @param int  $order_id
	 * @param bool $single
	 * @return mixed
	 */
	public static function get_order_site_transaction_id( $order_id, $single = false ) {
		return self::get_meta( $order_id, static::SITE_TRANSACTION_ID, $single );
	}

	/**
	 * @param int    $order_id
	 * @param string $stid
	 */
	public static function set_order_site_transaction_id( $order_id, $stid ) {
		self::set_meta( $order_id, static::SITE_TRANSACTION_ID, $stid );
	}

	/**
	 * @param int  $order_id
	 * @param bool $single
	 * @return mixed
	 */
	public static function get_order_promotion( $order_id, $single = false ) {
		return self::get_meta( $order_id, static::PROMOTION_APPLIED, $single );
	}

	/**
	 * @param int   $order_id
	 * @param array $data
	 */
	public static function set_order_promotion( $order_id, $data ) {
		self::set_meta( $order_id, static::PROMOTION_APPLIED, $data );
	}

	/**
	 * @param int  $order_id
	 * @param bool $single
	 * @return mixed
	 */
	public static function get_order_payment_data( $order_id, $single = false ) {
		return self::get_meta( $order_id, static::PAYMENT_DATA, $single );
	}

	/**
	 * @param int   $order_id
	 * @param array $data
	 */
	public static function set_order_payment_data( $order_id, $data ) {
		self::set_meta( $order_id, static::PAYMENT_DATA, $data );
	}
}
