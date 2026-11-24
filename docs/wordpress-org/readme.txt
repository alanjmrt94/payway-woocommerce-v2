=== Payway Payment Community ===
Contributors: alanjmrt94
Donate link: https://github.com/alanjmrt94
Tags: woocommerce, payments, payway, decidir, argentina
Requires at least: 5.8.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Independent WooCommerce payment gateway for Payway/Decidir. Community fork — not an official Payway product.

== Description ==

**Payway Payment Community** connects your WooCommerce store to Payway/Decidir (browser tokenization + server-side charge).

This is an **independent community fork** maintained by Alan Martinez (https://github.com/alanjmrt94). It is **not** developed, endorsed, or supported by Payway.

Based on https://github.com/payway-ar/plugin-woocommerce 0.3.3, keeping the same plugin folder (`wc-gateway-payway`) and tables (`prisma_payway_*`) so you can migrate without losing banks, cards, promotions, or keys.

= Highlights =

* Classic checkout and WooCommerce Blocks
* Amounts from the server-side order total (not the DOM)
* PHP SDK Decidir/Payway 2.0.10 — amounts in **cents**
* Admin menu: Setup, Banks, Cards, Promotions, Recent Payments, Status
* Cybersource support when your merchant site requires Fraud Detection
* HPOS compatible
* Private key never sent to the storefront

= Manuals =

* Spanish PDF: `docs/payway-woo-v2-manual-es.pdf`
* English PDF: `docs/payway-woo-v2-manual-en.pdf`
* Markdown source: `docs/manual/` and `docs/manual/en/`

= Disclaimer =

Not an official Payway product. Obtain Site Id and API keys from your Payway merchant portal. The fork author does not issue credentials.

== Installation ==

1. Upload the plugin ZIP so the folder `wc-gateway-payway` lands in `wp-content/plugins/`, or install via **Plugins → Add New → Upload Plugin**.
2. Activate **Payway Payment Community**.
3. Go to **WooCommerce → Settings → Payments → Payway Payment Community** and enable the method with Sandbox or Production keys.
4. Open **Payway → Setup**: apply recommended config and seed banks/cards/promotions if needed.
5. Set WooCommerce decimals to **2** and currency to ARS or USD.
6. Run a sandbox payment before going live.

== Frequently Asked Questions ==

= Is this official Payway software? =

No. It is a community fork by Alan Martinez (https://github.com/alanjmrt94).

= Where do I get API keys? =

From the Payway developer portal / merchant backoffice. Sandbox and Production keys differ. This plugin does not create keys.

= Why is the method missing at checkout? =

Check Enabled, keys for the active environment, at least one applicable promotion, ARS/USD currency, decimals = 2, and **Payway → Status**.

= Cybersource error 10734? =

Some merchant sites require Fraud Detection. Keep Cybersource ON and complete billing address (street + door number). Do not turn CS off as a generic fix.

= Does it support refunds from WooCommerce? =

Not yet. The gateway does not declare `refunds` in `supports` in 2.0.x.

== Screenshots ==

1. Setup checklist (Payway → Setup)
2. Gateway settings (WooCommerce → Payments)
3. Recent Payments panel
4. Status / diagnostics

== Changelog ==

= 2.0.5 =
* Plugin Check plugin_repo readiness: escaping, prepared SQL, translators comments, ABSPATH guards
* Bump Tested up to WordPress 7.1

= 2.0.4 =
* WordPress Coding Standards file names for scoped includes (class-wc-payway-*)
* PHPStan 2 cleanup in gateway error formatting

= 2.0.3 =
* Community brand assets; official Payway checkout icon
* Bilingual manuals/PDFs and WordPress.org store kit

= 2.0.2 =
* Admin notices, pagination, bank ordering, Blocks i18n, Setup blockers
* Cybersource unit_price from line totals; typed payment errors
* Spanish/English manuals and store assets preparation

= 2.0.0 =
* Breaking: PHP SDK V2.0.10 — amounts in cents
* Recent Payments, admin UX refresh, HPOS smoke

== Upgrade Notice ==

= 2.0.0 =
Breaking SDK change: amounts are sent in cents. Re-test a sandbox payment after upgrading.

== Store assets ==

WordPress.org SVN `/assets` (not inside the plugin ZIP):

* `banner-772x250.png`, `banner-1544x500.png`
* `icon-128x128.png`, `icon-256x256.png`, `icon.svg`
* Screenshots: `screenshot-1.png` … `screenshot-4.png`

Copies live in this repository under `docs/wordpress-org/` (and source brand under `assets/wordpress-org/`).
