# 12. FAQ

**Is this official Payway software?**  
No. It is a Community fork. Payway does not support this plugin.

**Who provides support?**  
The fork author Alan Martinez ([alanjmrt94](https://github.com/alanjmrt94)) via the GitHub repository. Do not use the official Payway App Store tipification meant for plugin 0.3.x.

**Does it support WooCommerce Blocks?**  
Yes, since 1.1.0 (with payment-method API feature-detect).

**Where do I get Site Id and keys?**  
From the Payway portal / backoffice or merchant onboarding email. Sandbox and production differ. This fork does not issue keys.

**Why is the method missing at checkout?**  
Check Enabled, keys for the active environment, at least one applicable promotion (or Setup seed), ARS/USD currency, decimals = 2, and **Status → Checkout surface**.

**How do I configure Cuota Simple 3 / 6?**  
In promotion plans, `fee_to_send` = **13** (Simple 3) or **16** (Simple 6). Setup may offer a MiPyME seed.

**What is “No expiry”?**  
The promo does not validate an end date (`no_expiry`). Ideal for permanent installment rules.

**Can I turn Cybersource off if payment fails?**  
Not as a generic fix. Some sandbox merchants **require** Fraud Detection; turning CS off can return **10734**. Complete billing (street + door) and keep CS aligned with the merchant. SDK V2 cents amounts remain correct with CS ON.

**What are the red key / promo notices?**  
With the method Enabled, missing keys or applicable promotions show error notices (including on the dashboard). The method stays hidden at checkout until fixed (Setup → seed).

**Are amounts in pesos or cents?**  
From **2.0.0** (PHP SDK 2.0.10) the plugin sends **cents** to Decidir/Payway. There is no extra ×100 in the plugin.

**Does it support refunds from WooCommerce?**  
Not yet: the gateway does not declare `refunds` in `supports` in 2.0.x.

**Do I lose banks/promos when migrating from the official plugin?**  
No, if you reuse the same database: `prisma_payway_*` tables and `woocommerce_payway_gateway_settings`.

**How do I change the plugin language?**  
No dedicated selector. The plugin follows WordPress locale; any `es*` locale loads `es_AR`. Everything else stays English.

**Where is the latest version?**  
[GitHub Releases](https://github.com/alanjmrt94/payway-woocommerce-v2/releases) of the Community fork (not the official Payway App Store).

**How do I see recent payments without leaving WP?**  
**Payway → Recent Payments**, using the Private Key of the active environment.
