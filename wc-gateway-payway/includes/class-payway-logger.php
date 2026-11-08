<?php
/**
 * Payway Payment Community — gateway de pago para WooCommerce (fork independiente).
 * Copyright (C) 2026 Alan Martinez
 *
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
 * @license GPL-2.0-or-later
 */
/**
 * Logger seguro — respeta Debug Mode y redacta secretos.
 *
 * @package wc-gateway-payway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wrapper sobre WC_Logger.
 */
class WC_Payway_Logger {

	const LOG_SOURCE = 'payway-gateway';

	/** @var WC_Logger */
	static $wc_logger;

	/** @var self|null */
	protected static $instance = null;

	public function __construct() {
		self::$wc_logger = wc_get_logger();
	}

	/**
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @return WC_Logger
	 */
	public static function get_logger() {
		if ( ! self::$wc_logger ) {
			self::$wc_logger = wc_get_logger();
		}
		return self::$wc_logger;
	}

	/**
	 * @param string $message
	 * @param string $level
	 * @return void
	 */
	public static function log( $message, $level = 'info' ) {
		if ( ! function_exists( 'wc_payway_is_debug_enabled' ) || ! wc_payway_is_debug_enabled() ) {
			// Errores siempre se registran; debug/info solo con Debug Mode.
			if ( ! in_array( $level, array( WC_Log_Levels::ERROR, 'error' ), true ) ) {
				return;
			}
		}

		self::get_logger()->log(
			$level,
			self::redact( $message ),
			array( 'source' => self::LOG_SOURCE )
		);
	}

	/**
	 * @param string $message
	 * @return void
	 */
	public static function info( $message ) {
		self::log( $message, WC_Log_Levels::INFO );
	}

	/**
	 * @param string $message
	 * @return void
	 */
	public static function debug( $message ) {
		self::log( $message, WC_Log_Levels::DEBUG );
	}

	/**
	 * @param string $message
	 * @return void
	 */
	public static function error( $message ) {
		self::log( $message, WC_Log_Levels::ERROR );
	}

	/**
	 * Redacta claves sensibles en texto libre.
	 *
	 * @param mixed $message
	 * @return string
	 */
	public static function redact( $message ) {
		if ( ! is_string( $message ) ) {
			$message = print_r( $message, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

		$patterns = array(
			'/("?(?:private_key|public_key|token|security_code|card_number)"?\s*[:=]\s*")[^"]*(")/i',
			'/("?(?:private_key|public_key|token)"?\s*=>\s*\')[^\']*(\')/i',
		);

		foreach ( $patterns as $pattern ) {
			$message = preg_replace( $pattern, '$1[REDACTED]$2', $message );
		}

		return $message;
	}
}
