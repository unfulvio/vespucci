(function ( $ ) {

	"use strict";

	/**
	 * Location box tabs.
	 * Adds jQuery UI Tabs to the location box.
	 */
	$(function() {

		var tabDiv = $( "#vespucci-location-tabs"),
			tabDivLi = $(tabDiv).find('li');

		$(tabDiv).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
		$(tabDivLi).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );

	});

	/**
	 * LeafletJS Map interactions.
	 * Map controls, geocoding, geosearch, etc.
	 */
	$(function() {

		// current location coordinates (hidden fields)
		var lat = $('#vespucci-location-lat'),
			lng = $('#vespucci-location-lng');

		if ( typeof( lat.val() ) === 'undefined' || typeof( lng.val() ) === 'undefined' )
			return false;

		// current location address
		var LocationAddress = {
			street 	: $('#vespucci-location-address-street'),
			area	: $('#vespucci-location-address-area'),
			city 	: $('#vespucci-location-address-city'),
			district: $('#vespucci-location-address-district'),
			state 	: $('#vespucci-location-address-state'),
			postcode: $('#vespucci-location-address-postcode'),
			country : $('#vespucci-location-address-country')
		};

		// international countrycode for current location (hidden field)
		var CountryCode = $('#vespucci-location-code');

		// user set zoom levels
		var LocationZoom = {
				default : $('#vespucci-map-zoom-default'),
				min 	: $('#vespucci-map-zoom-min'),
				max 	: $('#vespucci-map-zoom-max')
		};

		// Leaflet Map
		var map = new L.Map('vespucci-location-map', {
				center: new L.LatLng(
					lat.val(),
					lng.val()
				),
				zoom : parseInt( LocationZoom.default.val(), 10 )
			}),
			googleRoadmap	= new L.Google('ROADMAP'),
			googleSatellite = new L.Google('SATELLITE'),
			googleTerrain 	= new L.Google('TERRAIN'),
			defaultMapType = $('#vespucci-google-map-type');

		// use Google as default layer
		if ( defaultMapType.val() === 'terrain' ) {
			map.addLayer( googleTerrain );
		}  else if ( defaultMapType.val() === 'satellite' ) {
			map.addLayer( googleSatellite );
		} else {
			map.addLayer( googleRoadmap );
		}
		// add option to use other Google Maps map types
		map.addControl( new L.Control.Layers({
			'Roadmap' 	: googleRoadmap,
			'Terrain'	: googleTerrain,
			'Satellite'	: googleSatellite
		}, {}));

		var marker;
		// populate marker
		if ( lat.val() !== '0' && lng.val() !== '0' ) {
			var point = {
				lat : lat.val(),
				lng : lng.val()
			};
			LocationMarker( point );
		}

		// text field where the user sets the location name
		var LocationName = $('#vespucci-location-title');

		// send a GeoCode request when hitting enter on location name
		LocationName.keydown( function(key) {
			if ( key.which === 13 ) {
				// do not submit form
				key.preventDefault();
				if ( LocationName.val().length > 1 ) {
					GeoCode( LocationName.val() );
					map.setZoom( 14 );
				}
			}
		});

		// alternatively send a GeoCode request when clicking on the GeoCode UI button
		$('#vespucci-geocode-request').on( 'click', function() {
			if ( LocationName.val().length > 1 ) {
				GeoCode( LocationName.val() );
			}
		} );

		// run a geocode request based on user coordinates
		$('#vespucci-user-location').on('click', function() {
			map.locate({
				setView: true,
				maxZoom: 16
			});
		});

		/**
		 * GeoCode Address.
		 * Queries OpenStreetMap Nominatim to geocode a given address.
		 *
		 * @param	string	an address in string format to geocode from
		 */
		function GeoCode( string ) {

			// get first result from Nominatim (OpenStreetMap geocoding service)
			$.getJSON( "http://nominatim.openstreetmap.org/search.php?q=" + string + "&addressdetails=1&format=jsonv2", function( data ) {

				// get first result only
				var result = data[0];

				// bail out early if no results
				if ( typeof( result ) === 'undefined' )
					return;

				if ( typeof( result['display_name'] ) !== 'undefined' ) {
					// change the location title
					LocationName.val( result['display_name'] );
				}

				var coordinates = {
					lat : parseFloat( result['lat'] ),
					lng : parseFloat( result['lon'] )
				};
				// move the marker to the resulting coordinates
				LocationMarker( coordinates );

				// clear address fields
				$.each( LocationAddress, function() {
					this.val('');
				});

				// update address fields
				if ( typeof( result['address'] ) !== 'undefined' ) {
					UpdateAddress( result['address'] );
				}

				return result;
			});

		}

		/**
		 * Reverse GeoCode query.
		 * Queries OpenStreetMap Nominatim and returns location data from given point coordinates.
		 *
		 * @param 	point valid latlng coordinates
		 */
		function ReverseGeoCode( point ) {

			// bail out early if coordinates are invalid
			if ( isNaN( point['lat'] || isNaN( point['lng'] ) ) )
				return;

			// get first result from Nominatim (OpenStreetMap geocoding service)
			$.getJSON( " http://nominatim.openstreetmap.org/reverse?lat=" + point['lat'] + "&lon=" + point['lng'] + "addressdetails=1&format=jsonv2", function( result ) {

				if ( typeof( result['display_name'] !== 'undefined' ) ) {
					// change the location title
					LocationName.val( result['display_name'] );
				}

				// clear address fields
				$.each( LocationAddress, function() {
					this.val('');
				});

				// update address fields
				if ( typeof( result['address'] ) !== 'undefined' ) {
					UpdateAddress( result['address'] );
				}

				return result;
			});

		}

		/**
		 * Update address fields.
		 *
		 * @param	address	an object with address data
		 */
		function UpdateAddress( QueriedAddress ) {

			// update address fields
			$.each( QueriedAddress, function( key, value ) {
				switch (key) {
					case 'house_number' :
						LocationAddress.street.val( value + ' ' );
						break;
					case 'road' :
						LocationAddress.street.val( LocationAddress.street.val() + value );
						break;
					case 'neighbourhood' :
						LocationAddress.area.val( value + ' ' );
						break;
					case 'suburb' :
						LocationAddress.area.val( LocationAddress.area.val() + value );
						break;
					case 'city' :
					case 'town' :
					case 'village' :
					case 'hamlet' :
						LocationAddress.city.val( value );
						break;
					case 'county' :
					case 'province' :
						LocationAddress.district.val( value );
						break;
					case 'state' :
						LocationAddress.state.val( value );
						break;
					case 'postcode' :
						LocationAddress.postcode.val( value );
						break;
					case 'country' :
						LocationAddress.country.val( value );
						break;
					case 'countrycode' :
					case 'country_code' :
						CountryCode.val( value );
						break;
				}
			});

		}

		/**
		 * Location marker.
		 * Creates or moves a marker on the map for the current location.
		 *
		 * @param	coordinates	lat,lng point coordinates
		 */
		function LocationMarker( point ) {

			// bail out early if coordinates are invalid
			if ( isNaN( point['lat'] ) || isNaN( point['lng'] ) )
				return;

			var latlng = [point['lat'],point['lng']];

			if ( typeof(marker) === 'undefined' ) {
				// set new marker
				marker = new L.marker( latlng, {
					draggable: true
				});
				marker.addTo(map);
			} else {
				// update existing
				marker.setLatLng( latlng );
			}

			// center map to marker position
			map.setView( latlng, map.getZoom() );

			// set current location coordinates input fields
			lat.val(point['lat']);
			lng.val(point['lng']);

		}

		// Map events

		map.on( 'mouseover', function() {
			// check if there's already a marker set
			if ( typeof(marker) !== 'undefined' ) {
				// update location coordinates if marker is being dragged
				marker.on('dragend', function(e) {
					var point = marker.getLatLng();
					lat.val(point['lat']);
					lng.val(point['lng']);
					ReverseGeoCode( point );
				});
			}
		});

		map.on( 'click', function(e) {
			// create or move the marker on a clicked point on map
			var point = {
				lat : parseFloat( e.latlng.lat ),
				lng : parseFloat( e.latlng.lng )
			};
			LocationMarker( point );
			ReverseGeoCode( point );
		});

		map.on( 'zoomend', function() {
			// Updates default zoom level setting with current map zoom level
			var zoomLevel = parseInt( this.getZoom(), 10 );
			LocationZoom.default.val(zoomLevel);
		});

		map.on( 'locationfound', function(e) {
			// callback on success in locating user's current position
			var point = {
				lat : parseFloat( e.latlng.lat ),
				lng : parseFloat( e.latlng.lng )
			};
			LocationMarker( point );
			ReverseGeoCode( point );
		});

		map.on( 'locationerror', function(e) {
			// callback if there were errors in locating the user
			alert(e.message);
		});

	});

}(jQuery));