<?php
/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Vespucci
 * @subpackage Vespucci/lib
 * @author     Your Name <email@example.com>
 */
class Vespucci_Plugin {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		$plugin = Vespucci_Core::get_instance();
		$plugin_slug = $plugin->get_plugin_name();

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
			lat DECIMAL(9,6) NOT NULL DEFAULT 0,
			lng DECIMAL(9,6) NOT NULL DEFAULT 0,
			title TEXT NULL,
			street VARCHAR(144) NULL,
			area VARCHAR(128) NULL,
			city VARCHAR(96) NULL,
			state VARCHAR(96) NULL,
			postcode VARCHAR(24) NULL,
			country	VARCHAR(96) NULL,
			code CHAR(2) NOT NULL,
			updated DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY id (id),
			KEY coordinates (lat,lng),
			KEY lat (lat),
			KEY lng (lng),
			KEY title (title),
			KEY city (country,city),
			KEY region (country,state),
			KEY postcode (country,postcode),
			KEY country (country),
			KEY code (code)
		) $charset_collate;";

		endif;

		// table 'location_relationships' links each location to one wp object identified by id
		$table_name = $wpdb->prefix . 'location_relationships';
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '$table_name'", ARRAY_A );
		if ( count( $table_exists ) == 0 ) :

			$sql[] = "CREATE TABLE $table_name (
			object_name VARCHAR(80) NOT NULL,
			object_id BIGINT(20) UNSIGNED NOT NULL,
			location_id BIGINT(20) UNSIGNED NOT NULL,
			object_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY relationship (object_name,location_id,object_id),
			KEY object (object_name,object_id),
			KEY object_date (object_name,object_date)
		) $charset_collate;";

		endif;

		// table 'locationmeta' stores various location meta data for each location
		$table_name = $wpdb->prefix . 'locationmeta';
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '$table_name'", ARRAY_A );
		if ( count( $table_exists ) == 0 ) :

			$sql[] = "CREATE TABLE $table_name (
			meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			location_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			meta_key VARCHAR(255) NULL,
			meta_value LONGTEXT NULL,
			PRIMARY KEY meta_id (meta_id),
			KEY location_id (location_id),
			KEY meta_key (meta_key)
		) $charset_collate;";

		endif;

		// execute queries, if any
		if ( ! empty( $sql ) && count ( $sql ) > 0 ) :
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta( $sql );
		endif;

		// in case of SQL or "headers already sent" errors upon activation, uncomment for debug info
		//trigger_error( ob_get_contents(), E_USER_ERROR );

		$options = array();
		// set options default values
		$options[$plugin_slug . '_locations'] = $plugin->default_options( 'objects' );
		$options[$plugin_slug . '_settings'] = $plugin->default_options( 'settings' );
		$options[$plugin_slug . '_version'] = $plugin->get_version();
		foreach( $options as $key => $value )
			if ( get_option( $key ) )
				update_option( $key, $value );
			else
				add_option( $key, $value );

	}

	/**
	 *
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {

		// nothing here yet, just a placeholder

	}

}