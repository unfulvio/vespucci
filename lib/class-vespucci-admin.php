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
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string  $plugin_screen_hook_suffix
	 */
	private $plugin_screen_hook_suffix;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
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
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) )
			return;

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id
		     OR $this->plugin_screen_hook_suffix == 'toplevel_page_' . $this->name . '-settings' ) {

				wp_enqueue_style( $this->name . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), $this->version );
				wp_enqueue_style( $this->name . '-tiptip', plugins_url( 'assets/css/vendor/tiptip.css', __FILE__ ), array(), $this->version );

		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) )
			return;

		$options = get_option( $this->name . '_settings' );
		$api_key = isset( $options['map_provider_api_key']['google'] ) ? '&key=' . $options['map_provider_api_key']['google'] : '';

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id
			OR $this->plugin_screen_hook_suffix == 'toplevel_page_' . $this->name . '-settings' ) {

				wp_enqueue_media();
				wp_enqueue_script( $this->name . '-tiptip', plugins_url( 'assets/js/vendor/min/jquery.tipTip.min.js', __FILE__ ), array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'google-maps-api', '//maps.googleapis.com/maps/api/js?sensor=true' . $api_key );
				wp_enqueue_script( 'gmaps', plugins_url( 'assets/js/vendor/min/gmaps.min.js', __FILE__ ), array( 'jquery', 'google-maps-api' ) );
				wp_enqueue_script( $this->name . '-admin-map', plugins_url( 'assets/js/admin-map.js', __FILE__ ), array( 'gmaps', 'jquery-ui-tabs' ), $this->version, true );
				wp_enqueue_script( $this->name . '-admin-settings', plugins_url( 'assets/js/admin-settings.js', __FILE__ ), array( 'jquery', $this->name . '-tiptip' ), $this->version, true );

		}

	}

	/**
	 * Load components.
	 *
	 * @since  1.0.0
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/class-vespucci-admin-fields.php';
	}

	/**
	 * Register WordPress admin dashboard pages for Vespucci plugin.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu_pages() {

		// settings main page on WordPress menu
		$this->plugin_screen_hook_suffix = add_menu_page(
			__( 'Vespucci', $this->name ),
			__( 'Vespucci', $this->name ),
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
			__( 'Locations', $this->name ),
			__( 'Locations', $this->name ),
			'manage_options',
			$this->name . '-locations',
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
	 * @since 1.0.0
	 * 
	 * @param  array $links links to filter
	 *
	 * @return array filtered links
	 */
	public function add_action_links( $links ) {

		return array_merge( array(
			'settings'  => '<a href="' . admin_url( 'admin.php?page=' . $this->name . '-settings' ) . '">'   . __( 'Settings', $this->name )  . '</a>',
			'locations' => '<a href="' . admin_url( 'admin.php?page=' . $this->name . '-locations' ) . '">'  . __( 'Locations', $this->name ) . '</a>',
			'tools'     => '<a href="' . admin_url( 'admin.php?page=' . $this->name . '-tools' ) . '">'      . __( 'Tools', $this->name )     . '</a>',
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
		register_setting( $this->name . '_settings', $this->name . '_settings', array( $this, 'sanitize_fields' ) );
		// wp objects where to collect locations
		register_setting( $this->name . '_locations', $this->name . '_locations', array( $this, 'sanitize_fields' ) );

		$settings = array(
			array(
				'id'        => 'provider',
				'title'     => __( 'Map Provider', $this->name ),
				'callback'  => array( $this, 'settings_provider' ),
				'page'      => 'settings'
			),
			array(
				'id'        => 'defaults',
				'title'     => __( 'Map Defaults', $this->name ),
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
				'title'     => __( 'Locations', $this->name ),
				'callback'  => array( $this, 'settings_wp_objects' ),
				'page'      => 'locations'
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

		// get any saved data to pass to fields as argument
		$default_settings = Vespucci_Core::default_options( 'settings' );
		$saved_settings = get_option( $this->name . '_settings' );
		$default_locations = Vespucci_Core::default_options( 'objects' );
		$saved_locations = get_option( $this->name . '_locations' );

		$map_providers = Vespucci_Core::map_providers();
		if ( is_array( $map_providers ) ) :

			$providers = array();
			$providers_api = array();
			foreach( $map_providers as $map_provider => $value ) :

				$providers[$map_provider] = $value['label'];
				$providers_api[$map_provider] = $value['api_key'];

			endforeach;

			add_settings_field(
				$this->name . '_map_providers',
				__( 'Service', $this->name ),
				array( 'Vespucci_Admin_Fields', 'select_field' ),
				$this->name . '_settings',
				$this->name . '-provider',
				array(
					'id' => $this->name .'map_provider',
					'name' => $this->name .'_settings[map_provider]',
					'choices' => $providers,
					'label' => __( 'Default map provider' , $this->name ),
					'value' => isset( $saved_settings['map_provider'] ) ? $saved_settings['map_provider'] : $default_settings['map_provider'],
					'description' => __( 'Select mapping service to render maps.', $this->name ),
					'allow_null' => false
				)
			);

			foreach( $providers_api as $key => $value ) :

				add_settings_field(
					$this->name . '_map_provider_api_key_' . $key,
					__( 'API Key', $this->name ),
					array( 'Vespucci_Admin_Fields', 'text_field' ),
					$this->name . '_settings',
					$this->name . '-provider',
					array(
						'id' => $this->name .'_map_provider_api_key_' . $key,
						'name' => $this->name .'_settings[map_provider_api_key]['. $key .']',
						'label' => __( 'Map provider API key' , $this->name ),
						'value' => isset( $saved_settings['map_provider_api_key'][$key] ) ? $saved_settings['map_provider_api_key'][$key] : $default_settings['map_provider_api_key'][$key],
						'description' => __( 'Some mapping services may need an API key to work.' , $this->name ),
						'class' => 'regular-text'
					)
				);

			endforeach;

		endif;

		add_settings_field(
			$this->name . '_map_defaults',
			__( 'Map settings', $this->name ),
			array( 'Vespucci_Admin_Fields', 'location_box' ),
			$this->name . '_settings',
			$this->name . '-defaults',
			array(
				'id' => $this->name .'_map_defaults',
				'name' => $this->name .'_settings[box_defaults]',
				'label' => __( 'Default coordinates' , $this->name ),
				'value' => isset( $saved_settings['box_defaults'] ) && $saved_settings['box_defaults'] ? $saved_settings['box_defaults'] : $default_settings['box_defaults']
			)
		);

		add_settings_field(
			$this->name . '_disable_scripts',
			__( 'Disable scripts', $this->name ),
			array( 'Vespucci_Admin_Fields', 'bool_field' ),
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

		$sizes = get_intermediate_image_sizes();
		$choices = array();
		foreach( $sizes as $size ) :
			$choices[$size] = ucfirst( $size );
		endforeach;

		add_settings_field(
			$this->name . '_marker_size',
			__( 'Marker size', $this->name ),
			array( 'Vespucci_Admin_Fields', 'select_field' ),
			$this->name . '_settings',
			$this->name . '-markers',
			array(
				'id' => $this->name . '_current_marker',
				'name' => $this->name . '_settings[current_marker]',
				'label' => __( 'Default marker size' , $this->name ),
				'value' => isset( $saved_settings['marker_size'] ) && $saved_settings['marker_size'] ? $saved_settings['marker_size'] : '',
				'choices' => $choices,
				'description' => __( 'Default image size to use when using custom markers from the media library.' , $this->name ),
				'allow_null' => true
			)
		);

		add_settings_field(
			$this->name . '_current_marker',
			__( 'Current Marker', $this->name ),
			array( 'Vespucci_Admin_Fields', 'marker_field' ),
			$this->name . '_settings',
			$this->name . '-markers',
			array(
				'id' => $this->name . '_current_marker',
				'name' => $this->name . '_settings[current_marker]',
				'label' => __( 'Marker for the current location' , $this->name ),
				'value' => isset( $saved_settings['current_marker'] ) && $saved_settings['current_marker'] ? $saved_settings['current_marker'] : '',
				'description' => __( 'Marker to be used when the currently queried object is shown on map.' , $this->name ),
			)
		);

		add_settings_field(
			$this->name . '_cluster_marker',
			__( 'Cluster Marker', $this->name ),
			array( 'Vespucci_Admin_Fields', 'marker_field' ),
			$this->name . '_settings',
			$this->name . '-markers',
			array(
				'id' => $this->name . '_cluster_marker',
				'name' => $this->name . '_settings[cluster_marker]',
				'label' => __( 'Marker for close locations' , $this->name ),
				'value' => isset( $saved_settings['cluster_marker'] ) ? $saved_settings['cluster_marker'] : '',
				'description' => __( 'Overrides default map provider marker for clusters of locations.' , $this->name ),
			)
		);

		add_settings_field(
			$this->name . '_group_marker',
			__( 'Group Marker', $this->name ),
			array( 'Vespucci_Admin_Fields', 'marker_field' ),
			$this->name . '_settings',
			$this->name . '-markers',
			array(
				'id' => $this->name . '_group_marker',
				'name' => $this->name . '_settings[group_marker]',
				'label' => __( 'Marker for grouped locations' , $this->name ),
				'value' => isset( $saved_settings['group_marker'] ) ? $saved_settings['group_marker'] : '',
				'description' => __( 'To represent locations that share the same coordinates.' , $this->name ),
			)
		);

		$post_types = get_post_types( array(), 'objects');
		$choices = array();
		foreach ( $post_types as $key => $value ) :
				$choices[$key] = isset( $value->labels ) ? $value->labels->name : $key;
		endforeach;
		unset( $choices['revision'], $choices['nav_menu_item'] );

		add_settings_field(
			$this->name . '_post_types',
			__( 'Post types', $this->name ),
			array( 'Vespucci_Admin_Fields', 'wp_objects_field' ),
			$this->name . '_locations',
			$this->name . '-wp-objects',
			array(
				'id' => $this->name . '_locations_post_types',
				'name' => $this->name . '_locations[post_types]',
				'class' => $this->name . '_locations post-types-locations',
				'label' => __( 'Tick to collect location data for the following post types:', $this->name ),
				'value' => isset( $saved_locations['post_types'] ) && $saved_locations['post_types'] ? json_decode( $saved_locations['post_types'] ) : $default_locations['post_types'],
				'choices' => $choices,
			)
		);

		$taxonomies = get_taxonomies( array(), 'objects');
		$choices = array();
		foreach ( $taxonomies as $key => $value ) :
			$choices[$key] = isset( $value->labels ) ? $value->labels->name : ucfirst( $key );
		endforeach;
		unset( $choices['nav_menu'], $choices['post_format'], $choices['link_category'] );

		add_settings_field(
			$this->name . '_terms',
			__( 'Terms', $this->name ),
			array( 'Vespucci_Admin_Fields', 'wp_objects_field' ),
			$this->name . '_locations',
			$this->name . '-wp-objects',
			array(
				'id' => $this->name . '_locations_terms',
				'name' => $this->name . '_locations[terms]',
				'class' => $this->name . '_locations terms-locations',
				'label' => __( 'Tick to collect location data for the following taxonomies:', $this->name ),
				'value' => isset( $saved_locations['terms'] ) && $saved_locations['terms'] ? json_decode( $saved_locations['terms'] ) : $default_locations['terms'],
				'choices' => $choices,
			)
		);

		$user_roles = get_editable_roles();
		$choices = array();
		foreach ( $user_roles as $role ) :
			$choices[strtolower( $role['name'] )] = $role['name'];
		endforeach;

		add_settings_field(
			$this->name . '_users',
			__( 'Users', $this->name ),
			array( 'Vespucci_Admin_Fields', 'wp_objects_field' ),
			$this->name . '_locations',
			$this->name . '-wp-objects',
			array(
				'id' => $this->name . '_users',
				'name' => $this->name . '_locations[users]',
				'class' => $this->name . '-locations users-locations',
				'label' => __( 'Tick to collect location data for the following user types:', $this->name ),
				'value' => isset( $saved_locations['users'] ) && $saved_locations['users'] ? json_decode( $saved_locations['users'] ) : $default_locations['users'],
				'choices' => $choices,
			)
		);

		add_settings_field(
			$this->name . '_comments',
			__( 'Comments', $this->name ),
			array( 'Vespucci_Admin_Fields', 'wp_objects_field' ),
			$this->name . '_locations',
			$this->name . '-wp-objects',
			array(
				'id' => $this->name . '_comments',
				'name' => $this->name . '_locations[comments]',
				'class' => $this->name . '-locations comments-locations',
				'label' => __( 'Tick to collect location data for comments:', $this->name ),
				'value' => isset( $saved_locations['comments'] ) && $saved_locations['comments'] ? json_decode( $saved_locations['comments'] ) : $default_locations['comments'],
				'choices' => array( $comments['comments'] = __( 'Comments', $this->name ) ),
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
		echo '<p>' . __( 'Details on the mapping service to be used by this plugin.', $this->name ) . '</p>' . "\n";

	}

	/**
	 * Settings defaults section callback
	 *
	 * @since 1.0.0
	 */
	public function settings_defaults() {

		echo '<hr />' . "\n";
		echo '<p>' . __( 'Default settings used as fallback while rendering maps.', $this->name ) . '</p>' . "\n";

	}

	/**
	 * Settings markers section callback
	 *
	 * @since 1.0.0
	 */
	public function settings_markers() {

		echo '<hr />' . "\n";
		echo '<p>' . __( 'Default location markers to use as fallback while rendering maps.', $this->name ) . '</p>' . "\n";

	}

	/**
	 * Settings WP objects section callback
	 *
	 * @since 1.0.0
	 */
	public function settings_wp_objects() {

		echo '<hr />' . "\n";
		echo '<p>' . __( 'Collect coordinates for selected WordPress objects.', $this->name ) . '</p>' . "\n";

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

		return apply_filters( $this->name . '_save_plugin_options', $output, $input );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_location_box( $post_type ) {

		$post_types = array();
		$terms = array();
		$users = array();
		$comments = array();

		$wp_objects = get_option( $this->name . '_locations' );
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
				__( 'Location', $this->name ),
				array( $this, 'render_meta_box_content' ),
				'page',
				'normal',
				'low',
				array( 'type' => 'post' )
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
	public function save_location_box( $post_id ) {

		// Check if our nonce is set.
		if ( ! isset( $_POST[$this->name . '_location_nonce'] ) )
			return $post_id;

		$nonce = $_POST[$this->name . '_location_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, $this->name . '_location_nonce' ) )
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
		$data = sanitize_text_field( $_POST[$this->name . '_location'] );

		// at this point should update location data etc.
		$args = array(

		);
		Vespucci::update_location( $args );

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
	public function render_location_box_content( $object, $cb_args ) {

		wp_nonce_field( $this->name . '_location', $this->name . '_location_nonce' );

		$type = isset( $cb_args['args']['type'] ) ? $cb_args['args']['type'] : '';
		$saved = Vespucci::get_location( $object->ID, $type );
		$default = get_option( $this->name . '_settings' );
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