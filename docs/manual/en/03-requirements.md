# 3. Requirements and version matrix

Plugin version is declared **only** in the `Version:` header of `wc-gateway-payway/index.php`.

Extended source: [`docs/compatibility-matrix.md`](../../compatibility-matrix.md).

## Declared (minimum → recommended)

| Component | Minimum | Recommended |
|---|---|---|
| WordPress | 5.8.3 | Latest stable |
| WooCommerce | 6.0 | Latest stable (tested up to 11.x) |
| PHP | 7.4 | 8.1+ |
| MySQL / MariaDB | 5.7 / 10.2 | MariaDB 10.11 / MySQL 8 |
| Checkout | Classic or Blocks with payment-method API | Both OK |
| HPOS | off or on | on |
| WooCommerce decimals | **2** (required) | 2 |
| Currency | ARS or USD | ARS or USD |

## Lab tested — UAT 2.0.0 (2026-11-23)

Docker cell `recommended` after wipe + fresh install:

| Component | Value |
|---|---|
| WordPress | 6.7.2 |
| WooCommerce | 9.4.0 |
| PHP | 8.1.34 |
| MariaDB | 10.11 |
| Plugin / SDK | 2.0.0 base / 2.0.10 (current plugin **2.0.2**) |
| Classic sandbox | Order #14 approved (CS green) |
| Blocks sandbox | Order #16 approved (CS green) |

### Integration smoke (same date)

| Profile | Result |
|---|---|
| `min` (WP 5.8.3 / WC 6.0 / PHP 7.4) | OK |
| `recommended` | OK |
| `latest-hpos-on` (WP 7.1.5 / WC 11.3.1 / PHP 8.3) | OK |
| `latest-hpos-off` | OK |
| `wp60-wc60-php74` | OK (failed=0) |
