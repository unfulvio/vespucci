<?php
/**
 * Vespucci
 *
 * @package   Vespucci
 * @license   GPL-2.0+
 * @link      https://github.com/nekojira/vespucci
 */

/**
 * Vespucci static class
 * Public access functions with namespace
 *
 * @package Vespucci
 */
class Vespucci {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $name    The ID of this plugin.
	 */
	private $name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Instance of this class.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @var     object
	 */
	protected static $instance = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.0	 *
	 * @var      string    $name       The name of the plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name = '', $plugin_version = '' ) {

		// in case this class is instantiated outside the plugin
		if ( empty( $plugin_name ) && empty( $plugin_version ) ) {
			$plugin         = new Vespucci_Core();
			$plugin_name    = $plugin->get_plugin_name();
			$plugin_version = $plugin->get_version();
		}

		$this->name = $plugin_name;
		$this->version = $plugin_version;

	}

	/**
	 * Enqueue frontend styles.
	 * Register the stylesheets for the public-facing side of the plugin.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {

		// nothing here yet!

	}

	/**
	 * Enqueue frontend scripts.
	 * Register the stylesheets for the public-facing side of the plugin.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_scripts() {

		// nothing here yet!

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	private static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( self::$instance == null )
			self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Return the plugin name.
	 *
	 * @since   0.1.0
	 *
	 * @return  string  this plugin name
	 */
	public static function get_plugin_name() {
		$plugin = self::get_instance();
		return $plugin->name;
	}

	/**
	 * Return the plugin version.
	 *
	 * @since   0.1.0
	 *
	 * @return  string  this plugin version
	 */
	public static function get_version() {
		$plugin = self::get_instance();
		return $plugin->version;
	}

	/**
	 * Return a default option value.
	 * Returns one of the plugin default options, according to the option name passed as argument.
	 *
	 * @since   0.1.0
	 *
	 * @param   string  $option the option name
	 *
	 * @return  array|string    the option defaults or empty string if empty, error or invalid option
	 */
	public static function default_options( $option ) {

		$plugin_name = self::get_plugin_name();

		$defaults = array();
		$options = array();
		if ( $option == 'settings' ) {

			$options = array(
				'map_provider' => 'google',
				'map_provider_api_key' => array(
					'google' => '',
				),
				'disable_scripts' => false,
				'markers' => array(
					'size' => 'full',
					'current' => '',
					'cluster' => '',
					'group' => '',
				),
			);

		} elseif( $option == 'location' ) {

			$options = array(
				'status' => '',
				'lat' => 0.000000,
				'lng' => 0.000000,
				'title' => '',
				'street' => '',
				'area' => '',
				'city' => '',
				'district' => '',
				'state' => '',
				'postcode' => '',
				'country' => '',
				'countrycode' => '',
			);

		} elseif ( $option == 'meta' ) {

			$options = array(
				'dragging' => true,
				'map_type' => array(
					'google' => 'roadmap',
				),
				'zoom' => array(
					'default' => 2,
					'min' => 1,
					'max' => 22,
				),
				'radius' => '50km',
				'limit' => false,
				'marker' => array(
					'default' => '',
				),
			);

		} elseif ( $option == 'objects' ) {

			$options = array(
				'post_types' => array(
					'items' => array(),
					'markers' => array(),
				),
				'taxonomies' => array(
					'items' => array(),
					'markers' => array(),
				),
				'users' => array(
					'items' => array(),
					'markers' => array(),
				),
				'comments' => array(
					'items' => array(),
					'markers' => array(),
				)
			);

		}

		if ( is_array( $options ) && ! empty ($options ) )
			foreach ( $options as $key => $value )
				$defaults[$key] = $value;

		return apply_filters( $plugin_name . '_default_options', $defaults, $option );
	}

	/**
	 * Get the available map providers.
	 * Returns an array with registered map providers.
	 *
	 * @since   0.1.0
	 *
	 * @return  array   map providers
	 */
	public static function map_providers() {

		$plugin_name = self::get_plugin_name();

		$providers = array();
		$providers['google'] = array(
			'name'      => 'google-maps',
			'label'     => __( 'Google Maps', $plugin_name ),
			'api_key'   => '',
			'map_types' => array(
				'roadmap'   => __( 'Roadmap', $plugin_name ),
				'terrain'   => __( 'Terrain', $plugin_name ),
				'satellite' => __( 'Satellite', $plugin_name ),
			),
			'options'   => array(),
		);

		return apply_filters( $plugin_name . '_map_providers', $providers );
	}

	/**
	 * Convert an address to string.
	 * Helper function to format address data from object or array to string.
	 *
	 * @since  0.1.0
	 *
	 * @param  object|array  $address   the address
	 * @param  string        $sep       an optional separator for the items in the address (default empty space)
	 *
	 * @return string  the address formatted as a string
	 */
	private static function stringify_address( $address, $sep = ' ' ) {

		// this might be passed as an object, then typecast
		if ( is_object( $address ) )
			$address = (array) $address;

		if ( ! is_array( $address ) )
			return '';

		// format with separator
		foreach( $address as $key => $value )
			if ( ! empty ( $value ) )
				$formatted[ $key ] = $value . $sep . ' ';

		// merge with defaults
		$default = self::default_options( 'location' );
		$address_vars = wp_parse_args( $address, $default );
		extract( $address_vars, EXTR_SKIP );

		$string = $street . $area . $city . $district . $state . $postcode . $country;

		return $string;
	}

	/**
	 * Validate date.
	 * Helper function to validate a date string according to a date format.
	 *
	 * @since   0.1.0
	 *
	 * @param   string  $date   the date to check
	 * @param   string  $format the format on how the $date should be evaluated
	 *
	 * @return  bool    tells whether the date validates or not
	 */
	private static function validate_date( $date, $format = 'Y-m-d H:i:s') {
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}

	/**
	 * Check if a location exists.
	 * Queries the database to see if a location id corresponds to a valid row.
	 *
	 * @since   0.1.0
	 *
	 * @param   int $id the id of the location to check
	 *
	 * @return  bool    true if location exists, false if doesn't
	 */
	public static function location_exists( $id ) {

		if ( ! is_int( $id ) )
			return false;

		global $wpdb;
		$table = $wpdb->prefix . 'locations';
		$result = $wpdb->get_results( "
			SELECT 	*
			FROM 	$table
			WHERE 	id = $id
		" );
		$result = isset( $result[0] ) ? $result[0] : $result;

		return $result ? true : false;
	}

	/**
	 * Get location data.
	 * Returns a location as an object from database, for a given WordPress object.
	 * For example, will return any saved location data for 'post' with 'id' = 23 or 'user' with 'id' = 156.
	 *
	 * @since 0.1.0
	 *
	 * @param  int|string  $id    the corresponding WordPress object id
	 * @param  string      $type  the type of object to query (can be either 'post', 'term', 'user' or 'comment')
	 *
	 * @return object|string resulting location from database or empty string if no results
	 */
	public static function get_location( $type = '', $id = '' ) {

		if ( empty( $type ) || empty( $id ) ) {

			$result = self::try_get_location();

		} elseif ( ! is_int( $id ) || $id === 0 || ! in_array( $type, array( 'post', 'user', 'comment', 'term' ) ) ) {

			trigger_error( 'Trying to get a location using invalid arguments.', E_USER_NOTICE );
			$result = '';

		} else {

			global $wpdb;
			$result = '';

			// first find the corresponding location connected to the specified object
			$table    = $wpdb->prefix . 'location_relationships';
			$loc_id = $wpdb->get_var( $wpdb->prepare( "
					SELECT 	location_id
					FROM 	$table
					WHERE	object_name = %s
					AND 	object_id = %d
			", $type, $id ) );

			// if there's a match, look up for the location data
			if ( ! is_null( $loc_id ) )  :

				$table  = $wpdb->prefix . 'locations';
				$result = $wpdb->get_results( $wpdb->prepare( "
						SELECT 	*
			            FROM	$table
			            WHERE	id = %d
	            ", $loc_id ) );

			endif;

		}

		return isset( $result[0] ) ? $result[0] : $result;
	}

	/**
	 * Try to get location data.
	 * Internal helper function to retrieve a location from database,
	 * when no id or type variables are passed.
	 *
	 * @since 0.1.0
	 *
	 * @return object|string will return empty string if no success or object
	 */
	private static function try_get_location() {

		global $post, $comment, $user;

		$location = '';

		if ( isset( $post->ID ) ) {
			$location = self::get_location( 'post', $post->ID );
		} elseif ( isset( $user->ID ) ) {
			$location = self::get_location( 'user', $user->ID );
		} elseif ( isset( $comment->comment_ID ) ) {
			$location = self::get_location( 'comment', $comment->comment_ID );
		}

		return $location;
	}

	/**
	 * Save or update a location.
	 * Saves or updates (if already existing) a location to database.
	 *
	 * @since 0.1.0
	 *
	 * @param   array   $args   the location data
	 * @param   string  $type   the WordPress object the location is attached to ('post', 'user', 'term', 'comment'...)
	 * @param   int     $id     the id of the WordPres object the location is attached to
	 *
	 * @return  int|null    will return the location id if database interaction occurred, or null in case of errors
	 */
	public static function save_location( $args, $type, $id ) {

		// bail out early if location object relationship is unspecified or invalid
		if ( ! is_int( $id ) || ! in_array( $type, array( 'post', 'page', 'user', 'term', 'comment' ) ) ) {
			trigger_error( 'Cannot save location without a valid object type or object id.', E_USER_WARNING );
			return null;
		}

		// set default values to merge with passed args
		$defaults = self::default_options( 'location' );
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		// check if passed latitude and longitude exist and are numbers
		if ( ! is_numeric( $lat ) || ! is_numeric( $lng ) ) {
			trigger_error( 'Cannot save or update location without valid coordinates.', E_USER_WARNING );
			return null;
		}

		$type = $type == 'page' ? 'post' : $type;
		$obj_date = isset( $date ) ? $date : '';
		$obj_date =  $obj_date && self::validate_date( $obj_date ) == true ? $obj_date : '';

		if ( empty( $status ) ) :

			if ( $type == 'post' ) {

				$post = get_post( $id );
				$status = $post->post_status == 'publish' ? 'public' : 'private';

			} elseif ( $type == 'comment' ) {

				$comment = wp_get_comment_status( $id );
				$status = $comment == 'approve' ? 'public' : 'private';

			} else {

				$status = 'public';

			}

		endif;

		$data = array(
			'status'        => $status,
			'title'         => $title,
			'lat'           => $lat,
			'lng'           => $lng,
			'street'        => $street,
			'area'          => $area,
			'city'          => $city,
			'district'      => $district,
			'state'         => $state,
			'postcode'      => $postcode,
			'country'       => $country,
			'countrycode'   => $countrycode,
			'updated'       => current_time( 'mysql' ),
		);

		global $wpdb;
		$location = self::get_location( $type, $id );

		// save (location exists)
		if (  $location != true ) {

			$table = $wpdb->prefix . 'locations';
			$wpdb->insert( $table, $data );

			// the last generated auto increment id from the query above
			$loc_id = (int) $wpdb->insert_id;

		// update existing location
		} else {

			$loc_id = isset( $location->id ) ? (int) $location->id : '';

			if ( ! is_int( $loc_id ) || $loc_id === 0 )
				return null;

			$table = $wpdb->prefix . 'locations';
			$wpdb->update( $table, $data, array( 'id' => $loc_id ), '%s', array( '%d' ) );

		}

		// save or update relationship
		Vespucci::save_location_relationship( $type, $id, $loc_id, $obj_date );

		// save or update geo meta data, according to WordPress Geo standards
		if ( in_array( $type, array( 'post', 'user', 'comment' ) ) ):
			self::edit_wp_meta( $type, $id, 'update', array(
				'lat' => $lat,
				'lng' => $lng,
				'address' => self::stringify_address( $data ),
				'public' => $status == 'public' ? 1 : 0
			) );
		endif;

		return $loc_id;
	}

	/**
	 * Get location relationship.
	 * Returns the relationship data of a location.
	 *
	 * @since   0.1.0
	 *
	 * @param   int $id the location id
	 *
	 * @return  object|null|bool   returns false if no results, database object on match, null on error
	 */
	public static function get_location_relationship( $id ) {

		if ( ! is_int( $id ) )
			return null;

		global $wpdb;

		// first find the corresponding location connected to the specified object
		$table = $wpdb->prefix . 'location_relationships';
		$result = $wpdb->get_results( $wpdb->prepare( "
				SELECT 	*
				FROM 	$table
				WHERE	location_id = %d
		", $id ) );

		return $result ? $result : false;
	}

	/**
	 * Save location relationship.
	 * Saves relationship data of a location to a WordPress object to database.
	 *
	 * @since   0.1.0
	 *
	 * @param   string      $type       the associated WordPress object type (post, term, user, comment...)
	 * @param   int         $obj_id     the associated WordPress object id
	 * @param   int|string  $loc_id     the location id, if empty string will attempt to find it
	 * @param   string      $obj_date   the associated WordPress object published date (datetime)
	 */
	public static function save_location_relationship( $type, $obj_id, $loc_id = '', $obj_date = '' ) {

		$type = $type == 'page' ? 'post' : $type;

		if ( empty( $loc_id ) ) :

			$location = self::get_location( $type, $obj_id );
			$loc_id = isset( $location->id ) ? (int) $location->id : '';

		endif;

		// validate args
		if ( ! is_int( $loc_id ) || ! is_int( $obj_id ) || ! in_array( $type, array( 'post', 'page', 'user', 'term', 'comment' ) ) ) {
			trigger_error( 'Cannot update relationship without valid arguments.', E_USER_WARNING );
			return;
		}

		$type = $type == 'page' ? 'post' : $type;

		// if unset, get the object date
		if ( empty( $obj_date ) || ! is_string( $obj_date ) ) :

			if ( $type == 'post' ) {

				$obj = get_post( $obj_id );
				$obj_date = isset( $obj->post_date ) ? $obj->post_date : '';

			} elseif( $type == 'user' ) {

				$obj = get_user_by( 'id', $obj_id );
				$obj = isset( $obj->data ) ? $obj->data : '';
				$obj_date = isset( $obj->user_registered ) ? $obj->user_registered : '';

			} elseif( $type == 'comment' ) {

				$obj = get_comment( $obj_id );
				$obj_date = isset( $obj->comment_date ) ? $obj->comment_date : '';

			} else {

				$obj_date = current_time( 'mysql' );

			}

		endif;

		// check if date is valid
		if ( self::validate_date( $obj_date ) != true )
			return;

		// update existing location relationship
	    if ( $loc_id != 0 && self::get_location_relationship( $loc_id ) == true ) {

		    $data = array(
			    'object_name'   => $type,
			    'object_id'     => $obj_id,
			    'object_date'   => $obj_date,
			    'updated'       => current_time( 'mysql' ),
		    );
		    $format = array(
			    '%s', '%d', '%s', '%s',
		    );
		    // update
		    global $wpdb;
		    $table = $wpdb->prefix . 'location_relationships';
		    $wpdb->update( $table, $data, array( 'location_id' => $loc_id ), $format, array( '%d' ) );

		// save new location relationship
	    } else {

		    $data = array(
			    'object_name'   => $type,
			    'object_id'     => $obj_id,
			    'location_id'   => $loc_id,
			    'object_date'   => $obj_date,
			    'updated'       => current_time( 'mysql' ),
		    );
		    // save
		    global $wpdb;
		    $table = $wpdb->prefix . 'location_relationships';
		    $wpdb->insert( $table, $data );

	    }

	}

	/**
	 * Check if a location meta exists.
	 *
	 * @since   0.1.0
	 *
	 * @param   int     $id the location meta id
	 *
	 * @return  bool    whether the location meta exists or not
	 */
	public static function location_meta_exists( $id ) {

		if ( ! is_int( $id ) )
			return false;

		global $wpdb;
		$table = $wpdb->prefix . 'locationmeta';
		$result = $wpdb->get_results( "
			SELECT 	*
			FROM 	$table
			WHERE 	meta_id = $id
		" );
		$result = isset( $result[0] ) ? $result[0] : $result;

		return $result ? true : false;

	}

	/**
	 * Get a location meta.
	 * Returns a location meta value for the specified key and location.
	 * If no key is passed, will return an array with all location meta key-value pairs for the given location.
	 *
	 * @since   0.1.0
	 *
	 * @param   int     $loc_id     the location object id
	 * @param   string  $meta_key   the meta key name (optional)
	 *
	 * @return  string|array  the corresponding meta value
	 */
	public static function get_location_meta( $loc_id, $meta_key = '' ) {

		// validate arguments
		if ( ! is_int( $loc_id ) || self::location_exists( $loc_id ) != true )
			return '';

		global $wpdb;
		$table = $wpdb->prefix . 'locationmeta';

		if ( ! empty( $meta_key ) && is_string( $meta_key ) ) {

			$meta_key = sanitize_key( $meta_key );
			$meta_value = $wpdb->get_var( $wpdb->prepare( "
					SELECT 	meta_value
					FROM 	$table
					WHERE 	location_id = %d
					AND 	meta_key = %s
				", $loc_id, $meta_key
			) );

			return $meta_value ? maybe_unserialize( $meta_value ) : '';

		} else {

			$results = $wpdb->get_results( $wpdb->prepare( "
					SELECT 	*
		            FROM    $table
		            WHERE	location_id = %d
				", $loc_id
			) );

			return $results;

		}

	}

	/**
	 * Save location meta.
	 * Saves or updates one or more location meta.
	 *
	 * @since   0.1.0
	 *
	 * @param   int     $loc_id the id of the corresponding location
	 * @param   array   $args   location metadata in key-value pairs (could be multiple meta)
	 *
	 * @return  array    will return an array with the ids of the saved/updated location meta
	 */
	public static function save_location_meta( $loc_id, $args ) {

		$meta_ids = array();

		// bail out early if location does not exist
		if ( ! is_int( $loc_id ) || ! is_array( $args ) || self::location_exists( $loc_id ) != true )
			return $meta_ids;

		global $wpdb;
		$table = $wpdb->prefix . 'locationmeta';

		foreach( $args as $meta_key => $meta_value ) :

			$meta_key = sanitize_key( $meta_key );

			// serialize everything
			if ( is_int( $meta_value ) )
				$meta_value = serialize( strval( $meta_value ) );
			else
				$meta_value = serialize( $meta_value );

			$data = array(
				'location_id'   => $loc_id,
				'meta_key'      => $meta_key,
				'meta_value'    => $meta_value
			);
			$format = array( '%d', '%s', '%s' );

			$meta_id = (int) $wpdb->get_var( $wpdb->prepare( "
					SELECT 	meta_id
					FROM 	$table
					WHERE 	location_id = %d
					AND 	meta_key = %s
			", $loc_id, $meta_key ) );

			// update existing meta
			if ( $meta_id == true && $meta_id !== 0 ) {

				$wpdb->update( $table, $data, array( 'meta_id' => $meta_id ), $format, array( '%d' ) );

			// save new location meta
			} else {

				$wpdb->query( $wpdb->prepare( "
						INSERT INTO $table
						( location_id, meta_key, meta_value )
						VALUES ( %d, %s, %s )
				", $loc_id, $meta_key, $meta_value ) );

				$meta_id = (int) $wpdb->insert_id;

			}

			$meta_ids[] = $meta_id;

		endforeach;

		return $meta_ids;
	}

	/**
	 * Delete location meta.
	 * Deletes one or more location meta from database, according to arguments passed.
	 *
	 * @since   0.1.0
	 *
	 * @param   int     $loc_id the id of the corresponding location
	 * @param   array   $args   location metadata in key-value pairs (could be multiple meta)
	 */
	public static function delete_location_meta( $loc_id, $args ) {

		// bail out early if location does not exist or arguments are invalid
		if ( ! is_int( $loc_id ) || ! is_array( $args ) || self::location_exists( $loc_id ) == false )
			return;

		global $wpdb;
		$meta_row = $wpdb->prefix . 'locationmeta';

		foreach( $args as $meta_key => $meta_value ) :

			$meta_key = sanitize_key( $meta_key );
			$meta = self::get_location_meta( $loc_id, $meta_key );
			$meta_id = isset( $meta->meta_id ) ? $meta->meta_id : '';
			if ( is_int( $meta_id ) )
				$wpdb->delete( $meta_row, array( 'id' => $meta_id ), array( '%d' ) );

		endforeach;

	}

	/**
	 * Delete a location.
	 * Removes one or more locations from the database.
	 * Also synchronizes the data change with WordPress custom fields for geodata.
	 *
	 * @since   0.1.0
	 *
	 * @param   string  $type   the connected WordPress object type
	 * @param   int     $id     the connected WordPress object id
	 *
	 * @return  int|string    returns the id of the deleted location, empty string on error
	 */
	public static function delete_location( $type, $id ) {

		// bail out early if arguments are invalid
		if ( ! is_int( $id ) || ! in_array( $type, array( 'post', 'page', 'user', 'term', 'comment' ) ) ) {
			trigger_error( 'Invalid argument supplied to delete location', E_USER_WARNING );
			return '';
		}

		// get the connected location
		$type = $type == 'page' ? 'post' : $type;
		$location = self::get_location( $type, $id );
		// double check: if no location found, don't delete
		if ( $location != true || ! isset( $location->id ) )
			return '';

		$loc_id = (int) $location->id;
		global $wpdb;

		// delete the location
		$location_row = $wpdb->prefix . 'locations';
		$wpdb->delete( $location_row, array( 'id' => $loc_id ), array( '%d' ) );

		// delete this location relationship
		$location_relationship = $wpdb->prefix . 'location_relationships';
		$wpdb->delete( $location_relationship, array( 'location_id' => $loc_id ), array( '%d' ) );

		// delete the location metadata associated with this location
		$location_meta = $wpdb->prefix . 'locationmeta';
		$wpdb->query( $wpdb->prepare( "
				DELETE 	*
				FROM 	%s
				WHERE 	location_id = %d
		", $location_meta, $loc_id ) );

		// delete additional WordPress Geo metadata
		if ( in_array( $type, array( 'post', 'user', 'comment' ) ) )
			self::edit_wp_meta( $type, $id, 'delete' );

		return $loc_id;
	}

	/**
	 * Edit WordPress meta.
	 * Adds, updates or deletes WordPress custom meta fields with geo data.
	 * Follows WordPress standards to store geo data in custom meta fields.
	 *
	 * @link http://codex.wordpress.org/Geodata
	 *
	 * @since   0.1.0
	 *
	 * @param   string  $type   either 'user', 'comment' or 'post' (where WordPress allows meta natively)
	 * @param   int     $id     the id of the corresponding WordPress object
	 * @param   string  $action either 'update' (or 'add') or 'delete'
	 * @param   array   $args   meta keys and values used if $action is 'add' or 'update'
	 */
	public static function edit_wp_meta( $type, $id, $action, $args = array() ) {

		// bail out early if arguments supplied are invalid or of unexpected type
		if ( empty( $action ) || ! is_int( $id ) || ! in_array( $type, array( 'user', 'post', 'comment' ) )  ) {
			trigger_error( 'Cannot edit object meta without valid arguments.', E_USER_WARNING );
			return;
		}

		$plugin_name = self::get_plugin_name();

		$defaults = array(
			'address' => '',
			'public' => '',
			'lat' => '',
			'lng' => ''
		);
		$args = wp_parse_args(
			apply_filters( $plugin_name . '_edit_post_meta', $args, $action ),
			$defaults
		);

		if ( $action == 'update' || 'add' ) {

			foreach ( $args as $key => $value ) :

				if ( $key == 'address' ) {

					if ( is_object( $value ) || is_array( $value ) ) {
						call_user_func_array( 'update_' . $type . '_meta', array( $id, 'geo_address', self::stringify_address( $value ) ) );
					} elseif ( ! empty( $value ) && is_string( $value ) ) {
						call_user_func_array( 'update_' . $type . '_meta', array( $id, 'geo_address', $value ) );
					}

				} elseif ( $key == 'lat' || $key == 'lng' ) {

					if ( ! is_nan( $value ) ) {
						if ( $key == 'lat' )
							call_user_func_array( 'update_' . $type . '_meta', array( $id, 'geo_latitude', $value ) );
						elseif ( $key == 'lng' )
							call_user_func_array( 'update_' . $type . '_meta', array( $id, 'geo_longitude', $value ) );
					}

				} elseif ( $key == 'public' ) {

					if ( is_bool( $value ) || $value === 1 )
						call_user_func( 'update_' . $type . '_meta', array( $id, 'geo_public', $value ) );

				} else {

					call_user_func( 'update_' . $type . '_meta', array( $id, $key, $value ) );

				}

			endforeach;

		} elseif ( $action == 'delete' ) {

			foreach( $args as $key => $value ) :

				if ( $key == 'address' )
					call_user_func_array( 'delete_' . $type . '_meta', array( $id, 'geo_address' ) );
				elseif( $key == 'lat' )
					call_user_func_array( 'delete_' . $type . '_meta', array( $id, 'geo_latitude' ) );
				elseif ( $key == 'lng' )
					call_user_func_array( 'delete_' . $type . '_meta', array( $id, 'geo_longitude' ) );
				elseif ( $key == 'public' )
					call_user_func_array( 'delete_' . $type . '_meta', array( $id, 'geo_longitude' ) );
				else
					call_user_func_array( 'delete_' . $type . '_meta', array( $id, $key ) );

			endforeach;

		}

	}

}