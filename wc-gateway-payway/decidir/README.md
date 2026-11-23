# SDK PHP Payway / Decidir (vendored)

Este directorio es un puente Composer que instala **`decidir2/php-sdk` V2.0.10** (Packagist / [payway-ar/sdk-php-ventaonline](https://github.com/payway-ar/sdk-php-ventaonline)).

- Autoload: `vendor/autoload.php` (lo carga `WC_Payway_Api_Handler`).
- No hay copia duplicada fuera de `vendor/`.
- Para actualizar: cambiar la versión en `composer.json` y correr `composer update` desde este directorio.

**Montos:** desde V2 el SDK **no** multiplica ×100. El plugin envía importes en unidades menores (centavos).
