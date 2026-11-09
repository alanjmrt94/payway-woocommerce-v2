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

/** @var array $checks */
/** @var array $catalog */
/** @var array $notices */
/** @var WC_Payway_Admin_Navigation_Page_Setup $page */

$blocking_checks    = array_filter(
	$checks,
	static function ( $c ) {
		return empty( $c['group'] ) || 'blocking' === $c['group'];
	}
);
$recommended_checks = array_filter(
	$checks,
	static function ( $c ) {
		return isset( $c['group'] ) && 'recommended' === $c['group'];
	}
);
?>
<div class="wrap payway-admin-status">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Setup', 'wc-gateway-payway' ); ?></h1>

	<?php foreach ( (array) $notices as $notice ) : ?>
		<div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> is-dismissible">
			<p><?php echo esc_html( $notice['message'] ); ?></p>
		</div>
	<?php endforeach; ?>

	<div class="notice notice-info">
		<p><?php esc_html_e( 'Checklist aligned with the operations manual: required items for the method to appear, plus recommended settings (Cybersource, title, Sandbox, debug). API keys are set only under WooCommerce → Payments.', 'wc-gateway-payway' ); ?></p>
	</div>

	<form method="post" action="" style="margin-bottom:1.5em">
		<?php wp_nonce_field( 'payway_admin_setup', '_wpnonce_payway_admin_setup' ); ?>
		<p>
			<button type="submit" class="button button-primary" name="payway_setup_action" value="apply_recommended">
				<?php esc_html_e( 'Apply recommended settings (WC + gateway)', 'wc-gateway-payway' ); ?>
			</button>
			<span class="description" style="margin-left:8px">
				<?php esc_html_e( 'Sets Enabled, Sandbox, Cybersource, Debug, default title/description, and decimals=2. Does not change Site Id or keys.', 'wc-gateway-payway' ); ?>
			</span>
		</p>
	</form>

	<h2><?php esc_html_e( 'Required checklist', 'wc-gateway-payway' ); ?></h2>
	<?php $page->render_checks_table( $blocking_checks ); ?>

	<h2><?php esc_html_e( 'Recommended (manual)', 'wc-gateway-payway' ); ?></h2>
	<?php $page->render_checks_table( $recommended_checks ); ?>

	<h2><?php esc_html_e( 'Quick steps', 'wc-gateway-payway' ); ?></h2>
	<ol style="max-width:1100px">
		<li><?php esc_html_e( 'WooCommerce active.', 'wc-gateway-payway' ); ?></li>
		<li><?php esc_html_e( 'WooCommerce → Settings → General: currency ARS/USD and Number of decimals = 2.', 'wc-gateway-payway' ); ?></li>
		<li><?php esc_html_e( 'WooCommerce → Payments → Payway: Enabled, Sandbox (testing), Cybersource, Debug, title/description, Site Id + Public + Private.', 'wc-gateway-payway' ); ?></li>
		<li><?php esc_html_e( 'Payway → bank “Todos” (or specific banks) + cards with id_sps + one active promotion per card with installment plans.', 'wc-gateway-payway' ); ?></li>
		<li><?php esc_html_e( 'Classic checkout or WooCommerce Blocks checkout (both supported).', 'wc-gateway-payway' ); ?></li>
		<li><?php esc_html_e( 'If something fails: Payway → Status.', 'wc-gateway-payway' ); ?></li>
	</ol>

	<h2><?php esc_html_e( 'Default catalog setup', 'wc-gateway-payway' ); ?></h2>
	<p><?php esc_html_e( 'Mini-assistant: pick cards and installment periods, then create one default promotion per selected card (active, no expiry, bank “Todos”). Debit/prepaid/Maestro always get 1 installment.', 'wc-gateway-payway' ); ?></p>

	<form method="post" action="" id="payway-setup-catalog-form">
		<?php wp_nonce_field( 'payway_admin_setup', '_wpnonce_payway_admin_setup' ); ?>

		<h3><?php esc_html_e( '1. Cards', 'wc-gateway-payway' ); ?></h3>
		<p>
			<button type="button" class="button button-small" id="payway-cards-select-all"><?php esc_html_e( 'Select all', 'wc-gateway-payway' ); ?></button>
			<button type="button" class="button button-small" id="payway-cards-select-none"><?php esc_html_e( 'Select none', 'wc-gateway-payway' ); ?></button>
		</p>
		<table class="widefat striped" style="max-width:1100px">
			<thead>
				<tr>
					<th style="width:40px"><?php esc_html_e( 'Use', 'wc-gateway-payway' ); ?></th>
					<th><?php esc_html_e( 'Card', 'wc-gateway-payway' ); ?></th>
					<th><?php esc_html_e( 'ID SPS', 'wc-gateway-payway' ); ?></th>
					<th><?php esc_html_e( 'Plans', 'wc-gateway-payway' ); ?></th>
					<th><?php esc_html_e( 'Note', 'wc-gateway-payway' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $catalog as $card ) : ?>
					<?php
					$exists = (bool) WC_Payway_Default_Catalog::find_card_by_sps( $card['id_sps'] );
					$plans  = ( WC_Payway_Default_Catalog::PLAN_SINGLE === $card['plan_type'] )
						? '1'
						: WC_Payway_Default_Catalog::get_credit_periods_label();
					?>
					<tr>
						<td>
							<input
								type="checkbox"
								class="payway-card-sps"
								name="card_sps[]"
								value="<?php echo esc_attr( (string) $card['id_sps'] ); ?>"
								<?php checked( $exists || (int) $card['id_sps'] === 1 ); ?>
							/>
						</td>
						<td>
							<?php echo esc_html( $card['name'] ); ?>
							<?php if ( $exists ) : ?>
								<em>(<?php esc_html_e( 'already added', 'wc-gateway-payway' ); ?>)</em>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( (string) $card['id_sps'] ); ?></td>
						<td><?php echo esc_html( $plans ); ?></td>
						<td><?php echo isset( $card['note'] ) ? esc_html( $card['note'] ) : '—'; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h3 style="margin-top:1.5em"><?php esc_html_e( '2. Installment periods (credit)', 'wc-gateway-payway' ); ?></h3>
		<p class="description" style="max-width:1100px">
			<?php esc_html_e( 'Each selected period becomes a fee plan inside the same promotion for that card. Debit/prepaid cards ignore this list and keep a single installment.', 'wc-gateway-payway' ); ?>
		</p>
		<p>
			<button type="button" class="button button-small" id="payway-periods-select-all"><?php esc_html_e( 'Select all', 'wc-gateway-payway' ); ?></button>
			<button type="button" class="button button-small" id="payway-periods-select-none"><?php esc_html_e( 'Select none', 'wc-gateway-payway' ); ?></button>
		</p>
		<p class="payway-period-checks" style="display:flex;flex-wrap:wrap;gap:12px 18px;max-width:1100px">
			<?php foreach ( WC_Payway_Default_Catalog::get_credit_periods() as $period ) : ?>
				<label>
					<input
						type="checkbox"
						class="payway-promo-period"
						name="promo_periods[]"
						value="<?php echo esc_attr( (string) $period ); ?>"
						checked="checked"
					/>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: installment count */
							__( '%d installment(s)', 'wc-gateway-payway' ),
							(int) $period
						)
					);
					?>
				</label>
			<?php endforeach; ?>
		</p>

		<h3 style="margin-top:1.5em"><?php esc_html_e( '3. MiPyME (optional)', 'wc-gateway-payway' ); ?></h3>
		<p style="max-width:1100px">
			<label>
				<input type="checkbox" name="mipyme_installments" value="1" />
				<?php esc_html_e( 'MiPyME installments (3 → send 13, 6 → send 16)', 'wc-gateway-payway' ); ?>
			</label>
		</p>
		<p class="description" style="max-width:1100px">
			<?php esc_html_e( 'Requires MiPyME to be enabled in Mi Payway and the merchant category (rubro / MCC) to be eligible. If either is missing, Decidir may reject those installment codes.', 'wc-gateway-payway' ); ?>
		</p>

		<p class="submit" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
			<button type="submit" class="button" name="payway_setup_action" value="ensure_bank">
				<?php esc_html_e( 'Create bank Todos', 'wc-gateway-payway' ); ?>
			</button>
			<button type="submit" class="button" name="payway_setup_action" value="add_cards">
				<?php esc_html_e( 'Add selected cards', 'wc-gateway-payway' ); ?>
			</button>
			<button type="submit" class="button button-primary" name="payway_setup_action" value="create_promos">
				<?php esc_html_e( 'Create promotions for selected cards', 'wc-gateway-payway' ); ?>
			</button>
			<button type="submit" class="button" name="payway_setup_action" value="setup_defaults">
				<?php esc_html_e( 'Add cards + promotions (full default)', 'wc-gateway-payway' ); ?>
			</button>
		</p>
	</form>
	<script type="text/javascript">
	jQuery(function ($) {
		function setChecked(selector, on) {
			$(selector).prop('checked', !!on);
		}
		$('#payway-cards-select-all').on('click', function () { setChecked('.payway-card-sps', true); });
		$('#payway-cards-select-none').on('click', function () { setChecked('.payway-card-sps', false); });
		$('#payway-periods-select-all').on('click', function () { setChecked('.payway-promo-period', true); });
		$('#payway-periods-select-none').on('click', function () { setChecked('.payway-promo-period', false); });
	});
	</script>
</div>
