# 3. Requisitos y matriz de versiones

La versión del plugin se declara **solo** en el header `Version:` de `wc-gateway-payway/index.php`.

Fuente ampliada: [`docs/compatibility-matrix.md`](../compatibility-matrix.md).

## Declarado (mínimo → recomendado)

| Componente | Mínimo | Recomendado |
|---|---|---|
| WordPress | 5.8.3 | Última estable |
| WooCommerce | 6.0 | Última estable (probado hasta 11.x) |
| PHP | 7.4 | 8.1+ |
| MySQL / MariaDB | 5.7 / 10.2 | MariaDB 10.11 / MySQL 8 |
| Checkout | Clásico o Blocks con API de payment methods | Ambos OK |
| HPOS | off u on | on |
| Decimales WooCommerce | **2** (obligatorio) | 2 |
| Moneda | ARS o USD | ARS o USD |

## Probado en lab — UAT 2.0.0 (2026-11-23)

Celda Docker `recommended` tras wipe + install de cero:

| Componente | Valor |
|---|---|
| WordPress | 6.7.2 |
| WooCommerce | 9.4.0 |
| PHP | 8.1.34 |
| MariaDB | 10.11 |
| Plugin / SDK | 2.0.0 / 2.0.10 |
| Classic sandbox | Pedido #14 approved (CS green) |
| Blocks sandbox | Pedido #16 approved (CS green) |

### Smoke integration (misma fecha)

| Perfil | Resultado |
|---|---|
| `min` (WP 5.8.3 / WC 6.0 / PHP 7.4) | OK |
| `recommended` | OK |
| `latest-hpos-on` (WP 7.1.5 / WC 11.3.1 / PHP 8.3) | OK |
| `latest-hpos-off` | OK |

Notas:

- No implica cobertura pairwise de todas las combinaciones.
- En el site_id sandbox usado en UAT, Cybersource OFF puede fallar con código 10734 (Fraud Detection requerido por el comercio); no es un bug de scaling de montos del SDK V2.
