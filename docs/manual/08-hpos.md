# 8. HPOS (High-Performance Order Storage)

## Qué es

HPOS guarda los pedidos en tablas propias de WooCommerce en lugar de `wp_posts` / `postmeta`. Mejora el rendimiento en tiendas con mucho volumen.

Activación típica: **WooCommerce → Ajustes → Avanzado → Características** → *Almacenamiento de pedidos de alto rendimiento* (nombre exacto según versión/idioma).

## Compatibilidad del plugin

Payway Payment Community **declara** compatibilidad con `custom_order_tables`. Los metadatos `_payway_*` se leen y escriben con la API CRUD del pedido (`WC_Order::get_meta` / `update_meta_data`), no solo con `get_post_meta`.

## Qué verificar después de un pago

1. Abrí el pedido en el admin (HPOS ON u OFF).
2. Revisá el bloque **Payway Payment Information** (Trans. ID, Site Trans. ID, ticket, CS Decision, planes).
3. Desde **Payway → Últimos pagos**, el link al pedido debe abrir el mismo registro.

## ON vs OFF

| Modo | Qué probar |
|---|---|
| HPOS ON | Pedido en tablas HPOS; meta vía CRUD; Últimos pagos → pedido |
| HPOS OFF | Pedido clásico `shop_order`; misma meta visible |

Smoke lab: celdas `latest-hpos-on` y `latest-hpos-off` (integration failed=0). UAT sandbox con HPOS on en `recommended` / upgrades locales.

## Lecturas

- Matriz: [`../compatibility-matrix.md`](../compatibility-matrix.md)
- README del plugin: mención HPOS + este capítulo
