/* global wc_gateway_payway_params, wc_gateway_payway_accounting_format, PaywayCheckoutCore, wc_checkout_form */
jQuery(function ($) {
  'use strict';

  if (typeof wc_gateway_payway_params === 'undefined' || typeof PaywayCheckoutCore === 'undefined') {
    return;
  }

  var baseConfig = PaywayCheckoutCore.normalizeConfig(
    $.extend(true, {}, wc_gateway_payway_params, {
      currencySettings:
        typeof wc_gateway_payway_accounting_format !== 'undefined'
          ? wc_gateway_payway_accounting_format
          : {},
      credentials: {
        public_key: wc_gateway_payway_params.creds && wc_gateway_payway_params.creds.public_key
      }
    })
  );

  var form = {
    id: 'payway_gateway',
    cart_total: baseConfig.cart_total,
    $form: $('#payway_gateway-cc-form'),
    $wrapper: $('.fields-wrapper', '#payway_gateway-cc-form'),
    $error_container: $('#error_container', '#payway_gateway-cc-form'),
    $banksDropdown: $('#payway_gateway_cc_bank'),
    $cardsDropdown: $('#payway_gateway_cc_type'),
    $installmentsDropdown: $('#payway_gateway_cc_installments'),
    $cardExpirationMonth: $('#payway_gateway_cc_exp_month'),
    $cardNumber: $('#payway_gateway_cc_number'),
    $cardCVV: $('#payway_gateway_cc_cid'),
    $cardHolderName: $('#payway_gateway_cc_holder_name'),
    $cardHolderDocumentType: $('#payway_gateway_cc_doc_type'),
    $cardHolderDocumentNumber: $('#payway_gateway_cc_doc_number'),
    $cardExpirationYear: $('#payway_gateway_cc_exp_year'),
    $cardHolderDoorNumber: $('#payway_gateway_cc_holder_door_number'),
    config: baseConfig,
    isTokenReady: false,

    log: function () {
      this.refreshCartTotalFromServer();
      if (this.config.sandbox_enabled && typeof console === 'object') {
        console.log.apply(console, ['** payway'].concat([].slice.call(arguments)));
      }
    },

    refreshCartTotalFromServer: function () {
      var serverTotal = parseFloat(wc_gateway_payway_params.cart_total, 10);
      if (!isNaN(serverTotal) && serverTotal > 0) {
        this.cart_total = serverTotal;
        this.config.cart_total = serverTotal;
      }
    },

    init: function () {
      this.$error_container.empty().hide();
      this._attachListeners();
      this._loadPromotions();
      this._initRadioListeners();
    },

    _attachListeners: function () {
      this.$banksDropdown.on('change', this.onBanksChange.bind(this));
      this.$cardsDropdown.on('change', this.onCardsChange.bind(this));
      this.$cardExpirationMonth.on('blur change', this.validateCardExpirationMonth.bind(this));
      this.$cardExpirationYear.on('blur change', this.validateCardExpirationYear.bind(this));
      this.$cardHolderDocumentNumber.on('blur change', this.validateCardDocumentNumber.bind(this));
      this.$cardHolderDoorNumber.on('blur change', this.validateDoorNumber.bind(this));
      $('#place_order').on('click', this.capturePlaceOrder.bind(this));
    },

    _initRadioListeners: function () {
      var self = this;
      $('body').on('click', 'input.shipping_method', function () {
        self.$banksDropdown.val(self.$banksDropdown.find('option:first').val());
        self.$cardsDropdown.val(self.$cardsDropdown.find('option:first').val());
        self.$installmentsDropdown.val(self.$installmentsDropdown.find('option:first').val());
      });
    },

    _getFormValue: function (selector) {
      return $(selector, this.$form).val();
    },

    _validateGatewayForm: function () {
      return $('.form-row.woocommerce-invalid', this.$form).length < 1;
    },

    capturePlaceOrder: function () {
      if (!$('#payment_method_payway_gateway').is(':checked')) {
        return true;
      }
      if (!this._validateGatewayForm()) {
        return false;
      }

      var formElement = PaywayCheckoutCore.buildDecidirFormElement({
        card_number: this._getFormValue('#payway_gateway_cc_number'),
        security_code: this._getFormValue('#payway_gateway_cc_cid'),
        card_expiration_month: this._getFormValue('#payway_gateway_cc_exp_month'),
        card_expiration_year: this._getFormValue('#payway_gateway_cc_exp_year'),
        card_holder_name: this._getFormValue('#payway_gateway_cc_holder_name'),
        card_holder_doc_type: this._getFormValue('#payway_gateway_cc_doc_type'),
        card_holder_doc_number: this._getFormValue('#payway_gateway_cc_doc_number'),
        card_holder_door_number: this._getFormValue('#payway_gateway_cc_holder_door_number')
      });

      this.isTokenReady = false;
      PaywayCheckoutCore.createToken(formElement, this.config)
        .then(
          function (result) {
            $('#payway_gateway_cc_bin', this.$form).val(result.bin);
            $('#payway_gateway_cc_token', this.$form).val(result.id);
            $('#payway_gateway_cc_last_digits', this.$form).val(result.last_four_digits);
            this.isTokenReady = true;
            if (typeof wc_checkout_form !== 'undefined') {
              wc_checkout_form.$order_review.trigger('submit');
            } else {
              $('#payment_method_payway_gateway').closest('form').submit();
            }
          }.bind(this),
          function () {
            window.alert('Payment Token could not be generated at this time.');
          }
        );

      return false;
    },

    _loadPromotions: function () {
      var please = PaywayCheckoutCore.getPleaseSelect(this.config);
      var $bankField = this.$banksDropdown;
      if (!this.config.promotions || !this.config.promotions.banks || !$bankField.length) {
        return;
      }
      $bankField.empty().append($('<option>', { value: '', text: please }));
      $.each(this.config.promotions.banks, function (i, bank) {
        $bankField.append($('<option>', { value: bank.value, text: bank.name }));
      });
    },

    validateCardExpirationMonth: function () {
      var $field = this.$cardExpirationMonth;
      if (!$field.length) {
        return false;
      }
      var value = $field.val() || '';
      var date = new Date();
      var currentYear = date.getFullYear().toString().substr(2);
      var currentMonth = date.getMonth() + 1;
      if (currentMonth < 10) {
        currentMonth = '0' + currentMonth;
      }
      if (value === '' || value.length > 2 || isNaN(value) || value < 1 || value > 12) {
        $field.closest('.form-row').addClass('woocommerce-invalid');
        return false;
      }
      if (value.length === 1) {
        value = '0' + value;
        $field.val(value);
      }
      if (
        this.$cardExpirationYear.val() &&
        this.$cardExpirationYear.val() === currentYear &&
        value < currentMonth
      ) {
        $field.closest('.form-row').addClass('woocommerce-invalid');
        return false;
      }
      $field.closest('.form-row').removeClass('woocommerce-invalid').addClass('woocommerce-validated');
      return true;
    },

    validateCardExpirationYear: function () {
      var $field = this.$cardExpirationYear;
      if (!$field.length) {
        return false;
      }
      var value = $field.val() || '';
      var date = new Date();
      var currentYear = date.getFullYear().toString().substr(2);
      var currentMonth = date.getMonth() + 1;
      if (currentMonth < 10) {
        currentMonth = '0' + currentMonth;
      }
      if (value === '' || isNaN(value) || value.length !== 2 || value < currentYear) {
        $field.closest('.form-row').addClass('woocommerce-invalid');
        return false;
      }
      if (
        currentYear === value &&
        this.$cardExpirationMonth.val() &&
        this.$cardExpirationMonth.val() < currentMonth
      ) {
        $field.closest('.form-row').addClass('woocommerce-invalid');
        return false;
      }
      $field.closest('.form-row').removeClass('woocommerce-invalid').addClass('woocommerce-validated');
      return true;
    },

    validateCardDocumentNumber: function () {
      var $field = this.$cardHolderDocumentNumber;
      var value = $field.val() || '';
      if (value === '' || value.length <= 6 || isNaN(value)) {
        $field.closest('.form-row').addClass('woocommerce-invalid');
        return false;
      }
      $field.closest('.form-row').removeClass('woocommerce-invalid').addClass('woocommerce-validated');
      return true;
    },

    validateDoorNumber: function () {
      var $field = this.$cardHolderDoorNumber;
      var value = $field.val() || '';
      if (value === '' || isNaN(value)) {
        $field.closest('.form-row').addClass('woocommerce-invalid');
        return false;
      }
      $field.closest('.form-row').removeClass('woocommerce-invalid').addClass('woocommerce-validated');
      return true;
    },

    onBanksChange: function () {
      var bankId = this.$banksDropdown.val();
      var please = PaywayCheckoutCore.getPleaseSelect(this.config);
      this.$installmentsDropdown
        .empty()
        .append($('<option>', { value: '', text: please }));
      this.$cardsDropdown.empty().append($('<option>', { value: '', text: please }));
      if (!bankId || !this.config.promotions.cards[bankId]) {
        return;
      }
      $.each(
        this.config.promotions.cards[bankId],
        function (i, card) {
          this.$cardsDropdown.append($('<option>', { value: card.value, text: card.name }));
        }.bind(this)
      );
    },

    onCardsChange: function () {
      var bankId = this.$banksDropdown.val();
      var cardId = this.$cardsDropdown.val();
      var please = PaywayCheckoutCore.getPleaseSelect(this.config);
      this.$installmentsDropdown.empty().append($('<option>', { value: '', text: please }));
      if (!bankId || !cardId || !this.config.promotions.plans[bankId] || !this.config.promotions.plans[bankId][cardId]) {
        return;
      }
      this.refreshCartTotalFromServer();
      var plans = PaywayCheckoutCore.mapPlansWithCardId(
        this.config.promotions.plans[bankId][cardId],
        cardId
      );
      var options = PaywayCheckoutCore.buildInstallmentOptions(
        plans,
        this.cart_total,
        this.config.currencySettings
      );
      options.forEach(
        function (opt) {
          this.$installmentsDropdown.append($('<option>', { value: opt.value, text: opt.label }));
        }.bind(this)
      );
    }
  };

  form.init();
});
