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
	 * Toggles visibility of the location metabox.
	 */
	var disable_location = $('#vespucci-location-disable-save');
	var location_metabox = $('#vespucci-location-box');
	$(disable_location).change( function() {
		if ( this.checked ) {
			console.log('disable location is checked');
			$(location_metabox).addClass('hidden');
		} else {
			console.log('disable location is unchecked');
			$(location_metabox).removeClass('hidden');
		}
	});

	/**
	 * Marker field.
	 * Open the media uploader to upload marker and store the URL to media object.
	 */
	$('.marker-upload-button').click(function(e) {

		e.preventDefault();

		var button = this,
			field = $(button).closest('.marker-uploader'),
			image = $(field).find('.marker-preview img'),
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

		// When a file is selected, grab the URL and set it as the text field's value
		marker_uploader.on('select', function() {
			attachment = marker_uploader.state().get('selection').first().toJSON();
			input.val(attachment.url);
			// useful while triggering locations field events
			input.blur();
			// update preview
			PreviewMarker( input.val(), image );
		});

		//Open the uploader dialog
		marker_uploader.open();

	});

	/**
	 * Clears the marker input field
	 */
	$('.marker-remove-button').click(function(e) {

		e.preventDefault();

		var button = this,
			field = $(button).closest('.marker-uploader'),
			image = $(field).find('.marker-preview img'),
			input = $(field).find('input.uploaded-marker-url');

		// clear
		input.val('');
		// update preview
		PreviewMarker( input.val(), image );

	});


	/**
	 * Preview marker image.
	 *
	 * @param 	url		an url to an image
	 * @param 	target	the target img tag
	 */
	function PreviewMarker( url, target ) {

		$(target).attr( 'src', url );

		if ( url.length > 6 ) {
			$(target).attr( 'width', 96 );
			$(target).attr( 'height', 96 );
			$(target).show();
		} else {
			$(target).hide();
		}

	}

}(jQuery));