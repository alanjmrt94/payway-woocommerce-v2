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

WC_Payway_Admin_Sanitize::require_manage_cap();

$PAGE_KEY_NAME      = 'payway_admin_cards';
$ACTION_NAME_NEW    = 'newcard';
$ACTION_NAME_EDIT   = 'editcard';
$ACTION_NAME_DELETE = 'deletecard';

$params = array(
	'action'  => isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '',
	'card_id' => isset( $_GET['card_id'] ) ? absint( $_GET['card_id'] ) : 0,
);

$is_creation = $params['action'] === $ACTION_NAME_NEW;

$title = $is_creation
	? esc_html__( 'Add New', 'wc-gateway-payway' )
	: esc_html__( 'Cards', 'wc-gateway-payway' );
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
			?>
			<?php if ( $post_action === $ACTION_NAME_NEW ) : ?>
				<?php check_admin_referer( 'payway-create-edit-card', '_wpnonce_payway-create-edit-card' ); ?>
				<?php
					$errors = new WP_Error();
					$data   = WC_Payway_Admin_Sanitize::card_from_post( wp_unslash( $_POST ) );
				if ( '' === $data['card_name'] ) {
					$errors->add( 'card_name', __( 'Name is a required field', 'wc-gateway-payway' ) );
				}
				if ( '' === $data['id_sps'] ) {
					$errors->add( 'id_sps', __( 'ID SPS is a required field', 'wc-gateway-payway' ) );
				}
				if ( '' === $data['id_nps'] ) {
					$errors->add( 'id_nps', __( 'ID NPS is a required field', 'wc-gateway-payway' ) );
				}
				?>
				<?php if ( ! $errors->has_errors() ) : ?>
					<?php $result = $this->create_card( $data ); ?>
					<?php if ( $result ) : ?>
						<?php $redirect = remove_query_arg( array( 'wp_http_referer', 'updated', 'action' ) ); ?>
						<?php wp_safe_redirect( $redirect ); ?>
						<?php exit; ?>
					<?php else : ?>
						<p><?php echo esc_html__( 'Some things happened during Card creation', 'wc-gateway-payway' ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $PAGE_KEY_NAME ) ); ?>"><?php echo esc_html__( 'Go back', 'wc-gateway-payway' ); ?></a>
					<?php endif; ?>
				<?php else : ?>
					<div class="error">
						<ul>
						<?php foreach ( $errors->get_error_messages() as $error_message ) : ?>
							<li><?php echo esc_html( $error_message ); ?></li>
						<?php endforeach; ?>
						</ul>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $PAGE_KEY_NAME . '&action=' . $ACTION_NAME_NEW ) ); ?>"><?php echo esc_html__( 'Go back', 'wc-gateway-payway' ); ?></a>
				<?php endif; ?>
			<?php elseif ( $post_action === $ACTION_NAME_EDIT ) : ?>
				<?php check_admin_referer( 'payway-create-edit-card', '_wpnonce_payway-create-edit-card' ); ?>
				<?php
					$data   = WC_Payway_Admin_Sanitize::card_update_from_post( wp_unslash( $_POST ) );
					$result = $this->update_card( $data );
				?>
				<?php if ( $result ) : ?>
					<?php $redirect = remove_query_arg( array( 'wp_http_referer', 'updated', 'action' ) ); ?>
					<?php wp_safe_redirect( $redirect ); ?>
					<?php exit; ?>
				<?php else : ?>
					<p><?php echo esc_html__( 'Some things happened while updating the Card', 'wc-gateway-payway' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $PAGE_KEY_NAME ) ); ?>"><?php echo esc_html__( 'Go back', 'wc-gateway-payway' ); ?></a>
				<?php endif; ?>
			<?php else : ?>
			<p><?php echo esc_html__( 'Unhandled action.', 'wc-gateway-payway' ); ?></p>
			<?php endif; ?>
		<?php elseif ( $ACTION_NAME_NEW === $params['action']
			|| $ACTION_NAME_EDIT === $params['action'] ) : ?>
				<?php $is_edit = $ACTION_NAME_EDIT === $params['action']; ?>
				<?php $card_id = $params['card_id']; ?>
				<?php $card = $card_id ? $this->get_card( $card_id ) : false; ?>
				<?php if ( $is_edit && ! $card ) : ?>
					<?php $errors = new WP_Error(); ?>
					<?php $errors->add( 'card_id', __( 'Card does not exist', 'wc-gateway-payway' ) ); ?>
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
						<input name="card_id" type="hidden" value="<?php echo esc_attr( (string) $card_id ); ?>" />
					<?php endif; ?>
					<?php wp_nonce_field( 'payway-create-edit-card', '_wpnonce_payway-create-edit-card' ); ?>
					<?php
						$card_name = $is_edit ? $card->card_name : '';
						$id_sps    = $is_edit ? $card->id_sps : '';
						$id_nps    = $is_edit ? $card->id_nps : '';
					?>
					<table class="form-table" role="presentation">
						<tr class="form-field form-required">
							<th scope="row"><label for="card_name">
								<?php esc_html_e( 'Card Name', 'wc-gateway-payway' ); ?> <span class="description"><?php esc_html_e( '(required)', 'wc-gateway-payway' ); ?></span></label>
							</th>
							<td>
								<input name="card_name" type="text" id="card_name" value="<?php echo esc_attr( $card_name ); ?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="100" />
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row"><label for="id_sps">
								<?php esc_html_e( 'ID SPS', 'wc-gateway-payway' ); ?> <span class="description"><?php esc_html_e( '(required)', 'wc-gateway-payway' ); ?></span></label>
							</th>
							<td>
								<input name="id_sps" type="text" id="id_sps" value="<?php echo esc_attr( $id_sps ); ?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="100" />
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row"><label for="id_nps">
								<?php esc_html_e( 'ID NPS', 'wc-gateway-payway' ); ?> <span class="description"><?php esc_html_e( '(required)', 'wc-gateway-payway' ); ?></span></label>
							</th>
							<td>
								<input name="id_nps" type="text" id="id_nps" value="<?php echo esc_attr( $id_nps ); ?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="100" />
							</td>
						</tr>
					</table>
					<div>
						<?php if ( $is_edit ) : ?>
							<?php submit_button( __( 'Save', 'wc-gateway-payway' ), 'primary', 'editbank', true, array( 'id' => 'editcardsub' ) ); ?>
						<?php else : ?>
							<?php submit_button( __( 'Add', 'wc-gateway-payway' ), 'primary', 'createbank', true, array( 'id' => 'newcardsub' ) ); ?>
						<?php endif; ?>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $PAGE_KEY_NAME ) ); ?>"><?php echo esc_html__( 'Go back', 'wc-gateway-payway' ); ?></a>
				</form>
		<?php elseif ( $ACTION_NAME_DELETE === $params['action'] ) : ?>
			<?php
			WC_Payway_Admin_Sanitize::require_manage_cap();
			$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
			if ( ! $nonce || ! wp_verify_nonce( $nonce, '_wpnonce_payway-delete-card-' . $params['card_id'] ) ) {
				wp_die( esc_html__( 'Invalid nonce. The card was not deleted.', 'wc-gateway-payway' ) );
			}
			$this->delete_card( $params['card_id'] );
			$redirect = remove_query_arg( array( 'action', 'card_id', '_wpnonce' ) );
			wp_safe_redirect( $redirect );
			exit;
			?>
		<?php else : ?>
			<p><?php echo esc_html__( 'You should not be here.', 'wc-gateway-payway' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>
</div>
