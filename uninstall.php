<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Vespucci
 * @author    nekojira <fulvio@nekojira.com>
 * @license   GPL-2.0+
 * @link      https://github.com/nekojira/vespucci
 * @copyright 2014 nekojira
 */

// if uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit;

$plugin = Vespucci_Core::get_instance();
$plugin_name = $plugin->get_plugin_name();

global $wpdb;

// delete tables
$tables = array( 'locations', 'location_relationships', 'locationsmeta' );
foreach( $tables as $table ) :

	$name = $wpdb->prefix . $table;
	$drop_table = "DROP TABLE IF EXISTS {$name}";
	$optimize_table = "OPTIMIZE TABLE {$name}";

	$wpdb->query( $drop_table );
	$wpdb->query( $optimize_table );

endforeach;

// delete options
delete_option( $plugin_name . '_version' );
delete_option( $plugin_name . '_settings' );
delete_option( $plugin_name . '_locations' );