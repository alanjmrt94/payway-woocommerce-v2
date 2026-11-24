# 7. Checkout (classic & Blocks) and orders

![Classic checkout Payway](assets/07-checkout-classic.png)

![Blocks checkout Payway](assets/08-checkout-blocks.png)

![Approved order in admin (#14)](assets/09-order-admin.png)

## Payment flow

1. Buyer picks bank / card / installment plan on the Payway form.
2. **Tokenization** in the browser with Decidir JS (Payway/Decidir frontend hosts). The server receives a **token**, not PAN or CVV.
3. The plugin validates promotion, plan, card/bank server-side and builds the request.
4. The **amount** sent to Payway comes from the WooCommerce **order** total (not the checkout DOM).
5. With PHP SDK **V2.0.10**, `amount` and `purchase_totals.amount` are in **cents**.

## Classic checkout

- Form embedded in `[woocommerce_checkout]`.
- On shipping/coupon changes (`updated_checkout`), the total used for installment display refreshes.
- Tokenization hooked to the gateway place-order action (`checkout_place_order_payway_gateway`).

## WooCommerce Blocks

- Registered as a payment method when `AbstractPaymentMethodType` / `wc.wcBlocksRegistry` exist.
- Same `process_payment` as classic checkout.
- If the cart total changes, selected installments are cleared/recalculated.
- Storefront strings come from `config.i18n`.

## Orders

| Situation | Behaviour |
|---|---|
| Approved | Paid order; Payway meta (IDs, promo, etc.) |
| Declined | Status **`failed`** (not `cancelled`) |
| Buyer message | Typed when `status_details.error.type` exists (e.g. `invalid_card`) |
| Idempotency | Stable `site_transaction_id` `payway-{order_id}` + short lock in `process_payment` |

Order admin shows a dedicated Payway payment meta box (transaction IDs, promotion data).

## Test cards (Sandbox)

Use test cards from the Payway portal / docs for your sandbox merchant. Community UAT verified typed decline with PAN ending in `4999` (`invalid_card`).

Do not use real personal data: for demos/UAT prefer **John Doe** / `john.doe.uat@example.com`.
