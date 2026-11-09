# Payway Payment Community

Gateway de pago para WooCommerce basado en Payway/Decidir. Es un **fork independiente**, no un producto oficial de Payway.

> No está desarrollado, respaldado ni soportado por Payway. El mantenimiento corre por cuenta de Alan Martinez ([alanjmrt94](https://github.com/alanjmrt94)).

**Versión:** la del header `Version:` en [`wc-gateway-payway/index.php`](wc-gateway-payway/index.php) (única fuente; ver [`CHANGELOG.md`](CHANGELOG.md)). WooCommerce tested up to 11.0 · checkout clásico y Blocks.

## Instalación rápida

1. Descargá el ZIP de Releases (tiene que traer la carpeta `wc-gateway-payway` en la raíz).
2. **Plugins → Añadir nuevo → Subir plugin**, o copiá la carpeta a `wp-content/plugins/`.
3. Activá **Payway Payment Community**.
4. **WooCommerce → Ajustes → Pagos → Payway Payment Community**: Enabled + claves.
5. **Payway → Instalación**: checklist y defaults (banco Todos, tarjetas, promociones).

Más detalle: [`wc-gateway-payway/README.md`](wc-gateway-payway/README.md) · docs: [`docs/README.md`](docs/README.md) · cambios: [`CHANGELOG.md`](CHANGELOG.md)

## Destacados 1.1.x

- Blocks Checkout + checkout clásico (mismo `process_payment`).
- Instalación / Status con checklist y catálogo Decidir por defecto.
- Total cobrado desde el pedido en servidor; cuotas clásicas se refrescan en `updated_checkout`.

## Licencia

[GPL-2.0-or-later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html) — texto en [`LICENSE`](LICENSE), notas en [`docs/license.md`](docs/license.md).

Copyright (C) 2026 Alan Martinez.  
Basado en trabajo previo de IURCO / Prisma SA ([payway-ar/plugin-woocommerce](https://github.com/payway-ar/plugin-woocommerce)).
