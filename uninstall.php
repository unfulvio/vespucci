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

// vars
global $wpdb;
$plugin = Vespucci_Plugin::get_instance();
$plugin_slug = $plugin->get_plugin_slug();

if ( is_multisite() ) {

	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );

	if ( $blogs ) :

	 	foreach ( $blogs as $blog ) :

			switch_to_blog( $blog['blog_id'] );

		    $tables = array( 'locations', 'location_relationships', 'locationsmeta' );
		    foreach( $tables as $table ) :

			    $name = $wpdb->prefix . $table;
			    $drop_table = "DROP TABLE IF EXISTS {$name}";
			    $optimize_table = "OPTIMIZE TABLE {$name}";

			    $wpdb->query( $drop_table );
			    $wpdb->query( $optimize_table );

		    endforeach;

		    delete_option( $plugin_slug . '_version' );
		    delete_option( $plugin_slug . '_settings' );
		    delete_option( $plugin_slug . '_locations' );

		    restore_current_blog();

		endforeach;

	endif;

} else {

	$tables = array( 'locations', 'location_relationships', 'locationsmeta' );
	foreach( $tables as $table ) :

		$name = $wpdb->prefix . $table;
		$drop_table = "DROP TABLE IF EXISTS {$name}";
		$optimize_table = "OPTIMIZE TABLE {$name}";

		$wpdb->query( $drop_table );
		$wpdb->query( $optimize_table );

	endforeach;

	delete_option( $plugin_slug . '_version' );
	delete_option( $plugin_slug . '_settings' );
	delete_option( $plugin_slug . '_locations' );

}