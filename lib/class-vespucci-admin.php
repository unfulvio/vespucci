<?php
/**
 * Vespucci Admin
 *
 * @package   Vespucci_Admin
 * @author    nekojira <fulvio@nekojira.com>
 * @license   GPL-2.0+
 * @link      https://github.com/nekojira/vespucci
 * @copyright 2014 nekojira
 */

/**
 * Vespucci Admin class.
 * Initializes administrative settings in the WordPress dashboard.
 *
 * @package Vespucci_Admin
 * @author  nekojira <fulvio@nekojira.com>
 */
class Vespucci_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 *
	 * @access private
	 */
	private function __construct() {

		// Call $plugin_slug from public plugin class.
		$plugin = Vespucci_Plugin::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'admin_menu_pages' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );

		// Add meta boxes.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );

		// Add action links pointing to the options pages.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		self::load_components();

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @access    public
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
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Vespucci_Plugin::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$options = get_option( $this->plugin_slug . '_settings' );
		$api_key = isset( $options['map_provider_api_key']['google'] ) ? '&key=' . $options['map_provider_api_key']['google'] : '';

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( 'google-maps-api', '//maps.googleapis.com/maps/api/js?sensor=true' . $api_key );
			wp_enqueue_script( 'gmaps', plugins_url( 'assets/js/vendor/min/gmaps.min.js', __FILE__ ), array( 'jquery', 'google-maps-api' ) );
			wp_enqueue_script( $this->plugin_slug . '-admin-map', plugins_url( 'assets/js/admin-map.js', __FILE__ ), array( 'gmaps' ), Vespucci_Plugin::VERSION, true );
		}

		if ( $this->plugin_screen_hook_suffix == $screen->id || $this->plugin_slug . '-locations' ) {
			wp_enqueue_media();
			wp_enqueue_script( $this->plugin_slug . '-admin-settings', plugins_url( 'assets/js/admin-settings.js', __FILE__ ), array( 'jquery' ), Vespucci_Plugin::VERSION, true );
		}

	}

	/**
	 * Load components.
	 *
	 * @since  1.0.0
	 */
	private static function load_components() {

		require_once 'class-vespucci-admin-fields.php';

	}

	/**
	 * Register WordPress admin dashboard pages for Vespucci plugin.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu_pages() {

		// settings main page on WordPress menu
		$this->plugin_screen_hook_suffix = add_menu_page(
			__( 'Vespucci', $this->plugin_slug ),
			__( 'Vespucci', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug . '-settings',
			array( $this, 'settings_page' ),
			'dashicons-location',
			70
		);

		// general options page
		add_submenu_page(
			$this->plugin_slug . '-settings',
			__( 'Settings', $this->plugin_slug ),
			__( 'Settings', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug . '-settings',
			array( $this, 'settings_page' )
		);

		// options concerning locations entries in db
		add_submenu_page(
			$this->plugin_slug . '-settings',
			__( 'Locations', $this->plugin_slug ),
			__( 'Locations', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug . '-locations',
			array( $this, 'settings_page' )
		);

		// tools page does not update options, but allows to perform actions over db
		add_submenu_page(
			$this->plugin_slug . '-settings',
			__( 'Tools', $this->plugin_slug ),
			__( 'Tools', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug . '-tools',
			array( $this, 'settings_page' )
		);


	}

	/**
	 * Add action links for Vespucci plugin callback.
	 * Adds action links under this plugin entry in WordPress dashboard plugins page.
	 * 
	 * @since 1.0.0
	 * 
	 * @param  array $links links to filter
	 *
	 * @return array filtered links
	 */
	public function add_action_links( $links ) {

		return array_merge( array(
			'settings'  => '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_slug . '-settings' ) . '">'   . __( 'Settings', $this->plugin_slug )  . '</a>',
			'locations' => '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_slug . '-locations' ) . '">'  . __( 'Locations', $this->plugin_slug ) . '</a>',
			'tools'     => '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_slug . '-tools' ) . '">'      . __( 'Tools', $this->plugin_slug )     . '</a>',
		), $links );

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function settings_page() {

		include_once 'views/settings-page.php';

	}

	/**
	 * Add settings options for Vespucci plugin.
	 * Creates setting pages, sections and fields.
	 *
	 * @since 1.0.0
	 */
	public function add_settings() {

		// plugin and maps default settings
		register_setting( $this->plugin_slug . '_settings', $this->plugin_slug . '_settings', array( $this, 'sanitize_fields' ) );
		// wp objects where to collect locations
		register_setting( $this->plugin_slug . '_locations', $this->plugin_slug . '_locations', array( $this, 'sanitize_fields' ) );

		// default map provider and api key 
		add_settings_section(
			$this->plugin_slug . '-provider',
			__( 'Map Provider', $this->plugin_slug ),
			array( $this, 'settings_provider' ),
			$this->plugin_slug . '_settings'
		);

		// map defaults
		add_settings_section(
			$this->plugin_slug . '-defaults',
			__( 'Map Defaults', $this->plugin_slug ),
			array( $this, 'settings_defaults' ),
			$this->plugin_slug . '_settings'
		);

		// default markers
		add_settings_section(
			$this->plugin_slug . '-markers',
			__( 'Map Markers', $this->plugin_slug ),
			array( $this, 'settings_provider' ),
			$this->plugin_slug . '_settings'
		);

		// WordPress objects where to save location data
		add_settings_section(
			$this->plugin_slug . '-wp-objects',
			__( 'Locations', $this->plugin_slug ),
			array( $this, 'settings_wp_objects' ),
			$this->plugin_slug . '_locations'
		);

		// get any saved data to pass to fields as argument 
		$saved_settings = get_option( $this->plugin_slug . '_settings' );
		$saved_locations = get_option( $this->plugin_slug . '_locations' );

		add_settings_field(
			$this->plugin_slug . '_map_provider',
			__( 'Service', $this->plugin_slug ),
			array( 'Vespucci_Admin_Fields', 'select_field' ),
			$this->plugin_slug . '_settings',
			$this->plugin_slug . '-provider',
			array(
				'id' => $this->plugin_slug .'map_provider',
				'name' => $this->plugin_slug .'_settings[map_provider]',
				'choices' => array(
					'google' => __( 'Google Maps', $this->plugin_slug ),
					'mapbox' => __( 'Mapbox', $this->plugin_slug )
				),
				'label' => __( 'Default map provider' , $this->plugin_slug ),
				'value' => isset( $saved_settings['map_provider'] ) ? $saved_settings['map_provider'] : 'google',
				'description' => __( 'Select mapping service to render maps.', $this->plugin_slug )
			)
		);

		add_settings_field(
			$this->plugin_slug . '_map_provider_api_key',
			__( 'API Key', $this->plugin_slug ),
			array( 'Vespucci_Admin_Fields', 'text_field' ),
			$this->plugin_slug . '_settings',
			$this->plugin_slug . '-provider',
			array(
				'id' => $this->plugin_slug .'_map_provider_api_key',
				'name' => $this->plugin_slug .'_settings[map_provider_api_key]',
				'label' => __( 'Map provider API key' , $this->plugin_slug ),
				'value' => isset( $saved_settings['map_provider_api_key'] ) ? $saved_settings['map_provider_api_key'] : '',
				'description' => __( 'Some mapping services may need an API key to work.' , $this->plugin_slug ),
				'class' => 'regular-text'
			)
		);

		add_settings_field(
			$this->plugin_slug . '_map_defaults',
			__( 'Map settings', $this->plugin_slug ),
			array( 'Vespucci_Admin_Fields', 'map_field' ),
			$this->plugin_slug . '_settings',
			$this->plugin_slug . '-defaults',
			array(
				'id' => $this->plugin_slug .'_map_defaults',
				'name' => $this->plugin_slug .'_settings[map_defaults]',
				'label' => __( 'Default coordinates' , $this->plugin_slug ),
				'value' => isset( $saved_settings['map_defaults'] ) && $saved_settings['map_defaults'] ? $saved_settings['map_defaults'] : array(
					'coordinates' => array(
						'lat' => 43.783300,
						'lng' => 11.250000
					),
					'dragging' => true,
					'zoom' => 10,
					'radius' => '50km',
					'address' => array(
						'street' => '',
						'area' => '',
						'city' => __( 'Florence', $this->plugin_slug ),
						'state' => __( 'Tuscany', $this->plugin_slug ),
						'postcode' => '',
						'country' => __( 'Italy', $this->plugin_slug ),
					)
				),
				'description' => __( 'Set the default position of the map and other default map options.' , $this->plugin_slug )
			)
		);

		add_settings_field(
			$this->plugin_slug . '_disable_scripts',
			__( 'Disable scripts', $this->plugin_slug ),
			array( 'Vespucci_Admin_Fields', 'bool_field' ),
			$this->plugin_slug . '_settings',
			$this->plugin_slug . '-defaults',
			array(
				'id' => $this->plugin_slug . '_disable_scripts',
				'name' => $this->plugin_slug . '_settings[disable_scripts]',
				'label' =>  __( 'Load scripts manually', $this->plugin_slug ),
				'value' => isset( $saved_settings['disable_scripts'] ) ? $saved_settings['disable_scripts'] : false,
				'description' => __( "If checked, frontend maps won't work unless scripts are manually replaced and enqueued." , $this->plugin_slug )
			)
		);

		add_settings_field(
			$this->plugin_slug . '_current_marker',
			__( 'Current Marker', $this->plugin_slug ),
			array( 'Vespucci_Admin_Fields', 'marker_field' ),
			$this->plugin_slug . '_settings',
			$this->plugin_slug . '-markers',
			array(
				'id' => $this->plugin_slug . '_current_marker',
				'name' => $this->plugin_slug . '_settings[current_marker]',
				'label' => __( 'Marker for the current location' , $this->plugin_slug ),
				'value' => isset( $saved_settings['current_marker'] ) && $saved_settings['current_marker'] ? $saved_settings['current_marker'] : '',
				'description' => __( 'Marker to be used when the currently queried object is shown on map.' , $this->plugin_slug ),
				'size' => 'marker',
			)
		);

		add_settings_field(
			$this->plugin_slug . '_cluster_marker',
			__( 'Cluster Marker', $this->plugin_slug ),
			array( 'Vespucci_Admin_Fields', 'marker_field' ),
			$this->plugin_slug . '_settings',
			$this->plugin_slug . '-markers',
			array(
				'id' => $this->plugin_slug . '_cluster_marker',
				'name' => $this->plugin_slug . '_settings[cluster_marker]',
				'label' => __( 'Marker for close locations' , $this->plugin_slug ),
				'value' => isset( $saved_settings['cluster_marker'] ) ? $saved_settings['cluster_marker'] : '',
				'description' => __( 'Overrides default map provider marker for clusters of locations.' , $this->plugin_slug ),
				'size' => 'marker',
			)
		);

		add_settings_field(
			$this->plugin_slug . '_group_marker',
			__( 'Group Marker', $this->plugin_slug ),
			array( 'Vespucci_Admin_Fields', 'marker_field' ),
			$this->plugin_slug . '_settings',
			$this->plugin_slug . '-markers',
			array(
				'id' => $this->plugin_slug . '_group_marker',
				'name' => $this->plugin_slug . '_settings[group_marker]',
				'label' => __( 'Marker for grouped locations' , $this->plugin_slug ),
				'value' => isset( $saved_settings['group_marker'] ) ? $saved_settings['group_marker'] : '',
				'description' => __( 'To represent locations that share the same coordinates.' , $this->plugin_slug ),
				'size' => 'marker',
			)
		);

		$post_types = get_post_types( array(), 'objects');
		$choices = array();
		foreach ( $post_types as $key => $value ) :
				$choices[$key] = isset( $value->labels ) ? $value->labels->name : $key;
		endforeach;
		unset( $choices['revision'], $choices['nav_menu_item'] );

		add_settings_field(
			$this->plugin_slug . '_post_types',
			__( 'Post types', $this->plugin_slug ),
			array( 'Vespucci_Admin_Fields', 'locations_field' ),
			$this->plugin_slug . '_locations',
			$this->plugin_slug . '-wp-objects',
			array(
				'id' => $this->plugin_slug . '_locations_post_types',
				'name' => $this->plugin_slug . '_locations[post_types]',
				'class' => $this->plugin_slug . '_locations post-types-locations',
				'label' => __( 'Tick to collect location data for the following post types:', $this->plugin_slug ),
				'value' => isset( $saved_locations['post_types'] ) && $saved_locations['post_types'] ? json_decode( $saved_locations['post_types'] ) : array(),
				'choices' => $choices,
				'description' => __( 'You can also set a marker for each post type.', $this->plugin_slug )
			)
		);

		$taxonomies = get_taxonomies( array(), 'objects');
		$choices = array();
		foreach ( $taxonomies as $key => $value ) :
			$choices[$key] = isset( $value->labels ) ? $value->labels->name : ucfirst( $key );
		endforeach;
		unset( $choices['nav_menu'], $choices['post_format'], $choices['link_category'] );

		add_settings_field(
			$this->plugin_slug . '_terms',
			__( 'Terms', $this->plugin_slug ),
			array( 'Vespucci_Admin_Fields', 'locations_field' ),
			$this->plugin_slug . '_locations',
			$this->plugin_slug . '-wp-objects',
			array(
				'id' => $this->plugin_slug . '_locations_terms',
				'name' => $this->plugin_slug . '_locations[terms]',
				'class' => $this->plugin_slug . '_locations terms-locations',
				'label' => __( 'Tick to collect location data for the following taxonomies:', $this->plugin_slug ),
				'value' => isset( $saved_locations['terms'] ) && $saved_locations['terms'] ? json_decode( $saved_locations['terms'] ) : array(),
				'choices' => $choices,
				'description' => __( 'You can also set a marker for each taxonomy.', $this->plugin_slug )
			)
		);

		$user_roles = get_editable_roles();
		$choices = array();
		foreach ( $user_roles as $role ) :
			$choices[strtolower( $role['name'] )] = $role['name'];
		endforeach;

		add_settings_field(
			$this->plugin_slug . '_users',
			__( 'Users', $this->plugin_slug ),
			array( 'Vespucci_Admin_Fields', 'locations_field' ),
			$this->plugin_slug . '_locations',
			$this->plugin_slug . '-wp-objects',
			array(
				'id' => $this->plugin_slug . '_users',
				'name' => $this->plugin_slug . '_locations[users]',
				'class' => $this->plugin_slug . '-locations users-locations',
				'label' => __( 'Tick to collect location data for the following user types:', $this->plugin_slug ),
				'value' => isset( $saved_locations['users'] ) && $saved_locations['users'] ? json_decode( $saved_locations['users'] ) : array(),
				'choices' => $choices,
				'description' => __( 'You can also set a marker for each user type.', $this->plugin_slug )
			)
		);

	}

	/**
	 * Settings provider section callback
	 *
	 * @since 1.0.0
	 */
	public function settings_provider() {

		echo '<hr />' . "\n";
		echo '<p>' . __( 'Details on the mapping service to be used by this plugin.', $this->plugin_slug ) . '</p>' . "\n";

	}

	/**
	 * Settings defaults section callback
	 *
	 * @since 1.0.0
	 */
	public function settings_defaults() {

		echo '<hr />' . "\n";
		echo '<p>' . __( 'Default settings used as fallback while rendering maps.', $this->plugin_slug ) . '</p>' . "\n";

	}

	/**
	 * Settings markers section callback
	 *
	 * @since 1.0.0
	 */
	public function settings_markers() {

		echo '<hr />' . "\n";
		echo '<p>' . __( 'Default location markers to use as fallback while rendering maps.', $this->plugin_slug ) . '</p>' . "\n";

	}

	/**
	 * Settings WP objects section callback
	 *
	 * @since 1.0.0
	 */
	public function settings_wp_objects() {

		echo '<hr />' . "\n";
		echo '<p>' . __( 'Collect coordinates for selected WordPress objects.', $this->plugin_slug ) . '</p>' . "\n";

	}

	/**
	 * Callback function to sanitize Vespucci setting fields.
	 * Strips any php or html tags, performs specific checks on some types of fields.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $input an input value to sanitize
	 *
	 * @return mixed the sanitized value
	 */
	public function sanitize_fields( $input ) {

		$output = array();

		foreach ( $input as $key => $value ) :

			if ( isset( $input[$key] ) ) {
				$output[$key] = strip_tags( stripslashes( $input[$key] ) );
			}

		endforeach;

		return apply_filters( $this->plugin_slug . '_save_plugin_options', $output, $input );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {

		$post_types = array();
		$terms = array();
		$users = array();
		$comments = array();

		$wp_objects = get_option( $this->plugin_slug . '_locations' );
		if ( ! empty( $wp_objects ) &&  is_array( $wp_objects ) ) :

			foreach ( $wp_objects as $label => $items ) :

				if ( $label == 'post_types' ) :

					$types = (array) json_decode( $items );
					foreach( $types as $name => $marker )
						$post_types[] = $name;

				endif;

			endforeach;

		endif;

		if ( ! empty( $post_types ) && in_array( $post_type, $post_types ) ) :

			add_meta_box(
				'vespucci_location',
				__( 'Location', $this->plugin_slug ),
				array( $this, 'render_meta_box_content' ),
				'page',
				'normal',
				'low',
				'post'
			);

		endif;

	}

	/**
	 * Save the meta box data when the object is saved.
	 *
	 * @since  1.0.0
	 *
	 * @param  int  $post_id  the object id the meta box is attached to
	 *
	 * @return mixed
	 */
	public function save_meta_box( $post_id ) {


		// Check if our nonce is set.
		if ( ! isset( $_POST[$this->plugin_slug . '_location_nonce'] ) )
			return $post_id;

		$nonce = $_POST[$this->plugin_slug . '_location_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, $this->plugin_slug . '_location_nonce' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
		// so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		// Sanitize the user input.
		$data = sanitize_text_field( $_POST[$this->plugin_slug . '_location'] );

		// at this point should update location data etc.
		$args = array(

		);
		vespucci::update_location( $args );

		return $post_id;
	}

	/**
	 * Render the meta box HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param object  $object   the WordPress object where the meta box appears
	 * @param string  $cb_args  callback arguments
	 */
	public function render_meta_box_content( $object, $cb_args ) {

		wp_nonce_field( $this->plugin_slug . '_location', $this->plugin_slug . '_location_nonce' );

		$saved = Vespucci::get_location( $object->ID, $cb_args['args'] );
		$default = get_option( $this->plugin_slug . '_settings' );
		$value = $saved ? $saved : $default['map_defaults'];

		$args = array(
			'id' => 'vespucci-meta-box',
			'name' => 'vespucci_meta_box',
			'class' => '',
			'label' => '',
			'description' => '',
			'value' => $value,
		);
		Vespucci_Admin_Fields::map_field( $args );

	}


}