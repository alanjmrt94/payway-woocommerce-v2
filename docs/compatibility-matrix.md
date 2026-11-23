# Compatibilidad

El plugin apunta a ser usable en todo el rango declarado (mínimo → última estable). Abajo, lo **probado** en lab; no implica matriz pairwise completa.

La versión del plugin se declara **solo** en el header `Version:` de `wc-gateway-payway/index.php`.

| Componente | Mínimo declarado | Recomendado |
|---|---|---|
| WordPress | 5.8.3 | Última estable |
| WooCommerce | 6.0 | Última estable (probado hasta 11.x) |
| PHP | 7.4 | 8.1+ |
| MySQL / MariaDB | 5.7 / 10.2 | MariaDB 10.11 |

**Checkout:** clásico (`[woocommerce_checkout]`) y **WooCommerce Blocks** cuando existen `AbstractPaymentMethodType` / `wc.wcBlocksRegistry`.

## Versiones testeadas (lab)

### Celda Docker `recommended` — UAT 2.0.0 (2026-11-23)

Wipe completo + install de cero (`wp-env-down --volumes` → `wp-env-up`):

| Componente | Valor |
|---|---|
| Imagen Docker | `wordpress:6.7.2-php8.1-apache` |
| WordPress | **6.7.2** |
| WooCommerce | **9.4.0** |
| PHP | **8.1.34** |
| MariaDB | 10.11 |
| Plugin | **2.0.0** UAT base (actual **2.0.2**; SDK PHP **2.0.10**) |
| HPOS | on |
| Classic sandbox | OK pedido **#14** (`approved`, CS green, amount `300000` centavos, STID `payway-14`) |
| Blocks sandbox | OK pedido **#16** (`approved`, CS green, STID `payway-16`) |
| Rechazo tipado | PAN `…4999` → UI invalid_card |
| Recent Payments | `PaymentList` lista #14/#15/#16 con link a pedido |

### Smoke integration (misma fecha)

| Perfil | Resultado |
|---|---|
| `min` (WP 5.8.3 / WC 6.0 / PHP 7.4) | failed=0 |
| `wp60-wc60-php74` (WP 6.0.3 / WC 6.0 / PHP 7.4) | failed=0 (2026-11-23) |
| `recommended` | failed=0 |
| `latest-hpos-on` (WP 7.1.5 / WC 11.3.1 / PHP 8.3) | failed=0 |
| `latest-hpos-off` (WP 6.7.2 / WC 9.4 / HPOS off) | failed=0 |

Notas:

- CS OFF contra este site_id sandbox falla con **10734** (Fraud Detection requerido por el comercio). No es bug de montos: con CS ON los amounts V2 (centavos) OK. Detalle interno: `ci/.cursor/docs/cybersource-site-fd.md`.
- Refund nativo WC aún no está en `supports` del gateway.

### Pairwise (cobertura lab, no factorial completa)

| Celda | WP / WC / PHP | Qué cubre | Gap |
|---|---|---|---|
| `min` | 5.8.3 / 6.0 / 7.4 | Piso declarado + integration | Smoke pago sandbox opcional |
| `wp60-wc60-php74` | 6.0 / 6.0 / 7.4 | Woo 6 era intermedia | Smoke pago si keys |
| `recommended` | 6.7 / 9.4 / 8.1 | Diario + UAT sandbox | — |
| `latest-hpos-on` | latest / latest / 8.3 | Frontera + HPOS on | — |
| `latest-hpos-off` | 6.7 / 9.4 / 8.x | HPOS off | — |
