<?php
/**
 * Vespucci
 *
 * @package   Vespucci
 * @author    nekojira <fulvio@nekojira.com>
 * @license   GPL-2.0+
 * @link      https://github.com/nekojira/vespucci
 * @copyright 2014 nekojira
 */

/**
 * Vespucci static class
 * Public access functions with namespace
 *
 * @package Vespucci
 * @author  nejojira <fulvio@nekojira.com>
 */
class Vespucci {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $name    The ID of this plugin.
	 */
	private $name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $name       The name of the plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $name, $version ) {

		$this->name = $name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Public_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Public_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->name, plugin_dir_url( __FILE__ ) . 'css/plugin-name-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Public_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Public_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->name, plugin_dir_url( __FILE__ ) . 'js/plugin-name-public.js', array( 'jquery' ), $this->version, FALSE );

	}

	/**
	 * Get location data.
	 * Returns a location as an object from database, for a given WordPress object.
	 * For example, will return any saved location data for 'post' with 'id' = 23 or 'user' with 'id' = 156.
	 *
	 * @since 1.0.0
	 *
	 * @param  int|string  $id    the corresponding WordPress object id
	 * @param  string      $type  the type of object to query (can be either 'post', 'term', 'user' or 'comment')
	 *
	 * @return object|string resulting location from database or empty string if no results
	 */
	public static function get_location( $id = '', $type = '' ) {

		if ( ! $id || ! $type ) {
			$result = self::try_get_location();
		} elseif ( ! is_int( $id ) ) {
			trigger_error( 'Trying to get location with an ID which is not a number.', E_USER_WARNING );
			return '';
		} elseif ( ! in_array( $type, array( 'post', 'term', 'user', 'comment' ) ) ) {
			trigger_error( 'Trying to get location without specifying a valid object type.', E_USER_WARNING );
			return '';
		} else {
			global $wpdb;
			$table = $wpdb->prefix . self::plugin_slug() . '_' . $type . 's';
			$result = $wpdb->get_row( "
					SELECT *
					FROM $table
					WHERE id = $id
			" );
		}

		return is_array( $result ) ? $result[0] : '';
	}

	/**
	 * Try to get location data.
	 * Internal helper function to retrieve a location from database,
	 * when no id or type variables are passed.
	 *
	 * @since 1.0.0
	 *
	 * @return object|string will return empty string if no success or object
	 */
	private static function try_get_location() {

		global $post, $comment, $user;

		$location = '';
		if ( isset( $post->ID ) ) {
			$location = self::get_location( $post->ID, 'post' );
		} elseif ( isset( $user->ID ) ) {
			$location = self::get_location( $user->ID, 'user' );
		} elseif ( isset( $comment->comment_ID ) ) {
			$location = self::get_location( $comment->comment_ID, 'comment' );
		}

		return $location;
	}

	/**
	 * Get coordinates for location.
	 * Returns an array with lat and lng keys for a given location object.
	 *
	 * @since 1.0.0
	 *
	 * @param object|string $location the location (if empty string, will attempt to determine location)
	 *
	 * @return array|string coordinates as an array or empty string if no coordinates found or error
	 */
	public static function get_coordinates( $location = '' ) {

		$coordinates = '';
		$location = ! empty ( $location ) ? $location : self::try_get_location();

		if ( isset( $location->lat ) && isset( $location->lng ) )
			$coordinates = array( 'lat' => $location->lat, 'lng' => $location->lng );

		return $coordinates;
	}

	/**
	 * Convert an address to string.
	 * Formats an address from object or array to string.
	 *
	 * @since  1.0.0
	 *
	 * @param  object|array  $address  the address
	 * @param  string        $sep      an optional separator for the items in the address (default empty space)
	 *
	 * @return string  the address formatted as a string
	 */
	private static function stringify_address( $address, $sep = ' ' ) {

		if ( is_object( $address ) )
			$address = (array) $address;

		if ( ! is_array( $address ) )
			return '';

		$string = '';
		foreach( $address as $item )
			$string .= $item . $sep;

		return $string;
	}

	/**
	 * Get location address.
	 * Returns the address for a given location object from database as an array.
	 *
	 * @since 1.0.0
	 *
	 * @param  object|string $location  the location object, if empty string will attempt to determine the location
	 * @param  string        $format    the return format: associative array (default array, object, json or string)
	 * @param  string        $sep       if $format is 'string', an optional separator can be defined (default empty space)
	 *
	 * @return array|object  outputs the location address
	 */
	public static function get_address( $location = '', $format = 'array', $sep = ' ' ) {

		$location = ! empty ( $location ) ? $location : self::try_get_location();

		$address = '';
		if ( is_object( $location ) ) :

			// $format == OBJECT
			$address = new stdClass();
			$address->street = isset( $location->street ) ? $location->street : '';
			$address->area = isset( $location->area ) ? $location->area : '';
			$address->city = isset( $location->city ) ? $location->city : '';
			$address->state = isset( $location->state ) ? $location->state : '';
			$address->postcode = isset( $location->postcode ) ? $location->postcode : '';
			$address->country = isset( $location->country ) ? $location->country : '';

		endif;

		if ( $address AND $format == 'array' ) {
			$address = (array) $address;
		} elseif ( $address AND $format == 'json' ) {
			$address = json_encode( $address );
		} elseif( $address AND $format == 'string' ) {
			$address = self::stringify_address( $address, $sep );
		}

		return $address;
	}

	/**
	 * Output an address in HTML markup.
	 * Renders an address as HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $address an address with properly labeled keys
	 * @param bool   $country set to true to display country key, default false
	 * @param bool   $schema if true outputs additional schema.org microdata, default true
	 * @param bool   $echo if true, echoes the output, false only returns it
	 *
	 * @return string the address html markup, empty string if address data was absent
	 */
	public static function address_html( $address = array(), $country = false, $schema = true, $echo = true ) {

		$html = '';
		$address = ! empty ( $address ) ? $address : self::get_address();

		if ( ! empty ( $address ) && is_array( $address ) ) :

			$html = "\n";
			$html .= '<span class="address" ' . $schema == true ? 'itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"' : '' . '>' . "\n";
			$html .= isset( $address['street'] ) || isset( $address['area'] ) ? "\t" . '<span class="street" ' . $schema == true ? 'itemprop="streetAddress"' : '' . '>' . $address['street'] . ' ' . $address['area'] . '</span>' . "\n" : '';
			$html .= isset( $address['city'] ) ? "\t" . '<span class="city" ' . $schema == true ? 'itemprop="addressLocality"' : '' . '>' . $address['city'] . '</span>' . "\n" : '';
			$html .= isset( $address['state'] ) ? "\t" . '<span class="state" ' . $schema == true ? 'itemprop="addressRegion"' : '' . '>' . $address['state'] . '</span>' . "\n" : '';
			$html .= isset( $address['postcode'] ) ? "\t" . '<span class="postcode" ' . $schema == true ? 'itemprop="postalCode"' : '' . '>' . $address['postcode'] . '</span>' . "\n" : '';
			$html .= $country == true ? isset( $address['country'] ) ? "\t" . '<span class="country" ' . $schema == true ? 'itemprop="addressCountry"' : '' . '>' . $address['country'] . '</span>' . "\n" : '' : '';
			$html .= '</span>' . "\n";

		endif;

		if ( $echo == true )
			echo $html;

		return $html;
	}

	/**
	 * Get metadata for location.
	 * Returns meta information for a given location object
	 *
	 * @since 1.0.0
	 *
	 * @param object|string $location the location object, if empty string will attempt to determine the location itself
	 *
	 * @return array|string empty string if no meta or error, or array of data
	 */
	public static function get_meta( $location ) {

		$location_meta = '';
		$location = ! empty ( $location ) ? $location : self::try_get_location();

		if ( is_object( $location ) && isset( $location->meta ) ) :

			$meta = json_decode( $location->meta );

			if ( is_array( $meta ) ) :

				foreach ( $meta as $key => $value ) :
					$location_meta[$value] = $key;
				endforeach;

			endif;

		endif;

		return $location_meta;
	}

	/**
	 * Get locations for given arguments.
	 * Returns location objects according to queried parameters.
	 *
	 * @since 1.0.0
	 *
	 * @param string       $in      the key to query db for (either 'coordinates', 'postcode', 'city', 'state', 'country')
	 * @param string|array $value   the value to query for, a string value or array with lat, lng coordinates
	 * @param string       $type    type of objects to query (can be either 'posts', 'terms', 'users' or 'comments')
	 * @param bool         $private if set to false filters out private locations from results, default true
	 *
	 * @return array|string empty string if no matches, or array of objects with locations results from database
	 */
	public static function get_locations_for( $in, $value, $type, $private = true ) {

		if ( ! in_array( $in, array( 'coordinates', 'postcode', 'city', 'state', 'country' ) ) ) {
			trigger_error( 'Trying to search locations matching an invalid or missing criteria.', E_USER_WARNING );
			return '';
		}

		if ( ! in_array( $type, array( 'posts', 'terms', 'users', 'comments' ) ) ) {
			trigger_error( 'Trying to get locations for an invalid or missing type of objects.', E_USER_WARNING );
			return '';
		}

		global $wpdb;
		$table = $wpdb->prefix . self::plugin_slug() . '_' . $type;

		$results = '';
		if ( $in == 'coordinates' ) {

			$lat = isset( $value['lat'] ) ? $value['lat'] : '';
			$lng = isset( $value['lng'] ) ? $value['lng'] : '';
			if ( $lat && $lng ) {
				$results = $wpdb->get_results(
	                $wpdb->prepare( "
						SELECT *
		                FROM %s
		                WHERE lat = %d
		                AND lng = %d
					", $table, $lat, $lng )
				);
			}

		} else {

			if ( ! is_string( $value ) )
				return '';

			$results = $wpdb->get_results(
			                $wpdb->prepare( "
						SELECT *
						FROM %s
						WHERE %s = %s
					", $table, $in, $value )
			);

		}

		if ( $private == false )
			$results = self::get_public_locations( $results );

		return $results;
	}

	/**
	 * Haversine formula to query nearby objects.
	 * Queries database for objects within a distance from specified lat,lng coordinates and a radius.
	 * @see http://en.wikipedia.org/wiki/Haversine_formula
	 * @see https://developers.google.com/maps/articles/phpsqlsearch_v3#findnearsql
	 *
	 * @since 1.0.0
	 *
	 * @param string     $table  type of objects to query (can be either 'posts', 'terms' or 'users')
	 * @param number     $lat    latitude to center the query from
	 * @param number     $lng    longitude to center the query from
	 * @param number     $radius amount of distance to query objects until
	 * @param string     $unit   a valid distance unit for the specified $radius amount
	 * @param int|string $limit  limits the number of posts, no limit if empty string or negative
	 *
	 * @return string|array empty string if no results, array of objects if there are matches
	 */
	private static function haversine_query( $table, $lat, $lng, $radius, $unit = 'km', $limit = '' ) {

		// convert to kilometers always
		$radius = self::convert_distance( $radius, $unit, 'km' );
		// in miles the constant would be 3959
		$constant = 6371; // km

		$results = '';
		if ( ! is_nan( $radius ) ) :

			if ( is_int( $limit ) && $limit >= 1 )
				$limit = " LIMIT 0 , {$limit}";

			global $wpdb;
			$results = $wpdb->query( "
		            SELECT *, (
						$constant * acos( cos( radians( $lat ) ) *
		                        cos( radians( lat ) ) *
		                        cos( radians( lng ) - radians( $lng ) ) +
		                        sin( radians( $lat ) ) *
		                        sin( radians( lat ) )
						) ) AS distance
					FROM $table
					HAVING distance < $radius
					ORDER BY distance
					$limit "
			);

		endif;

		return $results;
	}

	/**
	 * Parse distance.
	 * Parses a single string containing a single number and a valid distance unit.
	 * If valid, returns them separated into an array, otherwise returns empty.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $distance value containing one number and one distance measurement unit
	 *
	 * @return array|string empty string if parse fails, array with parsed 'quantity' and 'unit' if succeeds
	 */
	public static function parse_distance( $distance ) {

		// allowed distance units
		$units = array( 'm', 'meters', 'meter', 'metre', 'metres', 'km', 'kilometers', 'kilometer', 'kilometres', 'kilometre', 'mil', 'swedish mile', 'swedish miles', 'norwegian mile', 'norwegian miles', 'scandinavian mile', 'scandinavian miles', 'ft', 'feet', 'foot', 'yd', 'yards', 'yard', 'mi', 'miles', 'mile', 'nm', 'nmi', 'nautical miles', 'nautical mile' );
		// disjoints allowed units from quantity
		$pattern = '/^(\d+)\s*(' . join( "|", array_map( "preg_quote", $units ) ) . ')$/';
		preg_match( $pattern, $distance, $matches );
		// get radius and unit
		list( , $radius, $unit ) = $matches;

		return is_numeric( $radius ) && is_string( $unit ) ? array( 'quantity' => $radius, 'unit' => $unit ) : '';
	}

	/**
	 * Get locations nearby given coordinates.
	 * Returns an array of location objects within a given distance from given coordinates center.
	 *
	 * @param string     $type     one of the allowed types to query (either 'posts', 'terms', 'users', 'comments')
	 * @param number     $lat      latitude to query from
	 * @param number     $lng      longitude to query from
	 * @param string     $distance radius to query within given coordinates above, default '50km'
	 * @param int|string $limit    optional, limit results to a given amount, empty string if no limit (default)
	 * @param bool       $private  if set to false, filters out results marked as private, default true
	 *
	 * @return array|string empty string if no results, or array of resulting location objects
	 */
	public static function get_locations_nearby( $type, $lat, $lng, $distance = '50km', $limit = '', $private = true ) {

		if ( ! in_array( $type, array( 'posts', 'terms', 'users', 'comments' ) ) ) {
			trigger_error( 'Trying to query locations for a missing or invalid group of objects.', E_USER_WARNING );
			return '';
		}

		if ( is_nan( $lat ) || is_nan( $lng ) ) {
			trigger_error( 'Specified longitude or latitude for query is not a number.', E_USER_WARNING );
			return '';
		}

		$parsed = self::parse_distance( $distance );
		if ( ! $parsed ) {
			trigger_error( 'Specified distance in location query could not be interpreted.', E_USER_WARNING );
			return '';
		}

		$radius = $parsed['quantity'];
		$unit = $parsed['unit'];

		global $wpdb;
		$table = $wpdb->prefix . self::plugin_slug() . '_' . $type;

		$results = self::haversine_query( $table, $lat, $lng, $radius, $unit, $limit );

		return $results;
	}

	/**
	 * Get posts nearby given coordinates.
	 * This is a shortcut alias for get_locations_nearby() method.
	 *
	 * @since 1.0.0
	 *
	 * @param number     $lat      latitude to query from
	 * @param number     $lng      longitude to query from
	 * @param string     $distance radius to query within given coordinates above, default '50km'
	 * @param int|string $limit    optional, limit results to a given amount, empty string if no limit (default)
	 * @param bool       $private  if set to false, filters out results marked as private, default true
	 *
	 * @return array|string empty string if no results, or array of resulting location objects matching posts
	 */
	public static function get_posts_nearby( $lat, $lng, $distance = '50km', $limit = '', $private = true ) {

		$results = self::get_locations_nearby( 'posts', $lat, $lng, $distance, $limit, $private );

		return $results;
	}

	/**
	 * Get terms nearby given coordinates.
	 * This is a shortcut alias for get_locations_nearby() method.
	 *
	 * @since 1.0.0
	 *
	 * @param number     $lat      latitude to query from
	 * @param number     $lng      longitude to query from
	 * @param string     $distance radius to query within given coordinates above, default '50km'
	 * @param int|string $limit    optional, limit results to a given amount, empty string if no limit (default)
	 * @param bool       $private  if set to false, filters out results marked as private, default true
	 *
	 * @return array|string empty string if no results, or array of resulting location objects matching terms
	 */
	public static function get_terms_nearby( $lat, $lng, $distance = '50km', $limit = '', $private = true ) {

		$results = self::get_locations_nearby( 'terms', $lat, $lng, $distance, $limit, $private );

		return $results;
	}

	/**
	 * Get users nearby given coordinates.
	 * This is a shortcut alias for get_locations_nearby() method.
	 *
	 * @since 1.0.0
	 *
	 * @param number     $lat      latitude to query from
	 * @param number     $lng      longitude to query from
	 * @param string     $distance radius to query within given coordinates above, default '50km'
	 * @param int|string $limit    optional, limit results to a given amount, empty string if no limit (default)
	 * @param bool       $private  if set to false, filters out results marked as private, default true
	 *
	 * @return array|string empty string if no results, or array of resulting location objects matching users
	 */
	public static function get_users_nearby( $lat, $lng, $distance = '50km', $limit = '', $private = true ) {

		$results = self::get_locations_nearby( 'users', $lat, $lng, $distance, $limit, $private );

		return $results;
	}

	/**
	 * Get comments nearby given coordinates.
	 * This is a shortcut alias for get_locations_nearby() method.
	 *
	 * @since 1.0.0
	 *
	 * @param number     $lat      latitude to query from
	 * @param number     $lng      longitude to query from
	 * @param string     $distance radius to query within given coordinates above, default '50km'
	 * @param int|string $limit    optional, limit results to a given amount, empty string if no limit (default)
	 * @param bool       $private  if set to false, filters out results marked as private, default true
	 *
	 * @return array|string empty string if no results, or array of resulting location objects matching users
	 */
	public static function get_comments_nearby( $lat, $lng, $distance = '50km', $limit = '', $private = true ) {

		$results = self::get_locations_nearby( 'comments', $lat, $lng, $distance, $limit, $private );

		return $results;
	}

	/**
	 * Convert a distance unit of measurement in metres.
	 * Takes the original unit and returns the equivalent in metres.
	 * The unit to convert must be a valid input or the function will return empty.
	 *
	 * @since 1.0.0
	 *
	 * @param string $unit a valid distance unit to convert in meters
	 *
	 * @return number|string returns a number or empty string if original unit to convert was invalid or unrecognized
	 */
	private static function metrify( $unit ) {

		$in_meters = '';
		if ( $unit == 'm' || 'meter' || 'meters' || 'metre' || 'metres' ) {
			$in_meters = 1;
		} elseif ( $unit == 'km' || 'kilometer' || 'kilometers' || 'kilometre' || 'kilometres' ) {
			$in_meters = 1000;
		} elseif ( $unit == 'mil' || 'swedish mile' || 'swedish miles' || 'norwegian mile' || 'norwegian miles' || 'scandinavian mile' || 'scandinavian miles' ) {
			$in_meters = 10000;
		} elseif ( $unit == 'ft' || 'foot' || 'feet' ) {
			$in_meters = 0.3048;
		} elseif ( $unit == 'yd' || 'yard' || 'yards' ) {
			$in_meters = 0.9144;
		} elseif ( $unit == 'mi' || 'mile' || 'miles' ) {
			$in_meters = 1609.344;
		} elseif ( $unit == 'nm' || 'nmi' || 'nautical mile' || 'nautical miles' ) {
			$in_meters = 1852;
		} else {
			trigger_error( 'The distance unit of measure could not be recognized.', E_USER_WARNING );
			return '';
		}

		return $in_meters;
	}

	/**
	 * Convert a distance from one unit of measure into another.
	 * Converts any amount of distance from one measurement into another.
	 * Will return the converted amount, otherwise empty string if one of the unit is invalid or unrecognized.
	 *
	 * @since 1.0.0
	 *
	 * @param  int|float  $amount  quantity to convert
	 * @param  string     $from    original distance unit to convert from
	 * @param  string     $to      distance unit to convert to
	 *
	 * @return number|string  resulting amount of converted distance, empty string on error
	 */
	private static function convert_distance( $amount, $from, $to ) {

		if ( is_nan( $amount ) ) {
			trigger_error( 'The distance amount to convert is not a number.', E_USER_WARNING );
			return '';
		}

		$from = self::metrify( $from );
		$to = self::metrify( $to );

		if ( ! $from OR ! $to )
			return '';

		$result = $amount * ( $from / $to );

		return $result;
	}

	/**
	 * Get distance between points.
	 * Calculates the distance between sets of coordinate pairs and returns the amount.
	 * Points must be passed as an array of ['lat'], ['lng'] values; must be at least one pair.
	 *
	 * @since 1.0.0
	 *
	 * @param  array   $points  distance as in coordinate pairs (lat, lng) to calculate from
	 * @param  string  $unit    unit of measurement to output the final results
	 * @param  bool    $sum     if true, will return the sum of all distances from one point to another,
	 *                          false will produce an array of distances, default true
	 *
	 * @return number|array|string  number if $sum is set to true, array of numbers if false, empty string on error
	 */
	public static function get_distance( $points, $unit, $sum = true ) {

		$distance = '';
		$result = '';

		$p = count( $points );
		if ( $p >= 2 ) :

			$i = 2;
			while ( $points ) :

				$point_1['lat'] = isset( $points[$i-1]['lat'] ) ? $points[$i-1]['lat'] : '';
				$point_1['lng'] = isset( $points[$i-1]['lng'] ) ? $points[$i-1]['lng'] : '';
				$point_2['lat'] = isset( $points[$i]['lat'] ) ? $points[$i]['lat'] : '';
				$point_2['lng'] = isset( $points[$i]['lng'] ) ? $points[$i]['lng'] : '';

				$dist = '';
				if ( is_numeric( $point_1['lat'] ) && is_numeric( $point_1['lng'] ) && is_numeric( $point_2['lat'] ) && is_numeric( $point_1['lng'] ) ) :

					// calculates the distance between two points given their latitude and longitude
					// @see http://www.geodatasource.com/developers/php
					$theta = $point_1['lng'] - $point_2['lng'];
					$dist = ( sin( deg2rad( $point_1['lat'] ) ) * sin( deg2rad( $point_2['lat'] ) ) ) + ( cos( deg2rad( $point_1['lat'] ) ) * cos( deg2rad( $point_2['lat'] ) ) * cos( deg2rad( $theta ) ) );
					$dist = acos( $dist );
					$dist = rad2deg( $dist );
					$dist = $dist * 60 * 1.1515; // miles
					$dist = self::convert_distance( $dist, 'miles', $unit );

				endif;

				// put the results in array
				$result[$i-1] = $dist;

				$i++;

			endwhile;

		endif;

		if ( $sum == true && is_array( $distance ) )
			$result = array_sum( $distance );

		return $result;
	}

	/**
	 * Validate data.
	 * Performs checks on variables used in database queries.
	 *
	 * @since 1.0.0
	 *
	 * @param  number  $lat     latitude
	 * @param  number  $lng     longitude
	 * @param  int     $id      object id, if $exists is true, must match a WordPress object
	 * @param  string  $type    must be a recognized value ('post', 'term' 'user' or 'comment')
	 * @param  string  $name    name of the taxonomy, if $type is 'term'
	 * @param  bool    $check  if true will consider if the WordPress object exists already in database
	 * @param  bool    $public  must be true or false, 1 or 0
	 *
	 * @return bool   true if succeeds, false if fails
	 */
	private static function validate_data( $lat, $lng, $id, $type, $name = '', $check = true, $public = true ) {

		// id should be a positive integer
		if ( ! is_int( $id ) ) {
			trigger_error( 'The specified ID is not a valid integer.', E_USER_WARNING );
			return false;
		}
		// coordinates must be numbers
		if ( is_nan( $lat ) || is_nan( $lng ) ) {
			trigger_error( 'The latitude or longitude value is not a number.', E_USER_WARNING );
			return false;
		}
		// public status must be true or false
		if ( ! is_bool( $public ) ) {
			trigger_error( 'Public value should be of boolean type.', E_USER_WARNING );
			return false;
		}

		// $type must be one of the accepted values and corresponding WP entity must exist
		if ( $type == 'post' ) {

			$post_exists = $check == true ? get_post( $id ) : true;
			return $post_exists ? true : false;

		} elseif ( $type == 'term' AND ! empty ( $name ) ) {

			$term_exists = $check == true ? term_exists( $id, $name ) : true;
			return $term_exists ? true : false;

		} elseif ( $type == 'user' ) {

			$user_exists = $check == true ? get_user_by( 'id', $id ) : true;
			return $user_exists ? true : false;

		} elseif ( $type = 'comment' ) {

			$comment_exists = $check == true ? get_comment( $id ) : true;
			return $comment_exists ? true : false;

		} else {

			trigger_error( 'Could not recognize the specified object type.', E_USER_WARNING );
			return false;
		}

	}

	/**
	 * Save a new location from database.
	 * If the location is already existing, will do an update.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args an array of arguments as follows:
	 *                    number 'lat'     latitude
	 *                    number 'lng'     longitude
	 *                    int    'id'      the corresponding id of the WordPress object
	 *                    string 'type'    the type of data to query (possible values: 'post', 'term or 'user')
	 *                    string 'name'    if 'type' value is 'term' a taxonomy 'name' must be specified too
	 *                    array  'address' an array containing the location address data
	 *                    string 'meta'    serialized data with location meta attributes
	 *                    bool   'public'  false if this location is private, true if public, default true
	 *                    bool   'exists'  if true, performs a check if a matching WordPress object with specified 'id' already exists, default false
	 *
	 * @return bool will return false in case of errors, otherwise true
	 */
	public static function save_location( $args ) {

		$defaults = array(
			'lat' => '',
			'lng' => '',
			'id' => '',
			'type' => '',
			'name' => '',
			'address' => '',
			'meta' => '',
			'public' => true
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( self::validate_data( $lat, $lng, $id, $type, $name, false, $public ) != true ) {
			trigger_error( 'Could not save location with invalid or missing data.', E_USER_WARNING );
			return false;
		}

		if ( self::get_location( $id, $type ) == true ) {
			self::update_location( $args );
			return true;
		}

		$address = self::get_address( $address );

		global $wpdb;
		$table = $wpdb->prefix . self::plugin_slug() . '_' . $type . 's';

		$data = array(
			'id'        => $id,
			'lat'       => $lat,
			'lng'       => $lng,
			'public'    => $public === true || 1 ? 1 : 0,
			'street'    => $address['street'],
			'area'      => $address['area'],
			'city'      => $address['city'],
			'state'     => $address['state'],
			'postcode'  => $address['postcode'],
			'country'   => $address['country'],
			'meta'      => $meta
		);
		$wpdb->insert( $table, $data );

		if ( in_array( $type, array( 'post', 'user', 'comment' ) ) ) :

			self::edit_wp_meta( $type, $id, 'add', array(
				'lat' => $lat,
				'lng' => $lng,
				'address' => self::stringify_address( $address ),
				'public' => $public
			) );

		endif;

		return true;
	}

	/**
	 * Update a location in database.
	 * Updates a location entry with new data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args an array of arguments as follows:
	 *                    number 'lat'     latitude
	 *                    number 'lng'     longitude
	 *                    int    'id'      the corresponding id of the WordPress object
	 *                    string 'type'    the type of data to query (possible values: 'post', 'term or 'user')
	 *                    string 'name'    if 'type' value is 'term' a taxonomy 'name' must be specified too
	 *                    array  'address' an array containing the location address data
	 *                    string 'meta'    serialized data with location meta attributes
	 *                    bool   'public'  false if this location is private, true if public, default true
	 *
	 * @return bool will return false in case of errors, otherwise true
	 */
	public static function update_location( $args ) {

		$defaults = array(
			'lat' => '',
			'lng' => '',
			'id' => '',
			'type' => '',
			'name' => '',
			'address' => '',
			'meta' => '',
			'public' => true,
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( self::validate_data( $lat, $lng, $id, $type, $name, true, $public ) != true ) {
			trigger_error( 'Could not update location with invalid or missing data.', E_USER_WARNING );
			return false;
		}

		$data = array();
		// id, lat, lng are numbers, hence %d
		$format = array( '%d', '%d', '%d' );

		$data['lat'] = $lat;
		$data['lng'] = $lng;
		$data['public'] = $public === true || 1 ? 1 : 0;

		if ( is_array( $address ) ) :

			$parsed = self::get_address( $address );
			if ( $parsed ) {
				foreach ( $parsed as $value => $key ) :
					$data[$key] = $value;
				endforeach;
				// each address column in db is saved as string, hence %s
				$format = array_merge( $format, array( '%s', '%s', '%s', '%s', '%s', '%s' ) );
			}

		endif;

		if ( is_array( $meta ) ) :

			$current = self::get_location( $id, $type );
			$existing = self::get_meta ( $current );
			$data['meta'] = json_encode( array_merge( $existing, $meta ) );
			// meta is stored in db column as serialized data, hence %s string
			$format = array_merge( $format, array( '%s' ) );

		endif;

		global $wpdb;
		$table = $wpdb->prefix . self::plugin_slug() . '_' . $type . 's';
		$wpdb->update( $table, $data, array( 'id' => $id ), $format, array( '%d' ) );

		if ( in_array( $type, array( 'post', 'user', 'comment' ) ) ):

			self::edit_wp_meta( $type, $id, 'update', array(
				'lat' => $lat,
				'lng' => $lng,
				'address' => $address,
				'public' => $public
			) );

		endif;

		return true;
	}

	/**
	 * Delete row from location database.
	 * Helper for delete_location() function to delete items in database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table the database table where the function takes effect
	 * @param array  $args  an array of arguments
	 * @see delete_location() function for more details on arguments to pass
	 */
	private static function delete_row( $table, $args ) {

		global $wpdb;
		$deleteMeta = '';

		// delete rows matching specified ID(s)
		if ( $args['by'] == 'id' ) {

			if ( ! is_array( $args['id'] ) ) {

				if ( is_int( $args['id'] ) ) {

					$wpdb->delete( $table, array( 'id' => $args['id'] ), array( '%d' ) );
					self::edit_wp_meta( $args['type'], $args['id'], 'delete' );

				} elseif( $args['id'] == '*' ) {

					$wpdb->delete( $table, array( 'id' => $args['id'] ), array( '%s' ) );
					$deleteMeta = $wpdb->get_results(
	                   $wpdb->prepare( "
			                SELECT *
							FROM %s
						", $table )
					);

				}

			} else {

				foreach ( $args['id'] as $id ) :

					if ( is_int( $id ) ) {

						$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
						self::edit_wp_meta( $args['type'], $id, 'delete' );

					}

				endforeach;
			}

			// delete rows matching specified coordinates
		} elseif ( $args['by'] == 'latlng' ) {

			if ( ! is_nan( $args['lat'] ) && ! is_nan( $args['lng'] ) ) :

				$lat = $args['lat'];
				$lng = $args['lng'];
				$wpdb->delete( $table, array( 'lat' => $lat, 'lng' => $lng ), array( '%d', '%d' ) );
				$deleteMeta = $wpdb->get_results(
                   $wpdb->prepare( "
		                SELECT id
						FROM %s
						WHERE lat = %d
						AND lng = %d
					", $table, $lat, $lng )
				);

			endif;

			// delete rows matching 'public' status true or false
		} elseif ( $args['by'] == 'status' ) {

			if ( is_bool( $args['public'] ) ) :

				$public = $args['public'];
				$wpdb->delete( $table, array( 'public' => $public ), array( '%d' ) );
				$deleteMeta = $wpdb->get_results(
                   $wpdb->prepare( "
				        SELECT id
						FROM %s
						WHERE public = %d
					", $table, $public )
				);

			endif;

		}

		if ( is_array( $deleteMeta ) AND in_array( $args['type'], array( 'post', 'user', 'comment' ) ) )
			foreach ( $deleteMeta as $delete )
				self::edit_wp_meta( $delete->id, $args['type'], 'delete' );

	}

	/**
	 * Delete a location.
	 * Removes one or more locations from the database.
	 * Also synchronizes the data change with WordPress custom fields for geodata.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args an array of arguments to set which locations to delete, as follows:
	 *                    'by' sets the criteria, can be 'id' (object id), 'latlng' (coordinates), 'status' (public/private)
	 *                    'id' string '*', unique id (int) or array of unique ids of locations to delete if 'by' value is 'id'
	 *                    'type' can be 'post', 'term', 'user' or 'all' ('all' will not work if 'by' is set to 'id')
	 *                    'lat' latitude to be set if 'by' value is set to 'latlng'
	 *                    'lng' longitude to be set if 'by' value is set to 'latlng'
	 *                    'public' location status, to be set if 'by' value is set to 'status'
	 */
	public static function delete_location( $args ) {

		$defaults = array(
			'by' => '',
			'id' => '',
			'type' => '',
			'lat' => '',
			'lng' => '',
			'public' => ''
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		// deletes matching rows within the specified plugin table
		if ( in_array( $args['type'], array( 'post', 'term', 'user', 'comment' ) ) ) {

			global $wpdb;
			$table = $wpdb->prefix . self::plugin_slug() . '_' . $args['type'] . 's';
			self::delete_row( $table, $args );

			// deletes matching rows from posts, terms and users plugin tables
		} elseif ( $args['type'] == 'all' ) {

			$tables = array( 'posts', 'terms', 'users', 'comments' );
			foreach( $tables as $type ) :

				global $wpdb;
				$table = $wpdb->prefix . self::plugin_slug() . '_' . $type;
				self::delete_row( $table, wp_parse_args( array( 'type' => $type ), $args ) );

			endforeach;

		}

	}

	/**
	 * Edit WordPress meta.
	 * Adds, updates or deletes WordPress custom meta fields with geo data.
	 * Follows WordPress standards to store geo data in custom meta fields.
	 * @see http://codex.wordpress.org/Geodata
	 *
	 * @since 1.0.0
	 *
	 * @param string  $type    either 'user', 'comment' or 'post' (where WordPress allows meta natively)
	 * @param int     $id      the id of the corresponding WordPress object
	 * @param string  $action  either 'update' (or 'add') or 'delete'
	 * @param array   $args    optional extra arguments if $action is 'add' or 'update':
	 *                         'address'  array with address data
	 *                         'public'   true or false
	 *                         'lat'      latitude
	 *                         'lng'      longitude
	 */
	private static function edit_wp_meta( $type, $id, $action, $args = array() ) {

		if ( ! is_int( $id ) ) {
			trigger_error( 'Cannot edit object meta without a valid ID.', E_USER_WARNING );
			return;
		}

		if ( in_array( $type, array( 'user', 'post', 'comment' ) ) ) {
			trigger_error( 'Cannot edit meta of the specified object type "' . $type . '".', E_USER_WARNING );
			return;
		}

		$defaults = array(
			'address' => '',
			'public' => '',
			'lat' => '',
			'lng' => ''
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( $action == 'update' || 'add' ) {

			if ( ! is_nan( $lat ) && ! is_nan( $lng ) ) {
				call_user_func( 'update_' . $type . '_meta', array( $id, 'geo_latitude', $lat ) );
				call_user_func( 'update_' . $type . '_meta', array( $id, 'geo_longitude', $lng ) );
			}

			if ( is_object( $address ) || is_array( $address ) ) {
				call_user_func( 'update_' . $type . '_meta', array( $id, 'geo_address', self::stringify_address( $address ) ) );
			} elseif ( ! empty( $address ) AND is_string( $address ) ) {
				call_user_func( 'update_' . $type . '_meta', array( $id, 'geo_address', $address ) );
			}

			if ( is_bool ( $public ) )
				call_user_func( 'update_' . $type . '_meta', array( $id, 'geo_public', $public ) );

		} elseif ( $action == 'delete' ) {

			call_user_func( 'delete_' . $type . '_meta', array( $id, 'geo_latitude' ) );
			call_user_func( 'delete_' . $type . '_meta', array( $id, 'geo_longitude' ) );
			call_user_func( 'delete_' . $type . '_meta', array( $id, 'geo_address' ) );
			call_user_func( 'delete_' . $type . '_meta', array( $id, 'geo_public' ) );

		}

	}

}