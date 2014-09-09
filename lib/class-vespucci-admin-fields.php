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
 * Vespucci Admin Fields static class.
 * Contains reusable fields used in the administration dashboard.
 *
 * @since   1.0.0
 * @package Vespucci
 */
class Vespucci_Admin_Fields extends Vespucci_Admin {

	/**
	 * Get the plugin name.
	 * Returns the plugin name string as defined in Vespucci Core class.
	 *
	 * @since   1.0.0
	 * @return  string  the plugin name
	 */
	private static function get_plugin_name() {

		$plugin = Vespucci_Core::get_instance();
		$plugin_name = $plugin->get_plugin_name();

		return $plugin_name;
	}

	/**
	 * Select field.
	 * Echoes a select field according to arguments passed.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args arguments to populate the field:
	 *                    'id' a unique identifier for this field
	 *                    'name' a unique name for this field
	 *                    'class' (optional) CSS classes for this field
	 *                    'label' this field label text
	 *                    'description' (optional) additional text description
	 *                    'choices' an array with key=>value choices
	 *                    'value' the default selected value
	 */
	public static function select_field( $args ) {

		$defaults = array(
			'id' => '',
			'name' => '',
			'class' => '',
			'label' => '',
			'description' => '',
			'choices' => '',
			'value' => '',
			'allow_null' => true
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( ! $id || ! $name || ! is_array( $choices ) ) {
			trigger_error( 'Missing or invalid required argument for select field.', E_USER_WARNING );
			return;
		}

		$html = $label ? '<label for="' . $id . '"> '  . $label . '</label>' . "\n" : '';

		$options = '<select id="' . $id . '" name="' . $name . '">' . "\n";
		$options .= $allow_null == true ? "\t" . '<option value=""></option>' . "\n" : '';
		foreach( $choices as $key => $option ) :
			$options  .=  "\t" . '<option value="' . $key . '" ' . selected( $key, $value, false ) . '>' . $option .'</option>' . "\n";
		endforeach;
		$options .= '</select>' . "\n";

		$html .= $options;
		$html .= $description ? '<span class="dashicons dashicons-editor-help tiptip" title="' . $description . '"></span>' . "\n" : '';

		echo $html;
	}

	/**
	 * Bool field.
	 * Echoes a single checkbox field input according to arguments passed.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args arguments to populate the field:
	 *                    'id' a unique identifier for this field
	 *                    'name' a unique name for this field
	 *                    'class' (optional) CSS classes for this field
	 *                    'label' the field label text
	 *                    'description' (optional) additional text description
	 *                    'value' true || 1 or false || 0 for default value, default false (unchecked)
	 */
	public static function bool_field( $args ) {

		$defaults = array(
			'id' => '',
			'name' => '',
			'class' => '',
			'label' => '',
			'description' => '',
			'value' => false,
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( ! $id || ! $name || ! $label ) {
			trigger_error( 'Missing or invalid required argument for bool field.', E_USER_WARNING );
			return;
		}

		$value = $value == true || $value == 1 ? 1 : 0;

		$html = '<label for="' . $id . '">'  . $label . '</label> ' . "\n";
		$html .= '<input type="checkbox" class="' . $class . '" id="' . $id . '" name="' . $name . '" value="1" ' . checked( 1, $value, false ) . '/>' . "\n";
		$html .= $description ? '<span class="dashicons dashicons-editor-help tiptip" title="' . $description . '"></span>' . "\n" : '';

		echo $html;
	}

	/**
	 * WordPress Objects field.
	 * Echoes a group of checkboxes field inputs according to arguments passed.
	 * Will populate an hidden field with checked choices via javascript.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args arguments to populate the field
	 *                    'id' a unique identifier for this field
	 *                    'name' a unique name for this field
	 *                    'class' (optional) CSS classes for this field
	 *                    'label' the field label text
	 *                    'description' (optional) additional text description
	 *                    'value' (optional) default value to populate the hidden field (and the checkboxes via javascript)
	 */
	public static function wp_objects_field( $args ) {

		$defaults = array(
			'id' => '',
			'name' => '',
			'class' => '',
			'label' => '',
			'description' => '',
			'value' => array(),
			'choices' => '',
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( ! $id || ! $name || ! is_array( $choices ) ) {
			trigger_error( 'Missing or invalid required argument for checkboxes field.', E_USER_WARNING );
			return;
		}

		$plugin_name = self::get_plugin_name();
		$saved = (array) $value;

		echo $label ? '<p>' . $label . '</p>' . "\n\n" : '';

		echo '<table class="' . $class . ' ' . $plugin_name . '-wp-objects-field">' . "\n";

		foreach( $choices as $option_key => $option_name ) :

			echo '<tr>';

				$checkbox = "\t" . '<td class="wp-object-item">' . "\n";
				// saved values (values in object are markers)
				$selected = isset( $saved[$option_key] ) ? $option_key : '';
				// individual choice among the current wp object group
				$checkbox .= "\t\t" . '<label for="location_' . $option_key . '">' . "\n";
				$checkbox .= "\t\t\t" . '<input type="checkbox" class="checkbox" id="location_' . $option_key . '" name="' . $option_key . '[location]" value="' . $option_key . '" ' . checked( $selected, $option_key, false ). ' />' . "\n";
				$checkbox .= "\t\t\t" . $option_name . "\n";
				$checkbox .= "\t\t" . '</label>' . "\n";
				$checkbox .= "\t" . '</td>' . "\n";

				echo $checkbox;

				echo '<td>';
					$args = array(
						'id' => $option_key . '_marker',
						'name' => $option_key . '[marker]',
						'label' => sprintf( __( 'Marker for %s', $plugin_name ), $option_name ),
						'value' => isset( $saved[$option_key] ) ? $saved[$option_key] : '',
						'size' => 'marker'
					);
					// adds a marker field for each available choice
					self::marker_field( $args );
				echo '</td>';

			echo '</tr>';

		endforeach;

		echo '</table>';

		echo '<input type="hidden" class="selected-locations" id="' . $id . '" name="' . $name . '" value="' . json_encode( $value ) . '" />' . "\n";
		echo $description ? '<span class="dashicons dashicons-editor-help tiptip" title="' . $description . '"></span>' . "\n" : '';

	}

	/**
	 * Text field.
	 * Echoes a text input according to arguments passed.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args arguments to populate the field
	 *                    'id' a unique identifier for this field
	 *                    'name' a unique name for this field
	 *                    'class' (optional) CSS classes for this field, default 'regular-text'
	 *                    'label' this field label text
	 *                    'description' (optional) additional text description
	 *                    'value' default value
	 */
	public static function text_field( $args ) {

		$defaults = array(
			'id' => '',
			'name' => '',
			'class' => 'regular-text',
			'label' => '',
			'description' => '',
			'value' => '',
			'placeholder' => ''
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( ! $id || ! $name || ! $label ) {
			trigger_error( 'Missing or invalid required argument for text field.', E_USER_WARNING );
			return;
		}

		$html = '<label for="' . $id . '">' . $label . '</label> ' . "\n";
		$html .= '<input type="text" class="' . $class . '" id="' . $id . '" name="' . $name . '" value="' . $value . '" placeholder="' . $placeholder . '" />' . "\n";
		$html .= $description ? '<span class="dashicons dashicons-editor-help tiptip" title="' . $description . '"></span>' . "\n" : '';

		echo $html;
	}

	/**
	 * Number field.
	 * Echoes a number input field according to arguments passed.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args arguments to populate the field
	 *                    'id' a unique identifier for this field
	 *                    'name' a unique name for this field
	 *                    'class' (optional) CSS classes for this field
	 *                    'label' this field label text
	 *                    'description' (optional) additional text description
	 *                    'value' default field value
	 *                    'min' (optional) minimum value, default 0
	 *                    'max' (optional) maximum value, default empty (unlimited)
	 */
	public static function number_field( $args ) {

		$defaults = array(
			'id' => '',
			'name' => '',
			'class' => '',
			'label' => '',
			'description' => '',
			'value' => '',
			'min' => 0,
			'max' => '',
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( ! $id || ! $name || ! $label ) {
			trigger_error( 'Missing or invalid required argument for number field.', E_USER_WARNING );
			return;
		}

		$min = $min ? ' min="' . $min . '" ' : '';
		$max = $max ? ' max="' . $max . '" ' : '';
		// odd php quirk if $value is == (int) 0 will not be parsed in wp_parse_args and return instead false, hence reassign:
		$value = $value != 0 ? $value : 0;

		$html = '<label for="' . $id . '">' . $label . '</label> ' . "\n";
		$html .= '<input type="number" class="small-text" id="' . $id . '" name="' . $name . '" value="' . $value . '" ' . $min . ' ' . $max . ' />' . "\n";
		$html .= $description ? '<span class="dashicons dashicons-editor-help tiptip" title="' . $description . '"></span>' . "\n" : '';

		echo $html;
	}

	/**
	 * Marker field.
	 * Echoes a field to upload a marker and media object to WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args arguments to populate the field
	 *                    'id' a unique identifier for this field
	 *                    'name' a unique name for this field
	 *                    'class' (optional) CSS classes for this field
	 *                    'label' this field label text
	 *                    'description' (optional) additional text description
	 *                    'value' default value
	 */
	public static function marker_field( $args ) {

		$defaults = array(
			'id' => '',
			'name' => '',
			'class' => '',
			'label' => '',
			'description' => '',
			'value' => ''
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( ! $id || ! $name || ! $label ) {
			trigger_error( 'Missing or invalid required argument for marker field.', E_USER_WARNING );
			return;
		}

		$html = '<label for="' . $id . '">' . $label . '</label>' . "\n";
		$html .= '<span class="marker-uploader ' . $class . '">' . "\n";
		$html .= "\t" . '<span class="marker-preview"><img src="" height="" width="" /></span>' . "\n";
		$html .= "\t" . '<input type="text" class="regular-text uploaded-marker-url" name="' . $name . '" id="' . $id . '" value="' . $value . '" />' . "\n";
		$html .= "\t" . '<input type="button" class="button marker-upload-button" name="' . $id . '_button" id="' . $id . '_button" value="' . __( 'Upload', self::get_plugin_name() ) . '" />' . "\n";
		$html .= '</span>' . "\n";
		$html .= $description ? '<span class="dashicons dashicons-editor-help tiptip" title="' . $description . '"></span>' . "\n" : '';

		echo $html;
	}

	/**
	 * Map field.
	 * Echoes a markup with multiple fields to save map data.
	 * Values are stored and retrieved from a hidden field.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args arguments to populate the field
	 *                    'id' a unique identifier for this field
	 *                    'name' a unique name for this field
	 *                    'class' (optional) CSS classes for this field
	 *                    'label' this field label text
	 *                    'description' (optional) additional text description
	 *                    'value' default value to populate the hidden field holding all the values
	 */
	public static function location_box( $args ) {

		$defaults = array(
			'id' => '',
			'name' => '',
			'class' => '',
			'value' => '',
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( ! $id || ! $name ) {
			trigger_error( 'Missing or invalid required argument supplied for the map field.', E_USER_WARNING );
			return;
		}

		$plugin_name = self::get_plugin_name();
		$default = get_option( $plugin_name . '_settings' );
		$saved = $value ? (array) $value : $default['box_defaults'];

		
		///////////////////////////////////
		var_dump( $saved );
		///////////////////////////////////
		
		?>
		<div id="<?php echo $plugin_name; ?>-location-box" <?php echo ! empty( $class ) ? 'class="' . $class . '"' : ''; ?>>

			<div id="<?php echo $plugin_name; ?>-location-geocode">
				<label for="<?php echo $plugin_name; ?>-location-search">
					<input type="text" id="<?php echo $plugin_name; ?>-map-search" placeholder="<?php _e( 'Search for address...', $plugin_name ); ?>" class="regular-text geolocation-search" value="" />
				</label>
				<div id="<?php echo $plugin_name; ?>-get-user-location" class="dashicons dashicons-location" title="<?php _e( 'Click to set to your location', $plugin_name ); ?>"></div>
				<div id="<?php echo $plugin_name; ?>-remove-location" class="dashicons dashicons-trash" title="<?php _e( 'Clear location', $plugin_name ); ?>"></div>
			</div>

			<div id="<?php echo $plugin_name; ?>-location-map" class="<?php echo $plugin_name; ?>-placeholder" style="width: 100%; height: 340px">
			</div>

			<div id="<?php echo $plugin_name; ?>-location-fields">

				<input type="hidden" id="<?php echo $plugin_name; ?>-location-lat" name="<?php echo $plugin_name; ?>_location['coordinates']['lat']" value="<?php echo isset( $saved['coordinates']['lat'] ) ? $saved['coordinates']['lat'] : 00.000000; ?>" />
				<input type="hidden" id="<?php echo $plugin_name; ?>-location-lng" name="<?php echo $plugin_name; ?>_location['coordinates']['lng']" value="<?php echo isset( $saved['coordinates']['lng'] ) ? $saved['coordinates']['lng'] : 00.000000; ?>" />
				<input type="hidden" id="<?php echo $plugin_name; ?>-location-code" name="<?php echo $plugin_name; ?>_location['address']['code']" value="<?php echo isset( $saved['address']['code'] ) ? $saved['address']['code'] : ''; ?>" />
				<?php

				$fields = self::location_box_fields( $saved );
				if ( is_array( $fields ) ) :

					echo '<div id="' . $plugin_name . '-location-tabs">';

						echo '<div class="ui-tabs-nav-back"></div>';

						echo '<ul>';

							foreach( $fields as $tab ) :

								$name = isset( $tab['name'] ) ? $tab['name'] : '';
								$label = isset( $tab['label'] ) ? $tab['label'] : '';
								$icon = isset( $tab['icon'] ) ? $tab['icon'] : '';

								if ( $name && $label ) :
									echo '<li><a href="#' . $plugin_name . '-' . $name . '" class="' . $plugin_name . '-location-tab"><div class="'. $icon . '"></div><span> ' . $label . '</span></a></li>';
								endif;

							endforeach;

						echo '</ul>';

						echo '<div id="' . $plugin_name . '-location-panels" class="ui-tabs-panels">';

							foreach( $fields as $panel ) :

								echo '<div id="' . $plugin_name . '-' . $panel['name'] . '">';

									if ( isset( $panel['callback'] ) && count( $panel['callback'] ) <= 1 ) {
										call_user_func( $panel['callback'][0] );
									} else {
										call_user_func_array( $panel['callback'][0], $panel['callback'][1] );
									}

								echo '</div>';

							endforeach;

						echo '</div>';

					echo '</div>';

				endif;

				//echo '<input class="' . $plugin_name . '-location-data" type="hidden" id="' . $id . '" name="' . $name . '" value="' . json_encode( $value ) . '" />';

			echo '</div>';

		echo '</div>';

	}

	/**
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	private static function location_box_fields( $values ) {

		$plugin_name = self::get_plugin_name();

		$panels = array();
		$panels['address'] = array(
			'name'      => 'location-address',
			'label'     => __( 'Address', $plugin_name ),
			'icon'      => 'dashicons dashicons-flag',
			'callback'  => array(
				array( 'Vespucci_Admin_Fields', 'address_field' ),
				array( array(
					'values' => $values['address'],
				) ),
			),
		);
		$panels['settings'] = array(
			'name'      => 'location-defaults',
			'label'     => __( 'Settings', $plugin_name ),
			'icon'      => 'dashicons dashicons-admin-generic',
			'callback'  => array(
				array( 'Vespucci_Admin_Fields', 'location_defaults_field' ),
				array( array(
					'values' => $values['settings'],
				) ),
			),
		);

		return apply_filters( $plugin_name . '_location_box_panels', $panels, $values );
	}

	/**
	 * Callback function
	 *
	 * @param array $args
	 */
	private static function address_field( $args ) {

		$plugin_name = self::get_plugin_name();

		$saved = $args['values'];

		$fields = array(
			'street' => array(
				'name' => __( 'Street', $plugin_name ),
				'placeholder' => __( 'Street address', $plugin_name ),
			),
			'area' => array(
				'name' => __( 'Area', $plugin_name ),
				'placeholder' => __( 'Area, neighbourhood or locality', $plugin_name ),
			),
			'city' => array(
				'name' => __( 'City', $plugin_name ),
				'placeholder' => __( 'City', $plugin_name ),
			),
			'state' => array(
				'name' => __( 'State', $plugin_name ),
				'placeholder' => __( 'State or region', $plugin_name ),
			),
			'postcode' => array(
				'name' => __( 'Postcode', $plugin_name ),
				'placeholder' => __( 'Postcode or zip', $plugin_name ),
			),
			'country' => array(
				'name' => __( 'Country', $plugin_name ),
				'placeholder' => __( 'Country', $plugin_name ),
			),
		);

		foreach ( $fields as $key => $field ) :

			echo '<p class="form-field">';
				self::text_field( array(
					'id' => $plugin_name . '-map-address-' . $key,
					'name' => $plugin_name . '_settings[box_defaults][address][' . $key . ']',
					'label' => $field['name'],
					'value' => isset( $saved['address'][$key] ) ? $saved['address'][$key] : '',
					'class' => 'regular-text',
					'placeholder' => $field['placeholder']
				) );
			echo '</p>';

		endforeach;

	}

	/**
	 * Callback function
	 *
	 * @param $args
	 */
	private static function location_defaults_field( $args ) {

		$plugin_name = self::get_plugin_name();
		$saved = $args['values'];

		// ALLOW DRAGGING

		echo '<p class="form-field">' . "\n";;

			$default_dragging = array(
				'id' => $plugin_name . '-map-dragging',
				'name' => $plugin_name . '_settings[box_defaults][settings][dragging]',
				'label' => __( 'Allow map dragging', $plugin_name ),
				'value' => isset( $saved['settings']['dragging'] ) ? $saved['settings']['dragging'] : '',
				'description' => __( 'If unchecked, disables dragging the map with finger or cursor.', $plugin_name ),
			);
			self::bool_field( $default_dragging );

		echo '</p>' . "\n";;

		// MAP TYPE

		echo '<p class="form-field">' . "\n";;

			$default = Vespucci_Core::default_options( 'settings' );
			$default_provider = $default['map_provider'];
			$default_map_type =  $default['box_defaults']['settings']['map_type'][$default_provider];

			$saved_settings = get_option( $plugin_name . '_settings' );
			$saved_provider = isset( $saved_settings['map_provider'] ) ? $saved_settings['map_provider'] : $default_provider;
			$saved_map_type = isset( $saved_settings['box_defaults']['settings']['map_type'][$saved_provider] ) ? $saved_settings['box_defaults']['settings']['map_type'][$saved_provider] : $default_map_type;

			$providers = Vespucci_Core::map_providers();
			$map_types = $providers[$saved_provider]['map_types'];

			$choices = array();
			foreach ( $map_types as $map_type => $label ) :
				$choices[$map_type] = $label;
			endforeach;

			$default_type = array(
				'id'        => $plugin_name . '-map-type',
				'name'      => $plugin_name . '_settings[box_defaults][settings][map_type][' . $saved_provider . ']',
				'label'     => __( 'Map type', $plugin_name ),
				'value'     => $saved_map_type,
				'choices'   => $choices,
				'allow_null' => true,
				'description' => __( 'Select the map type that should be used to render this location.', $plugin_name ),
			);
			self::select_field( $default_type );
		echo '</p>' . "\n";

		echo '<ul class="form-field">' . "\n";

			echo "\t" . '<li class="label">' . __( 'Zoom', $plugin_name ) . '</li>' . "\n";

			$default_zoom = array(
				'id' => $plugin_name . '-map-zoom-default',
				'name' => $plugin_name . '_settings[box_defaults][settings][zoom][default]',
				'label' => __( 'Default zoom', $plugin_name ),
				'value' => isset( $saved['settings']['zoom']['default'] ) ? $saved['settings']['zoom']['default'] : '',
			);
			echo "\t" . '<li>'; self::number_field( $default_zoom ); echo '</li>' . "\n";

			$default_zoom_min = array(
				'id' => $plugin_name . '-map-zoom-min',
				'name' => $plugin_name . '_settings[box_defaults][settings][zoom][min]',
				'label' => __( 'Min zoom', $plugin_name ),
				'value' => isset( $saved['settings']['zoom']['min'] ) ? $saved['settings']['zoom']['min'] : '',
			);
			echo "\t" . '<li>'; self::number_field( $default_zoom_min ); echo '</li>' . "\n";

			$default_zoom_max = array(
				'id' => $plugin_name . '-map-zoom-max',
				'name' => $plugin_name . '_settings[box_defaults][settings][zoom][max]',
				'label' => __( 'Max zoom', $plugin_name ),
				'value' => isset( $saved['settings']['zoom']['max'] ) ? $saved['settings']['zoom']['max'] : '',
			);
			echo "\t" . '<li>'; self::number_field( $default_zoom_max ); echo '</li>' . "\n";

		echo '</ul>';

		echo '<p class="form-field">';
			$default_radius = array(
				'id' => $plugin_name . '-map-radius',
				'name' => $plugin_name . '_settings[box_defaults][settings][radius]',
				'label' => __( 'Distance radius', $plugin_name ),
				'value' => isset( $saved['settings']['radius'] ) ? $saved['settings']['radius'] : '',
				'class' => 'small-text',
				'description' => __( 'When querying the map for nearby locations from this location, sets the default distance limit.', $plugin_name ),
			);
			self::text_field( $default_radius );
		echo '</p>';

		echo '<p class="form-field">';
			$default_marker = array(
				'id' => $plugin_name . '-map-marker',
				'name' => $plugin_name . '_settings[box_defaults][settings][marker]',
				'label' => __( 'Marker', $plugin_name ),
				'value' => isset( $saved['marker'] ) ? $saved['marker'] : '',
			);
			self::marker_field( $default_marker );
		echo '</p>';

	}

}