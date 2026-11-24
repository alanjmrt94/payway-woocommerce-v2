# 11. Upgrade and migration

Semantic Versioning (`MAJOR.MINOR.PATCH`). Source of truth: `Version:` header in `index.php`.

Author: Alan Martinez ([alanjmrt94](https://github.com/alanjmrt94)).

## From official 0.3.3

1. Note settings / banks / promos (or DB backup).
2. Deactivate the original plugin.
3. Replace the folder with this fork (or upload the ZIP with root `wc-gateway-payway`).
4. Activate **Payway Payment Community**.
5. Run a sandbox payment (ideally with Cybersource ON).

Tables `prisma_payway_*` and option `woocommerce_payway_gateway_settings` are reused on purpose.

## Upgrading within the Community series (1.x → 2.0.x)

1. Replace the plugin folder / ZIP.
2. **Breaking in 2.0.0:** amounts to the SDK are in **cents** (SDK V2 no longer multiplies ×100). No settings/table migration is required.
3. Re-test sandbox after upgrade.

## Soft deactivation

Deactivating clears caches/transients but **does not** drop tables or settings. Uninstall uses an option whitelist.
