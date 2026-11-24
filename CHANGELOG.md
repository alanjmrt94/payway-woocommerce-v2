# Changelog

Cambios notables de **Payway Payment Community** respecto del plugin original  
[`payway-ar/plugin-woocommerce`](https://github.com/payway-ar/plugin-woocommerce) **0.3.3**.

El formato sigue [Keep a Changelog](https://keepachangelog.com/) y [SemVer](https://semver.org/).

## [2.0.5] — 2026-11-24

### Fixed

- Cumplimiento Plugin Check (`plugin_repo`): escape de salida admin, `$wpdb->prepare` en factories, comentarios `translators:`, guards `ABSPATH`, text domain y notas de pedido.
- `Tested up to` en `readme.txt` → **7.1**.

### Changed

- Versión del plugin a **2.0.5**.

### Conteo SemVer

`fix=1` desde `2.0.4` → **N=1** → **2.0.5**.

---

## [2.0.4] — 2026-11-24

### Fixed

- Nombres de archivo WPCS para `WC_Payway_Errors` / Compatibility / Integrity Validator (`class-wc-payway-*`).
- Ajuste menor en `format_gateway_failure()` para PHPStan 2 (`is_array` redundante).

### Changed

- Versión del plugin a **2.0.4**.

### Conteo SemVer

`fix=1` desde `2.0.3` → **N=1** → **2.0.4**.

---

## [2.0.3] — 2026-11-23

### Added

- Brand Community (`assets/`) + favicon/isotipo en plugin; logo oficial Payway solo en checkout.
- Manuales PDF oficiales `docs/payway-woo-v2-manual-{es,en}.pdf` + espejo EN + capturas 07/08/10.
- Kit WordPress.org (`docs/wordpress-org/`, `readme.txt`).

### Changed

- Menú admin usa brand Community; se eliminan assets legacy de `img/` y `card-logos.png`.
- Versión del plugin a **2.0.3**.

### Conteo SemVer

`feat=1` desde `2.0.2` → **N=1** → **2.0.3**.

---

## [2.0.2] — 2026-11-23

### Added

- Blocks storefront `config.i18n` + strings `es_AR`.
- Notices de error (dashboard / Pagos / Payway) si Enabled sin keys o sin promos aplicables; blockers de Setup Status.
- Paginación real (LIMIT/OFFSET) en bancos/tarjetas/promos; bancos ordenados por nombre (#58).
- Meta box de pago en el pedido; redirect a Instalación al activar; detección débito sin plan de 1 cuota.
- Adaptador Decidir + helper `Payway\Support\Status`; filtro `woocommerce_payway_checkout_posted_data`.
- Manual ES versionado en `docs/manual/` (HPOS, FAQ 10734/centavos/notices).

### Fixed

- Cybersource `unit_price` desde total de línea / cantidad.
- Errores tipados en path de pago (sin `wc_add_notice` / JSON crudo al comprador).
- `_payway_full_response` solo con Debug ON; uninstall por whitelist de options.

### Changed

- Bootstrap lifecycle en `PaywayWC` (activate/deactivate suave); `decidir.js` filtrable y en footer cuando aplica.
- Validaciones create/update de bancos, tarjetas y promociones en factories.
- Versión del plugin a **2.0.2**.

### Conteo SemVer

`feat=1` + `fix=1` → **N=2** desde `2.0.0` → **2.0.2**.

---

## [2.0.0] — 2026-11-23

### Changed

- **Breaking:** SDK PHP Decidir/Payway **V1.4.0 → V2.0.10** (`decidir2/php-sdk`). El SDK V2 **ya no multiplica ×100** los montos: el plugin envía `amount` y `purchase_totals.amount` en **centavos**.
- Vendor unificado: `wc-gateway-payway/decidir/` es un puente Composer; se eliminó el árbol duplicado `decidir/Decidir/` y el vendor anidado basura.
- Endpoint prod del SDK PHP: `https://ventasonline.payway.com.ar` (lo define el SDK; el `decidir.js` de checkout sigue en hosts frontend).
- Connector `developer` / `service` atribuidos a Payway Payment Community.
- Admin: menú **Setup → Banks → Cards → Promotions → Recent Payments → Status**; settings WC agrupados (General / Sandbox / Production); tips en keys; notices si faltan keys o promos; CRUD con ayuda/i18n/`Edit` domain.
- Panel **Recent Payments** (`PaymentList`) con link a pedido WC por meta `_payway_*`.
- Versión del plugin a **2.0.0**; constante SDK codebase `2.0.10`.

### Verified (UAT sandbox 2026-11-23)

- Wipe `recommended` + install de cero → Apply recommended → keys → seed catálogo → checkout clásico (#14 approved, CS green, amount centavos) y Blocks (#16 approved).
- Rechazo tipado PAN `…4999` (mensaje invalid_card).
- CS OFF en sitio sandbox que exige Fraud Detection → `cybersource_error` / 10734 (limitación del comercio sandbox, no bug de monto).
- Refund automático WC: gateway aún sin `refunds` en `supports` (documentado).
- Integration smoke: `min`, `recommended`, `latest-hpos-on`, `latest-hpos-off` OK.

### Migration

- Actualizar el plugin a 2.0.0 (reemplazar carpeta / ZIP). No hace falta migrar settings ni tablas.
- Re-probar un pago sandbox (idealmente con Cybersource ON) tras el upgrade.

---

## [1.1.5] — 2026-11-09

### Changed

- Cache de settings del gateway: request-level + `wp_cache` en `wc_payway_get_config_options`; invalidación al add/update/delete de `woocommerce_payway_gateway_settings`; filtro `payway_skip_settings_cache`.
- Blocks payment method lee settings vía el getter cacheado.
- Versión del plugin a **1.1.5**.

---

## [1.1.4] — 2026-11-09

### Security

- Sanitize sistemático de `$_POST` en create/update de banks/cards/promotions (`WC_Payway_Admin_Sanitize`).
- Capabilities alineadas: menú Payway y mutaciones usan `manage_woocommerce` (antes menú en `manage_options`).
- Redirects admin con `wp_safe_redirect`; mensajes de validación de promociones escapados.

### Changed

- Versión del plugin a **1.1.4**.

---

## [1.1.3] — 2026-11-09

### Changed

- Checkout clásico: tokenización vía `checkout_place_order_payway_gateway` (ya no click en `#place_order`).
- Validación explícita del SDK Decidir antes de crear token.
- Eliminados assets JS muertos (`payment.js`, `form.js`, `card.js`, `jquery.card.js`, `decidir.js` local).
- Versión del plugin a **1.1.3**.

---

## [1.1.2] — 2026-11-09

### Fixed

- Cybersource: montos de `purchase_totals` en unidades mayores (un solo ×100 en el SDK); line items en minor units sin doble strip/escala.
- Idempotencia: `site_transaction_id` estable `payway-{order_id}` + lock transient 30s en `process_payment`.
- Checkout: rechazos tipados desde `status_details.error.type` (`invalid_card` → `PPC_CARD_001`; emisor → `PPC_PAY_002`).

### Changed

- Versión del plugin a **1.1.2**.
- Cache de promociones aplicables (transient por día, invalidación en CRUD banks/cards/promos; filtro `payway_skip_promo_cache`).

### UAT

- Lab `recommended`: pedido **#28** approved / CS green / STID `payway-28`; pedido **#29** failed con UI tipada (John Doe).

---

## [1.1.1] — 2026-11-09

### Fixed

- Checkout clásico: el display de cuotas refresca el `cart_total` en `updated_checkout` (shipping / cupones) vía campo hidden + fragmento WooCommerce + rebind del formulario ([#64](https://github.com/payway-ar/plugin-woocommerce/issues/64) residual display).
- Blocks: al cambiar el total del carrito se limpian las cuotas seleccionadas para recalcular montos.

### Changed

- Versión del plugin a **1.1.1** (única fuente: header `Version:` en `index.php`; Activator y assets la leen con `get_file_data`).
- README del plugin: Blocks ya soportado; nota de refresh de cuotas en clásico.

---

## [1.1.0] — 2026-11-08

### Added

- Compatibilidad **WooCommerce Blocks Checkout** (feature-detect): `AbstractPaymentMethodType` + `registerPaymentMethod`, formulario con banco/tarjeta/cuotas y tokenización Decidir; mismo `process_payment` que el checkout clásico.
- Menú **Payway → Instalación**: checklist obligatorio + recomendado del manual (decimales=2, Cybersource, título, Sandbox, debug, planes de cuotas), propuestas por ítem, botón «Aplicar config. recomendada» y seed de defaults.
- Catálogo Decidir seleccionable (Visa/MC/Cabal/Amex/etc.) con botón para agregar tarjetas y promociones por defecto (banco **Todos**, planes 1/3/6/9/12/18 o 1 cuota; opción MiPyME 3→13 / 6→16).
- Promociones con checkbox **Sin vencimiento** (`no_expiry`, activo por defecto): no se valida `to_date` en checkout.
- **Payway → Status**: diagnóstico de disponibilidad con los mismos checks y links de acción.
- Manual actualizado (04/05/06/09) con el flujo WooCommerce → Pagos + catálogo.

### Changed

- Versión del plugin a **1.1.0**.
- Schema **self-heal** (`ensure_core_schema`): crea tablas faltantes, agrega `no_expiry` y columnas core sin depender del bump de versión; no borra settings en activate; DDL de promos sin FK frágiles.
- **i18n**: strings del código en inglés; cualquier locale español (`es`, `es_ES`, `es_MX`, `es_AR`, …) carga `es_AR`; el resto queda en inglés. Menú Setup / Instalación según idioma.
- Título default del medio: **Checkout Payway**; descripción default traducida EN/ES.
- Nota de catálogo Amex Pura: “Decidir 2.0, not Ventas Online” (EN/ES).
- Status: check de superficie de checkout (clásico **o** Blocks con API); ya no exige solo shortcode si Blocks está soportado.
- `WC tested up to` → **11.0** tras upgrade in-place en celda `recommended` (WP **7.1.4**, WC **7 → 9 → 11.2.2**, PHP 8.1, MariaDB 10.11) y pago sandbox OK (pedido #25, Blocks).

---

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
- [#64](https://github.com/payway-ar/plugin-woocommerce/issues/64) — total y fees desde el pedido en servidor, no desde el DOM (UAT OK; display de cuotas cerrado en 1.1.1).
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

- Sin refunds / voids nativos desde WooCommerce.
- Pago sandbox de punta a punta con evidencia formal: pendiente (el camino WP/WC 7→9→11 fue smoke funcional).
- Publicación en WooCommerce Marketplace / WordPress.org: pendiente.

### Upgrade notes

1. Desactivar el plugin original.
2. Reemplazar `wp-content/plugins/wc-gateway-payway` por esta versión (o subir el ZIP de release).
3. Activar **Payway Payment Community**.
4. Revisar credenciales sandbox/prod y hacer una operación de prueba antes de producción.

Settings y tablas (`woocommerce_payway_gateway_settings`, `prisma_payway_*`) se reutilizan con el mismo gateway id.

---

[2.0.5]: https://github.com/alanjmrt94/payway-woocommerce-v2/releases/tag/v2.0.5
[2.0.4]: https://github.com/alanjmrt94/payway-woocommerce-v2/releases/tag/v2.0.4
[2.0.3]: https://github.com/alanjmrt94/payway-woocommerce-v2/releases/tag/v2.0.3
[2.0.2]: https://github.com/alanjmrt94/payway-woocommerce-v2/releases/tag/v2.0.2
[2.0.0]: https://github.com/alanjmrt94/payway-woocommerce-v2/releases/tag/v2.0.0
[1.1.5]: https://github.com/alanjmrt94/payway-woocommerce-v2/releases/tag/v1.1.5
[1.1.4]: https://github.com/alanjmrt94/payway-woocommerce-v2/releases/tag/v1.1.4
[1.1.3]: https://github.com/alanjmrt94/payway-woocommerce-v2/releases/tag/v1.1.3
[1.1.2]: https://github.com/alanjmrt94/payway-woocommerce-v2/releases/tag/v1.1.2
[1.1.1]: https://github.com/alanjmrt94/payway-woocommerce-v2/releases/tag/v1.1.1
[1.1.0]: https://github.com/alanjmrt94/payway-woocommerce-v2/releases/tag/v1.1.0
[1.0.0]: https://github.com/alanjmrt94/payway-woocommerce-v2/releases/tag/v1.0.0
