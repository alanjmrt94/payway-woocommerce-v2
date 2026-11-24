# 9. Diagnostics, Status, and Recent Payments

Internal codes (`PPC_` prefix): see `includes/class-wc-payway-errors.php`.

## Payway → Setup

Checklist of required and recommended items (Enabled, keys, decimals, promos, Cybersource, etc.), with action links and catalog seed.

## Payway → Status

Availability diagnostics: gateway off, missing keys, no applicable promos, currency, checkout surface (classic **or** Blocks), plugin/SDK version, debit without 1-installment plan, etc.

![Payway → Status](assets/11-status.png)

## Payway → Recent Payments

Lists recent payments via `PaymentList` using the **Private Key** of the active environment (Sandbox or Production).

- Missing keys: notice + link to Payments / Setup.
- When `_payway_*` meta exists on the order, the row may link to the WooCommerce order.

![Payway → Recent Payments](assets/12-recent-payments.png)

## Common errors

| Symptom | Likely cause | Action |
|---|---|---|
| Method missing | Gateway off / no keys / no applicable promos / Blocks without API | Setup + Payments + Status → Checkout surface |
| Red key/promo notices | Enabled without keys or applicable promos | Complete Payments + Setup seed |
| Promo loaded but missing | Expired, wrong day, or inactive | Activate, days, or **No expiry** |
| Debit without 1 installment | Missing `fee_to_send=1` plan on debit card | Promotions → plans |
| Token error | Decidir JS did not load | Browser console, CSP, network, frontend hosts |
| `invalid payment_method_id` | Wrong card `id_sps` | Fix card in admin |
| Auth / 401 | Bad keys or crossed environment | Verify Sandbox vs Production |
| CS / Fraud Detection (e.g. 10734) | Merchant requires Cybersource OFF, or incomplete bill_to | Enable CS; complete billing; do not “turn CS off” as a generic fix |
| CS Decision RED / REJECT | Fraud or issuer; not necessarily keys | Status CS tip; billing data; issuer |
| Empty logs | Debug off | Enable Debug Mode only for diagnostics |

Logs: **WooCommerce → Status → Logs** (`payway-gateway-*.log`). Secrets are redacted. With Debug **off**, wide `_payway_full_response` meta is not stored.
