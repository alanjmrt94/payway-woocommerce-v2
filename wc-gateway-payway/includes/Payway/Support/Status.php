<?php
/**
 * PSR-4 first step: namespaced helpers (manual require until full Composer PSR-4).
 *
 * @package Payway
 */

namespace Payway\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Order / gateway status helpers.
 */
final class Status {

	/**
	 * @param string $status
	 * @return bool
	 */
	public static function is_paid( $status ) {
		if ( class_exists( '\WC_Payway_Decidir_Adapter' ) ) {
			return \WC_Payway_Decidir_Adapter::is_success_status( $status );
		}
		return in_array( strtolower( (string) $status ), array( 'approved', 'accredited' ), true );
	}
}
