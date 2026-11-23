# 1. Introducción

**Payway Payment Community** es un gateway de pago para WooCommerce que conecta la tienda con Payway/Decidir (tokenización en el navegador + cobro en servidor).

Es un **fork independiente** mantenido por Alan Martinez ([alanjmrt94](https://github.com/alanjmrt94)), a partir del plugin publicado en [payway-ar/plugin-woocommerce](https://github.com/payway-ar/plugin-woocommerce) (IURCO / Prisma SA), versión de referencia **0.3.3**.

## Disclaimer

Este proyecto **no** es un producto oficial de Payway. No está desarrollado, mantenido, respaldado ni certificado por Payway. Su uso no implica garantía de funcionamiento, compatibilidad, seguridad ni soporte por parte de Payway. La responsabilidad del código corresponde exclusivamente al autor del fork.

## Qué cubre este manual

- Instalación y puesta en marcha (Instalación / Setup)
- Configuración de credenciales Sandbox y Producción
- Bancos, tarjetas, promociones y planes de cuotas
- Checkout clásico y WooCommerce Blocks
- Diagnóstico (**Estado** / Status), **Últimos pagos** y errores frecuentes
- Migración desde el plugin oficial y changelog **2.0.x**

## Identidad técnica (migración)

Se conservan a propósito:

| Concepto | Valor |
|---|---|
| Carpeta del plugin | `wc-gateway-payway` |
| Gateway id | `payway_gateway` |
| Text domain | `wc-gateway-payway` |
| Tablas | `prisma_payway_*` |
| Option de settings | `woocommerce_payway_gateway_settings` |

Así podés reemplazar el plugin original sin perder bancos, tarjetas, promociones ni claves.
