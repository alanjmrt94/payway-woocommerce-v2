/* global PaywayCheckoutCore */
(function () {
  'use strict';

  var settings =
    window.wc && window.wc.wcSettings && typeof window.wc.wcSettings.getSetting === 'function'
      ? window.wc.wcSettings.getSetting('payway_gateway_data', {})
      : {};

  if (!window.wc || !window.wc.wcBlocksRegistry || !window.wp || !window.wp.element) {
    return;
  }

  var registerPaymentMethod = window.wc.wcBlocksRegistry.registerPaymentMethod;
  var el = window.wp.element.createElement;
  var useState = window.wp.element.useState;
  var useEffect = window.wp.element.useEffect;
  var useRef = window.wp.element.useRef;
  var decodeEntities =
    window.wp.htmlEntities && window.wp.htmlEntities.decodeEntities
      ? window.wp.htmlEntities.decodeEntities
      : function (s) {
          return s;
        };

  var config = window.PaywayCheckoutCore
    ? window.PaywayCheckoutCore.normalizeConfig(
        Object.assign({}, settings, {
          credentials: {
            public_key: settings.creds && settings.creds.public_key
          },
          currencySettings: settings.accounting || {}
        })
      )
    : settings;

  var labelText = decodeEntities(settings.title || 'Checkout Payway');

  function asList(maybe) {
    if (!maybe) {
      return [];
    }
    if (Object.prototype.toString.call(maybe) === '[object Array]') {
      return maybe;
    }
    return Object.keys(maybe).map(function (k) {
      return maybe[k];
    });
  }

  function cartTotalFromProps(props, fallback) {
    if (props && props.cartTotals && props.cartTotals.total_price != null) {
      var minor = parseInt(props.cartTotals.currency_minor_unit, 10);
      if (isNaN(minor)) {
        minor = 2;
      }
      var raw = parseInt(props.cartTotals.total_price, 10);
      if (!isNaN(raw)) {
        return raw / Math.pow(10, minor);
      }
    }
    return fallback || 0;
  }

  function fieldRow(label, children, required) {
    return el(
      'div',
      { className: 'form-row' + (required ? ' validate-required' : ''), style: { marginBottom: '0.75em' } },
      el('label', null, label, required ? el('span', { className: 'required' }, ' *') : null),
      children
    );
  }

  function PaywayContent(props) {
    var eventRegistration = props.eventRegistration || {};
    var emitResponse = props.emitResponse || {};
    var please = window.PaywayCheckoutCore
      ? window.PaywayCheckoutCore.getPleaseSelect(config)
      : 'Please select...';

    var promotions = config.promotions || { banks: [], cards: {}, plans: {} };
    var banks = asList(promotions.banks);
    var liveCartTotal = cartTotalFromProps(props, config.cart_total);
    config.cart_total = liveCartTotal;

    var bank = useState('');
    var setBank = bank[1];
    var bankVal = bank[0];

    var card = useState('');
    var setCard = card[1];
    var cardVal = card[0];

    var installments = useState('');
    var setInstallments = installments[1];
    var installmentsVal = installments[0];

    var previousCartTotalRef = useRef(liveCartTotal);
    useEffect(
      function () {
        if (previousCartTotalRef.current === liveCartTotal) {
          return;
        }
        previousCartTotalRef.current = liveCartTotal;
        setInstallments('');
      },
      [liveCartTotal]
    );

    var cardNumber = useState('');
    var setCardNumber = cardNumber[1];
    var cardNumberVal = cardNumber[0];

    var expMonth = useState('');
    var setExpMonth = expMonth[1];
    var expMonthVal = expMonth[0];

    var expYear = useState('');
    var setExpYear = expYear[1];
    var expYearVal = expYear[0];

    var cvv = useState('');
    var setCvv = cvv[1];
    var cvvVal = cvv[0];

    var holderName = useState('');
    var setHolderName = holderName[1];
    var holderNameVal = holderName[0];

    var docNumber = useState('');
    var setDocNumber = docNumber[1];
    var docNumberVal = docNumber[0];

    var doorNumber = useState('');
    var setDoorNumber = doorNumber[1];
    var doorNumberVal = doorNumber[0];

    var error = useState('');
    var setError = error[1];
    var errorVal = error[0];

    var valuesRef = useRef({});
    valuesRef.current = {
      bank: bankVal,
      card: cardVal,
      installments: installmentsVal,
      cardNumber: cardNumberVal,
      expMonth: expMonthVal,
      expYear: expYearVal,
      cvv: cvvVal,
      holderName: holderNameVal,
      docNumber: docNumberVal,
      doorNumber: doorNumberVal
    };

    var cardsForBank =
      bankVal && promotions.cards && promotions.cards[bankVal]
        ? asList(promotions.cards[bankVal])
        : [];
    var plansRaw =
      bankVal && cardVal && promotions.plans && promotions.plans[bankVal]
        ? asList(promotions.plans[bankVal][cardVal] || [])
        : [];
    var installmentOptions = window.PaywayCheckoutCore
      ? window.PaywayCheckoutCore.buildInstallmentOptions(
          window.PaywayCheckoutCore.mapPlansWithCardId(plansRaw, cardVal),
          config.cart_total,
          config.currencySettings
        )
      : [];

    useEffect(
      function () {
        if (!eventRegistration.onPaymentSetup) {
          return undefined;
        }
        var unsubscribe = eventRegistration.onPaymentSetup(function () {
          var v = valuesRef.current;
          setError('');

          if (
            !v.bank ||
            !v.card ||
            !v.installments ||
            !v.cardNumber ||
            !v.expMonth ||
            !v.expYear ||
            !v.cvv ||
            !v.holderName ||
            !v.docNumber ||
            !v.doorNumber
          ) {
            var msg = 'Please complete all Payway payment fields.';
            setError(msg);
            return {
              type: emitResponse.responseTypes.ERROR,
              message: msg
            };
          }

          if (!window.PaywayCheckoutCore) {
            return {
              type: emitResponse.responseTypes.ERROR,
              message: 'Payway checkout core is not available.'
            };
          }

          var formElement = window.PaywayCheckoutCore.buildDecidirFormElement({
            card_number: v.cardNumber,
            security_code: v.cvv,
            card_expiration_month: v.expMonth,
            card_expiration_year: v.expYear,
            card_holder_name: v.holderName,
            card_holder_doc_type: 'dni',
            card_holder_doc_number: v.docNumber,
            card_holder_door_number: v.doorNumber
          });

          return window.PaywayCheckoutCore.createToken(formElement, config)
            .then(function (tokenResult) {
              return {
                type: emitResponse.responseTypes.SUCCESS,
                meta: {
                  paymentMethodData: {
                    payway_gateway_cc_token: tokenResult.id,
                    payway_gateway_cc_bin: tokenResult.bin,
                    payway_gateway_cc_last_digits: tokenResult.last_four_digits,
                    payway_gateway_cc_type: String(v.card),
                    payway_gateway_cc_installments: v.installments,
                    payway_gateway_cc_holder_door_number: String(v.doorNumber),
                    payway_gateway_cc_bank: String(v.bank)
                  }
                }
              };
            })
            .catch(function () {
              var tokenMsg = 'Payment Token could not be generated at this time.';
              setError(tokenMsg);
              return {
                type: emitResponse.responseTypes.ERROR,
                message: tokenMsg
              };
            });
        });
        return unsubscribe;
      },
      [eventRegistration, emitResponse]
    );

    function onBankChange(e) {
      setBank(e.target.value);
      setCard('');
      setInstallments('');
    }

    function onCardChange(e) {
      setCard(e.target.value);
      setInstallments('');
    }

    return el(
      'div',
      { className: 'wc-gateway-payway wc-block-components-payway-form', id: 'payway_gateway-cc-form' },
      settings.description
        ? el('p', { className: 'fieldset-description', dangerouslySetInnerHTML: { __html: settings.description } })
        : null,
      errorVal ? el('div', { className: 'woocommerce-error', role: 'alert' }, errorVal) : null,
      fieldRow(
        'Bank',
        el(
          'select',
          {
            value: bankVal,
            onChange: onBankChange,
            className: 'input-text'
          },
          [el('option', { key: '', value: '' }, please)].concat(
            banks.map(function (b) {
              return el('option', { key: String(b.value), value: String(b.value) }, b.name);
            })
          )
        ),
        true
      ),
      fieldRow(
        'Card',
        el(
          'select',
          {
            value: cardVal,
            onChange: onCardChange,
            className: 'input-text',
            disabled: !bankVal
          },
          [el('option', { key: '', value: '' }, please)].concat(
            cardsForBank.map(function (c) {
              return el('option', { key: String(c.value), value: String(c.value) }, c.name);
            })
          )
        ),
        true
      ),
      fieldRow(
        'Installments',
        el(
          'select',
          {
            value: installmentsVal,
            onChange: function (e) {
              setInstallments(e.target.value);
            },
            className: 'input-text',
            disabled: !cardVal
          },
          [el('option', { key: '', value: '' }, please)].concat(
            installmentOptions.map(function (opt) {
              return el('option', { key: opt.value, value: opt.value }, opt.label);
            })
          )
        ),
        true
      ),
      fieldRow(
        'Credit Card Number',
        el('input', {
          type: 'text',
          className: 'input-text',
          autoComplete: 'off',
          value: cardNumberVal,
          onChange: function (e) {
            setCardNumber(e.target.value);
          }
        }),
        true
      ),
      fieldRow(
        'Card Expiration Month',
        el('input', {
          type: 'text',
          className: 'input-text',
          placeholder: 'MM',
          value: expMonthVal,
          onChange: function (e) {
            setExpMonth(e.target.value);
          }
        }),
        true
      ),
      fieldRow(
        'Card Expiration Year',
        el('input', {
          type: 'text',
          className: 'input-text',
          placeholder: 'YY',
          value: expYearVal,
          onChange: function (e) {
            setExpYear(e.target.value);
          }
        }),
        true
      ),
      fieldRow(
        'CVV',
        el('input', {
          type: 'text',
          className: 'input-text',
          value: cvvVal,
          onChange: function (e) {
            setCvv(e.target.value);
          }
        }),
        true
      ),
      fieldRow(
        'Holder Name',
        el('input', {
          type: 'text',
          className: 'input-text',
          value: holderNameVal,
          onChange: function (e) {
            setHolderName(e.target.value);
          }
        }),
        true
      ),
      fieldRow(
        'Document Number',
        el('input', {
          type: 'text',
          className: 'input-text',
          value: docNumberVal,
          onChange: function (e) {
            setDocNumber(e.target.value);
          }
        }),
        true
      ),
      fieldRow(
        'Street number (door number)',
        el('input', {
          type: 'text',
          className: 'input-text',
          value: doorNumberVal,
          onChange: function (e) {
            setDoorNumber(e.target.value);
          }
        }),
        true
      )
    );
  }

  function Label(props) {
    var PaymentMethodLabel = props.components && props.components.PaymentMethodLabel;
    if (PaymentMethodLabel) {
      return el(PaymentMethodLabel, { text: labelText });
    }
    return el('span', null, labelText);
  }

  registerPaymentMethod({
    name: 'payway_gateway',
    label: el(Label, null),
    content: el(PaywayContent, null),
    edit: el(PaywayContent, null),
    canMakePayment: function () {
      if (!settings || settings.title === undefined) {
        return false;
      }
      var promos = settings.promotions || {};
      return asList(promos.banks).length > 0;
    },
    ariaLabel: labelText,
    supports: {
      features: settings.supports || ['products']
    }
  });
})();
