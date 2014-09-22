<?php
/**
 * Vespucci Plugin
 *
 * @package   Vespucci
 * @license   GPL-2.0+
 * @link      https://github.com/nekojira/vespucci
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.1.0
 * @package    Vespucci
 */
class Vespucci_Plugin {

	/**
	 * Activate plugin
	 * Fired upon plugin activation. Will create database tables and set default options.
	 *
	 * @since    0.1.0
	 */
	public static function activate() {

		$plugin = new Vespucci_Core;
		$plugin_name = $plugin->get_plugin_name();
		$plugin_version = $plugin->get_version();
		new Vespucci( $plugin_name, $plugin_version );

		// in case of SQL or "headers already sent" errors upon activation, uncomment for debug info
		// ob_start();


		global $wpdb;

		// charset
		$charset_collate = ! empty ( $wpdb->charset ) ? "DEFAULT CHARACTER SET {$wpdb->charset}" : "DEFAULT CHARACTER SET utf8";
		// collation
		$charset_collate .= ! empty ( $wpdb->collate ) ? " COLLATE {$wpdb->collate}" : " COLLATE utf8_general_ci";

		$sql = array();

		// table 'locations' stores basic location data (coordinates, address...)
		$table_name = $wpdb->prefix . 'locations';
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '$table_name'", ARRAY_A );
		if ( count( $table_exists ) == 0 ) :

			$sql[] = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			status varchar(20) NOT NULL default 'public',
			lat DECIMAL(9,6) NOT NULL default 0,
			lng DECIMAL(9,6) NOT NULL default 0,
			title TEXT NOT NULL,
			street VARCHAR(144) NOT NULL default '',
			area VARCHAR(128) NOT NULL default '',
			city VARCHAR(96) NOT NULL default '',
			district VARCHAR(96) NOT NULL default '',
			state VARCHAR(96) NOT NULL default '',
			postcode VARCHAR(24) NOT NULL default '',
			country	VARCHAR(96) NOT NULL default '',
			countrycode CHAR(2) NOT NULL,
			updated DATETIME NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY id (id),
			KEY coordinates (lat,lng),
			KEY lat (lat),
			KEY lng (lng),
			KEY city (city),
			KEY region (state),
			KEY postcode (postcode),
			KEY country (country),
			KEY countrycode (countrycode)
		) $charset_collate;";

		endif;

		// table 'location_relationships' links each location to one wp object identified by id
		$table_name = $wpdb->prefix . 'location_relationships';
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '$table_name'", ARRAY_A );
		if ( count( $table_exists ) == 0 ) :

			$sql[] = "CREATE TABLE $table_name (
			object_name VARCHAR(80) NOT NULL default '',
			object_id BIGINT(20) UNSIGNED NOT NULL default 0,
			location_id BIGINT(20) UNSIGNED NOT NULL default 0,
			object_date DATETIME NOT NULL default '0000-00-00 00:00:00',
			updated DATETIME NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY relationship (object_name,object_id,location_id),
			KEY object (object_name,object_id),
			KEY object_date (object_name,object_date),
			KEY updated (object_name,updated)
		) $charset_collate;";

		endif;

		// table 'locationmeta' stores various location meta data for each location
		$table_name = $wpdb->prefix . 'locationmeta';
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '$table_name'", ARRAY_A );
		if ( count( $table_exists ) == 0 ) :

			$sql[] = "CREATE TABLE $table_name (
			meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			location_id BIGINT(20) UNSIGNED NOT NULL default 0,
			meta_key VARCHAR(255) NULL,
			meta_value LONGTEXT NULL,
			PRIMARY KEY meta_id (meta_id),
			KEY location_id (location_id),
			KEY meta_key (meta_key)
		) $charset_collate; ";

		endif;

		// execute queries, if any
		if ( ! empty( $sql ) && count ( $sql ) > 0 ) :
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta( $sql );
		endif;

		// in case of SQL or "headers already sent" errors upon activation, uncomment for debug info
		// trigger_error( ob_get_contents(), E_USER_ERROR );

		// set options' default values
		$options = array();
		$options[$plugin_name . '_settings']    = Vespucci::default_options( 'settings' );
		$options[$plugin_name . '_location']    = Vespucci::default_options( 'location' );
		$options[$plugin_name . '_meta']        = Vespucci::default_options( 'meta' );
		$options[$plugin_name . '_objects']     = Vespucci::default_options( 'objects' );
		$options[$plugin_name . '_version']     = Vespucci::get_version();
		// before saving, check if there are options saved already in db
		foreach( $options as $key => $value )
			if ( get_option( $key ) != true )
				add_option( $key, $value );
	}

	/**
	 * Deactivate plugin.
	 * Fired upon plugin deactivation.
	 *
	 * @since   0.1.0
	 */
	public static function deactivate() {
		// nothing here yet, just a placeholder
	}

}