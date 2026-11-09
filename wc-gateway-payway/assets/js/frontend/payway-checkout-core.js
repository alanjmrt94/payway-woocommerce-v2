/* global Decidir, accounting */
(function (window) {
  'use strict';

  function asBool(value) {
    return value === true || value === 'yes' || value === 1 || value === '1';
  }

  function getPleaseSelect(config) {
    if (config && config.i18n && config.i18n.please_select) {
      return config.i18n.please_select;
    }
    return 'Please select...';
  }

  function normalizeConfig(raw) {
    var config = raw && typeof raw === 'object' ? raw : {};
    var cs = config.cybersource_enabled;
    if (typeof config.disableCybersource === 'undefined') {
      config.disableCybersource = !asBool(cs);
    }
    if (!config.i18n || typeof config.i18n !== 'object') {
      config.i18n = { please_select: 'Please select...' };
    }
    if (!config.credentials && config.creds) {
      config.credentials = {
        public_key: config.creds.public_key,
        site_id: config.creds.site_id
      };
    }
    config.cart_total = parseFloat(config.cart_total, 10) || 0;
    return config;
  }

  function buildDecidirFormElement(fieldValues) {
    var fieldList = [
      { name: 'card_number', value: fieldValues.card_number || '' },
      { name: 'security_code', value: fieldValues.security_code || '' },
      { name: 'card_expiration_month', value: fieldValues.card_expiration_month || '' },
      { name: 'card_expiration_year', value: fieldValues.card_expiration_year || '' },
      { name: 'card_holder_name', value: fieldValues.card_holder_name || '' },
      { name: 'card_holder_doc_type', value: fieldValues.card_holder_doc_type || 'dni' },
      { name: 'card_holder_doc_number', value: fieldValues.card_holder_doc_number || '' },
      { name: 'card_holder_door_number', value: fieldValues.card_holder_door_number || '' }
    ];
    var wrapper = document.createElement('div');
    for (var i = 0; i < fieldList.length; i++) {
      var field = document.createElement('input');
      field.setAttribute('type', 'text');
      field.setAttribute('name', fieldList[i].name);
      field.setAttribute('data-decidir', fieldList[i].name);
      field.setAttribute('value', fieldList[i].value);
      wrapper.appendChild(field);
    }
    return wrapper;
  }

  /**
   * @param {HTMLElement} formElement Element with data-decidir inputs
   * @param {object} config
   * @returns {Promise<{id:string,bin:string,last_four_digits:string}>}
   */
  function createToken(formElement, config) {
    config = normalizeConfig(config);
    return new Promise(function (resolve, reject) {
      if (typeof Decidir === 'undefined') {
        reject(new Error('Decidir SDK is not loaded'));
        return;
      }
      if (!config.endpoint_url || !config.credentials || !config.credentials.public_key) {
        reject(new Error('Payway credentials are incomplete'));
        return;
      }

      var api = new Decidir(config.endpoint_url, !!config.disableCybersource);
      api.setPublishableKey(config.credentials.public_key);
      api.setTimeout(0);
      api.createToken(formElement, function (status, data) {
        if (status === 200 || status === 201) {
          resolve({
            id: data && data.id ? data.id : '',
            bin: data && data.bin ? data.bin : '',
            last_four_digits: data && data.last_four_digits ? data.last_four_digits : ''
          });
          return;
        }
        reject(new Error('Token cannot be generated at this moment'));
      });
    });
  }

  function formatMoney(amount, currencySettings) {
    if (typeof accounting !== 'undefined' && accounting.formatMoney) {
      return accounting.formatMoney(amount, currencySettings || {});
    }
    var n = Number(amount);
    if (isNaN(n)) {
      return String(amount);
    }
    return n.toFixed(2);
  }

  function buildInstallmentOptions(plans, cartTotal, currencySettings) {
    var options = [];
    var total = parseFloat(cartTotal, 10) || 0;
    if (!plans || !plans.length) {
      return options;
    }
    for (var i = 0; i < plans.length; i++) {
      var plan = plans[i];
      var coefficient = parseFloat(plan.coefficient);
      var charge = 0;
      var feePeriod = plan.fee_period;
      var installmentPrice = total / parseInt(feePeriod, 10);
      if (coefficient > 1) {
        charge = (total * coefficient) - total;
        installmentPrice = (total + charge) / parseInt(feePeriod, 10);
      }
      var feeToSend = parseInt(plan.fee_to_send, 10);
      var periodNum = parseInt(feePeriod, 10);
      var isMipyme =
        (periodNum === 3 && feeToSend === 13) ||
        (periodNum === 6 && feeToSend === 16);
      options.push({
        value: plan.rule_id + '-' + plan.cardId + '-' + plan.fee_to_send,
        label:
          feePeriod +
          (isMipyme ? ' MiPyME' : '') +
          ' x ' +
          formatMoney(installmentPrice, currencySettings) +
          ' (' +
          formatMoney(total + charge, currencySettings) +
          ')',
        plan: plan
      });
    }
    return options;
  }

  function mapPlansWithCardId(plans, cardId) {
    if (!plans) {
      return [];
    }
    return plans.map(function (plan) {
      var copy = {};
      for (var k in plan) {
        if (Object.prototype.hasOwnProperty.call(plan, k)) {
          copy[k] = plan[k];
        }
      }
      copy.cardId = cardId;
      return copy;
    });
  }

  window.PaywayCheckoutCore = {
    normalizeConfig: normalizeConfig,
    getPleaseSelect: getPleaseSelect,
    buildDecidirFormElement: buildDecidirFormElement,
    createToken: createToken,
    formatMoney: formatMoney,
    buildInstallmentOptions: buildInstallmentOptions,
    mapPlansWithCardId: mapPlansWithCardId,
    asBool: asBool
  };
})(window);
