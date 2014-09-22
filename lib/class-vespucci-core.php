<?php
/**
 * Vespucci Core
 *
 * @package   Vespucci
 * @license   GPL-2.0+
 * @link      https://github.com/nekojira/vespucci
 */

/**
 * Vespucci Core class
 * Contains plugin activation and deactivation actions
 *
 * @package Vespucci
 * @since   0.1.0
 */
class Vespucci_Core {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      Vespucci_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $Vespucci    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Instance of this class.
	 *
	 * @since   0.1.0
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
	 * @since    0.1.0
	 */
	public function __construct() {

		$this->plugin_name = 'vespucci';
		$this->version = '0.1.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies.
	 * Also creates an instance of the loader which will be used to register the hooks with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function load_dependencies() {

		// public functions
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/class-vespucci.php';

		// actions and filters
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/class-vespucci-loader.php';

		// internationalization
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/class-vespucci-i18n.php';

		// dashboard features
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/class-vespucci-admin.php';

		$this->loader = new Vespucci_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Vespucci_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.1.0
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
	 * @since    0.1.0
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

		// location meta box actions
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_location_box' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_post_location' );
		$this->loader->add_action( 'wp_trash_post', $plugin_admin, 'trash_post_location' );
		$this->loader->add_action( 'delete_post', $plugin_admin, 'delete_post_location' );

		// action links pointing to the options pages.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->get_plugin_name() . '.php' );
		$this->loader->add_filter( 'public_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
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
	 * @since    0.1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.1.0
	 * @return    Vespucci_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}