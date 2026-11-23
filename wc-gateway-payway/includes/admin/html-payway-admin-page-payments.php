<?php
/**
 * Template: Recent payments list.
 *
 * @package wc-gateway-payway
 * @license GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

/** @var string $environment */
/** @var int $page */
/** @var int $size */
/** @var array $list_result */
/** @var string $payments_url */

$base_url = admin_url( 'admin.php?page=payway_admin_payments' );
?>
<div class="wrap payway-admin-payments">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Recent Payments', 'wc-gateway-payway' ); ?></h1>

	<form method="get" action="" class="payway-payments-filters" style="margin:1.5em 0;display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
		<input type="hidden" name="page" value="payway_admin_payments" />
		<p>
			<label for="payway_env"><strong><?php esc_html_e( 'Environment', 'wc-gateway-payway' ); ?></strong></label><br />
			<select name="payway_env" id="payway_env">
				<option value="<?php echo esc_attr( WC_Payway_Payment_List::ENV_SANDBOX ); ?>" <?php selected( $environment, WC_Payway_Payment_List::ENV_SANDBOX ); ?>>
					<?php esc_html_e( 'Sandbox', 'wc-gateway-payway' ); ?>
				</option>
				<option value="<?php echo esc_attr( WC_Payway_Payment_List::ENV_PRODUCTION ); ?>" <?php selected( $environment, WC_Payway_Payment_List::ENV_PRODUCTION ); ?>>
					<?php esc_html_e( 'Production', 'wc-gateway-payway' ); ?>
				</option>
			</select>
		</p>
		<p>
			<label for="payway_page"><strong><?php esc_html_e( 'Page', 'wc-gateway-payway' ); ?></strong></label><br />
			<input type="number" min="0" step="1" name="payway_page" id="payway_page" value="<?php echo esc_attr( (string) $page ); ?>" class="small-text" />
		</p>
		<p>
			<label for="payway_size"><strong><?php esc_html_e( 'Page size', 'wc-gateway-payway' ); ?></strong></label><br />
			<input type="number" min="1" max="<?php echo esc_attr( (string) WC_Payway_Payment_List::MAX_PAGE_SIZE ); ?>" step="1" name="payway_size" id="payway_size" value="<?php echo esc_attr( (string) $size ); ?>" class="small-text" />
		</p>
		<p>
			<?php submit_button( __( 'Apply', 'wc-gateway-payway' ), 'secondary', '', false ); ?>
		</p>
	</form>

	<?php if ( ! WC_Payway_Payment_List::environment_has_credentials( $environment ) ) : ?>
		<div class="notice notice-warning inline">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: WooCommerce Payments settings URL */
						__( 'No API keys configured for this environment. Add them under <a href="%s">WooCommerce → Payments → Payway</a>.', 'wc-gateway-payway' ),
						esc_url( $payments_url )
					)
				);
				?>
			</p>
		</div>
	<?php elseif ( ! $list_result['success'] ) : ?>
		<div class="notice notice-error inline">
			<p><?php echo esc_html( $list_result['error_message'] ); ?></p>
		</div>
	<?php elseif ( empty( $list_result['results'] ) ) : ?>
		<div class="notice notice-info inline">
			<p><?php esc_html_e( 'No payments returned for the selected filters.', 'wc-gateway-payway' ); ?></p>
		</div>
	<?php else : ?>
		<table class="widefat striped payway-payments-table" style="max-width:1400px">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'wc-gateway-payway' ); ?></th>
					<th><?php esc_html_e( 'Site transaction ID', 'wc-gateway-payway' ); ?></th>
					<th><?php esc_html_e( 'Status', 'wc-gateway-payway' ); ?></th>
					<th><?php esc_html_e( 'Amount', 'wc-gateway-payway' ); ?></th>
					<th><?php esc_html_e( 'Currency', 'wc-gateway-payway' ); ?></th>
					<th><?php esc_html_e( 'Date', 'wc-gateway-payway' ); ?></th>
					<th><?php esc_html_e( 'Installments', 'wc-gateway-payway' ); ?></th>
					<th><?php esc_html_e( 'WooCommerce order', 'wc-gateway-payway' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $list_result['results'] as $row ) : ?>
					<?php
					$id        = WC_Payway_Payment_List::payment_field( $row, 'id' );
					$site_tx   = WC_Payway_Payment_List::payment_field( $row, 'site_transaction_id' );
					$status    = WC_Payway_Payment_List::payment_field( $row, 'status' );
					$amount    = WC_Payway_Payment_List::payment_field( $row, 'amount' );
					$currency  = WC_Payway_Payment_List::payment_field( $row, 'currency' );
					$date      = WC_Payway_Payment_List::payment_field( $row, 'date' );
					if ( empty( $date ) ) {
						$date = WC_Payway_Payment_List::payment_field( $row, 'payment_date' );
					}
					$installments = WC_Payway_Payment_List::payment_field( $row, 'installments' );
					$order_id     = WC_Payway_Payment_List::find_wc_order_id( $id, $site_tx );
					?>
					<tr>
						<td><?php echo esc_html( (string) $id ); ?></td>
						<td><?php echo esc_html( (string) $site_tx ); ?></td>
						<td><?php echo esc_html( (string) $status ); ?></td>
						<td><?php echo esc_html( (string) $amount ); ?></td>
						<td><?php echo esc_html( (string) $currency ); ?></td>
						<td><?php echo esc_html( (string) $date ); ?></td>
						<td><?php echo esc_html( (string) $installments ); ?></td>
						<td>
							<?php if ( $order_id ) : ?>
								<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) ); ?>">
									<?php
									printf(
										/* translators: %d: WooCommerce order ID */
										esc_html__( 'Order #%d', 'wc-gateway-payway' ),
										(int) $order_id
									);
									?>
								</a>
							<?php else : ?>
								—
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		$prev = max( 0, $page - 1 );
		$next = $page + 1;
		$nav_base = add_query_arg(
			array(
				'payway_env'  => $environment,
				'payway_size' => $size,
			),
			$base_url
		);
		?>
		<p class="tablenav" style="margin-top:1em">
			<?php if ( $page > 0 ) : ?>
				<a class="button" href="<?php echo esc_url( add_query_arg( 'payway_page', $prev, $nav_base ) ); ?>"><?php esc_html_e( '← Previous page', 'wc-gateway-payway' ); ?></a>
			<?php endif; ?>
			<?php if ( ! empty( $list_result['has_more'] ) ) : ?>
				<a class="button" href="<?php echo esc_url( add_query_arg( 'payway_page', $next, $nav_base ) ); ?>"><?php esc_html_e( 'Next page →', 'wc-gateway-payway' ); ?></a>
			<?php endif; ?>
		</p>
	<?php endif; ?>
</div>