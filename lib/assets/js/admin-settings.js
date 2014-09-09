(function ( $ ) {

	"use strict";

	// TipTip tooltips
	$(function(){
		$('.tiptip').tipTip({
			fadeIn: 0,
			fadeOut: 0
		});
	});

	/**
	 * Marker field.
	 * Open the media uploader to upload marker and store the URL to media object.
	 */
	$('.marker-upload-button').click(function(e) {

		e.preventDefault();

		var button = this,
			field = $(button).closest('.marker-uploader'),
			input = $(field).find('input.uploaded-marker-url'),
			attachment;

		// If the uploader object has already been created, reopen the dialog
		if ( marker_uploader ) {
			marker_uploader.open();
			return;
		}

		//Extend the wp.media object
		var marker_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
				text: 'Choose Image'
			},
			multiple: false
		});

		//When a file is selected, grab the URL and set it as the text field's value
		marker_uploader.on('select', function() {
			attachment = marker_uploader.state().get('selection').first().toJSON();
			input.val(attachment.url);
			// useful while triggering locations field events
			input.blur();
		});

		//Open the uploader dialog
		marker_uploader.open();

	});

	/**
	 * Locations field.
	 * For each location, store saved items and default marker information in hidden field.
	 */
	$('.locations').each(function(){

		var choices = $(this).find('.location'),
			chosenLocations = $(this).find('.selected-locations'),
			selections = {};

		// loop through each object group (post types, terms, users)
		choices.each(function(){

			// each group of objects may have a set of many items (e.g. user roles)
			var field = $(this).find('.checkbox'),
				name = field.val(),
				marker = $(this).find('input.uploaded-marker-url'),
				button = $(this).find('input.marker-upload-button');

			/**
			 * Update selected locations.
			 * Save selections and markers into a corresponding hidden field to store data.
			 * *
			 * @param checkbox
			 */
			function updateSelections(checkbox) {

				if ( checkbox.prop('checked') ) {

					selections[name] = marker.val();
					// console.log( name + ' checked; current selection: ' + JSON.stringify(selections) );
					marker.removeClass('disabled').removeAttr('disabled');
					button.removeClass('disabled').removeAttr('disabled');

				} else {

					delete selections[name];
					// console.log( 'unchecked ' + name + '; current selection: ' + JSON.stringify(selections) );
					marker.addClass('disabled').attr('disabled', 'disabled');
					button.addClass('disabled').attr('disabled', 'disabled');

				}

				// object name as key, marker (or empty string if unset) as value
				var value = JSON.stringify(selections);
				chosenLocations.val(value);

			}

			// update fields once
			updateSelections( field );

			// update when a checkbox is checked/unchecked
			field.on('click', function() {
				updateSelections( $(this) );
				// console.log( 'selection changed, current selections are: ' + JSON.stringify( selections ) )
			});

			// update if an associated marker is updated
			marker.on('change, blur', function() {
				updateSelections( field );
				// console.log( 'marker changed, current selections are: ' + JSON.stringify( selections ) )
			});

		});

	});

}(jQuery));