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
 * Admin: Instalación / checklist / defaults.
 */
class WC_Payway_Admin_Navigation_Page_Setup {

	/**
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( WC_Payway_Admin_Sanitize::required_capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wc-gateway-payway' ) );
		}

		wp_enqueue_style(
			'wc-payway-admin',
			plugins_url( 'assets/css/style-admin.css', WC_PAYWAY_PLUGIN_FILE ),
			array(),
			function_exists( 'wc_payway_asset_version' ) ? wc_payway_asset_version() : '0.0.0'
		);

		$notices = array();

		if ( isset( $_POST['payway_setup_action'] ) ) {
			check_admin_referer( 'payway_admin_setup', '_wpnonce_payway_admin_setup' );
			$notices = $this->handle_actions( sanitize_text_field( wp_unslash( $_POST['payway_setup_action'] ) ) );
		}

		$checks  = WC_Payway_Setup_Status::get_checks();
		$catalog = WC_Payway_Default_Catalog::get_cards_catalog();
		$page    = $this;

		include WC_PAYWAY_ABSPATH . 'includes/admin/html-payway-admin-page-setup.php';
	}

	/**
	 * @param array $rows
	 * @return void
	 */
	public function render_checks_table( $rows ) {
		include WC_PAYWAY_ABSPATH . 'includes/admin/html-payway-admin-checks-table.php';
	}

	/**
	 * @param string $action
	 * @return array<int,array{type:string,message:string}>
	 */
	private function handle_actions( $action ) {
		$notices = array();
		$selected = isset( $_POST['card_sps'] ) && is_array( $_POST['card_sps'] )
			? array_map( 'absint', wp_unslash( $_POST['card_sps'] ) )
			: array();

		if ( 'apply_recommended' === $action ) {
			$result = WC_Payway_Setup_Status::apply_recommended_settings();
			$notices[] = array(
				'type'    => 'success',
				'message' => sprintf(
					/* translators: 1: changed count 2: list */
					__( 'Recommended settings applied (%1$d changes): %2$s. API keys were not modified.', 'wc-gateway-payway' ),
					count( $result['changed'] ),
					$result['changed'] ? implode( ', ', $result['changed'] ) : '—'
				),
			);
			return $notices;
		}

		if ( 'ensure_bank' === $action ) {
			$id = WC_Payway_Default_Catalog::ensure_default_bank();
			$notices[] = $id
				? array(
					'type'    => 'success',
					'message' => __( 'Bank “Todos” is available.', 'wc-gateway-payway' ),
				)
				: array(
					'type'    => 'error',
					'message' => __( 'Could not create bank Todos.', 'wc-gateway-payway' ),
				);
			return $notices;
		}

		if ( 'add_cards' === $action ) {
			if ( empty( $selected ) ) {
				$notices[] = array(
					'type'    => 'error',
					'message' => __( 'Select at least one card.', 'wc-gateway-payway' ),
				);
				return $notices;
			}

			$result = WC_Payway_Default_Catalog::add_cards( $selected );
			$notices[] = array(
				'type'    => empty( $result['errors'] ) ? 'success' : 'warning',
				'message' => sprintf(
					/* translators: 1: created count 2: skipped count */
					__( 'Cards: %1$d created, %2$d already existed.', 'wc-gateway-payway' ),
					count( $result['created'] ),
					count( $result['skipped'] )
				),
			);
			foreach ( $result['errors'] as $error ) {
				$notices[] = array(
					'type'    => 'error',
					'message' => $error,
				);
			}
			return $notices;
		}

		if ( 'create_promos' === $action || 'setup_defaults' === $action ) {
			if ( empty( $selected ) ) {
				$notices[] = array(
					'type'    => 'error',
					'message' => __( 'Select at least one card for promotions.', 'wc-gateway-payway' ),
				);
				return $notices;
			}

			$mipyme  = ! empty( $_POST['mipyme_installments'] );
			$periods = isset( $_POST['promo_periods'] )
				? WC_Payway_Default_Catalog::sanitize_credit_periods( wp_unslash( $_POST['promo_periods'] ), false )
				: WC_Payway_Default_Catalog::get_credit_periods();

			if ( empty( $periods ) ) {
				$notices[] = array(
					'type'    => 'error',
					'message' => __( 'Select at least one installment period.', 'wc-gateway-payway' ),
				);
				return $notices;
			}

			WC_Payway_Default_Catalog::ensure_default_bank();
			$cards_result = WC_Payway_Default_Catalog::add_cards( $selected );
			$promo_result = WC_Payway_Default_Catalog::create_default_promotions( $selected, $mipyme, $periods );

			$notices[] = array(
				'type'    => 'success',
				'message' => sprintf(
					/* translators: 1: cards created 2: promos created 3: promos skipped 4: periods list */
					__( 'Defaults: %1$d new cards, %2$d promotions created, %3$d promotions already existed (periods: %4$s).', 'wc-gateway-payway' ),
					count( $cards_result['created'] ),
					count( $promo_result['created'] ),
					count( $promo_result['skipped'] ),
					implode( '/', $periods )
				),
			);

			foreach ( array_merge( $cards_result['errors'], $promo_result['errors'] ) as $error ) {
				$notices[] = array(
					'type'    => 'error',
					'message' => $error,
				);
			}
		}

		return $notices;
	}
}
