# 13. Technical notes

## Layout

```
wc-gateway-payway/
  index.php                    # Version header + bootstrap
  wc-payment-gateway-payway.php
  includes/                    # core, admin, processors, meta, errors, logger
    Payway/Support/            # PSR-4 starter (Status helper)
  template/                    # classic checkout
  decidir/                     # Composer bridge → decidir2/php-sdk V2.0.10
  assets/
  i18n/
```

## Relevant layers

- `PaywayWC` — bootstrap, lifecycle (activate/deactivate), factories
- `WC_Payway_Decidir_Adapter` — typed response / success statuses
- `Payway\Support\Status` — small PSR-4 helper
- `WC_Payway_Compatibility` — HPOS detection
- `WC_Payway_Integrity_Validator` — promo / plan / token
- `WC_Payway_Errors` — `PPC_*` + SEC-08 codes
- `WC_Payway_Logger` — debug gate + redaction
- `WC_Payway_Meta` — CRUD order meta (batch save)
- `WC_Payway_Setup_Status` — Setup / Status checks
- Admin: Setup, Banks, Cards, Promotions, Recent Payments, Status
- Blocks: feature-detect registration + same `process_payment` + `config.i18n`

## Amounts (SDK V2)

PHP SDK **2.0.10** does not multiply ×100. The plugin sends `amount` and `purchase_totals.amount` in **cents**.

## Useful hooks

- `wc_payway_request_builder_process_before|after`
- `woocommerce_payway_supported_currencies`
- `woocommerce_payway_icon`
- `woocommerce_payway_decidir_js_url` — `decidir.js` URL (versioned CDN default)
- `woocommerce_payway_checkout_posted_data`
- `payway_skip_settings_cache` / `payway_skip_promo_cache`

## Packaging

```bash
ci/bin/package-plugin-zip.sh           # reads Version: header
ci/bin/package-plugin-zip.sh 2.0.2     # force version in filename
# → ci/dist/wc-gateway-payway-2.0.2.zip
```

Manual PDFs:

```bash
cd ci && npm install && npx playwright install chromium
node bin/build-manual-pdfs.mjs
# → docs/payway-woo-v2-manual-es.pdf · docs/payway-woo-v2-manual-en.pdf
```
