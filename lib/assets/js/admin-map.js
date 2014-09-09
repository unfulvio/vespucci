(function ( $ ) {

	"use strict";

	$(function() {

		var tabDiv = $( "#vespucci-location-tabs"),
			tabDivLi = $(tabDiv).find('li');

		$(tabDiv).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
		$(tabDivLi).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );

	});

	$(function() {

		// coordinates (hidden)
		var lat = $('#vespucci-location-lat'),
			lng = $('#vespucci-location-lng');

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
			div: '#vespucci-location-map',
			lat: lat.val(),
			lng: lng.val()
		});

	});

}(jQuery));