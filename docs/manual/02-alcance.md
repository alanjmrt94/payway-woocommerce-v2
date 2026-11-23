# 2. Alcance, limitaciones y novedades vs oficial 0.3.3

## Incluido en 2.0.0

- Checkout **clásico** (`[woocommerce_checkout]`) y **WooCommerce Blocks** (Store API / payment method registration)
- Sandbox y Producción (Site Id + Public Key + Private Key por ambiente)
- Menú admin **Payway**: Instalación → Bancos → Tarjetas → Promociones → Últimos pagos → Estado
- Seed de catálogo Decidir y «Aplicar config. recomendada»
- Promociones con **Sin vencimiento** (`no_expiry`)
- Cybersource (antifraude) cuando el comercio lo tiene habilitado
- HPOS (High-Performance Order Storage)
- SDK PHP **decidir2/php-sdk V2.0.10** (montos en **centavos**)
- Panel **Últimos pagos** vía `PaymentList` (con link al pedido WooCommerce)
- i18n: strings en inglés; locales `es*` cargan `es_AR`

## Limitaciones conocidas

- Refunds / voids nativos de WooCommerce: el gateway **aún no** declara `refunds` en `supports`
- Un solo Site Id por instalación (no multi-establecimiento en el mismo gateway)
- Matriz pairwise completa WP×WC×PHP: no cubierta; ver smoke en la matriz de compatibilidad
- No es el plugin del App Store / portal oficial de Payway; el soporte del fork es vía el repositorio GitHub del autor

## Novedades respecto del oficial 0.3.3

| Tema | Oficial 0.3.3 (referencia) | Payway Payment Community 2.0.0 |
|---|---|---|
| Nombre / soporte | Producto portal Payway | Fork Community; soporte GitHub del autor |
| WooCommerce Blocks | No | Sí (feature-detect) |
| Monto cobrado | Riesgo de confiar en DOM (#64) | Total desde el **pedido en servidor** |
| Refresh de cuotas | Display podía quedar viejo | Clásico: `updated_checkout`; Blocks: recálculo al cambiar carrito |
| Private key en storefront | Histórico problemático | **No** se envía al frontend |
| Pedidos rechazados | Podían quedar `cancelled` | Estado `failed` |
| Onboarding | Manual puro | **Instalación** + checklist + seed + Status |
| Promos sin fecha | Fechas obligatorias en la práctica | Checkbox **Sin vencimiento** |
| SDK PHP | V1.4.x (×100 en SDK) | **V2.0.10**; el plugin envía centavos |
| Admin pagos | — | **Últimos pagos** + link a pedido |
| WC testeado | Manual histórico ≤ 8.4 | Declarado hasta **11.x**; smoke lab hasta 11.3.1 |
| Packaging ZIP | Issues de carpeta raíz | Carpeta `wc-gateway-payway` en la raíz del ZIP |

Detalle versión a versión: [14 — Changelog](14-changelog.md) y [`CHANGELOG.md`](../../CHANGELOG.md) en la raíz del repo.
