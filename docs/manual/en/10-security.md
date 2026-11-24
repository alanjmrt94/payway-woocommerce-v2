# 10. Operational security

- Never share the **Private Key** in public tickets, screenshots, or the frontend.
- The Private Key is **not** sent to the storefront in this fork.
- Do not share Debug logs with third parties without reviewing redaction (tokens, keys).
- Update the plugin when security releases ship (see changelog).
- Do not disable admin nonces or capabilities (`manage_woocommerce` for menu and mutations).
- The plugin does not store PAN or CVV; tokenization is in the browser.
- Gateway meta is sanitized (no token/PAN in stored subsets).
- On staging always use Sandbox; in production keep Debug off except short incidents.

## Debug OFF checklist (production)

1. **Debug Mode** = no in Payments.
2. Confirm no sensitive leftovers in `wc-logs`.
3. `_payway_full_response` is written only with Debug ON (redacted subset).
4. Uninstall removes only **whitelisted** options (no broad `LIKE`).
