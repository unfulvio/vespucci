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
 * @package Vespucci_Admin
 * @author  nekojira <fulvio@nekojira.com>
 */
class Vespucci_Admin_Fields extends Vespucci_Admin {

	/**
	 * Call plugin slug from public plugin class.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public static function plugin_slug() {

		$plugin = Vespucci_Plugin::get_instance();
		return $plugin->get_plugin_slug();

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
			'value' => ''
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( ! $id || ! $name || ! is_array( $choices ) ) {
			trigger_error( 'Missing or invalid required argument for select field.', E_USER_WARNING );
			return;
		}

		$html = $label ? '<label for="' . $id . '"> '  . $label . '</label>' . "\n" : '';

		$options = '<select id="' . $id . '" name="' . $name . '">' . "\n";
		foreach( $choices as $key => $option ) :
			$options  .=  "\t" . '<option value="' . $key . '" ' . selected( $key, $value, false ) . '>' . $option .'</option>' . "\n";
		endforeach;
		$options .= '</select>' . "\n";

		$html .= $options;
		$html .= $description ? '<p class="description">' . $description . '</p>' . "\n" : '';

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

		$html  = '<input type="checkbox" class="' . $class . '" id="' . $id . '" name="' . $name . '" value="1" ' . checked( 1, $value, false ) . '/>' . "\n";
		$html .= '<label for="' . $id . '"> '  . $label . '</label>' . "\n";
		$html .= $description ? '<p class="description">' . $description . '</p>' . "\n" : '';

		echo $html;

	}

	/**
	 * Locations field.
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
	public static function locations_field( $args ) {

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

		$saved = (array) $value;

		echo $label ? '<p>' . $label . '</p>' . "\n\n" : '';

		$html = '<fieldset class="' . $class . ' locations">' . "\n";

		$checkboxes = '';
		foreach( $choices as $option_key => $option_name ) :

			$checkboxes .= "\t" . '<div class="location">' . "\n";

				// saved values (values in object are markers)
				$selected = isset( $saved[$option_key] ) ? $option_key : '';

				// individual choice among the current wp object group
				$checkboxes .= "\t\t" . '<label for="location_' . $option_key . '">' . "\n";
				$checkboxes .= "\t\t\t" . '<input type="checkbox" class="checkbox" id="location_' . $option_key . '" name="' . $option_key . '[location]" value="' . $option_key . '" ' . checked( $selected, $option_key, false ). ' />' . "\n";
				$checkboxes .= "\t\t\t" . $option_name . "\n";
				$checkboxes .= "\t\t" . '</label>' . "\n";

				$args = array(
					'id' => $option_key . '_marker',
					'name' => $option_key . '[marker]',
					'label' => sprintf( __( 'Marker for %s'), $option_name ),
					'value' => isset( $saved[$option_key] ) ? $saved[$option_key] : '',
					'size' => 'marker'
				);
				// adds a marker field for each available choice
				self::marker_field( $args );

			$checkboxes .= "\t" . '</div>' . "\n";

		endforeach;

		$html .= $checkboxes;
		$html .= "\t" . '<input type="hidden" class="selected-locations" id="' . $id . '" name="' . $name . '" value="' . json_encode( $value ) . '" />' . "\n";
		$html .= '</fieldset>' . "\n";
		$html .= $description ? '<p class="description">' . $description . '</p>' . "\n" : '';

		echo $html;

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
		$html .= $description ? '<p class="description">' . $description . '</p>' . "\n" : '';

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
		$html .= $description ? '<p class="description">' . $description . '</p>' . "\n" : '';

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
	 *                    'size' image size to be used
	 */
	public static function marker_field( $args ) {

		$defaults = array(
			'id' => '',
			'name' => '',
			'class' => '',
			'label' => '',
			'description' => '',
			'value' => '',
			'size' => 'marker'
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( ! $id || ! $name || ! $label || ! $size ) {
			trigger_error( 'Missing or invalid required argument for marker field.', E_USER_WARNING );
			return;
		}

		$html = '<div class="marker-uploader ' . $class . '">' . "\n";
		$html .= "\t" . '<span class="marker-preview"><img src="" height="" width="" /></span>' . "\n";
		$html .= "\t" . '<input type="text" class="regular-text uploaded-marker-url" name="' . $name . '" id="' . $id . '" value="' . $value . '" />' . "\n";
		$html .= "\t" . '<input type="button" class="button marker-upload-button" name="' . $id . '_button" id="' . $id . '_button" value="' . __( 'Upload', self::plugin_slug() ) . '" />' . "\n";
		$html .= '<label for="' . $id . '">' . $label . '</label>';
		$html .= $description ? '<p class="description">' . $description . '</p>' . "\n" : '';
		$html .= '</div>' . "\n";

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
	public static function map_field( $args ) {

		$defaults = array(
			'id' => '',
			'name' => '',
			'class' => '',
			'label' => '',
			'description' => '',
			'value' => '',
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( ! $id || ! $name ) {
			trigger_error( 'Missing or invalid required argument supplied for the map field.', E_USER_WARNING );
			return;
		}

		$plugin = Vespucci_Plugin::get_instance();
		$plugin_slug = $plugin->get_plugin_slug();
		
		$default = get_option( $plugin_slug . '_settings' );
		$saved = $value ? (array) $value : $default['map_defaults'];

		var_dump( json_encode( $value ) );
		?>
		<div id="vespucci-map-settings">

			<?php echo $description ? '<p class="description">'. $description . '</p><br />' : ''; ?>

			<div id="vespucci-map-geocode">
				<label for="vespucci-map-search">
					<input type="text" id="vespucci-map-search" placeholder="<?php _e( 'Search for address...', $plugin_slug ); ?>" class="regular-text geolocation-search" value="" />
				</label>
				<div class="dashicons dashicons-location current-location" title="<?php _e( 'Click to set to your location', $plugin_slug ); ?>"></div>
				<div class="dashicons dashicons-trash remove-location" title="<?php _e( 'Clear location', $plugin_slug ); ?>"></div>
			</div>

			<div id="vespucci-map" class="vespucci-placeholder" style="width: 100%; height: 340px">
			</div>

			<input type="hidden" id="vespucci-map-lat" name="vespucci_coordinates['lat']" value="<?php echo isset( $saved['coordinates']['lat'] ) ? $saved['coordinates']['lat'] : 43.783300; ?>" />
			<input type="hidden" id="vespucci-map-lng" name="vespucci_coordinates['lng']" value="<?php echo isset( $saved['coordinates']['lng'] ) ? $saved['coordinates']['lng'] : 11.250000; ?>" />

			<div id="vespucci-map-fields">
				<div class="vespucci-left">
					<h4><?php _e( 'Map defaults:', $plugin_slug ); ?></h4>
					<?php

					echo '<p>';
					$default_dragging = array(
						'id' => 'vespucci-map-dragging',
						'name' => 'default_dragging',
						'label' => __( 'Allow map dragging', $plugin_slug ),
						'value' => isset( $saved['dragging'] ) ? $saved['dragging'] : true
					);
					self::bool_field( $default_dragging );
					echo '</p>';

					echo '<p>';
					$default_radius = array(
						'id' => 'vespucci-map-type',
						'name' => 'vespucci_map_type',
						'label' => __( 'Map type', $plugin_slug ),
						'value' => isset( $saved['map_type'] ) ? $saved['map_type'] : 'traffic',
						'choices' => array(
							'' => '',
							'traffic' => __( 'Traffic', self::plugin_slug() ),
							'terrain' => __( 'Terrain', self::plugin_slug() ),
							'satellite' => __( 'Satellite', self::plugin_slug() ),
						)
					);
					self::select_field( $default_radius );
					echo '</p>';

					echo '<p>';
					$default_zoom = array(
						'id' => 'vespucci-map-zoom-default',
						'name' => 'vespucci_map_zoom[default]',
						'label' => __( 'Default zoom', $plugin_slug ),
						'value' => isset( $saved['zoom']['default'] ) ? $saved['zoom']['default'] : 10
					);
					self::number_field( $default_zoom );
					echo '</p>';

					echo '<p>';
					$default_zoom_min = array(
						'id' => 'vespucci-map-zoom-min',
						'name' => 'vespucci_map_zoom[min]',
						'label' => __( 'Min zoom', $plugin_slug ),
						'value' => isset( $saved['zoom']['min'] ) ? $saved['zoom']['min'] : 0
					);
					self::number_field( $default_zoom_min );
					echo '</p>';

					echo '<p>';
					$default_zoom_max = array(
						'id' => 'vespucci-map-zoom-max',
						'name' => 'vespucci_map_zoom[max]',
						'label' => __( 'Max zoom', $plugin_slug ),
						'value' => isset( $saved['zoom']['max'] ) ? $saved['zoom']['max'] : 21
					);
					self::number_field( $default_zoom_max );
					echo '</p>';

					echo '<p>';
					$default_radius = array(
						'id' => 'vespucci-map-radius',
						'name' => 'vespucci_map_radius',
						'label' => __( 'Distance radius', $plugin_slug ),
						'value' => isset( $saved['radius'] ) ? $saved['radius'] : '50km',
						'class' => 'tiny-text'
					);
					self::text_field( $default_radius );
					echo '</p>';

					echo '<p>';
					$default_marker = array(
						'id' => 'vespucci-map-marker',
						'name' => 'vespucci_map_marker',
						'label' => __( 'Marker', $plugin_slug ),
						'value' => isset( $saved['marker'] ) ? $saved['marker'] : ''
					);
					self::marker_field( $default_marker );
					echo '</p>';

					?>
				</div>

				<div class="vespucci-right">
					<div id="vespucci-map-address">
						<h4><?php _e( 'Address:', $plugin_slug ); ?></h4>
						<?php

						$fields = array(
							'street' => array(
								'name' => __( 'Street', $plugin_slug ),
								'placeholder' => __( 'Street address', $plugin_slug ),
							),
							'area' => array(
								'name' => __( 'Area', $plugin_slug ),
								'placeholder' => __( 'Area, neighbourhood or locality', $plugin_slug ),
							),
							'city' => array(
								'name' => __( 'City', $plugin_slug ),
								'placeholder' => __( 'City', $plugin_slug ),
							),
							'state' => array(
								'name' => __( 'State', $plugin_slug ),
								'placeholder' => __( 'State or region', $plugin_slug ),
							),
							'postcode' => array(
								'name' => __( 'Postcode', $plugin_slug ),
								'placeholder' => __( 'Postcode or zip', $plugin_slug ),
							),
							'country' => array(
								'name' => __( 'Country', $plugin_slug ),
								'placeholder' => __( 'Country', $plugin_slug ),
							),
						);

						foreach ( $fields as $key => $field ) :

							echo '<p>';
							self::text_field( array(
								'id' => 'vespucci-map-address-' . $key,
								'name' => 'vespucci_map_address[' . $key . ']',
								'label' => $field['name'],
								'value' => isset( $saved['address'][$key] ) ? $saved['address'][$key] : '',
								'class' => 'regular-text default-' . $key,
								'placeholder' => $field['placeholder']
							) );
							echo '</p>';

						endforeach;

						?>
					</div>
				</div>

			</div>

			<input class="vespucci-map-data" type="hidden" id="<?php echo $id; ?>" name="<?php echo $name; ?>" value=<?php echo json_encode( $value ); ?> />
		</div>
	<?php

	}

}