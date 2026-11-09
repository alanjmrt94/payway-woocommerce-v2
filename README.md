# Payway Payment Community

Gateway de pago para WooCommerce basado en Payway/Decidir. Es un **fork independiente**, no un producto oficial de Payway.

> No está desarrollado, respaldado ni soportado por Payway. El mantenimiento corre por cuenta de Alan Martinez ([alanjmrt94](https://github.com/alanjmrt94)).

**Versión actual:** ver header `Version:` en [`wc-gateway-payway/index.php`](wc-gateway-payway/index.php) · detalle en [`CHANGELOG.md`](CHANGELOG.md) · release [v1.1.1](https://github.com/alanjmrt94/payway-woocommerce-v2/releases/tag/v1.1.1).

Partió del plugin original [payway-ar/plugin-woocommerce](https://github.com/payway-ar/plugin-woocommerce) **0.3.3** y lo mantiene como fork Community (misma carpeta `wc-gateway-payway` y tablas `prisma_payway_*` para migrar sin romper datos).

## Destacados vs 0.3.3

### Checkout y montos

- **WooCommerce Blocks** + checkout clásico (mismo `process_payment`).
- El total cobrado sale del **pedido en servidor**, no del DOM ([#64](https://github.com/payway-ar/plugin-woocommerce/issues/64)).
- Cuotas clásicas se **refrescan** al cambiar envío/cupones (`updated_checkout`); en Blocks se recalculan si cambia el carrito.
- `validate_fields()` real + validación en servidor de promo / plan / tarjeta / banco.

### Seguridad y robustez

- La **private key** ya no se envía al storefront.
- Deletes admin con nonce + capability; escape en checkout y pedido.
- Meta del gateway sanitizada (sin token/PAN); logs debug con redacción (apagado por defecto).
- Pedidos rechazados → estado `failed` (antes `cancelled`).
- Schema **self-heal** (tablas/columnas, promos sin FK frágiles, `no_expiry`).

### Admin y puesta en marcha

- **Payway → Instalación**: checklist + «Aplicar config. recomendada» + seed de catálogo Decidir (banco Todos, tarjetas, planes).
- **Payway → Status** con los mismos checks y links de acción.
- Promociones con **Sin vencimiento** (`no_expiry`).
- Compatible con **HPOS**; lab smoke WP/WC hasta **11.x** / PHP 8.3.

### i18n y packaging

- Strings en inglés; locales `es*` cargan `es_AR`.
- ZIP de release con carpeta raíz correcta (`wc-gateway-payway`).
- Licencia **GPL-2.0-or-later** completa + docs de matriz / manual.

Detalle versión a versión: [`CHANGELOG.md`](CHANGELOG.md).

## Instalación rápida

1. Descargá el ZIP de [Releases](https://github.com/alanjmrt94/payway-woocommerce-v2/releases) (carpeta `wc-gateway-payway` en la raíz).
2. **Plugins → Añadir nuevo → Subir plugin**, o copiá la carpeta a `wp-content/plugins/`.
3. Activá **Payway Payment Community**.
4. **WooCommerce → Ajustes → Pagos → Payway Payment Community**: Enabled + claves.
5. **Payway → Instalación**: checklist y defaults (banco Todos, tarjetas, promociones).

Más detalle: [`wc-gateway-payway/README.md`](wc-gateway-payway/README.md) · docs: [`docs/README.md`](docs/README.md).

## Licencia

[GPL-2.0-or-later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html) — texto en [`LICENSE`](LICENSE), notas en [`docs/license.md`](docs/license.md).

Copyright (C) 2026 Alan Martinez.  
Basado en trabajo previo de IURCO / Prisma SA ([payway-ar/plugin-woocommerce](https://github.com/payway-ar/plugin-woocommerce)).
