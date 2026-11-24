# 5. Configuration (Payments / Sandbox / Production)

Configuring banks and promotions under **Payway** is not enough: the payment method is enabled in WooCommerce.

![WooCommerce → Payments](assets/04-wc-payments-list.png)

![Gateway settings (masked keys)](assets/05-gateway-settings.png)

## Step by step

1. Go to **WooCommerce → Settings → Payments**.
2. Find **Payway Payment Community** (`payway_gateway`) and open **Manage**.
3. Check **Enabled**.
4. Set **Title** and **Description** (buyer-facing). Community default title is **Checkout Payway**.
5. Choose the environment:
   - **Sandbox Mode** on → use Sandbox Site Id / Public / Private.
   - Sandbox off → use Production credentials.
6. Review **Cybersource** (fraud) and **Debug Mode** (redacted logs; **off in production** except short diagnostics).
7. **Save changes**.

Without **Enabled** and the three keys for the active environment, the method does not operate. This plugin does **not** create or regenerate keys.

Also required: **WooCommerce → Settings → General → Number of decimals = 2**.

![WooCommerce decimals = 2](assets/10-decimals-2.png)

From **Payway → Setup** you can **Apply recommended config** (Enabled, Sandbox, Cybersource, Debug, title/description, decimals=2; migrates legacy titles like `Credit Card` / `keep-me`) without touching keys.

If the gateway is **Enabled** and keys or applicable promotions are missing, you will see **error** notices on the WP dashboard, Payments, and the Payway menu. One Site Id = one merchant: this plugin has no parent/child multi-site.

### Cybersource and 10734

If the Payway site requires Fraud Detection, leaving Cybersource **off** may fail with code **10734**. That is not an amount bug (SDK V2 uses cents). Keep CS aligned with the merchant site.

## Main fields

| Field | Use |
|---|---|
| Enabled | Shows/hides the method at checkout |
| Title / Description | Buyer-facing copy |
| Sandbox Mode | Test vs production environment |
| Cybersource | Fraud detection; required for some sandbox merchants |
| Debug | Detailed logs with secret redaction |
| Site Id / Public Key / Private Key | Credentials per environment |

Settings are grouped as **General / Sandbox / Production** in admin UI.

## Where to get keys

Developer portal / Payway merchant backoffice (or onboarding email). Sandbox and Production use different keys. If you lack them, ask your commercial contact or Payway desk — **not** the Community fork author, who does not issue keys.

## Disable without losing data

In **WooCommerce → Settings → Payments**, uncheck **Enabled**. Buyers stop seeing the method; banks, promos, and keys remain stored.

## Recent Payments

**Payway → Recent Payments** lists recent API payments with the active environment’s Private Key and can link to the WooCommerce order when `_payway_*` meta exists.
