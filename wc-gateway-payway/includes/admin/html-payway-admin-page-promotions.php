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

WC_Payway_Admin_Sanitize::require_manage_cap();

$PAGE_KEY_NAME      = 'payway_admin_promotions';
$ACTION_NAME_NEW    = 'newpromotion';
$ACTION_NAME_EDIT   = 'editpromotion';
$ACTION_NAME_DELETE = 'deletepromotion';

$params = array(
	'action'        => isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '',
	'promotion_id'  => isset( $_GET['promotion_id'] ) ? absint( $_GET['promotion_id'] ) : 0,
);

$is_creation = $params['action'] === $ACTION_NAME_NEW;

// Page Title
$title = $is_creation
	? esc_html__('Add New', 'wc-gateway-payway')
	: esc_html__('Promotions', 'wc-gateway-payway');
?>
<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php echo $title; ?>
	</h1>
	<?php if ( '' === $params['action'] ) : ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $PAGE_KEY_NAME . '&action=' . $ACTION_NAME_NEW ) ); ?>" class="page-title-action">
			<?php echo esc_html__( 'Add New', 'wc-gateway-payway' ); ?>
		</a>
		<?php echo $this->get_table(); ?>
	<?php else : ?>
		<?php if ( isset( $_POST['action'] ) ) : ?>
			<?php
			WC_Payway_Admin_Sanitize::require_manage_cap();
			$post_action = sanitize_key( wp_unslash( $_POST['action'] ) );
			$sanitized   = WC_Payway_Admin_Sanitize::promotion_from_post( wp_unslash( $_POST ) );
			?>
			<?php if ( $post_action === $ACTION_NAME_NEW ) : ?>
				<?php check_admin_referer( 'payway-create-edit-promotion', '_wpnonce_payway-create-edit-promotion' ); ?>
				<?php
					/** @var WP_Error|null */
					$validation_result = $this->validate_promotion_data( $sanitized );
				?>
				<?php if ( ! $validation_result || ( ! is_wp_error( $validation_result ) && ! $validation_result->has_errors() ) ) : ?>
					<?php $result = $this->create_promotion( $sanitized ); ?>
					<?php if ( $result ) : ?>
						<?php $redirect = remove_query_arg( array( 'wp_http_referer', 'updated', 'action' ) ); ?>
						<?php wp_safe_redirect( $redirect ); ?>
						<?php exit; ?>
					<?php else : ?>
						<p><?php echo esc_html__( 'Some things happened during Promotion creation', 'wc-gateway-payway' ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $PAGE_KEY_NAME ) ); ?>"><?php echo esc_html__( 'Go back', 'wc-gateway-payway' ); ?></a>
						<?php return; ?>
					<?php endif; ?>
				<?php else : ?>
					<div class="error">
						<ul>
						<?php foreach ( $validation_result->get_error_messages() as $error_message ) : ?>
							<li><?php echo esc_html( $error_message ); ?></li>
						<?php endforeach; ?>
						</ul>
					</div>
					<?php return; ?>
				<?php endif; ?>
			<?php elseif ( $post_action === $ACTION_NAME_EDIT ) : ?>
				<?php check_admin_referer( 'payway-create-edit-promotion', '_wpnonce_payway-create-edit-promotion' ); ?>
				<?php
					/** @var WP_Error|null */
					$validation_result = $this->validate_promotion_data( $sanitized );
				?>
				<?php if ( ! $validation_result || ( ! is_wp_error( $validation_result ) && ! $validation_result->has_errors() ) ) : ?>
					<?php $result = $this->update_promotion( $sanitized ); ?>
					<?php if ( $result ) : ?>
						<?php $redirect = remove_query_arg( array( 'wp_http_referer', 'updated', 'action' ) ); ?>
						<?php wp_safe_redirect( $redirect ); ?>
						<?php exit; ?>
					<?php else : ?>
						<p><?php echo esc_html__( 'Ensure all required fields are being filled with valid information', 'wc-gateway-payway' ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $PAGE_KEY_NAME ) ); ?>"><?php echo esc_html__( 'Go back', 'wc-gateway-payway' ); ?></a>
					<?php endif; ?>
					<?php return; ?>
				<?php else : ?>
					<div class="error">
						<ul>
						<?php foreach ( $validation_result->get_error_messages() as $error_message ) : ?>
							<li><?php echo esc_html( $error_message ); ?></li>
						<?php endforeach; ?>
						</ul>
					</div>
					<?php return; ?>
				<?php endif; ?>
			<?php else : ?>
			<!-- If you ended up here, means someone is playing around with params... -->
			<?php endif; ?>
		<?php elseif ( $ACTION_NAME_NEW === $params['action']
			|| $ACTION_NAME_EDIT === $params['action'] ) : ?>
				<?php $is_edit = $ACTION_NAME_EDIT === $params['action']; ?>
				<?php $promotion_id = $params['promotion_id']; ?>
				<?php $promotion = $promotion_id ? $this->get_promotion( $promotion_id ) : false; ?>
				<?php if ( $is_edit && ! $promotion ) : ?>
					<?php $errors = new WP_Error(); ?>
					<?php $errors->add( 'promotion_id', __( 'Promotion does not exist', 'wc-gateway-payway' ) ); ?>
					<div class="error">
						<ul>
						<?php foreach ( $errors->get_error_messages() as $err ) : ?>
							<li><?php echo esc_html( $err ); ?></li>
						<?php endforeach; ?>
						</ul>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $PAGE_KEY_NAME ) ); ?>"><?php echo esc_html__( 'Go back', 'wc-gateway-payway' ); ?></a>
					<?php return; ?>
				<?php endif; ?>
				<form method="post" name="<?php echo esc_attr( $params['action'] ); ?>" id="<?php echo esc_attr( $params['action'] ); ?>" class="validate" novalidate="novalidate">
					<input name="action" type="hidden" value="<?php echo esc_attr( $params['action'] ); ?>" />
					<?php if ( $is_edit ) : ?>
						<input name="promotion_id" type="hidden" value="<?php echo esc_attr( (string) $promotion_id ); ?>" />
					<?php endif; ?>
					<?php wp_nonce_field( 'payway-create-edit-promotion', '_wpnonce_payway-create-edit-promotion' ); ?>
					<?php
						// Load up the passed data, else set to a default.
						$rule_name = $is_edit ? $promotion->rule_name : '';
						$bank_id = $is_edit ? $promotion->bank_id : '';
						$card_id = $is_edit ? $promotion->card_id : '';
						$is_active = $is_edit ? (int) $promotion->is_active : 1;
						$no_expiry = $is_edit
							? (int) ( isset( $promotion->no_expiry ) ? $promotion->no_expiry : 1 )
							: 1;
						$from_date = $is_edit ? $promotion->from_date : '';
						$to_date = $is_edit ? $promotion->to_date : '';
						$priority = $is_edit ? $promotion->priority : '';
						$applicable_days = $is_edit
							? (strpos($promotion->applicable_days, ',') !== false)
								? explode( ',', $promotion->applicable_days )
								: $promotion->applicable_days
							: '';
					?>
					<table class="form-table" role="presentation">
						<tr class="form-field form-required">
							<th scope="row"><label for="rule_name">
								<?php _e( 'Promotion Name', 'wc-gateway-payway' ); ?> <span class="description"><?php echo __( '(required)', 'wc-gateway-payway' ); ?></span></label>
							</th>
							<td>
								<input name="rule_name" type="text" id="rule_name" value="<?php echo esc_attr( $rule_name ); ?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="100" />
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row"><label for="is_active">
								<?php _e( 'Is Active', 'wc-gateway-payway' ); ?></label>
							</th>
							<td>
								<select name="is_active" type="text" id="is_active">
									<option value="1" <?php echo $is_active === 1 ? 'selected' : '' ?>><?php echo __( 'Enabled', 'wc-gateway-payway' ) ?></option>
									<option value="0" <?php echo $is_active === 0 ? 'selected' : '' ?>><?php echo __( 'Disabled', 'wc-gateway-payway' ) ?></option>
								</select>
							</td>
						</tr>
						<tr class="form-field">
							<th scope="row"><label for="no_expiry">
								<?php esc_html_e( 'No expiry', 'wc-gateway-payway' ); ?></label>
							</th>
							<td>
								<label>
									<input type="checkbox" name="no_expiry" id="no_expiry" value="1" <?php checked( $no_expiry, 1 ); ?> />
									<?php esc_html_e( 'Do not validate end date (recommended for installment plans).', 'wc-gateway-payway' ); ?>
								</label>
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row"><label for="bank_id">
								<?php _e( 'Bank', 'wc-gateway-payway' ); ?> <span class="description"><?php echo __( '(required)', 'wc-gateway-payway' ); ?></span></label>
							</th>
							<td>
								<select name="bank_id" type="text" id="bank_id" aria-required="true">
									<?php foreach ($this->get_banks() as $bank): ?>
										<option value="<?php echo $bank['value']; ?>" <?php echo $bank_id === $bank['value'] ? 'selected' : '' ?>><?php echo $bank['label']; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row"><label for="card_id">
								<?php _e( 'Card', 'wc-gateway-payway' ); ?> <span class="description"><?php echo __( '(required)', 'wc-gateway-payway' ); ?></span></label>
							</th>
							<td>
								<select name="card_id" type="text" id="card_id" aria-required="true">
									<?php foreach ($this->get_cards() as $card): ?>
										<option value="<?php echo $card['value']; ?>" <?php echo $card_id === $card['value'] ? 'selected' : '' ?>><?php echo $card['label']; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row"><label for="from_date">
								<?php _e( 'From Date', 'wc-gateway-payway' ); ?> <span class="description"><?php echo __( '(required)', 'wc-gateway-payway' ); ?></span></label>
							</th>
							<td>
								<input type="datetime-local" name="from_date" id="from_date" value="<?php echo esc_attr( $from_date ); ?>" />
							</td>
						</tr>
						<tr class="form-field form-required" id="payway-to-date-row" <?php echo $no_expiry ? 'style="display:none"' : ''; ?>>
							<th scope="row"><label for="to_date">
								<?php _e( 'To Date', 'wc-gateway-payway' ); ?> <span class="description"><?php echo __( '(required when expiry is enabled)', 'wc-gateway-payway' ); ?></span></label>
							</th>
							<td>
								<input type="datetime-local" name="to_date" id="to_date" value="<?php echo esc_attr( $to_date ); ?>"/>
							</td>
						</tr>
						<script type="text/javascript">
						jQuery(function ($) {
							$('#no_expiry').on('change', function () {
								$('#payway-to-date-row').toggle(!this.checked);
							});
						});
						</script>
						<tr class="form-field form-required">
							<th scope="row"><label for="priority">
								<?php _e( 'Priority', 'wc-gateway-payway' ); ?></label>
							</th>
							<td>
								<input name="priority" type="text" id="priority" value="<?php echo esc_attr( $priority ); ?>" maxlength="2" />
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row"><label for="applicable_days">
								<?php _e( 'Applicable days', 'wc-gateway-payway' ); ?> <span class="description"><?php echo __( '(required)', 'wc-gateway-payway' ); ?></span></label>
							</th>
							<td>
								<select multiple="true" name="applicable_days[]" id="applicable_days" aria-required="true">
									<?php foreach ($this->get_weekdays_for_element() as $day): ?>
										<option value="<?php echo $day['value']; ?>" <?php echo $this->is_day_in_promotion( $day['value'], $promotion ) ? 'selected' : '' ?>><?php echo $day['label']; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>

					<?php require_once( WC_PAYWAY_ABSPATH . 'includes/admin/html-payway-admin-page-promo-plans.php' ); ?>

					<div>
						<?php if ( $is_edit ): ?>
							<?php submit_button( __( 'Save', 'wc-gateway-payway' ), 'primary', 'editbank', true, array( 'id' => 'editcardsub' ) ); ?>
						<?php else: ?>
							<?php submit_button( __( 'Add', 'wc-gateway-payway' ), 'primary', 'createbank', true, array( 'id' => 'newcardsub' ) ); ?>
						<?php endif; ?>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $PAGE_KEY_NAME ) ); ?>"><?php echo __('Go back', 'wc-gateway-payway'); ?></a>
				</form>
		<?php elseif ( $ACTION_NAME_DELETE === $params['action'] ) : ?>
			<?php
			WC_Payway_Admin_Sanitize::require_manage_cap();
			$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
			if ( ! $nonce || ! wp_verify_nonce( $nonce, '_wpnonce_payway-delete-promotion-' . $params['promotion_id'] ) ) {
				wp_die( esc_html__( 'Invalid nonce. The promotion was not deleted.', 'wc-gateway-payway' ) );
			}
			$this->delete_promotion( $params['promotion_id'] );
			$redirect = remove_query_arg( array( 'action', 'promotion_id', '_wpnonce' ) );
			wp_safe_redirect( $redirect );
			exit;
			?>
		<?php else : ?>
			<!-- If you ended up here, means someone is playing around with params... -->
		<?php endif; ?>
	<?php endif; ?>
</div>
