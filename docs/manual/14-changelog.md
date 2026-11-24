# 14. Changelog

Resumen orientado al comercio. Detalle completo: [`CHANGELOG.md`](../../CHANGELOG.md) en la raíz del repo.

Cambios notables respecto del plugin original [payway-ar/plugin-woocommerce](https://github.com/payway-ar/plugin-woocommerce) **0.3.3**.

## [2.0.3] — 2026-11-23

### Added

- Brand Community + logo oficial Payway en checkout; PDF/manual EN; kit WP.org.
- Versión **2.0.3**.

## [2.0.2] — 2026-11-23

### Added / Fixed (lote noviembre)

- Notices keys/promos, paginación admin, orden de bancos, i18n Blocks, Setup blockers / débito 1 cuota.
- CS `unit_price` por línea; errores tipados; Debug OFF sin meta `full_response` amplio.
- Manual ES en `docs/manual/`; versión **2.0.2**.

## [2.0.0] — 2026-11-23

### Changed

- **Breaking:** SDK PHP Decidir/Payway **V1.4.0 → V2.0.10**. Montos en **centavos** (el SDK V2 ya no ×100).
- Vendor unificado bajo `wc-gateway-payway/decidir/` (puente Composer).
- Endpoint prod PHP del SDK: `https://ventasonline.payway.com.ar`.
- Admin: menú **Instalación → Bancos → Tarjetas → Promociones → Últimos pagos → Estado**; settings WC en secciones General / Sandbox / Production; tips y notices.
- Panel **Últimos pagos** (`PaymentList`) con link a pedido WC.
- Versión **2.0.0**; SDK codebase `2.0.10`.

### Verified (UAT sandbox)

- Classic #14 y Blocks #16 approved (CS green).
- Rechazo tipado PAN `…4999`.
- Smoke `min` / `recommended` / `latest-hpos-on` / `latest-hpos-off`.

### Migration

Reemplazar carpeta/ZIP; re-probar sandbox. Sin migración de settings/tablas.

---

## [1.1.5] — 2026-11-09

Cache de settings del gateway (request-level + `wp_cache`) e invalidación en CRUD de options.

## [1.1.4] — 2026-11-09

Sanitize admin POST; capabilities `manage_woocommerce`; redirects seguros.

## [1.1.3] — 2026-11-09

Tokenización clásica vía `checkout_place_order_payway_gateway`; limpieza de JS muertos.

## [1.1.2] — 2026-11-09

Cybersource amounts; idempotencia `payway-{order_id}`; rechazos tipados; cache de promos.

## [1.1.1] — 2026-11-09

Refresh de montos de cuotas en clásico (`updated_checkout`) y recálculo en Blocks.

## [1.1.0] — 2026-11-08

Blocks Checkout; menú Instalación + Status; seed catálogo; `no_expiry`; i18n `es*`; WC tested up to 11.0.

## [1.0.0] — 2026-11-08

Primera versión del fork Community: identidad, GPL, hardening, validación servidor, HPOS, packaging ZIP correcto, migración sin romper `prisma_payway_*`.
