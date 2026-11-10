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

$PAGE_KEY_NAME      = isset( $PAGE_KEY_NAME ) ? $PAGE_KEY_NAME : 'payway_admin_promotions';
$ACTION_NAME_NEW    = isset( $ACTION_NAME_NEW ) ? $ACTION_NAME_NEW : 'newpromotion';
$ACTION_NAME_EDIT   = isset( $ACTION_NAME_EDIT ) ? $ACTION_NAME_EDIT : 'editpromotion';
$ACTION_NAME_DELETE = isset( $ACTION_NAME_DELETE ) ? $ACTION_NAME_DELETE : 'deletepromotion';

if ( ! isset( $params ) || ! is_array( $params ) ) {
	$params = array(
		'action' => isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '',
	);
}
?>
<?php if ( empty( $params['action'] ) ) : ?>
<?php else : ?>
	<?php if ( $ACTION_NAME_NEW === $params['action']
		|| $ACTION_NAME_EDIT === $params['action'] ) : ?>
		<?php $is_edit = $ACTION_NAME_EDIT === $params['action']; ?>

		<div class="wrap-table-fee-plans" id="wrap-table-fee-plans">
			<h2><?php echo __( 'Fee Plans configuration', 'wc-gateway-payway' ); ?></h2>
			<p>
				<label>
					<input type="checkbox" id="payway-mipyme-toggle" value="1" />
					<?php esc_html_e( 'MiPyME installments (3 → send 13, 6 → send 16)', 'wc-gateway-payway' ); ?>
				</label>
			</p>
			<p class="description">
				<?php esc_html_e( 'Requires MiPyME to be enabled in Mi Payway and the merchant category (rubro / MCC) to be eligible. If either is missing, Decidir may reject those installment codes.', 'wc-gateway-payway' ); ?>
			</p>
			<table id="payway-admin-promotions-plans">
				<thead>
					<tr>
						<th><?php echo __( 'Period' , 'wc-gateway-payway' ); ?></th>
						<th><?php echo __( 'Coeficient' , 'wc-gateway-payway' ); ?></th>
						<th><?php echo __( 'TEA' , 'wc-gateway-payway' ); ?></th>
						<th><?php echo __( 'CFT' , 'wc-gateway-payway' ); ?></th>
						<th><?php echo __( 'Fee to send' , 'wc-gateway-payway' ); ?></th>
						<th><?php echo __( 'Delete' , 'wc-gateway-payway' ); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="6">
							<a href="#" id="fee-plans-add-new" title="<?php echo __( 'Add new plan', 'wc-gateway-payway' ); ?>">
								<?php echo __( 'Add new plan', 'wc-gateway-payway' ); ?>
							</a>
						</td>
					</tr>
				</tfoot>
				<tbody><!-- gets filled out with the script at the bottom --></tbody>
			</table>
		</div>
	<?php else: ?>
		<p>You shouldn't be here...</p>
	<?php endif; ?>
<?php endif; ?>
<script type="text/javascript">
jQuery( function( $ ) {
  	var payway_plan_wrapper_id = '#wrap-table-fee-plans',
		plan_add_new_row_identifier = '#fee-plans-add-new',
		plan_delete_row_identifier = '.plans-remove-row',
		mipymeMap = <?php echo wp_json_encode( WC_Payway_Default_Catalog::MIPYME_FEE_TO_SEND ); ?>;

    // Denotes total number of rows
    var row_id = 0,
		plans = <?php echo $promotion && $promotion->fee_plans != '' ? $promotion->fee_plans : json_encode([]); ?>;

	function add_plan_row( data ) {
		// append a new row into the Plans table
		$(payway_plan_wrapper_id + ' tbody').append(
			get_row_html( data )
		);
	}

	// builds the HTML for a new row (with/without pre-existing data)
	function get_row_html( data ) {
		var row_fee_period = data && data.fee_period || '',
			row_coefficient = data && data.coefficient || '',
			row_tea = data && data.tea || '',
			row_cft = data && data.cft || '',
			row_fee_to_send = data && data.fee_to_send || '';

		return `<tr id="plan-row-${row_id}">
			<td><input type="text" name="fee_plans[${row_id}][fee_period]"
				id="plan_${row_id}_fee_period" class="promotion-fee-plan-field field-fee-period" value="${row_fee_period}" /></td>
			<td><input type="text" name="fee_plans[${row_id}][coefficient]"
				id="plan_${row_id}_coefficient" class="promotion-fee-plan-field field-coefficient" value="${row_coefficient}" /></td>
			<td><input type="text" name="fee_plans[${row_id}][tea]"
				id="plan_${row_id}_tea" class="promotion-fee-plan-field field-tea" value="${row_tea}" /></td>
			<td><input type="text" name="fee_plans[${row_id}][cft]"
				id="plan_${row_id}_cft" class="promotion-fee-plan-field field-cft" value="${row_cft}"/></td>
			<td><input type="text" name="fee_plans[${row_id}][fee_to_send]"
				id="plan_${row_id}_fee_to_send" class="promotion-fee-plan-field field-fee-to-send" value="${row_fee_to_send}" /></td>
			<td>
				<a href="#" class="plans-remove-row" id="fee-plans-remove-row" title="<?php echo __( 'Delete', 'wc-gateway-payway' ); ?>">
					<?php echo __( 'Delete', 'wc-gateway-payway' ); ?>
				</a>
			</td>
		</tr>`;
	};

	function plansHaveMipyme( list ) {
		if ( ! list || ! list.length ) {
			return false;
		}
		for ( var i = 0; i < list.length; i++ ) {
			var period = parseInt( list[i].fee_period, 10 );
			var send = parseInt( list[i].fee_to_send, 10 );
			if ( mipymeMap[ period ] && mipymeMap[ period ] === send ) {
				return true;
			}
		}
		return false;
	}

	function applyMipymeToRows( enabled ) {
		$( payway_plan_wrapper_id + ' tbody tr' ).each( function () {
			var $row = $( this );
			var period = parseInt( $row.find( '.field-fee-period' ).val(), 10 );
			var $send = $row.find( '.field-fee-to-send' );
			if ( ! period || ! $send.length ) {
				return;
			}
			if ( enabled && mipymeMap[ period ] ) {
				$send.val( String( mipymeMap[ period ] ) );
			} else if ( ! enabled && mipymeMap[ period ] && parseInt( $send.val(), 10 ) === mipymeMap[ period ] ) {
				$send.val( String( period ) );
			}
		} );
	}

	// click event - add row
	$(payway_plan_wrapper_id + ' ' + plan_add_new_row_identifier)
		.on('click', function (e) {
			e.preventDefault();
			++row_id;
			$(payway_plan_wrapper_id + ' tbody').append(get_row_html());
			if ( $( '#payway-mipyme-toggle' ).is( ':checked' ) ) {
				applyMipymeToRows( true );
			}
		});

	// click event - remove row
	$(payway_plan_wrapper_id + ' tbody')
		.on('click', plan_delete_row_identifier, function (e) {
			e.preventDefault();
			$(this).closest('tr').remove();
		});

	$( '#payway-mipyme-toggle' ).on( 'change', function () {
		applyMipymeToRows( $( this ).is( ':checked' ) );
	} );

	if (plans) {
		$.each(plans, function (index, data) {
			row_id++;
			add_plan_row(data);
		});
		if ( plansHaveMipyme( plans ) ) {
			$( '#payway-mipyme-toggle' ).prop( 'checked', true );
		}
	} else {
		$('#wrap-table-fee-plans #fee-plans-add-new').trigger('click');
	}
});

</script>
