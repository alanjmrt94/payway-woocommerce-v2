# Changelog

Cambios notables de **Payway Payment Community** respecto del plugin original  
[`payway-ar/plugin-woocommerce`](https://github.com/payway-ar/plugin-woocommerce) **0.3.3**.

El formato sigue [Keep a Changelog](https://keepachangelog.com/) y [SemVer](https://semver.org/).

## [1.0.0] — 2026-11-08

Primera versión del fork. No es un producto oficial de Payway.

Se mantienen a propósito la carpeta `wc-gateway-payway`, el gateway id `payway_gateway`, el text domain y las tablas `prisma_payway_*`, para no romper settings ni datos al migrar desde el plugin original.

### Added

- Identidad **Payway Payment Community** (disclaimer de fork independiente).
- Licencia **GPL-2.0-or-later** completa (`LICENSE` + avisos en el plugin).
- Documentación básica: README, matriz de compatibilidad provisional, notas de licencia.
- Declaración de compatibilidad con **HPOS** y meta de pedidos vía API de WooCommerce.
- Validación en servidor de promoción, plan de cuotas, tarjeta/banco y montos (ya no se confía solo en el browser).
- `validate_fields()` real en el checkout.
- Catálogo de errores internos más claros para admin y comprador.
- Modo debug de logs (apagado por defecto) con redacción de claves y tokens.

### Security

- La **private key** ya no se envía al storefront.
- Borrados de bancos/tarjetas/promos con nonce y capability.
- Escape de salida en checkout y detalle de pedido.
- La meta guardada del gateway es un subset sanitizado (sin token/PAN).
- Si el pedido ya está pagado, no se vuelve a completar el cobro; se revisan status `approved` / `accredited` (idempotencia más fuerte: pendiente).

### Fixed

Mitigaciones respecto de issues abiertos en el repo original (el fork no espera a que upstream los cierre):

- [#71](https://github.com/payway-ar/plugin-woocommerce/issues/71) — `payment_method_id` / errores de request más claros (falta UAT sandbox).
- [#64](https://github.com/payway-ar/plugin-woocommerce/issues/64) — total y fees desde el pedido en servidor, no desde el DOM (falta UAT con FX).
- [#59](https://github.com/payway-ar/plugin-woocommerce/issues/59) — dónde obtener Site Id / keys.
- [#35](https://github.com/payway-ar/plugin-woocommerce/issues/35) / [#47](https://github.com/payway-ar/plugin-woocommerce/issues/47) — packaging con carpeta raíz correcta.
- [#33](https://github.com/payway-ar/plugin-woocommerce/issues/33) / [#43](https://github.com/payway-ar/plugin-woocommerce/issues/43) — carga temprana del text domain (faltan cadenas del form).

### Changed

- Nombre visible Community; **sin** renombrar la carpeta del plugin (migración más simple).
- Pedidos con pago rechazado pasan a `failed` (antes `cancelled`).
- `needs_setup()` respeta si ya hay credenciales del ambiente activo.
- Versión del SDK vendored alineada a **1.4.0**.
- Headers de compatibilidad WP / WC / PHP documentados (WC tested up to 9.4, provisional).

### Known limitations

- Solo checkout **clásico** (WooCommerce Blocks queda para más adelante).
- Sin refunds / voids nativos desde WooCommerce.
- Todavía no hay pago sandbox real de punta a punta en este fork.
- Publicación en WooCommerce Marketplace / WordPress.org: pendiente.

### Upgrade notes

1. Desactivar el plugin original.
2. Reemplazar `wp-content/plugins/wc-gateway-payway` por esta versión (o subir el ZIP de release).
3. Activar **Payway Payment Community**.
4. Revisar credenciales sandbox/prod y hacer una operación de prueba antes de producción.

Settings y tablas (`woocommerce_payway_gateway_settings`, `prisma_payway_*`) se reutilizan con el mismo gateway id.

---

[1.0.0]: https://github.com/alanjmrt94/payway-woocommerce-v2/releases/tag/v1.0.0
