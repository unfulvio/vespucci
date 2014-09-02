<?php
/**
 * Vespucci Plugin
 *
 * @package   Vespucci
 * @author    nekojira <fulvio@nekojira.com>
 * @license   GPL-2.0+
 * @link      https://github.com/nekojira/vespucci
 * @copyright 2014 nekojira
 */

/**
 * Vespucci Plugin class
 * Contains plugin activation and deactivation actions
 *
 * @package Vespucci
 * @author  nejojira <fulvio@nekojira.com>
 */
class Vespucci_Plugin {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * Unique identifier for this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'vespucci';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize plugin.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		self::load_components();

	}

	/**
	 * Return the Vespucci plugin slug.
	 *
	 * @since   1.0.0
	 *
	 * @return  string slug variable
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of Vespucci class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when Vespucci plugin is activated.
	 *
	 * @since   1.0.0
	 *
	 * @param   boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) :

					switch_to_blog( $blog_id );
					self::single_activate();

					restore_current_blog();

				endforeach;

			} else {

				self::single_activate();

			}

		} else {

			self::single_activate();

		}

	}

	/**
	 * Fired when Vespucci plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) :

					switch_to_blog( $blog_id );
					self::single_deactivate();

					restore_current_blog();

				endforeach;

			} else {

				self::single_deactivate();

			}

		} else {

			self::single_deactivate();

		}

	}

	/**
	 * Fired when a new site is activated with a WP multisite environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 * Creates tables in database and saves plugin version in options.
	 * Will perform checks to see if the tables already exist in db (eg. in case of installed plugin update).
	 * @see http://codex.wordpress.org/Creating_Tables_with_Plugins
	 *
	 * @since 1.0.0
	 */
	private static function single_activate() {

		// in case of SQL or "headers already sent" errors upon activation, uncomment for debug info
		// ob_start();

		$plugin = self::get_instance();
		$plugin_slug = $plugin->get_plugin_slug();
		
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
				street VARCHAR(144) NULL,
				area VARCHAR(128) NULL,
				city VARCHAR(96) NULL,
				state VARCHAR(96) NULL,
				postcode VARCHAR(24) NULL,
				country	VARCHAR(96) NULL,
				updated DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				PRIMARY KEY id (id),
				KEY coordinates (lat,lng),
				KEY lat (lat),
				KEY lng (lng),
				KEY city (country,city),
				KEY region (country,state),
				KEY postcode (country,postcode),
				KEY country (country)
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
		// trigger_error(ob_get_contents(),E_USER_ERROR)

		// register default options
		$defaults = array(
			'map_provider' => 'google',
			'map_provider_api_key' => array(
				'google' => '',
				'mapbox' => '',
			),
			'map_defaults' => array(
				'coordinates' => array(
					'lat' => 0.000000,
					'lng' => 0.000000
				),
				'dragging' => true,
				'zoom' => array(
					'default' => 10,
					'min' => 0,
					'max' => 21,
				),
				'radius' => '50km',
				'limit' => false,
				'marker' => '',
				'address' => array(
					'street' => '',
					'area' => '',
					'city' => '',
					'state' => '',
					'postcode' => '',
					'country' => '',
				)
			),
			'disable_scripts' => false,
			'current_marker' => '',
			'cluster_marker' => '',
			'group_marker' => '',
		);
		$options = array();
		$options[$plugin_slug . '_locations'] = array('post_types' => array(), 'terms' => array(),'users' => array(), 'comments' => array() );
		$options[$plugin_slug . '_settings'] = $defaults;
		$options[$plugin_slug . '_version'] = self::VERSION;

		foreach( $options as $key => $value ) :
			if ( get_option( $key ) )
				update_option( $key, $value );
			else
				add_option( $key, $value );
		endforeach;

	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since 1.0.0
	 */
	private function single_deactivate() {

		// nothing here yet, just a placeholder

	}

	/**
	 * Load the Vespucci plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Load components.
	 *
	 * @since  1.0.0
	 */
	private static function load_components() {

		require_once 'class-vespucci.php';

	}
	
}