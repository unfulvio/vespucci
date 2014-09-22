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

$plugin_slug = Vespucci::get_plugin_name();
?>
<div id="<?php echo $plugin_slug; ?>" class="wrap">

	<h2><?php printf( _x( 'Vespucci %s ', 'Settings page', $plugin_slug ), esc_html( get_admin_page_title() ) ); ?></h2>

	<?php settings_errors(); ?>

	<br />

	<h2 class="nav-tab-wrapper">
		<a href="?page=<?php echo $plugin_slug . '-settings'; ?>" class="nav-tab"><?php _e( 'Settings', $plugin_slug ); ?></a>
		<a href="?page=<?php echo $plugin_slug . '-objects'; ?>" class="nav-tab"><?php _e( 'Objects', $plugin_slug ); ?></a>
		<a href="?page=<?php echo $plugin_slug . '-tools'; ?>" class="nav-tab"><?php _e( 'Tools', $plugin_slug ); ?></a>
	</h2>

	<br />

	<?php

	$active_tab = isset( $_GET['page'] ) ? $_GET['page'] : $plugin_slug . '-settings';

	if ( $active_tab == $plugin_slug . '-settings' ) {

		?>
		<h3><mark>Under development.</mark></h3>
		<p><mark>Most of these settings will be saved, but there's currently no frontend functionality to make use for some of them.</mark></p>
		<p>This framework wants to be extensible and ideally developers should be made able to extend these settings if they want to.</p>
		<?php

		echo '<form id="' . $plugin_slug . '_settings_form" class="' . $plugin_slug . '-form" method="post" action="options.php" onSubmit="return false">';

		settings_fields( $plugin_slug . '_settings' );
		do_settings_sections( $plugin_slug . '_settings' );

		echo '<br /><hr />';
		submit_button( __( 'Save Changes', $plugin_slug), 'primary', $plugin_slug . '-submit', true, array( 'onClick' => 'this.form.submit()' ) );

		echo '</form>';

	} elseif ( $active_tab == $plugin_slug . '-objects' ) {

		?>
		<h3><mark>Under development.</mark></h3>
		<p><mark>Currently only posts, post types and pages should work.</mark></p>
		<p>Other WordPress objects are selectable, but you won't be able to save a location for them at the moment.</p>
		<?php
		echo '<form id="' . $plugin_slug . '_wp_objects_form" class="' . $plugin_slug . '-form" method="post" action="options.php"">';

		settings_fields( $plugin_slug . '_objects' );
		do_settings_sections( $plugin_slug . '_objects' );

		echo '<br /><hr />';
		submit_button( __( 'Save Changes', $plugin_slug), 'primary', $plugin_slug . '-submit', true );

		echo '</form>';

	} elseif( $active_tab == $plugin_slug . '-tools' ) {


		?>
		<h3><mark>Under development.</mark></h3>
		<p>In future this page should include some tools to perform database operations or mass handling of location entries in database.</p>
		<p>Like other parts of this framework, tools functionality should be extensible and allow third parties to write tools for it.</p>
		<p>Examples of possible future tools:</p>
		<ol>
			<li>Import and export utilities.</li>
			<li>Convert and import from other plugins.</li>
			<li>Map all locations to post metadata.</li>
			<li>Create locations from post metadata.</li>
			<li>Generate KML files or other export files.</li>
			<li>...</li>
		</ol>
		<?php

		do_settings_sections( $plugin_slug . '-tools' );
	}

	?>
</div>