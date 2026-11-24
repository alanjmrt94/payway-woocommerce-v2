# 8. HPOS (High-Performance Order Storage)

## What it is

HPOS stores orders in WooCommerce’s own tables instead of `wp_posts` / `postmeta`. It improves performance on high-volume stores.

Typical enablement: **WooCommerce → Settings → Advanced → Features** → High-performance order storage (exact label depends on version/locale).

## Plugin compatibility

Payway Payment Community **declares** compatibility with `custom_order_tables`. `_payway_*` metadata is read/written via the order CRUD API (`WC_Order::get_meta` / `update_meta_data`), not only `get_post_meta`.

## What to verify after a payment

1. Open the order in admin (HPOS ON or OFF).
2. Check the **Payway Payment Information** box (Trans. ID, Site Trans. ID, ticket, CS Decision, plans).
3. From **Payway → Recent Payments**, the order link should open the same record.

## ON vs OFF

| Mode | What to test |
|---|---|
| HPOS ON | Order in HPOS tables; meta via CRUD; Recent Payments → order |
| HPOS OFF | Classic `shop_order`; same meta visible |

Lab smoke: `latest-hpos-on` and `latest-hpos-off` (integration failed=0).

## Further reading

- Matrix: [`../../compatibility-matrix.md`](../../compatibility-matrix.md)
- Plugin README: HPOS mention + this chapter
