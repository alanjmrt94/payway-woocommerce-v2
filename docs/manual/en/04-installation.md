# 4. Installation

![Upload plugin from wp-admin](assets/01-plugins-upload.png)

![Payway Payment Community active in Plugins](assets/02-plugin-activated.png)

## Download the ZIP

1. Get the ZIP from [GitHub Releases](https://github.com/alanjmrt94/payway-woocommerce-v2/releases) (version **2.0.2** or newer).
2. After unzip, the root folder must be **`wc-gateway-payway`** (one level; not a nested zip or a `payway-woocommerce-v2` folder).

## Via WordPress admin

1. Open wp-admin.
2. **Plugins → Add New → Upload Plugin**.
3. Select the `.zip` and **Install Now**.
4. **Activate** **Payway Payment Community**.

## Manual copy

1. Unzip the archive.
2. Copy `wc-gateway-payway` into `wp-content/plugins/`.
3. In **Plugins → Installed Plugins**, activate **Payway Payment Community**.

## After activation

The **Payway** sidebar menu (Spanish locale) follows this order:

**Setup → Banks → Cards → Promotions → Recent Payments → Status**

![Payway → Setup](assets/03-setup-checklist.png)

### Recommended go-live order

1. **Payway → Setup → Apply recommended config**  
   Sets Enabled, Sandbox, Cybersource, Debug, title/description, and **decimals = 2**.
2. **WooCommerce → Settings → Payments → Payway Payment Community**  
   Enter Site Id / Public Key / Private Key for the active environment. See [05 — Configuration](05-configuration.md).
3. In **Setup**: seed the catalog (bank “All”, cards and default promotions) or create them manually. See [06 — Banks, cards & promotions](06-banks-cards-promotions.md).
4. Store currency: **ARS** or **USD**.
5. **Classic** or **Blocks** checkout (both supported).

If the method does not appear at checkout, open **Payway → Status** and [09 — Diagnostics](09-diagnostics.md).

## Common install issues

| Symptom | Check |
|---|---|
| Method hidden at checkout | Enabled + keys for active env + at least one applicable promo (Setup seed) |
| Debit rejects / no 1-installment plan | Each debit card needs a plan with `fee_to_send` = 1 |
| Currency notice | ARS/USD only; Status flags unsupported currency |
| Woo / Blocks compat | WC ≥ 6.0; Blocks needs payment-method API (Status → Checkout surface) |
| CS / 10734 | Merchant requires Fraud Detection → Cybersource ON; see [05](05-configuration.md) and [12 FAQ](12-faq.md) |

After activation, admin may redirect you to **Payway → Setup** for the checklist.

## WooCommerce as a dependency

The plugin declares `Requires Plugins: woocommerce`. Install and activate WooCommerce before (or with) Payway Payment Community.

## Test environment

Every WordPress install differs (theme, cache, other plugins). Try first on a staging clone faithful to production.
