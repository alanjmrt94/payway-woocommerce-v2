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
 * @author IURCO - Prisma SA
 */

defined( 'ABSPATH' ) || exit;

/**
 * Payway Activation / schema self-heal.
 *
 * Inspired on MailChimp_WooCommerce_Activator class
 * @see https://github.com/mailchimp/mc-woocommerce
 */
class WC_Payway_Activator implements WC_Payway_Activator_Interface {

	/**
	 * @return array|bool
	 */
	private static function wc_payway_get_config_options() {
		return get_option( 'woocommerce_payway_gateway_settings' );
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	private static function wc_payway_get_config_option( $name ) {
		$options = static::wc_payway_get_config_options();

		return ( $options && isset( $options[ $name ] ) )
			? $options[ $name ]
			: false;
	}

	/**
	 * @return bool
	 */
	public static function wc_payway_is_woocommerce_plugin_installed() {
		return in_array(
			'woocommerce/woocommerce.php',
			apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
			true
		);
	}

	/**
	 * @return bool
	 */
	public static function check_woocommerce_install() {
		if ( ! static::wc_payway_is_woocommerce_plugin_installed() ) {
			if ( function_exists( 'is_plugin_active' ) && is_plugin_active( WC_PAYWAY_PLUGIN_FILE ) ) {
				deactivate_plugins( array( WC_PAYWAY_PLUGIN_FILE ) );
			}
			add_action( 'admin_notices', array( __CLASS__, 'display_deactivation_notice' ) );
			return false;
		}

		return true;
	}

	/**
	 * @return void
	 */
	public static function display_deactivation_notice() {
		echo '<div class="notice notice-error"><p>';
		echo wp_kses_post(
			__( 'Payway Payment Gateway requires the <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to run.', 'wc-gateway-payway' )
		);
		echo '</p></div>';
	}

	/**
	 * Activation: never wipe settings. Always self-heal schema + sync version.
	 *
	 * @return bool
	 */
	public static function activate() {
		if ( ! static::check_woocommerce_install() ) {
			return false;
		}

		static::ensure_core_schema();
		static::update_plugin_db_versions();

		return true;
	}

	/**
	 * Runs on every request (idempotent): tables + columns + version bump.
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		static $done = false;
		if ( $done ) {
			return;
		}
		$done = true;

		static::ensure_core_schema();

		$current_version = get_option( 'payway_gateway_version', false );
		if ( ! $current_version
			|| version_compare( (string) $current_version, static::WC_PAYWAY_VERSION_VALUE, '<' )
		) {
			static::update_plugin_db_versions();
		}
	}

	/**
	 * Public entry: create missing tables and add missing columns.
	 * Safe on clean install, upgrade, and mid-request recovery.
	 *
	 * @return void
	 */
	public static function ensure_core_schema() {
		static $ready = false;
		if ( $ready ) {
			return;
		}

		global $wpdb;
		if ( ! isset( $wpdb ) || ! $wpdb ) {
			return;
		}

		static::ensure_banks_table();
		static::ensure_cards_table();
		static::ensure_promotions_table();

		$ready = static::core_tables_exist() && static::promotions_has_no_expiry_column();
	}

	/**
	 * BC alias used by promotions / status.
	 *
	 * @return void
	 */
	public static function ensure_promotions_schema() {
		static::ensure_core_schema();
	}

	/**
	 * @return bool
	 */
	public static function core_tables_exist() {
		return static::table_exists( static::TABLE_NAME_BANKS )
			&& static::table_exists( static::TABLE_NAME_CARDS )
			&& static::table_exists( static::TABLE_NAME_PROMOTIONS );
	}

	/**
	 * @param string $base_table_name Without prefix.
	 * @return bool
	 */
	public static function table_exists( $base_table_name ) {
		global $wpdb;
		$table = $wpdb->prefix . $base_table_name;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
	}

	/**
	 * @return bool
	 */
	public static function promotions_has_no_expiry_column() {
		return static::column_exists( static::TABLE_NAME_PROMOTIONS, 'no_expiry' );
	}

	/**
	 * @param string $base_table_name
	 * @param string $column
	 * @return bool
	 */
	private static function column_exists( $base_table_name, $column ) {
		global $wpdb;
		$table = $wpdb->prefix . $base_table_name;
		if ( ! static::table_exists( $base_table_name ) ) {
			return false;
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$cols = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM {$table} LIKE %s", $column ) );
		return ! empty( $cols );
	}

	/**
	 * @param string $base_table_name
	 * @param string $column
	 * @param string $definition Full column definition after ADD COLUMN
	 * @return void
	 */
	private static function ensure_column( $base_table_name, $column, $definition ) {
		if ( static::column_exists( $base_table_name, $column ) ) {
			return;
		}
		global $wpdb;
		$table = $wpdb->prefix . $base_table_name;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "ALTER TABLE {$table} ADD COLUMN {$definition}" );
	}

	/**
	 * @return void
	 */
	private static function ensure_banks_table() {
		global $wpdb;
		$table           = $wpdb->prefix . static::TABLE_NAME_BANKS;
		$charset_collate = $wpdb->get_charset_collate();

		if ( ! static::table_exists( static::TABLE_NAME_BANKS ) ) {
			// Sin IF NOT EXISTS: ya chequeamos ausencia. Sin FK.
			$sql = "CREATE TABLE {$table} (
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(150) NOT NULL,
				logo varchar(150) DEFAULT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) {$charset_collate};";
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $sql );
		}

		static::ensure_column( static::TABLE_NAME_BANKS, 'logo', 'logo varchar(150) DEFAULT NULL' );
		static::ensure_column(
			static::TABLE_NAME_BANKS,
			'created_at',
			'created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP'
		);
	}

	/**
	 * @return void
	 */
	private static function ensure_cards_table() {
		global $wpdb;
		$table           = $wpdb->prefix . static::TABLE_NAME_CARDS;
		$charset_collate = $wpdb->get_charset_collate();

		if ( ! static::table_exists( static::TABLE_NAME_CARDS ) ) {
			$sql = "CREATE TABLE {$table} (
				id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				card_name varchar(150) NOT NULL,
				id_sps int(11) UNSIGNED NOT NULL,
				id_nps int(11) UNSIGNED NOT NULL,
				logo text,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY id_sps (id_sps)
			) {$charset_collate};";
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $sql );
		}

		static::ensure_column( static::TABLE_NAME_CARDS, 'logo', 'logo text NULL' );
		static::ensure_column(
			static::TABLE_NAME_CARDS,
			'created_at',
			'created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP'
		);
	}

	/**
	 * Promotions without FK in DDL (hosts that reject FK still work).
	 * Integrity se mantiene por lógica de la app + índices.
	 *
	 * @return void
	 */
	private static function ensure_promotions_table() {
		global $wpdb;
		$table           = $wpdb->prefix . static::TABLE_NAME_PROMOTIONS;
		$charset_collate = $wpdb->get_charset_collate();

		if ( ! static::table_exists( static::TABLE_NAME_PROMOTIONS ) ) {
			$sql = "CREATE TABLE {$table} (
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				rule_name varchar(150) NOT NULL,
				card_id int(11) unsigned NOT NULL,
				bank_id int(11) unsigned NOT NULL,
				from_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				to_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				priority int(2) unsigned DEFAULT NULL,
				is_active tinyint(1) NOT NULL DEFAULT 1,
				no_expiry tinyint(1) NOT NULL DEFAULT 1,
				applicable_days varchar(50) NOT NULL,
				fee_plans text DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY bank_id (bank_id),
				KEY card_id (card_id),
				KEY is_active (is_active)
			) {$charset_collate};";
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $sql );
		}

		static::ensure_column(
			static::TABLE_NAME_PROMOTIONS,
			'priority',
			'priority int(2) unsigned DEFAULT NULL'
		);
		static::ensure_column(
			static::TABLE_NAME_PROMOTIONS,
			'is_active',
			'is_active tinyint(1) NOT NULL DEFAULT 1'
		);
		static::ensure_column(
			static::TABLE_NAME_PROMOTIONS,
			'no_expiry',
			'no_expiry tinyint(1) NOT NULL DEFAULT 1 AFTER is_active'
		);
		static::ensure_column(
			static::TABLE_NAME_PROMOTIONS,
			'applicable_days',
			"applicable_days varchar(50) NOT NULL DEFAULT ''"
		);
		static::ensure_column(
			static::TABLE_NAME_PROMOTIONS,
			'fee_plans',
			'fee_plans text DEFAULT NULL'
		);
	}

	/**
	 * @return void
	 */
	public static function deactivate() {}

	/**
	 * @return void
	 */
	public static function uninstall() {
		self::payway_clean_config_options();
		self::payway_drop_core_tables();
	}

	/**
	 * @return void
	 */
	public static function update_plugin_db_versions() {
		update_option(
			static::CONFIG_OPTION_GATEWAY_VERSION_NAME,
			static::WC_PAYWAY_VERSION_VALUE
		);

		update_option(
			static::CONFIG_OPTION_SDK_VERSION_NAME,
			static::WC_PAYWAY_SDK_VERSION_VALUE
		);
	}

	/**
	 * Only used on uninstall (or explicit wipe). Never on activate/upgrade.
	 *
	 * @return void
	 */
	public static function payway_clean_config_options() {
		global $wpdb;

		$plugin_options = $wpdb->get_results(
			"SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%payway%gateway%'"
		);

		foreach ( $plugin_options as $option ) {
			delete_option( $option->option_name );
		}
	}

	/**
	 * Legacy helper kept for callers; prefer ensure_core_schema().
	 *
	 * @return void
	 */
	private static function create_core_tables() {
		static::ensure_core_schema();
	}

	/**
	 * @return void
	 */
	public static function payway_flush_core_tables() {
		try {
			global $wpdb;
			$truncate_banks       = 'TRUNCATE ' . $wpdb->prefix . static::TABLE_NAME_BANKS;
			$truncate_cards       = 'TRUNCATE ' . $wpdb->prefix . static::TABLE_NAME_CARDS;
			$truncate_promotions  = 'TRUNCATE ' . $wpdb->prefix . static::TABLE_NAME_PROMOTIONS;
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $truncate_promotions );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $truncate_cards );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $truncate_banks );
		} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}
	}

	/**
	 * @return void
	 */
	public static function payway_drop_core_tables() {
		try {
			global $wpdb;
			$prefix = $wpdb->prefix;
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $prefix . static::TABLE_NAME_PROMOTIONS );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $prefix . static::TABLE_NAME_CARDS );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $prefix . static::TABLE_NAME_BANKS );
		} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}
	}
}
