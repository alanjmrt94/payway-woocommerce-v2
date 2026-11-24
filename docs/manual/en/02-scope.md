# 2. Scope, limitations, and what’s new vs official 0.3.3

## Included in 2.0.x

- **Classic** checkout (`[woocommerce_checkout]`) and **WooCommerce Blocks**
- Sandbox and Production (Site Id + Public Key + Private Key per environment)
- Admin menu **Payway**: Setup → Banks → Cards → Promotions → Recent Payments → Status
- Decidir catalog seed and “Apply recommended config”
- Promotions with **No expiry** (`no_expiry`)
- Cybersource (fraud detection) when the merchant site requires it
- HPOS (High-Performance Order Storage)
- PHP SDK **decidir2/php-sdk V2.0.10** (amounts in **cents**)
- **Recent Payments** panel via `PaymentList` (link to WooCommerce order)
- i18n: English strings; `es*` locales load `es_AR`

## Known limitations

- Native WooCommerce refunds/voids: gateway does **not** yet declare `refunds` in `supports`
- One Site Id per install (no multi-merchant parent/child in this gateway)
- Full pairwise WP×WC×PHP matrix is not covered; see smoke cells in the compatibility matrix
- Not the official Payway App Store / portal plugin; fork support is via the author’s GitHub repo

## What’s new vs official 0.3.3

| Topic | Official 0.3.3 (reference) | Payway Payment Community 2.0.x |
|---|---|---|
| Name / support | Payway portal product | Community fork; GitHub support by [alanjmrt94](https://github.com/alanjmrt94) |
| WooCommerce Blocks | No | Yes (feature-detect) |
| Charged amount | Risk of trusting DOM (#64) | Total from the **server-side order** |
| Installment refresh | Display could go stale | Classic: `updated_checkout`; Blocks: recalc on cart change |
| Private key on storefront | Historically problematic | **Not** sent to the frontend |
| Declined orders | Could end as `cancelled` | Status `failed` |
| Onboarding | Purely manual | **Setup** + checklist + seed + Status |
| Promos without end date | Dates effectively required | **No expiry** checkbox |
| PHP SDK | V1.4.x (×100 in SDK) | **V2.0.10**; plugin sends cents |
| Admin payments | — | **Recent Payments** + order link |
| WC tested | Historical manual ≤ 8.4 | Declared up to **11.x**; lab smoke to 11.3.1 |
| ZIP packaging | Root-folder issues | `wc-gateway-payway` at ZIP root |

Version-by-version detail: [14 — Changelog](14-changelog.md) and root [`CHANGELOG.md`](../../../CHANGELOG.md).
