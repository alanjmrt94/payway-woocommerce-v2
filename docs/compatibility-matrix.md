# Compatibilidad

El plugin apunta a ser usable en todo el rango declarado (mínimo → última estable). Abajo, lo **probado** en lab; no implica matriz pairwise completa.

| Componente | Mínimo declarado | Recomendado |
|---|---|---|
| WordPress | 5.8.3 | Última estable |
| WooCommerce | 6.0 | Última estable (probado hasta 11.x) |
| PHP | 7.4 | 8.1+ |
| MySQL / MariaDB | 5.7 / 10.2 | MariaDB 10.11 |

**Checkout:** clásico (`[woocommerce_checkout]`) y **WooCommerce Blocks** cuando existen `AbstractPaymentMethodType` / `wc.wcBlocksRegistry`.

## Versiones testeadas (lab)

### Celda Docker `recommended` — estado final (2026-11-09)

Partió del perfil default y se actualizó in-place (mismos volúmenes):

| Componente | Perfil inicial | Tras upgrades / UAT |
|---|---|---|
| Imagen Docker | `wordpress:6.7.2-php8.1-apache` | misma (WP/WC viven en volumen) |
| WordPress | 6.7.2 | **7.1.4** |
| WooCommerce | 9.4.0 | **11.2.2** (pasó por 7 → 9 → 11) |
| PHP | 8.1 | **8.1.34** |
| MariaDB | 10.11 | **10.11.19** |
| Plugin | — | **1.1.0** |
| HPOS | on | on |
| Pago sandbox | — | OK pedido **#25** (Blocks / Store API, `approved`) |

### Camino de actualización (misma tienda)

| Paso | Qué | Resultado |
|---|---|---|
| 1 | Stack inicial recommended | OK |
| 2 | Update WordPress | OK → 7.1.4 |
| 3 | WooCommerce **7** | OK |
| 4 | WooCommerce **9** | OK |
| 5 | WooCommerce **11** | OK → 11.2.2 + pago sandbox |

Notas:

- Humo funcional + **una** compra sandbox aprobada en el stack final; no cubre todas las celdas WP×WC×PHP.
- Pendiente: smoke HPOS off dedicado; i18n storefront/`es_AR` (ver backlog interno).
