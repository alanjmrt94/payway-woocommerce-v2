<?php
/**
 * Plugin Name: Payway Payment Community
 * Plugin URI: https://github.com/alanjmrt94/payway-woocommerce-v2
 * Description: Gateway de pago independiente para WooCommerce basado en la integración Payway/Decidir. No es un producto oficial de Payway.
 * Version: 1.1.5
 * Author: Alan Martinez
 * Author URI: https://github.com/alanjmrt94
 * Text Domain: wc-gateway-payway
 * Domain Path: /i18n/languages
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: woocommerce
 *
 * WordPress requires at least: 5.8.3
 * Requires at least: 5.8.3
 * PHP requires at least: 7.4
 * Requires PHP: 7.4
 * WooCommerce requires at least: 6.0
 * WooCommerce tested up to: 11.0
 * WC requires at least: 6.0
 * WC tested up to: 11.0
 * MySQL requires at least: 5.7
 * MariaDB requires at least: 10.2
 *
 * Compatibilidad declarada:
 * - WordPress:    mínimo 5.8.3 — recomendado última estable
 * - WooCommerce:  mínimo 6.0 — probado en upgrade local hasta 11.x
 * - PHP:          mínimo 7.4 — recomendado 8.1 o superior
 * - MySQL:        mínimo 5.7 — recomendado 8.0 o superior
 * - MariaDB:      mínimo 10.2 — recomendado 10.4 o superior
 *
 * Nota: las claves "Requires at least", "Requires PHP", "WC requires at least" y
 * "WC tested up to" se conservan porque WordPress y WooCommerce las leen de forma
 * automática. Las líneas con nombre completo son documentación legible para humanos.
 *
 * Copyright (C) 2026 Alan Martinez
 * Based on prior work © IURCO / Prisma SA where applicable.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <https://www.gnu.org/licenses/>.
 *
 * @package wc-gateway-payway
 * @author Alan Martinez (alanjmrt94)
 * @license GPL-2.0-or-later
 *
 * Atribución: basado en el plugin original de IURCO / Prisma SA (payway-ar/plugin-woocommerce).
 * Este fork es un desarrollo independiente. No está respaldado ni certificado por Payway.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WC_PAYWAY_PLUGIN_FILE' ) ) {
	define( 'WC_PAYWAY_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'WC_PAYWAY_PLUGIN_SLUG' ) ) {
	define( 'WC_PAYWAY_PLUGIN_SLUG', 'wc-gateway-payway' );
}

// Include the main Payway class.
if ( ! class_exists( 'PaywayWC', false ) ) {
	include_once dirname( WC_PAYWAY_PLUGIN_FILE ) . '/includes/class-payway-wc.php';
}

require_once 'includes/interfaces/class-wc-payway-activator-interface.php';
require_once 'includes/class-payway-activator.php';

/**
 * Object-cache group for gateway settings.
 */
if ( ! defined( 'WC_PAYWAY_SETTINGS_CACHE_GROUP' ) ) {
	define( 'WC_PAYWAY_SETTINGS_CACHE_GROUP', 'payway' );
}

/**
 * Object-cache key for gateway settings.
 */
if ( ! defined( 'WC_PAYWAY_SETTINGS_CACHE_KEY' ) ) {
	define( 'WC_PAYWAY_SETTINGS_CACHE_KEY', 'woocommerce_payway_gateway_settings' );
}

/**
 * Returns Payway Gateway configuration options (request + object cache).
 *
 * @param bool $force_refresh Bypass caches and re-read from the database.
 * @return array|false
 */
function wc_payway_get_config_options( $force_refresh = false ) {
	static $cached  = null;
	static $hydrated = false;

	$skip_cache = (bool) apply_filters( 'payway_skip_settings_cache', false );

	if ( $force_refresh || $skip_cache ) {
		$hydrated = false;
		$cached   = null;
		wp_cache_delete( WC_PAYWAY_SETTINGS_CACHE_KEY, WC_PAYWAY_SETTINGS_CACHE_GROUP );
	}

	if ( $hydrated && ! $skip_cache ) {
		return $cached;
	}

	if ( ! $force_refresh && ! $skip_cache ) {
		$from_cache = wp_cache_get( WC_PAYWAY_SETTINGS_CACHE_KEY, WC_PAYWAY_SETTINGS_CACHE_GROUP );
		if ( false !== $from_cache ) {
			$cached   = $from_cache;
			$hydrated = true;
			return $cached;
		}
	}

	$cached = get_option( 'woocommerce_payway_gateway_settings' );
	if ( false === $cached ) {
		$cached = false;
	}

	if ( ! $skip_cache ) {
		wp_cache_set( WC_PAYWAY_SETTINGS_CACHE_KEY, $cached, WC_PAYWAY_SETTINGS_CACHE_GROUP );
	}

	$hydrated = true;

	return $cached;
}

/**
 * Drops request + object caches for gateway settings.
 *
 * @return void
 */
function wc_payway_invalidate_config_options_cache() {
	wc_payway_get_config_options( true );
}

/**
 * Returns specific option value
 *
 * @param string $name config option to retrieve
 * @return mixed
 */
function wc_payway_get_config_option( $name ) {
	$options = wc_payway_get_config_options();

	return ( is_array( $options ) && isset( $options[ $name ] ) )
		? $options[ $name ]
		: false;
}

/**
 * Returns the version value that the database holds
 *
 * @return string
 */
function wc_payway_get_version() {
	return get_option(
		WC_Payway_Activator_Interface::WC_PAYWAY_VERSION
	);
}

/**
 * Returns the SDK version value that the database holds
 *
 * @return string
 */
function wc_payway_get_sdk_version() {
	return get_option(
		WC_Payway_Activator_Interface::WC_PAYWAY_SDK_VERSION
	);
}

/**
 * Returns the plugin version from the `Version:` header in this file.
 * Single source of truth for bumps (WordPress + Activator + asset cache-bust).
 *
 * @return string
 */
function wc_payway_get_version_in_codebase() {
	static $version = null;
	if ( null !== $version ) {
		return $version;
	}

	$file = defined( 'WC_PAYWAY_PLUGIN_FILE' ) ? WC_PAYWAY_PLUGIN_FILE : __FILE__;
	$data = get_file_data( $file, array( 'Version' => 'Version' ), 'plugin' );
	$version = ! empty( $data['Version'] ) ? (string) $data['Version'] : '0.0.0';

	return $version;
}

/**
 * Version string for enqueued CSS/JS (always the codebase header, not the DB option).
 *
 * @return string
 */
function wc_payway_asset_version() {
	if ( defined( 'WC_PAYWAY_PLUGIN_VERSION_CODEBASE' ) && WC_PAYWAY_PLUGIN_VERSION_CODEBASE ) {
		return (string) WC_PAYWAY_PLUGIN_VERSION_CODEBASE;
	}

	return wc_payway_get_version_in_codebase();
}

/**
 * Returns the SDK version declared in the codebase
 *
 * @return string
 */
function wc_payway_get_sdk_version_in_codebase() {
	return WC_Payway_Activator_Interface::WC_PAYWAY_SDK_VERSION_VALUE;
}

/**
 * Whether debug logging is enabled in gateway settings
 *
 * @return bool
 */
function wc_payway_is_debug_enabled() {
	return 'yes' === wc_payway_get_config_option(
		WC_Payway_Config_Interface::FIELD_DEBUG_ENABLED
	);
}

register_activation_hook( __FILE__, array( 'WC_Payway_Activator', 'activate' ) );
register_uninstall_hook( __FILE__, array( 'WC_Payway_Activator', 'uninstall' ) );

add_action(
	'update_option_woocommerce_payway_gateway_settings',
	'wc_payway_invalidate_config_options_cache',
	10,
	0
);
add_action(
	'add_option_woocommerce_payway_gateway_settings',
	'wc_payway_invalidate_config_options_cache',
	10,
	0
);
add_action(
	'delete_option_woocommerce_payway_gateway_settings',
	'wc_payway_invalidate_config_options_cache',
	10,
	0
);

add_action( 'plugins_loaded', array( 'PaywayWC', 'instance' ) );

add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				WC_PAYWAY_PLUGIN_FILE,
				true
			);
		}
	}
);
