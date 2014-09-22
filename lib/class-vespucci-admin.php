<?php
/**
 * Vespucci Admin
 *
 * @package   Vespucci
 * @license   GPL-2.0+
 * @link      https://github.com/nekojira/vespucci
 */

/**
 * Vespucci Admin class.
 * Initializes administrative settings in the WordPress dashboard.
 *
 * @package Vespucci
 */
class Vespucci_Admin {

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
	 *
	 * @since   0.1.0
	 * @access  private
	 * @var     string  $plugin_screen_hook_suffix
	 */
	private $plugin_screen_hook_suffix;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.0
	 * @var      string    $name       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $name, $version ) {

		$this->name = $name;
		$this->version = $version;
		$this->load_dependencies();

	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     0.1.0
	 */
	public function enqueue_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) )
			return;

		/*
		 * @todo needs fix: plugin enqueues admin styles in all pages
		 */
		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id
		     OR $this->plugin_screen_hook_suffix == 'toplevel_page_' . $this->name . '-settings' ) :

				wp_enqueue_style( $this->name . '-leaflet', '//cdn.leafletjs.com/leaflet-0.7.3/leaflet.css', array(), $this->version );
				wp_enqueue_style( $this->name . '-tiptip', plugins_url( 'assets/css/vendor/tiptip.css', __FILE__ ), array(), $this->version );
				wp_enqueue_style( $this->name . '-admin', plugins_url( 'assets/css/admin.css', __FILE__ ), array( $this->name . '-tiptip' ), $this->version );

		endif;

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     0.1.0
	 */
	public function enqueue_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) )
			return;

		$options = get_option( $this->name . '_settings' );
		$api_key = isset( $options['map_provider_api_key']['google'] ) ? '&key=' . $options['map_provider_api_key']['google'] : '';

		/*
		 * @todo needs fix: plugin enqueue scripts in every admin page
		 */
		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id
			OR $this->plugin_screen_hook_suffix == 'toplevel_page_' . $this->name . '-settings' ) :

				wp_enqueue_media();
				wp_enqueue_script( $this->name . '-tiptip', plugins_url( 'assets/js/vendor/min/jquery.tipTip.min.js', __FILE__ ), array( 'jquery' ), $this->version, true );
				wp_enqueue_script( $this->name . '-google-maps', '//maps.googleapis.com/maps/api/js?sensor=true' . $api_key );
				wp_enqueue_script( $this->name . '-leaflet', '//cdn.leafletjs.com/leaflet-0.7.3/leaflet.js', array( $this->name . '-google-maps' ) );
				wp_enqueue_script( $this->name . '-leaflet-google', plugins_url( 'assets/js/vendor/min/Google.min.js', __FILE__ ), array( $this->name . '-google-maps', $this->name . '-leaflet' ), $this->version, true );
				wp_enqueue_script( $this->name . '-map', plugins_url( 'assets/js/min/vespucci-admin-map.min.js', __FILE__ ), array( 'jquery-ui-tabs' ), $this->version, true );
				wp_enqueue_script( $this->name . '-admin', plugins_url( 'assets/js/min/vespucci-admin.min.js', __FILE__ ), array( 'jquery', $this->name . '-tiptip', $this->name . '-leaflet' ), $this->version, true );

		endif;

	}

	/**
	 * Load components.
	 *
	 * @since  0.1.0
	 */
	private function load_dependencies() {

		// contains the plugin administration dashboard form fields
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/class-vespucci-fields.php';

	}

	/**
	 * Register WordPress admin dashboard pages for Vespucci plugin.
	 *
	 * @since    0.1.0
	 */
	public function admin_menu_pages() {

		// settings main page on WordPress menu
		$this->plugin_screen_hook_suffix = add_menu_page(
			__( 'Locations', $this->name ),
			__( 'Locations', $this->name ),
			'manage_options',
			$this->name . '-settings',
			array( $this, 'settings_page' ),
			'dashicons-location',
			70
		);

		// general options page
		add_submenu_page(
			$this->name . '-settings',
			__( 'Settings', $this->name ),
			__( 'Settings', $this->name ),
			'manage_options',
			$this->name . '-settings',
			array( $this, 'settings_page' )
		);

		// options concerning locations entries in db
		add_submenu_page(
			$this->name . '-settings',
			__( 'Objects', $this->name ),
			__( 'Objects', $this->name ),
			'manage_options',
			$this->name . '-objects',
			array( $this, 'settings_page' )
		);

		// tools page does not update options, but allows to perform actions over db
		add_submenu_page(
			$this->name . '-settings',
			__( 'Tools', $this->name ),
			__( 'Tools', $this->name ),
			'manage_options',
			$this->name . '-tools',
			array( $this, 'settings_page' )
		);

	}

	/**
	 * Add action links for Vespucci plugin callback.
	 * Adds action links under this plugin entry in WordPress dashboard plugins page.
	 * 
	 * @since 0.1.0
	 * 
	 * @param  array $links links to filter
	 *
	 * @return array filtered links
	 */
	public function add_action_links( $links ) {

		return array_merge( array(
			'settings'  => '<a href="' . admin_url( 'admin.php?page=' . $this->name . '-settings' ) . '">'  . __( 'Settings', $this->name )  . '</a>',
			'objects'   => '<a href="' . admin_url( 'admin.php?page=' . $this->name . '-objects' ) . '">'   . __( 'Objects', $this->name ) . '</a>',
			'tools'     => '<a href="' . admin_url( 'admin.php?page=' . $this->name . '-tools' ) . '">'     . __( 'Tools', $this->name )     . '</a>',
		), $links );

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    0.1.0
	 */
	public function settings_page() {
		include_once 'views/settings-page.php';
	}

	/**
	 * Add settings options for Vespucci plugin.
	 * Creates setting pages, sections and fields.
	 *
	 * @since 0.1.0
	 */
	public function add_settings() {

		// settings defaults
		register_setting( $this->name . '_settings',$this->name . '_settings',  array( $this, 'sanitize_options' ) );
		// location defaults
		register_setting( $this->name . '_settings',$this->name . '_location',  array( $this, 'sanitize_options' ) );
		// location meta defaults
		register_setting( $this->name . '_settings',$this->name . '_meta',      array( $this, 'sanitize_options' ) );
		// wp objects where to attach locations
		register_setting( $this->name . '_objects', $this->name . '_objects',   array( $this, 'sanitize_options' ) );

		$settings = array(
			array(
				'id'        => 'provider',
				'title'     => __( 'Map Provider', $this->name ),
				'callback'  => array( $this, 'settings_provider' ),
				'page'      => 'settings'
			),
			array(
				'id'        => 'defaults',
				'title'     => __( 'Location Defaults', $this->name ),
				'callback'  => array( $this, 'settings_defaults' ),
				'page'      => 'settings'
			),
			array(
				'id'        => 'markers',
				'title'     => __( 'Map Markers', $this->name ),
				'callback'  => array( $this, 'settings_provider' ),
				'page'      => 'settings'
			),
			array(
				'id'        => 'wp-objects',
				'title'     => __( 'Objects', $this->name ),
				'callback'  => array( $this, 'settings_wp_objects' ),
				'page'      => 'objects'
			),
		);
		foreach( $settings as $setting ) :
			add_settings_section(
				$this->name . '-' . $setting['id'],
				$setting['title'],
				$setting['callback'],
				$this->name . '_' . $setting['page']
			);
		endforeach;

		// Default options
		$default_settings   = Vespucci::default_options( 'settings' );
		$default_location   = Vespucci::default_options( 'location' );
		$default_meta       = Vespucci::default_options( 'meta' );
		$default_objects    = Vespucci::default_options( 'objects' );
		// Saved options
		$saved_settings     = get_option( $this->name . '_settings' );
		$saved_location     = get_option( $this->name . '_location' );
		$saved_meta         = get_option( $this->name . '_meta' );
		$saved_objects      = get_option( $this->name . '_objects' );

		// MAP PROVIDERS

		$map_providers = Vespucci::map_providers();
		if ( ! empty( $map_providers ) && is_array( $map_providers ) ) :

			$providers = array();
			$providers_api = array();
			foreach( $map_providers as $map_provider => $value ) :
				$providers[$map_provider]       = $value['label'];
				$providers_api[$map_provider]   = $value['api_key'];
			endforeach;

			add_settings_field(
				$this->name . '_map_providers',
				__( 'Service', $this->name ),
				array( 'Vespucci_Fields', 'select_field' ),
				$this->name . '_settings',
				$this->name . '-provider',
				array(
					'id'            => $this->name .'map_provider',
					'name'          => $this->name .'_settings[map_provider]',
					'choices'       => $providers,
					'label'         => __( 'Default map provider' , $this->name ),
					'value'         => isset( $saved_settings['map_provider'] ) ? $saved_settings['map_provider'] : $default_settings['map_provider'],
					'description'   => __( 'Select mapping service to render maps.', $this->name ),
					'allow_null'    => false
				)
			);

			foreach( $providers_api as $key => $value ) :

				add_settings_field(
					$this->name . '_map_provider_api_key_' . $key,
					__( 'API Key', $this->name ),
					array( 'Vespucci_Fields', 'text_field' ),
					$this->name . '_settings',
					$this->name . '-provider',
					array(
						'id'            => $this->name .'_map_provider_api_key_' . $key,
						'name'          => $this->name .'_settings[map_provider_api_key]['. $key .']',
						'label'         => __( 'Map provider API key' , $this->name ),
						'value'         => isset( $saved_settings['map_provider_api_key'][$key] ) ? $saved_settings['map_provider_api_key'][$key] : $default_settings['map_provider_api_key'][$key],
						'description'   => __( 'Some mapping services may need an API key to work.' , $this->name ),
						'class'         => 'regular-text'
					)
				);

			endforeach;

		endif;

		// LOCATION DEFAULTS

		$values = array();
		$values['location'] = $saved_location ? $saved_location : $default_location;
		$values['meta']     = $saved_meta ? $saved_meta : $default_meta;
		$saved_values       = apply_filters( $this->name . '_location_box_default_values', $values );

		add_settings_field(
			$this->name . '_map_defaults',
			__( 'Map settings', $this->name ),
			array( 'Vespucci_Fields', 'location_box' ),
			$this->name . '_settings',
			$this->name . '-defaults',
			array(
				'id' => $this->name .'_map_defaults',
				'label' => __( 'Default coordinates' , $this->name ),
				'value' => $saved_values,
				'description' => __( 'You can configure map and location settings which will be used as default for each location.', $this->name ),
			)
		);

		// DISABLE SCRIPTS

		add_settings_field(
			$this->name . '_disable_scripts',
			__( 'Disable scripts', $this->name ),
			array( 'Vespucci_Fields', 'bool_field' ),
			$this->name . '_settings',
			$this->name . '-defaults',
			array(
				'id' => $this->name . '_disable_scripts',
				'name' => $this->name . '_settings[disable_scripts]',
				'label' =>  __( 'Load scripts manually', $this->name ),
				'value' => isset( $saved_settings['disable_scripts'] ) ? $saved_settings['disable_scripts'] : false,
				'description' => __( "If checked, frontend maps won't work unless scripts are manually replaced and enqueued." , $this->name )
			)
		);

		// MARKERS

		$sizes = get_intermediate_image_sizes();
		$choices = array();
		$choices[''] = __( 'Full', $this->name );
		foreach( $sizes as $size ) :
			$choices[$size] = ucfirst( $size );
		endforeach;

		add_settings_field(
			$this->name . '_marker_size',
			__( 'Marker size', $this->name ),
			array( 'Vespucci_Fields', 'select_field' ),
			$this->name . '_settings',
			$this->name . '-markers',
			array(
				'id' => $this->name . '_marker_size',
				'name' => $this->name . '_settings[markers][size]',
				'label' => __( 'Default marker size' , $this->name ),
				'value' => isset( $saved_settings['markers']['size'] ) && $saved_settings['markers']['size'] ? $saved_settings['markers']['size'] : '',
				'choices' => $choices,
				'description' => __( 'Default image size to use when using custom markers from the media library.' , $this->name ),
				'allow_null' => false
			)
		);

		add_settings_field(
			$this->name . '_current_marker',
			__( 'Current Marker', $this->name ),
			array( 'Vespucci_Fields', 'marker_field' ),
			$this->name . '_settings',
			$this->name . '-markers',
			array(
				'id' => $this->name . '_current_marker',
				'name' => $this->name . '_settings[markers][current]',
				'value' => isset( $saved_settings['markers']['current'] ) && $saved_settings['markers']['current'] ? $saved_settings['markers']['current'] : '',
				'description' => __( 'Marker to be used when the currently queried object is shown on map.' , $this->name ),
			)
		);

		add_settings_field(
			$this->name . '_cluster_marker',
			__( 'Cluster Marker', $this->name ),
			array( 'Vespucci_Fields', 'marker_field' ),
			$this->name . '_settings',
			$this->name . '-markers',
			array(
				'id' => $this->name . '_cluster_marker',
				'name' => $this->name . '_settings[markers][cluster]',
				'value' => isset( $saved_settings['markers']['cluster'] ) && $saved_settings['markers']['cluster'] ? $saved_settings['markers']['cluster'] : '',
				'description' => __( 'Overrides default map provider marker for clusters of locations.' , $this->name ),
			)
		);

		add_settings_field(
			$this->name . '_group_marker',
			__( 'Group Marker', $this->name ),
			array( 'Vespucci_Fields', 'marker_field' ),
			$this->name . '_settings',
			$this->name . '-markers',
			array(
				'id' => $this->name . '_group_marker',
				'name' => $this->name . '_settings[markers][group]',
				'value' => isset( $saved_settings['markers']['group'] ) && $saved_settings['markers']['group'] ? $saved_settings['markers']['group'] : '',
				'description' => __( 'To represent locations that share the same coordinates.' , $this->name ),
			)
		);

		// WORDPRESS OBJECTS

		// Post types

		$post_types = get_post_types( array(), 'objects');
		$choices = array();
		foreach ( $post_types as $key => $value ) :
				$choices[$key] = isset( $value->labels ) ? $value->labels->name : $key;
		endforeach;
		unset( $choices['revision'], $choices['nav_menu_item'] );

		add_settings_field(
			$this->name . '_post_types',
			__( 'Post types', $this->name ),
			array( 'Vespucci_Fields', 'wp_objects_field' ),
			$this->name . '_objects',
			$this->name . '-wp-objects',
			array(
				'id' => $this->name . '-objects-post-types',
				'name' => $this->name . '_objects[post_types]',
				'class' => $this->name . '-objects-field post-objects',
				'label' => __( 'Tick to collect location data for the following post types:', $this->name ),
				'value' => isset( $saved_objects['post_types'] ) ? $saved_objects['post_types'] : $default_objects['post_types'],
				'choices' => $choices
			)
		);

		// Taxonomies

		$taxonomies = get_taxonomies( array(), 'objects');
		$choices = array();
		foreach ( $taxonomies as $key => $value ) :
			$choices[$key] = isset( $value->labels ) ? $value->labels->name : ucfirst( $key );
		endforeach;
		unset( $choices['nav_menu'], $choices['post_format'], $choices['link_category'] );

		add_settings_field(
			$this->name . '_taxonomies',
			__( 'Taxonomies', $this->name ),
			array( 'Vespucci_Fields', 'wp_objects_field' ),
			$this->name . '_objects',
			$this->name . '-wp-objects',
			array(
				'id' => $this->name . '-objects-taxonomies',
				'name' => $this->name . '_objects[taxonomies]',
				'class' => $this->name . '-objects-field taxonomy-objects',
				'label' => __( 'Tick to collect location data for the following taxonomies:', $this->name ),
				'value' => isset( $saved_objects['taxonomies'] ) ? $saved_objects['taxonomies'] : $default_objects['taxonomies'],
				'choices' => $choices,
			)
		);

		// Users (user roles)

		$user_roles = get_editable_roles();
		$choices = array();
		foreach ( $user_roles as $role ) :
			$choices[strtolower( $role['name'] )] = $role['name'];
		endforeach;

		add_settings_field(
			$this->name . '_users',
			__( 'Users', $this->name ),
			array( 'Vespucci_Fields', 'wp_objects_field' ),
			$this->name . '_objects',
			$this->name . '-wp-objects',
			array(
				'id' => $this->name . '-objects-users',
				'name' => $this->name . '_objects[users]',
				'class' => $this->name . '-objects-field user-objects',
				'label' => __( 'Tick to collect location data for the following user types:', $this->name ),
				'value' => isset( $saved_objects['users'] ) ? $saved_objects['users'] : $default_objects['users'],
				'choices' => $choices,
			)
		);

		// Comments

		add_settings_field(
			$this->name . '_comments',
			__( 'Comments', $this->name ),
			array( 'Vespucci_Fields', 'wp_objects_field' ),
			$this->name . '_objects',
			$this->name . '-wp-objects',
			array(
				'id' => $this->name . '-objects-comments',
				'name' => $this->name . '_objects[comments]',
				'class' => $this->name . '-objects-field comment-objects',
				'label' => __( 'Tick to collect location data for comments:', $this->name ),
				'value' => isset( $saved_objects['comments'] ) ? $saved_objects['comments'] : $default_objects['comments'],
				'choices' => array( 'comments' => __( 'Comments', $this->name ) ),
			)
		);

	}

	/**
	 * Settings provider section callback
	 *
	 * @since 0.1.0
	 */
	public function settings_provider() {

		echo '<hr />' . "\n";
		echo '<p>' . __( 'Details on the mapping service to be used by this plugin.', $this->name ) . '</p>' . "\n";

	}

	/**
	 * Settings defaults section callback
	 *
	 * @since 0.1.0
	 */
	public function settings_defaults() {

		echo '<hr />' . "\n";
		echo '<p>' . __( 'Default settings used as fallback while rendering maps.', $this->name ) . '</p>' . "\n";

	}

	/**
	 * Settings markers section callback
	 *
	 * @since 0.1.0
	 */
	public function settings_markers() {

		echo '<hr />' . "\n";
		echo '<p>' . __( 'Default location markers to use as fallback while rendering maps.', $this->name ) . '</p>' . "\n";

	}

	/**
	 * Settings WP objects section callback
	 *
	 * @since 0.1.0
	 */
	public function settings_wp_objects() {

		echo '<hr />' . "\n";
		echo '<p>' . __( 'Collect coordinates for selected WordPress objects.', $this->name ) . '</p>' . "\n";

	}

	/**
	 * Callback function to sanitize plugin's options setting fields.
	 *
	 * @since   0.1.0
	 *
	 * @param   array   $input an array with all the input fields
	 *
	 * @return  array   the sanitized values
	 */
	public function sanitize_options( $input ) {

		$output = self::sanitize_fields( $input );

		return apply_filters( $this->name . '_save_options', $output, $input );
	}

	/**
	 * Sanitize location fields.
	 *
	 * @since   0.1.0
	 *
	 * @param   $input  array   an array of fields to sanitize
	 *
	 * @return  array   the sanitized inputs
	 */
	public function sanitize_fields( $input ) {

		$output = '';
		if ( is_array( $input ) ) :

			foreach( $input as $key => $value ) :

				if ( is_string( $value ) ) {
					$output[$key] = sanitize_text_field( $value );
				} else {
					$output[$key] = $value;
				}

			endforeach;

		elseif ( is_string( $input ) ) :

			$output = sanitize_text_field( $input );

		else :

			$output = $input;

		endif;

		return $output;
	}

	/**
	 * Add location meta box.
	 * Adds the meta box container to attach a location to posts or pages.
	 *
	 * @since   0.1.0
	 *
	 * @param   string  $post_type  the post type to add the meta box to
	 */
	public function add_location_box( $post_type ) {

		$post_types = array();
		$objects = get_option( $this->name . '_objects' );
		if ( is_array( $objects ) ) :

			if ( isset( $objects['post_types']['items'] ) )
				foreach ( $objects['post_types']['items'] as $type )
					$post_types[] = $type;

		endif;

		if ( ! empty( $post_types ) && in_array( $post_type, $post_types ) ) :

			$post_screen = $post_type == 'page' ? 'page' : 'post';
			add_meta_box(
				$id             = 'vespucci',
				$title          = __( 'Location', $this->name ),
				$callback       = array( $this, 'render_location_box_contents' ),
				$screen         = $post_screen,
				$context        = 'normal',
				$priority       = 'default',
				$callback_args  = array(
					'type' => 'post',
				)
			);

		endif;

	}

	/**
	 * Render the post location meta box HTML.
	 * Outputs the contents of the meta box for a location attached to post or page.
	 *
	 * @since   0.1.0
	 *
	 * @param   object  $object     the WordPress object where the meta box appears
	 * @param   string  $callback   callback arguments
	 */
	public function render_location_box_contents( $object, $callback ) {

		// default values
		$default_location   = get_option( $this->name . '_location' );
		$default_meta       = get_option( $this->name . '_meta' );
		$default = array(
			'location'  => $default_location ? $default_location : Vespucci::default_options( 'location' ),
			'meta'      => $default_meta ? $default_meta : Vespucci::default_options( 'meta' ),
		);

		// type of WordPress object (post, user, comment, term...)
		$type = isset( $callback['args']['type'] ) ? $callback['args']['type'] : '';
		$type = $type == 'page' ? 'post' : $type;
		// query db for saved data for the current location linked to specified object
		$saved_location = Vespucci::get_location( $type, (int) $object->ID );
		$saved_loc_id = isset( $saved_location->id ) ? (int) $saved_location->id : '';
		$saved_meta_rows = $saved_loc_id ? (array) Vespucci::get_location_meta( $saved_loc_id ) : '';
		$saved_meta = '';
		if ( is_array( $saved_meta_rows ) )
			foreach( $saved_meta_rows as $column )
				$saved_meta[$column->meta_key] = maybe_unserialize( $column->meta_value );

		// data to be used
		$saved = array(
			'location' => $saved_location ? $saved_location : $default_location,
			'meta' => $saved_meta ? $saved_meta : $default_meta,
		);

		$has_location = get_post_meta( $object->ID, $this->name . '_has_location' );

		// echoes additional bool field to disable location for this post
		echo '<div id="' . $this->name . '-location-box-disable">';
			$disable_location = array(
				'id' => $this->name . '-location-disable-save',
				'name' => $this->name . '_location[disable_location]',
				'class' => '',
				'label' => __( 'Do not save a location', $this->name ),
				'value' => $has_location == '1' ? '' : false,
			);
			Vespucci_Fields::bool_field( $disable_location );
		echo '</div>';

		$args = array(
			'class'      => $has_location == '1' ? 'hidden' : '',
			'description'=> '',
			'value'      => $saved ? $saved : $default,
		);
		Vespucci_Fields::location_box( $args );

		wp_nonce_field( $this->name . '_location', $this->name . '_location_nonce' );

	}

	/**
	 * Save post location.
	 * Saves or updates a location according to post location meta box user inputs.
	 * Will also update the location relationships and meta data, as well as WordPress post Geo meta.
	 *
	 * @since   0.1.0
	 *
	 * @param   int  $post_id  the object id the meta box is attached to
	 *
	 * @return  int  $post_id
	 */
	public function save_post_location( $post_id ) {

		// do not save data for revisions
		if ( $parent_id = wp_is_post_revision( $post_id ) == true )
			$post_id = $parent_id;

		// retrieve data from POST
		$location_data = isset( $_POST[$this->name . '_location'] ) ? $_POST[$this->name . '_location'] : '';
		$location_meta = isset( $_POST[$this->name . '_meta'] ) ? $_POST[$this->name . '_meta'] : '';
		// copies status and date from post object
		$status = isset( $_POST['post_status'] ) ? $_POST['post_status'] : '';
		$location_data['status'] = $status == 'publish' ? 'public' : 'private';
		// this meta box nonce
		$nonce = isset( $_POST[$this->name . '_location_nonce'] ) ? $_POST[$this->name . '_location_nonce'] : '';

		// check if data and nonce are set
		if ( ! $location_data || ! $status || ! $nonce )
			return $post_id;

		// @todo nonce verification upon post metabox saving
		// verify that the nonce is valid
		//if ( ! wp_verify_nonce( $nonce, $this->name . '_location_nonce' ) )
		//	return $post_id;

		// check if this is just an autosave (form was not manually submitted)
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		// check the user's permissions
		if ( 'page' == $_POST['post_type'] )
			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
			else
				if ( ! current_user_can( 'edit_post', $post_id ) )
					return $post_id;

		// check if user wants to save location for this post
		$disable_location = isset( $location_data['disable_location'] ) ? $location_data['disable_location'] : false;
		if ( $disable_location == false ) {
			update_post_meta( $post_id, '_' . $this->name . '_has_location', '1' );
		} else {
			update_post_meta( $post_id, '_' . $this->name . '_has_location', '' );
			// also delete an existing location
			Vespucci::delete_location( 'post', $post_id );
			return $post_id;
		}

		// sanitize user inputs
		$input  = $location_data;
		$output =  self::sanitize_fields( $location_data );
		$location_data = apply_filters( $this->name . '_save_location', $output, $input );

		// save or update location
		$loc_id = Vespucci::save_location( $location_data, 'post', $post_id );

		// save or update location meta data
		if ( is_array( $location_meta ) && is_int( $loc_id ) ) {
			$meta_input = $location_meta;
			$meta_output = self::sanitize_fields( $location_meta );
			$location_meta = apply_filters( $this->name . '_save_location_meta', $meta_output, $meta_input );
			Vespucci::save_location_meta( $loc_id, $location_meta );
		}

		return $post_id;
	}

	/**
	 * Trash post location.
	 * Trashes a location upon post deletion.
	 *
	 * @since   0.1.0
	 *
	 * @param   int  $post_id  the object id the meta box is attached to
	 */
	public function trash_post_location( $post_id ) {

		$location_data = (array) Vespucci::get_location( 'post', $post_id );
		if ( ! empty( $location_data ) ) {
			$location_data['status'] = 'private';
			Vespucci::save_location( $location_data, 'post', $post_id );
		}

	}

	/**
	 * Delete post location.
	 * Deletes a post location from database and all its meta.
	 *
	 * @since   0.1.0
	 *
	 * @param   int $post_id    the id of the post being deleted
	 */
	public function delete_post_location( $post_id ) {

		Vespucci::delete_location( 'post', $post_id );

	}

}