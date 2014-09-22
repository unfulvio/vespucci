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
 * @since   0.1.0
 * @package Vespucci
 */
class Vespucci_Fields {

	/**
	 * Select field.
	 * Echoes a select field according to arguments passed.
	 *
	 * @since   0.1.0
	 *
	 * @param   array   $args   arguments to populate the field
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
	 * @since   0.1.0
	 *
	 * @param   array   $args   arguments to populate the field
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

		$value = $value === true || $value === 1 ? 1 : 0;

		$html = '<label for="' . $id . '">'  . $label . '</label> ' . "\n";
		$html .= '<input type="checkbox" class="' . $class . '" id="' . $id . '" name="' . $name . '" value="1" ' . checked( 1, $value, false ) . '/>' . "\n";
		$html .= $description ? '<span class="dashicons dashicons-editor-help tiptip" title="' . $description . '"></span>' . "\n" : '';

		echo $html;
	}

	/**
	 * WordPress Objects field.
	 * Echoes a group of checkboxes field inputs according to arguments passed.
	 *
	 * @since   0.1.0
	 *
	 * @param   array   $args   arguments to populate the field
	 */
	public static function wp_objects_field( $args ) {

		$defaults = array(
			'id' => '',
			'name' => '',
			'class' => '',
			'label' => '',
			'description' => '',
			'value' => array(),
			'choices' => ''
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( ! $id || ! $name || ! is_array( $choices ) ) {
			trigger_error( 'Missing or invalid required argument for wp objects field.', E_USER_WARNING );
			return;
		}

		$saved = (array) $value;

		echo $label ? '<p>' . $label . '</p>' . "\n\n" : '';

		echo '<table class="' . $class . '">' . "\n";

			foreach( $choices as $option_key => $option_name ) :

				echo '<tr>';

					$checkbox = "\t" . '<td class="wp-object-item ' . $option_key . '-item">' . "\n";
					// saved values (values in object are markers)
					$selected = isset( $saved['items'] ) ? in_array( $option_key, $saved['items'] ) ? $option_key : false : false;
					// individual choice among the current wp object group
					$checkbox .= "\t\t" . '<label for="' . $id . '-' . $option_key . '">' . "\n";
					$checkbox .= "\t\t\t" . '<input type="checkbox" class="checkbox" id="' . $id . '-' . $option_key . '" name="' . $name . '[items][]" value="' . $option_key . '" ' . checked( $selected, $option_key, false ). ' />' . "\n";
					$checkbox .= "\t\t\t" . $option_name . "\n";
					$checkbox .= "\t\t" . '</label>' . "\n";
					$checkbox .= "\t" . '</td>' . "\n";

					echo $checkbox;

					echo '<td class="wp-object-marker ' . $option_key . '-marker">';
						$args = array(
							'id' => $option_key . '_marker',
							'name' => $name . '[markers][' . $option_key . ']',
							'value' => isset( $saved['markers'][$option_key] ) ? $saved['markers'][$option_key] : ''
						);
						// adds a marker field for each available choice
						self::marker_field( $args );
					echo '</td>';

				echo '</tr>';

			endforeach;

		echo '</table>';

		echo $description ? '<span class="dashicons dashicons-editor-help tiptip" title="' . $description . '"></span>' . "\n" : '';

	}

	/**
	 * Text field.
	 * Echoes a text input according to arguments passed.
	 *
	 * @since   0.1.0
	 *
	 * @param   array   $args   arguments to populate the field
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

		$html  = '<label for="' . $id . '">' . $label . '</label> ' . "\n";
		$html .= '<input type="text" class="' . $class . '" id="' . $id . '" name="' . $name . '" value="' . $value . '" placeholder="' . $placeholder . '" />' . "\n";
		$html .= $description ? '<span class="dashicons dashicons-editor-help tiptip" title="' . $description . '"></span>' . "\n" : '';

		echo $html;
	}

	/**
	 * Number field.
	 * Echoes a number input field.
	 *
	 * @since   0.1.0
	 *
	 * @param   array   $args   arguments to populate the field
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
		// odd php or wp_parse_args() quirk (didn't investigate):
		// if ( $value == (int) 0 ), then $value will not be parsed in wp_parse_args() and return instead false, hence reassign:
		$value = $value != 0 ? $value : 0;

		$html  = '<label for="' . $id . '">' . $label . '</label> ' . "\n";
		$html .= '<input type="number" class="small-text" id="' . $id . '" name="' . $name . '" value="' . $value . '" ' . $min . ' ' . $max . ' />' . "\n";
		$html .= $description ? '<span class="dashicons dashicons-editor-help tiptip" title="' . $description . '"></span>' . "\n" : '';

		echo $html;
	}

	/**
	 * Marker field.
	 * Echoes a field to upload a marker image as a media object to WordPress.
	 *
	 * @since   0.1.0
	 *
	 * @param   array   $args   arguments to populate the field
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

		if ( ! $id || ! $name ) {
			trigger_error( 'Missing or invalid required argument for marker field.', E_USER_WARNING );
			return;
		}

		$plugin_name = Vespucci::get_plugin_name();
		$size = $value == true ? '96' : '';

		$html = '<label for="' . $id . '">' . $label . '</label>' . "\n";

		$html .= '<span class="marker-uploader ' . $class . '">' . "\n";
		$html .= "\t" . '<span class="marker-preview"><img src="' . $value . '" height="' . $size . '" width="' . $size . '" /></span>' . "\n";
		$html .= "\t" . '<input type="text" class="uploaded-marker-url hidden" name="' . $name . '" id="' . $id . '" value="' . $value . '" />' . "\n";
		$html .= "\t" . '<span class="dashicons dashicons-plus-alt marker-field-action marker-upload-button" title="' . __( 'Upload a marker', $plugin_name ) .'"></span>' . "\n";
		$html .= "\t" . '<span class="dashicons dashicons-dismiss marker-field-action marker-remove-button" title="' . __( 'Remove marker', $plugin_name ) .'"></span>' . "\n";
		$html .= $description ? "\t" . '<span class="dashicons dashicons-editor-help tiptip" title="' . $description . '"></span>' . "\n" : '';
		$html .= '</span>' . "\n";


		echo $html;
	}

	/**
	 * Map field.
	 * Echoes a markup with multiple fields to save map data.
	 * Values are stored and retrieved from a hidden field.
	 *
	 * @since   0.1.0
	 *
	 * @param   array   $args   arguments to populate the fields within this field group:
	 */
	public static function location_box( $args ) {

		$defaults = array(
			'class' => '',
			'value' => '',
			'description' => '',
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		$plugin_name = Vespucci::get_plugin_name();

		// data passed in args
		$value          = (array) $value;
		$saved_data     = isset( $value['location'] ) ? (array) $value['location'] : '';
 		// default options saved in database
		$saved_option   = get_option( $plugin_name . '_location' );
		// default options from plugin
		$default_option = Vespucci::default_options( 'location' );

		// populate basic location inputs (outside of tabbed panels)
		$keys = array( 'title', 'lat', 'lng', 'countrycode' );
		$setting = '';
		foreach ( $keys as $key ) :
			if ( isset( $saved_data[$key] ) )
				$setting[$key] = $saved_data[$key];
			elseif( isset( $saved_option[$key] ) )
				$setting[$key] = $saved_option[$key];
			elseif( isset( $default_option[$key] ) )
				$setting[$key] = $default_option[$key];
			else
				$setting[$key] = '';
		endforeach;

		// begin output

		echo $description ? '<p class="description">' . $description . '</p><br />' : '';
		?>
		<div id="<?php echo $plugin_name; ?>-location-box" <?php echo ! empty( $class ) ? 'class="postbox ' . $class . '"' : ''; ?>>

			<label id="<?php echo $plugin_name; ?>-location-geocode" for="<?php echo $plugin_name; ?>-location-title">
				<input name="<?php echo $plugin_name . '_location[title]'; ?>" type="text" id="<?php echo $plugin_name; ?>-location-title" placeholder="<?php _e( 'Enter a location name...', $plugin_name ); ?>" class="location-title" value="<?php echo $setting['title']; ?>" size="30" autocomplete="off" />
				<span id="vespucci-geocode-request" class="dashicons dashicons-admin-site" title="<?php _e( 'Look up for typed address', $plugin_name ); ?>"></span>
				<span id="vespucci-user-location" class="dashicons dashicons-location" title="<?php _e( 'Look up for your current location', $plugin_name ); ?>"></span>
			</label>

			<div id="<?php echo $plugin_name; ?>-location-map" class="<?php echo $plugin_name; ?>-placeholder" style="width: 99%; width: calc(100% - 2px); height: 340px">
			</div>

			<div id="<?php echo $plugin_name; ?>-location-fields">
				<input type="hidden" id="<?php echo $plugin_name; ?>-location-lat" name="<?php echo $plugin_name; ?>_location[lat]" value="<?php echo $setting['lat']; ?>" />
				<input type="hidden" id="<?php echo $plugin_name; ?>-location-lng" name="<?php echo $plugin_name; ?>_location[lng]" value="<?php echo $setting['lng']; ?>" />
				<input type="hidden" id="<?php echo $plugin_name; ?>-location-code" name="<?php echo $plugin_name; ?>_location[countrycode]" value="<?php echo $setting['countrycode']; ?>" />
				<?php

				$fields = self::location_box_fields( $value );
				if ( is_array( $fields ) ) :

					?>
					<div id="<?php echo $plugin_name; ?>-location-tabs" class="location-tabs">
						<div class="ui-tabs-nav-back"></div>
						<ul>
							<?php

							foreach( $fields as $tab ) :

								$name   = isset( $tab['name']   ) ? $tab['name']    : '';
								$label  = isset( $tab['label']  ) ? $tab['label']   : '';
								$icon   = isset( $tab['icon']   ) ? $tab['icon']    : '';

								if ( $name && $label )
									echo '<li><a href="#' . $plugin_name . '-' . $name . '" class="' . $plugin_name . '-location-tab"><div class="'. $icon . '"></div><span> ' . $label . '</span></a></li>';

							endforeach;

							?>
						</ul>

						<div id="<?php echo $plugin_name; ?>-location-panels" class="ui-tabs-panels">
							<?php

							foreach( $fields as $panel ) :

								echo '<div id="' . $plugin_name . '-' . $panel['name'] . '">';

									if ( isset( $panel['callback'] ) && count( $panel['callback'] ) <= 1 )
										call_user_func( $panel['callback'][0] );
									else
										call_user_func_array( $panel['callback'][0], $panel['callback'][1] );

								echo '</div>';

							endforeach;

							?>
						</div>
					</div>
					<?php

				endif;

				?>
			</div>
		</div>
		<?php

	}

	/**
	 * Location box fields.
	 * Configures and passes data for location box fields.
	 *
	 * @since   0.1.0
	 *
	 * @param   array   $values default or saved values to pass for fields values
	 *
	 * @return  array   location box fields panel data
	 */
	private static function location_box_fields( $values ) {

		$plugin_name = Vespucci::get_plugin_name();

		$panels = array();

		// address panel
		$panels['address'] = array(
			'name'      => 'location-address',
			'label'     => __( 'Address', $plugin_name ),
			'icon'      => 'dashicons dashicons-flag',
			'callback'  => array(
				array( 'Vespucci_Fields', 'address_field' ),
				array( array(
					'values' => isset( $values['location'] ) ? $values['location'] : '',
				) ),
			),
		);

		// location marker panel
		$panels['marker'] = array(
			'name'      => 'location-marker',
			'label'     => __( 'Marker', $plugin_name ),
			'icon'      => 'dashicons dashicons-location',
			'callback'  => array(
				array( 'Vespucci_Fields', 'location_marker_field' ),
				array( array(
					'values' => isset( $values['meta']['marker']['default'] ) ? $values['meta']['marker']['default'] : '',
				) ),
			),
		);

		// location meta panel
		$panels['meta'] = array(
			'name'      => 'location-defaults',
			'label'     => __( 'Settings', $plugin_name ),
			'icon'      => 'dashicons dashicons-admin-generic',
			'callback'  => array(
				array( 'Vespucci_Fields', 'location_defaults_field' ),
				array( array(
					'values' => isset( $values['meta'] ) ? $values['meta'] : '',
				) ),
			),
		);

		return apply_filters( $plugin_name . '_location_box_panels', $panels, $values );
	}

	/**
	 * Address fields for location box panel.
	 * Callback function to render address fields in location box.
	 *
	 * @since   0.1.0
	 *
	 * @param   array   $address    the saved or default address fields formatted as key-value pairs
	 */
	private static function address_field( $address ) {

		$plugin_name = Vespucci::get_plugin_name();

		$saved = (array) $address['values'];

		$fields = array(
			'street' => array(
				'name' => __( 'Street', $plugin_name ),
				'placeholder' => __( 'Road or street address', $plugin_name ),
			),
			'area' => array(
				'name' => __( 'Area', $plugin_name ),
				'placeholder' => __( 'Area, neighbourhood or locality', $plugin_name ),
			),
			'city' => array(
				'name' => __( 'City', $plugin_name ),
				'placeholder' => __( 'City or town', $plugin_name ),
			),
			'district' => array(
				'name' => __( 'District', $plugin_name ),
				'placeholder' => __( 'County, province or district', $plugin_name ),
			),
			'state' => array(
				'name' => __( 'State', $plugin_name ),
				'placeholder' => __( 'State or region', $plugin_name ),
			),
			'postcode' => array(
				'name' => __( 'Postcode', $plugin_name ),
				'placeholder' => __( 'Postal code or zip', $plugin_name ),
			),
			'country' => array(
				'name' => __( 'Country', $plugin_name ),
				'placeholder' => __( 'Country', $plugin_name ),
			),
		);

		foreach ( $fields as $key => $field ) :

			echo '<p class="form-field">';

				self::text_field( array(
					'id'            => $plugin_name . '-location-address-' . $key,
					'name'          => $plugin_name . '_location[' . $key . ']',
					'label'         => $field['name'],
					'value'         => isset( $saved[$key] ) ? $saved[$key] : '',
					'class'         => 'regular-text',
					'placeholder'   => $field['placeholder']
				) );

			echo '</p>';

		endforeach;

	}

	/**
	 * Marker field for location box panel.
	 * Callback function to render marker field in location box.
	 *
	 * @since   0.1.0
	 *
	 * @param   array   $data   saved or default marker data
	 */
	private static function location_marker_field( $data ) {

		$plugin_name = Vespucci::get_plugin_name();
		$saved = is_object( $data['values'] ) ? (array) $data['values'] : $data['values'];

		echo '<p class="form-field">';

			$default_marker = array(
				'id'    => $plugin_name . '-map-marker',
				'name'  => $plugin_name . '_meta[marker][default]',
				'label' => __( 'Marker', $plugin_name ),
				'value' => isset( $saved ) ? $saved : '',
			);
			self::marker_field( $default_marker );

		echo '</p>';

	}

	/**
	 * Meta data fields for location defaults.
	 * Callback function to render meta fields in location box.
	 *
	 * @since   0.1.0
	 *
	 * @param   array   $saved   saved values or default data for each field
	 */
	private static function location_defaults_field( $saved ) {

		$plugin_name = Vespucci::get_plugin_name();

		// MAP DRAGGING

		echo '<p class="form-field">' . "\n";
			$default_dragging = array(
				'id'            => $plugin_name . '-map-dragging',
				'name'          => $plugin_name . '_meta[dragging]',
				'label'         => __( 'Allow map dragging', $plugin_name ),
				'value'         => isset( $saved['values']['dragging'] ) ? $saved['values']['dragging'] : '',
				'description'   => __( 'If unchecked, disables ability to drag the map with finger or mouse.', $plugin_name ),
			);
			self::bool_field( $default_dragging );

		echo '</p>' . "\n";

		// MAP TYPE

		// default options
		$settings           = Vespucci::default_options( 'settings' );
		$default_provider   = $settings['map_provider'];
		$default_meta       = Vespucci::default_options( 'meta' );
		// saved options
		$saved_settings     = get_option( $plugin_name . '_settings' );
		$saved_provider     = isset( $saved_settings['map_provider'] ) ? $saved_settings['map_provider'] : $default_provider;
		$saved_meta         = isset( $saved['values'] ) ? $saved['values'] : get_option( $plugin_name . '_meta' );

		// registered map providers
		$providers = Vespucci::map_providers();
		foreach ( $providers as $provider => $property ) :

			$map_types = isset( $property['map_types'] ) ? $property['map_types'] : '';
			if ( ! empty( $map_types ) ) :

				// saved value according to each map provider in the loop
				$saved_value = isset( $saved_meta['map_type'][$provider] ) ? $saved_meta['map_type'][$provider] : '';

				$saved_value = empty( $saved_value ) && isset( $default_meta['map_type'][$provider] ) ? $default_meta['map_type'][$provider] : $saved_value;

				// show only field for current map provider
				$class = $provider != $saved_provider ? 'hidden' : '';
				// map types choices for current provider in the loop
				$choices = array();
				foreach ( $map_types as $map_type => $label )
					$choices[$map_type] = $label;

				echo '<p class="form-field">' . "\n";

					$default_type = array(
						'id'            => $plugin_name . '-' . $provider . '-map-type',
						'name'          => $plugin_name . '_meta[map_type][' . $provider . ']',
						'label'         => __( 'Map type', $plugin_name ),
						'value'         => $saved_value,
						'choices'       => $choices,
						'class'         => $class,
						'allow_null'    => false,
						'description'   => __( 'Select the map type that should be used to render this location.', $plugin_name ),
					);
					self::select_field( $default_type );

				echo '</p>' . "\n";

			endif;

		endforeach;

		// ZOOM

		echo '<ul class="form-field">' . "\n";

			echo "\t" . '<li class="label">' . __( 'Zoom', $plugin_name ) . '</li>' . "\n";

			$default_zoom = array(
				'id'    => $plugin_name . '-map-zoom-default',
				'name'  => $plugin_name . '_meta[zoom][default]',
				'label' => __( 'Default zoom', $plugin_name ),
				'value' => isset( $saved['values']['zoom']['default'] ) ? $saved['values']['zoom']['default'] : '',
				'min'   => 0,
				'max'   => 22,
			);
			echo "\t" . '<li>'; self::number_field( $default_zoom ); echo '</li>' . "\n";

			$default_zoom_min = array(
				'id'    => $plugin_name . '-map-zoom-min',
				'name'  => $plugin_name . '_meta[zoom][min]',
				'label' => __( 'Min zoom', $plugin_name ),
				'value' => isset( $saved['values']['zoom']['min'] ) ? $saved['values']['zoom']['min'] : '',
				'min'   => 0,
				'max'   => 22,
			);
			echo "\t" . '<li>'; self::number_field( $default_zoom_min ); echo '</li>' . "\n";

			$default_zoom_max = array(
				'id'    => $plugin_name . '-map-zoom-max',
				'name'  => $plugin_name . '_meta[zoom][max]',
				'label' => __( 'Max zoom', $plugin_name ),
				'value' => isset( $saved['values']['zoom']['max'] ) ? $saved['values']['zoom']['max'] : '',
				'min'   => 0,
				'max'   => 22,
			);
			echo "\t" . '<li>'; self::number_field( $default_zoom_max ); echo '</li>' . "\n";

		echo '</ul>';

		// RADIUS

		echo '<p class="form-field">';
			$default_radius = array(
				'id'            => $plugin_name . '-map-radius',
				'name'          => $plugin_name . '_meta[radius]',
				'label'         => __( 'Distance radius', $plugin_name ),
				'value'         => isset( $saved['values']['radius'] ) ? $saved['values']['radius'] : '',
				'class'         => 'medium-text',
				'description'   => __( 'When querying the map for nearby locations from this location, sets the default distance limit.', $plugin_name ),
			);
			self::text_field( $default_radius );
		echo '</p>';

	}

}