<?php
/**
 * Template for Vespucci administration dashboard settings panel.
 *
 * @package   Vespucci
 * @author    nekojira <fulvio@nekojira.com>
 * @license   GPL-2.0+
 * @link      https://github.com/nekojira/vespucci
 * @copyright 2014 nekojira
 */

// get plugin textdomain from slug
$plugin = Vespucci::get_instance();
$plugin_slug = $plugin->get_plugin_slug();

?>
<div id="<?php echo $plugin_slug; ?>" class="wrap">

	<h2><?php printf( _x( 'Vespucci %s ', 'Settings page', $plugin_slug ), esc_html( get_admin_page_title() ) ); ?></h2>

	<?php settings_errors(); ?>

	<br />

	<h2 class="nav-tab-wrapper">
		<a href="?page=<?php echo $plugin_slug . '-settings'; ?>" class="nav-tab"><?php _e( 'Settings', $plugin_slug ); ?></a>
		<a href="?page=<?php echo $plugin_slug . '-locations'; ?>" class="nav-tab"><?php _e( 'Locations', $plugin_slug ); ?></a>
		<a href="?page=<?php echo $plugin_slug . '-tools'; ?>" class="nav-tab"><?php _e( 'Tools', $plugin_slug ); ?></a>
	</h2>

	<br />

	<?php

	$active_tab = isset( $_GET['page'] ) ? $_GET['page'] : $plugin_slug . '-settings';

	if ( $active_tab == $plugin_slug . '-settings' ) {

		echo '<form method="post" action="options.php">';

		settings_fields( $plugin_slug . '_settings' );
		do_settings_sections( $plugin_slug . '_settings' );

		echo '<br /><hr />';
		submit_button();

		echo '</form>';

	} elseif ( $active_tab == $plugin_slug . '-locations' ) {

		echo '<form method="post" action="options.php">';

		settings_fields( $plugin_slug . '_locations' );
		do_settings_sections( $plugin_slug . '_locations' );

		echo '<br /><hr />';
		submit_button();

		echo '</form>';

	} elseif( $active_tab == $plugin_slug . '-tools' ) {

		do_settings_sections( $plugin_slug . '-tools' );

	}

	?>
</div>