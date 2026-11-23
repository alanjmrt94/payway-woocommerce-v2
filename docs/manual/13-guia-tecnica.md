# 13. Guía técnica

## Estructura

```
wc-gateway-payway/
  index.php                    # header Version + bootstrap
  wc-payment-gateway-payway.php
  includes/                    # core, admin, processors, meta, errors, logger
  template/                    # checkout clásico
  decidir/                     # puente Composer → decidir2/php-sdk V2.0.10
  assets/
  i18n/
```

## Capas relevantes

- `PaywayWC` — bootstrap, lifecycle (activate/deactivate), factories
- `WC_Payway_Decidir_Adapter` — tipado de respuesta / status de éxito
- `Payway\Support\Status` — helper PSR-4 acotado
- `WC_Payway_Compatibility` — HPOS / detección
- `WC_Payway_Integrity_Validator` — promo / plan / token
- `WC_Payway_Errors` — códigos `PPC_*` + SEC-08
- `WC_Payway_Logger` — debug gate + redacción
- `WC_Payway_Meta` — CRUD order meta (batch save)
- `WC_Payway_Setup_Status` — checks de Instalación / Estado
- Admin: Setup, Banks, Cards, Promotions, Recent Payments, Status
- Blocks: registro feature-detect + mismo `process_payment` + `config.i18n`

## Montos (SDK V2)

El SDK PHP **2.0.10** no multiplica ×100. El plugin envía `amount` y `purchase_totals.amount` en **centavos**.

## Hooks útiles

- `wc_payway_request_builder_process_before|after`
- `woocommerce_payway_supported_currencies`
- `woocommerce_payway_icon`
- `woocommerce_payway_decidir_js_url` — URL de `decidir.js` (default CDN versionada)
- `payway_skip_settings_cache` / `payway_skip_promo_cache`

## Empaquetado

```bash
ci/bin/package-plugin-zip.sh          # lee Version del header
ci/bin/package-plugin-zip.sh 2.0.0    # fuerza versión en el nombre
# → dist/wc-gateway-payway-2.0.0.zip
```

El ZIP debe tener `wc-gateway-payway/` en la raíz.
