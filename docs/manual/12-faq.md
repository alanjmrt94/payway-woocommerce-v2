# 12. FAQ

**¿Es oficial de Payway?**  
No. Es un fork Community. Payway no brinda soporte sobre este plugin.

**¿Quién da soporte?**  
El autor del fork (alanjmrt94) vía el repositorio GitHub. No uses la tipificación del App Store / portal oficial de Payway pensada para el plugin 0.3.x.

**¿Soporta WooCommerce Blocks?**  
Sí, desde 1.1.0 (con feature-detect de la API de payment methods).

**¿Dónde obtengo Site Id y keys?**  
Del portal / backoffice Payway o del mail de alta del comercio. Sandbox y producción son distintas. Este fork no emite claves.

**¿Por qué no aparece el medio en el checkout?**  
Revisá Enabled, keys del ambiente activo, al menos una promoción aplicable (o seed desde Instalación), moneda ARS/USD, decimales = 2, y **Estado → Checkout surface**.

**¿Cómo configuro Cuota Simple 3 / 6?**  
En planes de la promoción, `fee_to_send` = **13** (Simple 3) o **16** (Simple 6). Instalación puede ofrecer seed MiPyME.

**¿Qué es “Sin vencimiento”?**  
La promo no valida fecha de fin (`no_expiry`). Ideal para planes de cuotas permanentes del checkout.

**¿Puedo apagar Cybersource si falla el pago?**  
No como solución genérica. Algunos comercios sandbox **exigen** Fraud Detection; apagar CS puede devolver **10734**. Completá facturación (calle + puerta) y dejá CS alineado al comercio. Detalle: montos V2 en centavos siguen siendo correctos con CS ON.

**¿Qué son los avisos rojos de keys / promociones?**  
Con el medio Enabled, si faltan claves del ambiente activo o no hay promociones aplicables, el admin muestra notices de error (también en el escritorio). El medio permanece oculto en el checkout hasta corregirlo (Instalación → seed).

**¿Los montos van en pesos o centavos?**  
Desde **2.0.0** (SDK PHP 2.0.10) el plugin envía **centavos** a Decidir/Payway. No hay ×100 adicional en el plugin.

**¿Soporta devoluciones desde WooCommerce?**  
Aún no: el gateway no declara `refunds` en `supports` en 2.0.0.

**¿Pierdo bancos/promos al migrar desde el oficial?**  
No, si reutilizás la misma base: tablas `prisma_payway_*` y option `woocommerce_payway_gateway_settings`.

**¿Cómo cambio el idioma del plugin?**  
No hay selector propio. El plugin sigue el idioma de WordPress; cualquier locale `es*` carga `es_AR`. El resto queda en inglés.

**¿Dónde está la última versión?**  
[GitHub Releases](https://github.com/alanjmrt94/payway-woocommerce-v2/releases) del fork Community (no el App Store Payway del manual 2024 del plugin oficial).

**¿Cómo veo pagos recientes sin salir de WP?**  
**Payway → Últimos pagos**, con Private Key del ambiente activo.
