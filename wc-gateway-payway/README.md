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

1. WooCommerce → Ajustes → Pagos → **Payway Payment Community**
2. Site Id, Public Key y Private Key (sandbox o producción)
3. Bancos, tarjetas y promociones en el menú **Payway**
4. Moneda ARS o USD; decimales de WooCommerce en **2**

### Credenciales

Salen del portal / backoffice Payway del comercio (sandbox y prod son distintas). Este fork no emite claves; si el portal no está claro, consultá a tu contacto comercial o a la mesa de Payway.

## Cuándo no aparece el formulario

- Gateway deshabilitado  
- Sin promociones aplicables activas  
- Checkout Blocks (no soportado aún)

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
