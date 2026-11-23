# 11. Actualización y migración

## Versionado

SemVer (`MAJOR.MINOR.PATCH`). Fuente de verdad: header `Version:` en `index.php`.

- **MAJOR** — salto de producto o breaking (ej. SDK PHP V1 → V2 → serie `2.x`)
- **MINOR** — funcionalidades compatibles de un lote temático
- **PATCH** — correcciones del lote

## Migración desde el plugin oficial (0.3.x)

Misma carpeta `wc-gateway-payway` a propósito:

1. Backup de DB y/o exportá settings / bancos / promos.
2. Desactivá el plugin original.
3. Reemplazá `wp-content/plugins/wc-gateway-payway` por esta versión (o subí el ZIP Community).
4. Activá **Payway Payment Community**.
5. Revisá credenciales del ambiente y hacé **una operación sandbox** (idealmente con Cybersource ON).

Se reutilizan `woocommerce_payway_gateway_settings` y tablas `prisma_payway_*`.

## Actualizar dentro de la serie Community (1.x → 2.0.0)

1. Reemplazá la carpeta / ZIP.
2. **Breaking 2.0.0:** montos al SDK en **centavos** (el SDK V2 ya no multiplica ×100). No hace falta migrar settings ni tablas.
3. Re-probá un pago sandbox tras el upgrade.
4. Revisá **Estado** (versión plugin / SDK 2.0.10) y **Últimos pagos**.

## Dónde ver cambios

- [`CHANGELOG.md`](../../CHANGELOG.md) en la raíz del repo
- [14 — Changelog](14-changelog.md) (resumen orientado al manual)
