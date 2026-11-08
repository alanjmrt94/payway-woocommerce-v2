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

$title = __('Status Page', 'wc-gateway-payway');
?>
<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php echo esc_html( $title ); ?>
	</h1>
	<div class="notice notice-warning">
		<p><?php esc_html_e( 'Este proyecto es un desarrollo independiente (Payway Payment Community). No es un producto oficial de Payway, no está desarrollado, mantenido, respaldado ni certificado por Payway.', 'wc-gateway-payway' ); ?></p>
		<p><?php esc_html_e( 'Autor del fork: Alan Martinez (alanjmrt94).', 'wc-gateway-payway' ); ?></p>
	</div>
	<div class="notice notice-info">
		<p><?php echo wp_kses_post( __( 'Los logs están integrados con WooCommerce. Vas a encontrarlos en <strong>WooCommerce / Estado / Registros</strong>. Buscá archivos `payway-gateway-xxx.log`.', 'wc-gateway-payway' ) ); ?></p>
		<p>
			<a class="button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-status&tab=logs' ) ); ?>"><?php esc_html_e( 'Ir a logs de WooCommerce', 'wc-gateway-payway' ); ?></a>
		</p>
	</div>
	<table class="wc_status_table widefat" cellspacing="0" id="status">
		<thead>
			<tr>
				<th colspan="2"><h2><?php esc_html_e( 'Plugin Information', 'wc-gateway-payway' ); ?></h2></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php esc_html_e( 'Plugin Version', 'wc-gateway-payway' ); ?>:</td>
				<td><?php echo $plugin['version']
					? $plugin['version']
					: '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __('No version found', 'wc-gateway-payway') . '</mark>'; ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Payway SDK Version', 'wc-gateway-payway' ); ?>:</td>
				<td><?php echo $plugin['sdk_version']
					? $plugin['sdk_version']
					: '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __('No version found', 'wc-gateway-payway') . '</mark>'; ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Codebase Plugin Version', 'wc-gateway-payway' ); ?>:</td>
				<td><?php echo $plugin['cb_version']
					? $plugin['cb_version']
					: '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __('No version found', 'wc-gateway-payway') . '</mark>'; ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Codebase SDK Version', 'wc-gateway-payway' ); ?>:</td>
				<td><?php echo $plugin['cb_sdk_version']
					? $plugin['cb_sdk_version']
					: '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __('No version found', 'wc-gateway-payway') . '</mark>'; ?></td>
			</tr>
		</tbody>
	</table>
</div>
