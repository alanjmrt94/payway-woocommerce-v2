# 14. Changelog

Merchant-oriented summary. Full detail: root [`CHANGELOG.md`](../../../CHANGELOG.md).

Notable changes versus original [payway-ar/plugin-woocommerce](https://github.com/payway-ar/plugin-woocommerce) **0.3.3**.

## [2.0.2] — 2026-11-23

### Added / Fixed (November lote)

- Key/promo notices, admin pagination, bank ordering, Blocks i18n, Setup blockers / debit 1-installment.
- CS `unit_price` from line totals; typed errors; Debug OFF without wide `full_response` meta.
- Spanish manual under `docs/manual/`; version **2.0.2**.

## [2.0.0] — 2026-11-23

### Changed

- **Breaking:** PHP SDK Decidir/Payway **V1.4.0 → V2.0.10**. Amounts in **cents** (SDK V2 no longer ×100).
- Unified vendor under `wc-gateway-payway/decidir/` (Composer bridge).
- SDK PHP prod endpoint: `https://ventasonline.payway.com.ar`.
- Admin: menu **Setup → Banks → Cards → Promotions → Recent Payments → Status**; WC settings sections; tips and notices.
- **Recent Payments** panel (`PaymentList`) with WC order link.
- Version **2.0.0**; SDK codebase `2.0.10`.

### Verified (UAT sandbox)

- Classic #14 and Blocks #16 approved (CS green).
- Typed decline PAN `…4999`.
- Smoke `min` / `recommended` / `latest-hpos-on` / `latest-hpos-off`.

### Migration

Replace folder/ZIP; re-test sandbox. No settings/table migration.
