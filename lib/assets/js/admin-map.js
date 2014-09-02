(function ( $ ) {

	"use strict";

	$('#vespucci-map-settings').each(function(){

		// coordinates (hidden)
		var lat = $('#vespucci-map-lat'),
			lng = $('#vespucci-map-lng');

		// user input fields
		var fields = $('#vespucci-map-fields'),
			dragging = fields.find('#vespucci-map-dragging'),
			radius = fields.find('#vespucci-map-radius'),
			zoom = fields.find('#vespucci-map-zoom'),
			marker = fields.find('#vespucci-map-marker'),
			address = fields.find('#vespucci-map-address');

		// individual address user input fields
		var street = address.find('#vespucci-map-address-street'),
			area = address.find('#vespucci-map-address-area'),
			city = address.find('#vespucci-map-address-city'),
			state = address.find('#vespucci-map-address-state'),
			postcode = address.find('#vespucci-map-address-postcode'),
			country = address.find('#vespucci-map-address-country');

		// geocode input field and controls
		var geocodeField = $('#vespucci-map-geocode'),
			geocodeSearch = geocodeField.find('#vespucci-map-search'),
			geocodeUserLocation = geocodeField.find('.current-location'),
			geocodeRemoveLocation = geocodeField.find('.remove-location');

		// final output (hidden field holding all data)
		var output = $('#vespucci-map-data');

		// the map
		var map = new GMaps({
			div: '#vespucci-map',
			lat: lat.val(),
			lng: lng.val()
		});

		$('#geocoding_form').submit(function(e){
			e.preventDefault();
			map.geocode({
				address: $('#address').val().trim(),
				callback: function(results, status){
					if(status=='OK'){
						var latlng = results[0].geometry.location;
						map.setCenter(latlng.lat(), latlng.lng());
						map.addMarker({
							lat: latlng.lat(),
							lng: latlng.lng()
						});
					}
				}
			});
		});


		/**
		 * Update address fields.
		 * Whenever a new address is geocoded or the marker is dragged, address fields are updated.		 *
		 * (However the opposite is not true, a manually entered address in such fields does not update the map)
		 *
		 * @param address the address object
		 */
		function updateAddress(address){


			updateOutput();

		}

		/**
		 * Update the final output.
		 * Updates the value of the hidden output field with all the values stored in json format.
		 */
		function updateOutput() {

			var object = {
				coordinates : {
					lat : lat.val(),
					lng : lng.val()
				},
				dragging : dragging.val(),
				zoom : zoom.val(),
				radius : radius.val(),
				marker : marker.val(),
				address : {
					street : street.val(),
					area : area.val(),
					city : city.val(),
					state : state.val(),
					postcode : postcode.val(),
					country : country.val()

				}
			};

			var value = JSON.stringify(object);
			output.val(value);

		}

		// get the user location
		geocodeUserLocation.on('click', function(e) {

			e.preventDefault();

			// attempt to use browser location
			if ( navigator.geolocation ) {
				navigator.geolocation.getCurrentPosition(getPosition, errors);
			} else {
				FAIL("NOT_SUPPORTED");
			}

			// get the user coordinates
			function getPosition(position) {
				var userLat = position.coords.latitude;
				var userLng = position.coords.longitude;
				SUCCESS(userLat, userLng);
			}

			// error codes
			function errors(error) {
				switch (error.code) {
					case error.PERMISSION_DENIED:
						FAIL("PERMISSION_DENIED");
						break;
					case error.POSITION_UNAVAILABLE:
						FAIL("POSITION_UNAVAILABLE");
						break;
					case error.TIMEOUT:
						FAIL("TIMEOUT");
						break;
					default:
						break;
				}
			}

			// error handling
			function FAIL(error_codes) {
				switch (error_codes) {
					case "NOT_SUPPORTED":
						alert('Browser does not support sharing of location data');
						break;
					case "PERMISSION_DENIED":
						alert('Permission denied');
						break;
					case "POSITION_UNAVAILABLE":
						alert('Position not available');
						break;
					case "TIMEOUT":
						alert('Current position lookup timeout');
						break;
					case "UNKNOWN_ERROR":
						alert('An unknown error occurred');
						break;
					default:
						break;
				}
			}

			function SUCCESS(userLat, userLng) {
				lat.val(userLat);
				lng.val(userLng);
				new map.init();
			}

		});

		geocodeRemoveLocation.on('click', function() {
			geocodeSearch.val('');
		});

	});

}(jQuery));