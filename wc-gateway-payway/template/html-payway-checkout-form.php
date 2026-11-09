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

$gateway_identifier = $this->id;
$gateway_field_id   = $gateway_identifier . '_';
$description        = $this->get_option( 'description' );

?>
<fieldset id="<?php echo esc_attr( $gateway_identifier ); ?>-cc-form" class="wc-gateway-payway">
	<?php if ( ! empty( $description ) ) : ?>
		<hr style="border: 1px solid #ddd;">
		<p class="fieldset-description">
			<?php echo wp_kses_post( $description ); ?>
		</p>
		<hr style="border: 1px solid #ddd; margin: 20px 0;">
	<?php endif; ?>
	<div id="error_container"></div>
	<input type="hidden"
		id="<?php echo esc_attr( $gateway_field_id ); ?>cart_total"
		name="<?php echo esc_attr( $gateway_field_id ); ?>cart_total"
		value="<?php echo esc_attr( $this->get_order_total() ); ?>"
		data-payway-cart-total="<?php echo esc_attr( $this->get_order_total() ); ?>" />
	<div class="fields-wrapper">
		<div class="form-row validate-required">
			<label for="<?php echo esc_attr( $gateway_field_id ); ?>cc_bank"><?php echo esc_html__( 'Bank', 'wc-gateway-payway' ); ?> <span class="required">*</span></label>
			<select id="<?php echo esc_attr( $gateway_field_id ); ?>cc_bank"
				class="input-text wc-credit-card-form-cc-bank"
				name="<?php echo esc_attr( $gateway_field_id ); ?>cc_bank">
				<option value=""><?php echo esc_html__( 'Please select...', 'wc-gateway-payway' ); ?></option>
			</select>
		</div>
		<div class="form-row validate-required">
			<label for="<?php echo esc_attr( $gateway_field_id ); ?>cc_type"><?php echo esc_html__( 'Card', 'wc-gateway-payway' ); ?> <span class="required">*</span></label>
			<select id="<?php echo esc_attr( $gateway_field_id ); ?>cc_type"
				class="input-text wc-credit-card-form-cc-type"
				name="<?php echo esc_attr( $gateway_field_id ); ?>cc_type">
				<option value=""><?php echo esc_html__( 'Please select...', 'wc-gateway-payway' ); ?></option>
			</select>
		</div>
		<div class="form-row validate-required">
			<label for="<?php echo esc_attr( $gateway_field_id ); ?>cc_installments"><?php echo esc_html__( 'Installments', 'wc-gateway-payway' ); ?> <span class="required">*</span></label>
			<select id="<?php echo esc_attr( $gateway_field_id ); ?>cc_installments"
				class="input-text wc-credit-card-form-cc-installments"
				name="<?php echo esc_attr( $gateway_field_id ); ?>cc_installments">
				<option value=""><?php echo esc_html__( 'Please select...', 'wc-gateway-payway' ); ?></option>
			</select>
		</div>

		<div class="form-row validate-required">
			<label for="<?php echo esc_attr( $gateway_field_id ); ?>cc_number"><?php echo esc_html__( 'Credit Card Number', 'wc-gateway-payway' ); ?> <span class="required">*</span></label>
			<input type="text"
				id="<?php echo esc_attr( $gateway_field_id ); ?>cc_number"
				name="<?php echo esc_attr( $gateway_field_id ); ?>cc_number"
				placeholder="<?php echo esc_attr__( 'Credit Card Number', 'wc-gateway-payway' ); ?>"
				class="input-text"
				autocomplete="off" />
		</div>

		<div class="form-row validate-required">
			<label for="<?php echo esc_attr( $gateway_field_id ); ?>cc_exp_month"><?php echo esc_html__( 'Card Expiration Month', 'wc-gateway-payway' ); ?> <span class="required">*</span></label>
			<input type="text"
				id="<?php echo esc_attr( $gateway_field_id ); ?>cc_exp_month"
				name="<?php echo esc_attr( $gateway_field_id ); ?>cc_exp_month"
				placeholder="MM"
				class="input-text"
				autocomplete="off" />
		</div>
		<div class="form-row validate-required">
			<label for="<?php echo esc_attr( $gateway_field_id ); ?>cc_exp_year"><?php echo esc_html__( 'Card Expiration Year', 'wc-gateway-payway' ); ?> <span class="required">*</span></label>
			<input type="text"
				id="<?php echo esc_attr( $gateway_field_id ); ?>cc_exp_year"
				name="<?php echo esc_attr( $gateway_field_id ); ?>cc_exp_year"
				placeholder="YY"
				class="input-text"
				autocomplete="off" />
		</div>
		<div class="form-row validate-required">
			<label for="<?php echo esc_attr( $gateway_field_id ); ?>cc_cid">CVV: <span class="required">*</span></label>
			<input type="text"
				id="<?php echo esc_attr( $gateway_field_id ); ?>cc_cid"
				name="<?php echo esc_attr( $gateway_field_id ); ?>cc_cid"
				placeholder="CVV"
				class="input-text"
				autocomplete="off" />
		</div>

		<div class="form-row validate-required">
			<label for="<?php echo esc_attr( $gateway_field_id ); ?>cc_holder_name"><?php echo esc_html__( 'Holder Name', 'wc-gateway-payway' ); ?> <span class="required">*</span></label>
			<input type="text"
				id="<?php echo esc_attr( $gateway_field_id ); ?>cc_holder_name"
				name="<?php echo esc_attr( $gateway_field_id ); ?>cc_holder_name"
				placeholder="<?php echo esc_attr__( 'Holder Name', 'wc-gateway-payway' ); ?>"
				class="input-text"
				autocomplete="off" />
		</div>

		<div class="form-row validate-required">
			<label for="<?php echo esc_attr( $gateway_field_id ); ?>cc_doc_type"><?php echo esc_html__( 'Document Type', 'wc-gateway-payway' ); ?> <span class="required">*</span></label>
			<select id="<?php echo esc_attr( $gateway_field_id ); ?>cc_doc_type" name="<?php echo esc_attr( $gateway_field_id ); ?>cc_doc_type">
				<option selected value="dni">DNI</option>
			</select>
		</div>
		<div class="form-row validate-required">
			<label for="<?php echo esc_attr( $gateway_field_id ); ?>cc_doc_number"><?php echo esc_html__( 'Document Number', 'wc-gateway-payway' ); ?> <span class="required">*</span></label>
			<input type="text"
				id="<?php echo esc_attr( $gateway_field_id ); ?>cc_doc_number"
				name="<?php echo esc_attr( $gateway_field_id ); ?>cc_doc_number"
				class="input-text"
				autocomplete="off" />
		</div>

		<div class="form-row">
			<label for="cc-direccion"><?php echo esc_html__( 'Billing Address', 'wc-gateway-payway' ); ?></label>
			<input type="text"
				id="cc-direccion"
				name="cc-direccion"
				class="input-text"
				autocomplete="off" />
		</div>

		<div class="form-row validate-required">
			<label for="<?php echo esc_attr( $gateway_field_id ); ?>cc_holder_door_number"><?php echo esc_html__( 'Street number (door number)', 'wc-gateway-payway' ); ?> <span class="required">*</span></label>
			<input type="text"
				id="<?php echo esc_attr( $gateway_field_id ); ?>cc_holder_door_number"
				name="<?php echo esc_attr( $gateway_field_id ); ?>cc_holder_door_number"
				class="input-text"
				autocomplete="off" />
		</div>

		<input type="hidden" id="<?php echo esc_attr( $gateway_field_id ); ?>cc_token" name="<?php echo esc_attr( $gateway_field_id ); ?>cc_token" />
		<input type="hidden" id="<?php echo esc_attr( $gateway_field_id ); ?>cc_bin" name="<?php echo esc_attr( $gateway_field_id ); ?>cc_bin" />
		<input type="hidden" id="<?php echo esc_attr( $gateway_field_id ); ?>cc_last_digits" name="<?php echo esc_attr( $gateway_field_id ); ?>cc_last_digits" />
	</div>
	<div class="clear"></div>
</fieldset>
