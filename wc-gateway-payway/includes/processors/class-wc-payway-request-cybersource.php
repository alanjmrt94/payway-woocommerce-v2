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

class WC_Payway_Request_CyberSource_Processor implements WC_Payway_Request_Processor_Interface {
	/**
	 * @var string
	 */
	const FRAUD_DETECTION = 'fraud_detection';

	/**
	 * @var boolean
	 */
	const SEND_TO_CS = true;

	/**
	 * @var string
	 */
	const CHANNEL = 'Web';

	/**
	 *
	 * @param WC_Order $order
	 * @param array    $checkout_posted_data
	 * @return array
	 */
	public function process( $order, $checkout_posted_data ) {
		$cc_door_number = isset( $checkout_posted_data['door_number'] ) ? $checkout_posted_data['door_number'] : '';
		return array(
			self::FRAUD_DETECTION => $this->get_data( $order, $cc_door_number ),
		);
	}

	/**
	 *
	 * @param WC_Order $order
	 * @param string   $cc_door_number
	 * @return array
	 */
	private function get_data( $order, $cc_door_number ) {
		return array(
			'send_to_cs'               => (bool) self::SEND_TO_CS,
			'channel'                  => self::CHANNEL,
			'dispatch_method'          => '',
			'bill_to'                  => $this->get_bill_to( $order ),
			'purchase_totals'          => $this->get_purchase_total( $order ),
			'customer_in_site'         => $this->get_customer_in_site( $order, $cc_door_number ),
			'retail_transaction_data'  => array(
				'ship_to'                 => $this->get_ship_to( $order ),
				'items'                   => $this->get_order_items( $order ),
				'days_to_delivery'        => '',
				'dispatch_method'         => '',
				'tax_voucher_required'    => true,
				'customer_loyalty_number' => '',
				'coupon_code'             => '',
			),
		);
	}

	/**
	 * Major currency units (e.g. 30000.00). SDK setCybersource() applies the only ×100.
	 *
	 * @param WC_Order $order
	 * @return array
	 */
	private function get_purchase_total( $order ) {
		return array(
			'currency' => $order->get_currency(),
			'amount'   => (float) $order->get_total(),
		);
	}

	/**
	 * Line items use minor units (cents). SDK Retail only auto-scales csit* keys, not total_amount/unit_price.
	 *
	 * @param WC_Order $order
	 * @return array
	 */
	private function get_order_items( $order ) {
		$items = array();

		foreach ( $order->get_items() as $item ) {
			$product      = $item->get_product();
			$product_sku  = $product ? $product->get_sku() : '';
			$product_price = $product ? $product->get_price() : 0;
			$total_price_item = $item->get_total();
			$item_id      = $item->get_id();
			$item_name    = $item->get_name();
			$description  = '';
			if ( $product ) {
				$description = $product->get_short_description() ? $product->get_short_description() : $product->get_description();
			}
			$quantity = $item->get_quantity();

			if ( '' === $product_sku ) {
				$product_sku = $item_id . '-' . strtolower( str_replace( ' ', '-', $item_name ) );
			}

			$items[] = array(
				'code'         => $product_sku,
				'name'         => $item_name,
				'description'  => $description,
				'sku'          => $product_sku,
				'total_amount' => $this->amount_to_minor_units( $total_price_item ),
				'quantity'     => $quantity,
				'unit_price'   => $this->amount_to_minor_units( $product_price ),
			);
		}

		return $items;
	}

	/**
	 * Convert a WC major-unit amount to integer minor units (cents). Single conversion only.
	 *
	 * @param mixed $amount
	 * @return int
	 */
	private function amount_to_minor_units( $amount ) {
		$normalized = is_string( $amount )
			? str_replace( ',', '.', $amount )
			: $amount;
		return (int) round( (float) $normalized * 100 );
	}

	/**
	 * @param WC_Order $order
	 * @return array
	 */
	private function get_bill_to( $order ) {
		$bill_to = array(
			'customer_id'  => (string) ( $order->get_customer_id() ? $order->get_customer_id() : $order->get_billing_first_name() . '_' . $order->get_billing_last_name() ),
			'first_name'   => $order->get_billing_first_name(),
			'last_name'    => $order->get_billing_last_name(),
			'email'        => $order->get_billing_email(),
			'phone_number' => $order->get_billing_phone(),
			'country'      => $order->get_billing_country(),
			'state'        => $order->get_billing_state(),
			'city'         => $order->get_billing_city(),
			'postal_code' => $order->get_billing_postcode(),
			'street1'      => $order->get_billing_address_1(),
		);

		$street2 = $order->get_billing_address_2();
		if ( ! empty( $street2 ) ) {
			$bill_to['street2'] = $street2;
		}

		return $bill_to;
	}

	/**
	 *
	 * @param WC_Order $order
	 * @return array
	 */
	private function get_ship_to( $order ) {
		if ( ! $order->has_shipping_address() ) {
			$bill_to = $this->get_bill_to( $order );
			unset( $bill_to['customer_id'] );
			return $bill_to;
		}

		$shipping_phone = $order->get_shipping_phone() != ''
			? $order->get_shipping_phone()
			: $order->get_billing_phone();

		$shipto = array(
			'customer_id'  => $order->get_customer_id() ? $order->get_customer_id() : $order->get_shipping_first_name() . '_' . $order->get_shipping_last_name(),
			'first_name'   => $order->get_shipping_first_name(),
			'last_name'    => $order->get_shipping_last_name(),
			'email'        => $order->get_billing_email(),
			'phone_number' => $shipping_phone,
			'country'      => $order->get_shipping_country(),
			'state'        => $order->get_shipping_state(),
			'city'         => $order->get_shipping_city(),
			'postal_code' => $order->get_shipping_postcode(),
			'street1'      => $order->get_shipping_address_1(),
		);

		$street2 = $order->get_shipping_address_2();
		if ( ! empty( $street2 ) ) {
			$shipto['street2'] = $street2;
		}

		return $shipto;
	}

	/**
	 *
	 * @param WC_Order $order
	 * @param string   $cc_door_number
	 * @return array
	 */
	private function get_customer_in_site( $order, $cc_door_number ) {
		$cid      = $order->get_customer_id();
		$customer = null;

		if ( $cid ) {
			$customer = new WC_Customer( $cid );
		}

		// Decidir CS MDD39 expects a domicile string (e.g. "Av. Corrientes 1234"),
		// not the door number alone — that yields invalid_param on customer_in_site.street.
		$street = trim( (string) $order->get_billing_address_1() );
		$door   = trim( (string) $cc_door_number );
		if ( $door && $street && false === strpos( $street, $door ) ) {
			$street = trim( $street . ' ' . $door );
		} elseif ( ! $street && $door ) {
			$street = $door;
		}

		return array(
			'is_guest'            => $cid ? false : true,
			'password'            => '',
			'num_of_transactions' => ( $cid && $customer ) ? (int) $customer->get_order_count() : 0,
			'cellphone_number'    => $order->get_billing_phone(),
			'date_of_birth'       => '',
			'street'              => $street,
		);
	}

	/**
	 * @param WC_Customer $customer
	 * @return int
	 */
	private function get_days_in_site( $customer ) {
		try {
			$datetime1 = date_create( $customer->get_date_created() );
			$datetime2 = date_create( 'now' );

			$interval = date_diff( $datetime1, $datetime2 );
			$days     = $interval->format( '%a' );
		} catch ( \Exception $exception ) {
			$days = 0;
		}

		return $days;
	}
}
