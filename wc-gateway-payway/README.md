# Payway Payment Community

Plugin WooCommerce para Payway/Decidir — **fork independiente** (no oficial de Payway).  
Disclaimer y licencia: ver el [README del repositorio](../README.md).

**No lo uses en producción** sin probar antes en un clon del sitio.

## Requisitos

- WordPress >= 5.8.3  
- WooCommerce >= 6.0 (probado hasta lo declarado en el header del plugin)  
- PHP >= 7.4  
- MySQL >= 5.7 o MariaDB >= 10.2  
- Checkout **clásico** (shortcode). Blocks: todavía no.  
- Compatible con HPOS

## Instalación

El ZIP debe traer la carpeta `wc-gateway-payway` en la raíz. Subilo en **Plugins → Añadir nuevo → Subir plugin** o copialo a `wp-content/plugins/`.

## Configuración

1. WooCommerce → Ajustes → Pagos → **Payway Payment Community** (Enabled + claves)
2. **Payway → Instalación**: checklist y defaults (banco Todos, tarjetas, promos)
3. O bien bancos / tarjetas / promociones a mano en el menú **Payway**
4. Moneda ARS o USD; decimales de WooCommerce en **2**
5. Si falla: **Payway → Status**

### Credenciales

Salen del portal / backoffice Payway del comercio (sandbox y prod son distintas). Este fork no emite claves; si el portal no está claro, consultá a tu contacto comercial o a la mesa de Payway.

## Cuándo no aparece el formulario

- Gateway deshabilitado o sin claves  
- Sin promociones aplicables (activas; con **Sin vencimiento** o fechas vigentes)  
- Checkout Blocks sin la API de payment methods (en WC recientes con Blocks, el medio sí se registra)

## Migración desde el plugin original

Misma carpeta `wc-gateway-payway` a propósito (se reutilizan options y tablas `prisma_payway_*`).

1. Anotá settings / bancos / promos (o backup de DB)  
2. Desactivá el original  
3. Reemplazá la carpeta por este fork (o subí el ZIP)  
4. Activá **Payway Payment Community**  
5. Probá una operación en sandbox

## Docs y cambios

- [`docs/README.md`](../docs/README.md)  
- [`CHANGELOG.md`](../CHANGELOG.md)  
- [`LICENSE`](../LICENSE) (`GPL-2.0-or-later`)
