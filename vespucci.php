<?php
/**
 * Vespucci: a geo spatial framework for WordPress
 *
 * @package   Vespucci
 * @license   GPL-2.0+
 * @link      http://github.com/nekojira/vespucci
 *
 * @wordpress-plugin
 * Plugin Name:       Vespucci
 * Plugin URI:        https://github.com/nekojira/vespucci
 * Description:       A geo spatial framework for WordPress.
 * Version:           0.1.0
 * Text Domain:       vespucci
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/nekojira/vespucci
 */

// abort if called directly
if ( ! defined( 'WPINC' ) )
	die;

// activation
require_once plugin_dir_path( __FILE__ ) . 'lib/class-vespucci-plugin.php';
register_activation_hook( __FILE__, array( 'Vespucci_Plugin', 'activate' ) );

// deactivation
require_once plugin_dir_path( __FILE__ ) . 'lib/class-vespucci-plugin.php';
register_activation_hook( __FILE__, array( 'Vespucci_Plugin', 'deactivate' ) );

// plugin core
require_once plugin_dir_path( __FILE__ ) . 'lib/class-vespucci-core.php';

// instantiate
$vespucci = new Vespucci_Core();
$vespucci->run();
