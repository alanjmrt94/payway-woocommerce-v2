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

/** @var array $plugin */
/** @var array $setup_checks */
/** @var array $setup_failures */

$title = __('Status Page', 'wc-gateway-payway');
?>
<div class="wrap payway-admin-status">
	<h1 class="wp-heading-inline">
		<?php echo esc_html( $title ); ?>
	</h1>
	<div class="notice notice-warning">
		<p><?php esc_html_e( 'This project is an independent development (Payway Payment Community). It is not an official Payway product and is not developed, maintained, endorsed, or certified by Payway.', 'wc-gateway-payway' ); ?></p>
		<p><?php esc_html_e( 'Fork author: Alan Martinez (alanjmrt94).', 'wc-gateway-payway' ); ?></p>
	</div>
	<div class="notice notice-info">
		<p><?php echo wp_kses_post( __( 'Logs are integrated with WooCommerce. Find them under <strong>WooCommerce / Status / Logs</strong>. Look for `payway-gateway-xxx.log` files.', 'wc-gateway-payway' ) ); ?></p>
		<p>
			<a class="button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-status&tab=logs' ) ); ?>"><?php esc_html_e( 'Go to WooCommerce logs', 'wc-gateway-payway' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=payway_admin_setup' ) ); ?>"><?php esc_html_e( 'Go to Setup', 'wc-gateway-payway' ); ?></a>
		</p>
	</div>

	<h2><?php esc_html_e( 'Availability diagnosis', 'wc-gateway-payway' ); ?></h2>
	<?php if ( empty( $setup_failures ) ) : ?>
		<div class="notice notice-success inline">
			<p><?php esc_html_e( 'No configuration blockers detected: the method should be able to appear on classic checkout (if the rest of the site is OK).', 'wc-gateway-payway' ); ?></p>
		</div>
	<?php else : ?>
		<div class="notice notice-error inline">
			<p><?php esc_html_e( 'There are issues that may prevent the plugin from appearing or working correctly:', 'wc-gateway-payway' ); ?></p>
			<ul style="list-style:disc;margin-left:1.5em">
				<?php foreach ( $setup_failures as $failure ) : ?>
					<li>
						<strong><?php echo esc_html( $failure['label'] ); ?>:</strong>
						<?php echo esc_html( $failure['message'] ); ?>
						<?php if ( ! empty( $failure['symptom'] ) ) : ?>
							<br><em><?php
							printf(
								/* translators: %s: shopper-facing WooCommerce message */
								esc_html__( 'Shopper symptom: %s', 'wc-gateway-payway' ),
								esc_html( $failure['symptom'] )
							);
							?></em>
						<?php endif; ?>
						<?php if ( ! empty( $failure['action_url'] ) ) : ?>
							— <a href="<?php echo esc_url( $failure['action_url'] ); ?>"><?php echo esc_html( $failure['action_label'] ); ?></a>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<table class="wc_status_table widefat payway-admin-status" cellspacing="0" id="payway-setup-status">
		<thead>
			<tr>
				<th colspan="4"><h2><?php esc_html_e( 'Configuration checklist', 'wc-gateway-payway' ); ?></h2></th>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Requirement', 'wc-gateway-payway' ); ?></th>
				<th><?php esc_html_e( 'Status', 'wc-gateway-payway' ); ?></th>
				<th><?php esc_html_e( 'Suggestion', 'wc-gateway-payway' ); ?></th>
				<th><?php esc_html_e( 'Action', 'wc-gateway-payway' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $setup_checks as $check ) : ?>
				<?php
				$is_ok      = ! empty( $check['ok'] );
				$is_blocking = ! empty( $check['blocking'] );
				if ( $is_ok ) {
					$mark_class = 'yes payway-ok';
					$mark_label = __( 'OK', 'wc-gateway-payway' );
				} elseif ( $is_blocking ) {
					$mark_class = 'error payway-error';
					$mark_label = __( 'Error', 'wc-gateway-payway' );
				} else {
					$mark_class = 'warning payway-warning';
					$mark_label = __( 'Warning', 'wc-gateway-payway' );
				}
				?>
				<tr>
					<td>
						<strong><?php echo esc_html( $check['label'] ); ?></strong>
						<?php if ( $is_blocking ) : ?>
							<br><small><?php esc_html_e( 'Required', 'wc-gateway-payway' ); ?></small>
						<?php else : ?>
							<br><small><?php esc_html_e( 'Recommended', 'wc-gateway-payway' ); ?></small>
						<?php endif; ?>
					</td>
					<td>
						<mark class="<?php echo esc_attr( $mark_class ); ?>">
							<span class="dashicons dashicons-<?php echo $is_ok ? 'yes' : 'warning'; ?>"></span>
							<?php echo esc_html( $mark_label ); ?>
						</mark>
						<?php echo esc_html( $check['message'] ); ?>
					</td>
					<td><?php echo ! empty( $check['proposed'] ) ? esc_html( $check['proposed'] ) : '—'; ?></td>
					<td>
						<?php if ( ! $is_ok && ! empty( $check['action_url'] ) ) : ?>
							<a href="<?php echo esc_url( $check['action_url'] ); ?>"><?php echo esc_html( $check['action_label'] ); ?></a>
						<?php else : ?>
							—
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

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
					? esc_html( (string) $plugin['version'] )
					: '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__('No version found', 'wc-gateway-payway') . '</mark>'; ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Payway SDK Version', 'wc-gateway-payway' ); ?>:</td>
				<td><?php echo $plugin['sdk_version']
					? esc_html( (string) $plugin['sdk_version'] )
					: '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__('No version found', 'wc-gateway-payway') . '</mark>'; ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Codebase Plugin Version', 'wc-gateway-payway' ); ?>:</td>
				<td><?php echo $plugin['cb_version']
					? esc_html( (string) $plugin['cb_version'] )
					: '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__('No version found', 'wc-gateway-payway') . '</mark>'; ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Codebase SDK Version', 'wc-gateway-payway' ); ?>:</td>
				<td><?php echo $plugin['cb_sdk_version']
					? esc_html( (string) $plugin['cb_sdk_version'] )
					: '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__('No version found', 'wc-gateway-payway') . '</mark>'; ?></td>
			</tr>
		</tbody>
	</table>
</div>
