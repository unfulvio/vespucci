<?php
/**
 * Vespucci: Geo Data for WordPress
 *
 * @package   Vespucci
 * @author    nekojira <fulvio@nekojira.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 nekojira
 *
 * @wordpress-plugin
 * Plugin Name:       Vespucci
 * Plugin URI:        https://github.com/nekojira/vespucci
 * Description:       Bring geo spatial coordinates data storage and retrieval into WordPress and BuddyPress.
 * Version:           1.0.0
 * Author:            nekojira
 * Author URI:        https://gitub.con/nekojira/
 * Text Domain:       vespucci
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/nekojira/vespucci
 */

// abort if called directly
if ( ! defined( 'WPINC' ) )
	die;

require_once plugin_dir_path( __FILE__ ) . 'lib/class-vespucci-plugin.php';
register_activation_hook( __FILE__, array( 'Vespucci_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Vespucci_Plugin', 'deactivate' ) );
add_action( 'plugins_loaded', array( 'Vespucci_Plugin', 'get_instance' ) );

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'lib/class-vespucci-admin.php';
	add_action( 'plugins_loaded', array( 'Vespucci_Admin', 'get_instance' ) );
}