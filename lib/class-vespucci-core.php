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
class Vespucci_Core {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Instance of this class.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     object
	 */
	protected static $instance = null;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'vespucci';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
	 * - Plugin_Name_i18n. Defines internationalization functionality.
	 * - Plugin_Name_Admin. Defines all hooks for the dashboard.
	 * - Plugin_Name_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		// actions and filters
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/class-vespucci-loader.php';

		// internationalization
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/class-vespucci-i18n.php';

		// dashboard functions
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/class-vespucci-admin.php';

		// public functions
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/class-vespucci.php';

		$this->loader = new Vespucci_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Vespucci_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Vespucci_Admin( $this->get_plugin_name(), $this->get_version() );

		// scripts and styles
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// option pages and menus
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu_pages' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'add_settings' );

		// location meta box
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_location_box' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_location_box' );

		// action links pointing to the options pages.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->get_plugin_name() . '.php' );
		$this->loader->add_filter( 'public_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Vespucci( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
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
		if ( self::$instance == null )
			self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Return a default option value.
	 * Returns one of the plugin default options, according to the argument passed.
	 *
	 * @since   1.0.0
	 * @param   string  $option the option name
	 * @return  array|string    the option defaults or empty string if error or invalid option
	 */
	public static function default_options( $option ) {

		$plugin = self::get_instance();
		$plugin_name = $plugin->get_plugin_name();

		$defaults = '';
		if ( $option == 'settings' ) {

			$defaults = array(
				'map_provider' => 'google',
				'map_provider_api_key' => array(
					'google' => '',
				),
				'box_defaults' => array(
					'coordinates' => array(
						'lat' => 0.000000,
						'lng' => 0.000000
					),
					'settings' => array(
						'dragging' => true,
						'map_type' => array(
							'google' => '',
						),
						'zoom' => array(
							'default' => 10,
							'min' => 0,
							'max' => 21,
						),
						'radius' => '50km',
						'limit' => false,
						'marker' => '',
					),
					'address' => array(
						'street' => '',
						'area' => '',
						'city' => '',
						'state' => '',
						'postcode' => '',
						'country' => '',
					),
				),
				'disable_scripts' => false,
				'current_marker' => '',
				'cluster_marker' => '',
				'group_marker' => '',
			);

		} elseif ( $option == 'objects' ) {

			$defaults = array(
				'post_types' => array(),
				'terms' => array(),
				'users' => array(),
				'comments' => array()
			);

		}

		return apply_filters( $plugin_name . '_default_options', $defaults, $option );
	}

	/**
	 * Get the available map providers.
	 * Returns an array with registered map providers.
	 *
	 * @since   1.0.0
	 * @return  array   map providers
	 */
	public static function map_providers() {

		$plugin = self::get_instance();
		$plugin_name = $plugin->get_plugin_name();

		$providers = array();
	    $providers['google'] = array(
		    'name'      => 'google-maps',
		    'label'     => __( 'Google Maps', $plugin_name ),
		    'api_key'   => '',
		    'map_types' => array(
			    'traffic'   => __( 'Traffic', $plugin_name ),
			    'terrain'   => __( 'Terrain', $plugin_name ),
			    'satellite' => __( 'Satellite', $plugin_name ),
		    ),
		    'options'   => array(),
	    );

	    return apply_filters( $plugin_name . '_map_providers', $providers );
	}

}