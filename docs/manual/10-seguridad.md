# 10. Seguridad operativa

- Nunca compartas la **Private Key** en tickets públicos, capturas ni en el frontend.
- La Private Key **no** se envía al storefront en este fork.
- No copies logs con Debug a terceros sin revisar redacción (tokens, keys).
- Actualizá el plugin cuando haya releases de seguridad (notas en el changelog).
- No deshabilites nonces ni capabilities del admin (`manage_woocommerce` para menú y mutaciones).
- El plugin no almacena PAN ni CVV; la tokenización es en el navegador.
- Meta del gateway sanitizada (sin token/PAN en subsets guardados).
- En staging usá siempre Sandbox; en producción desactivá Debug salvo incidentes puntuales.

## Checklist Debug OFF (producción)

1. **Debug Mode** = no en Pagos.
2. Confirmar que no quedan logs sensibles en `wc-logs`.
3. `_payway_full_response` solo se escribe con Debug ON (subset redactado).
4. Uninstall borra solo options en **whitelist** (no `LIKE` amplio).
