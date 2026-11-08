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
 * Catálogo de errores internos del plugin.
 *
 * @package wc-gateway-payway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Códigos y mensajes comprensibles (sin secretos).
 */
class WC_Payway_Errors {

	const CONFIG_INCOMPLETE         = 'PPC_CONFIG_001';
	const AUTH_CREDENTIALS          = 'PPC_AUTH_001';
	const CONNECTIVITY              = 'PPC_NET_001';
	const VALIDATION_TOKEN          = 'PPC_VAL_001';
	const VALIDATION_INSTALLMENTS   = 'PPC_VAL_002';
	const VALIDATION_PROMOTION      = 'PPC_VAL_003';
	const VALIDATION_FEE_PLAN       = 'PPC_VAL_004';
	const VALIDATION_PAYMENT_METHOD = 'PPC_VAL_005';
	const PROCESSING                = 'PPC_PAY_001';
	const ISSUER_REJECTED           = 'PPC_PAY_002';
	const COMPATIBILITY             = 'PPC_COMPAT_001';
	const INTERNAL                  = 'PPC_INT_001';
	const GATEWAY_COMM              = 'PPC_API_001';

	/**
	 * Mensaje orientado al comercio/comprador.
	 *
	 * @param string $code
	 * @return string
	 */
	public static function message( $code ) {
		$messages = array(
			self::CONFIG_INCOMPLETE         => __( 'La configuración del medio de pago está incompleta. Revisá credenciales y promociones.', 'wc-gateway-payway' ),
			self::AUTH_CREDENTIALS          => __( 'Error de autenticación con Payway. Verificá las claves del ambiente activo.', 'wc-gateway-payway' ),
			self::CONNECTIVITY              => __( 'No se pudo conectar con el servicio de pagos. Reintentá más tarde.', 'wc-gateway-payway' ),
			self::VALIDATION_TOKEN          => __( 'No se recibió el token de la tarjeta. Recargá el checkout e intentá de nuevo.', 'wc-gateway-payway' ),
			self::VALIDATION_INSTALLMENTS   => __( 'Seleccioná banco, tarjeta y cuotas válidas.', 'wc-gateway-payway' ),
			self::VALIDATION_PROMOTION      => __( 'La promoción seleccionada no está disponible. Actualizá la página y volvé a elegir las cuotas.', 'wc-gateway-payway' ),
			self::VALIDATION_FEE_PLAN       => __( 'El plan de cuotas seleccionado no es válido.', 'wc-gateway-payway' ),
			self::VALIDATION_PAYMENT_METHOD => __( 'El identificador de tarjeta (payment_method_id) no es válido para este comercio.', 'wc-gateway-payway' ),
			self::PROCESSING                => __( 'Ocurrió un error al procesar el pago.', 'wc-gateway-payway' ),
			self::ISSUER_REJECTED           => __( 'El pago fue rechazado por el emisor o la entidad procesadora.', 'wc-gateway-payway' ),
			self::COMPATIBILITY             => __( 'Incompatibilidad de entorno detectada. Revisá la matriz de versiones del plugin.', 'wc-gateway-payway' ),
			self::INTERNAL                  => __( 'Error interno del plugin. Contactá al administrador del sitio.', 'wc-gateway-payway' ),
			self::GATEWAY_COMM              => __( 'Error de comunicación con Payway.', 'wc-gateway-payway' ),
		);

		return isset( $messages[ $code ] )
			? $messages[ $code ]
			: __( 'Error desconocido al procesar el pago.', 'wc-gateway-payway' );
	}

	/**
	 * Mapea tipologías Decidir / HTTP a códigos internos.
	 *
	 * @param string|int $gateway_code
	 * @return string
	 */
	public static function from_gateway( $gateway_code ) {
		$map = array(
			'authentication_error'    => self::AUTH_CREDENTIALS,
			'invalid_request_error'   => self::VALIDATION_PAYMENT_METHOD,
			'malformed_request_error' => self::GATEWAY_COMM,
			'not_found_error'         => self::GATEWAY_COMM,
			'api_error'               => self::GATEWAY_COMM,
			401                       => self::AUTH_CREDENTIALS,
			402                       => self::VALIDATION_PAYMENT_METHOD,
		);

		return isset( $map[ $gateway_code ] )
			? $map[ $gateway_code ]
			: self::PROCESSING;
	}
}
