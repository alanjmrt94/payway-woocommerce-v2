# 1. Introduction

**Payway Payment Community** is a WooCommerce payment gateway that connects the store to Payway/Decidir (browser tokenization + server-side charge).

It is an **independent fork** maintained by Alan Martinez ([alanjmrt94](https://github.com/alanjmrt94)), based on the plugin published at [payway-ar/plugin-woocommerce](https://github.com/payway-ar/plugin-woocommerce) (IURCO / Prisma SA), reference version **0.3.3**.

## Disclaimer

This project is **not** an official Payway product. It is not developed, maintained, endorsed, or certified by Payway. Use does not imply any warranty of fitness, compatibility, security, or support from Payway. Responsibility for the code rests solely with the fork author.

## What this manual covers

- Installation and go-live (Setup)
- Sandbox and Production credentials
- Banks, cards, promotions, and installment plans
- Classic checkout and WooCommerce Blocks
- Diagnostics (**Status**), **Recent Payments**, and common errors
- Migration from the official plugin and changelog for **2.0.x**

## Technical identity (migration)

Kept on purpose:

| Concept | Value |
|---|---|
| Plugin folder | `wc-gateway-payway` |
| Gateway id | `payway_gateway` |
| Text domain | `wc-gateway-payway` |
| Tables | `prisma_payway_*` |
| Settings option | `woocommerce_payway_gateway_settings` |

You can replace the original plugin without losing banks, cards, promotions, or keys.
