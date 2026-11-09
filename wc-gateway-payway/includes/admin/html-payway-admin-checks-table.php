<?php
/**
 * Shared checklist table for Instalación / Status.
 *
 * @package wc-gateway-payway
 * @var array $rows
 */

defined( 'ABSPATH' ) || exit;
?>
<table class="widefat striped payway-admin-status" style="max-width:1100px;margin-bottom:1.5em">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Status', 'wc-gateway-payway' ); ?></th>
			<th><?php esc_html_e( 'Requirement', 'wc-gateway-payway' ); ?></th>
			<th><?php esc_html_e( 'Detail', 'wc-gateway-payway' ); ?></th>
			<th><?php esc_html_e( 'Suggestion', 'wc-gateway-payway' ); ?></th>
			<th><?php esc_html_e( 'Action', 'wc-gateway-payway' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( (array) $rows as $check ) : ?>
			<?php
			$is_ok      = ! empty( $check['ok'] );
			$is_blocking = ! empty( $check['blocking'] );
			if ( $is_ok ) {
				$mark_class = 'yes payway-ok';
				$mark_label = __( 'OK', 'wc-gateway-payway' );
				$mark_icon  = 'yes';
			} elseif ( $is_blocking ) {
				$mark_class = 'error payway-error';
				$mark_label = __( 'Error', 'wc-gateway-payway' );
				$mark_icon  = 'warning';
			} else {
				$mark_class = 'warning payway-warning';
				$mark_label = __( 'Warning', 'wc-gateway-payway' );
				$mark_icon  = 'warning';
			}
			?>
			<tr>
				<td>
					<mark class="<?php echo esc_attr( $mark_class ); ?>">
						<span class="dashicons dashicons-<?php echo esc_attr( $mark_icon ); ?>"></span>
						<?php echo esc_html( $mark_label ); ?>
					</mark>
				</td>
				<td><strong><?php echo esc_html( $check['label'] ); ?></strong></td>
				<td>
					<?php echo esc_html( $check['message'] ); ?>
					<?php if ( empty( $check['ok'] ) && ! empty( $check['symptom'] ) ) : ?>
						<br><em><?php
						printf(
							/* translators: %s: shopper-facing WooCommerce message */
							esc_html__( 'Shopper symptom: %s', 'wc-gateway-payway' ),
							esc_html( $check['symptom'] )
						);
						?></em>
					<?php endif; ?>
				</td>
				<td><?php echo ! empty( $check['proposed'] ) ? esc_html( $check['proposed'] ) : '—'; ?></td>
				<td>
					<?php if ( ! $is_ok && ! empty( $check['action_url'] ) ) : ?>
						<a class="button button-secondary" href="<?php echo esc_url( $check['action_url'] ); ?>">
							<?php echo esc_html( $check['action_label'] ); ?>
						</a>
					<?php else : ?>
						—
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
