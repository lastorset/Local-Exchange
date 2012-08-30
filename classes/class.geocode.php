<?php

/** Contains functions that transmit geocoding requests to Google and process
    the result. */
class cGeocode {
	static $url_template = "https://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false";

	static function only_zero($string) {
		$len = mb_strlen($string);
		for ($i = 0; $i < $len; $i++)
			if ($string[$i] != '0')
				return false;

		return true;
	}

	static function Geocode(array $address_components) {
		// Input validation
		if (count($address_components) == 0)
			throw new Exception("Need an address in order to geocode");

		// Get a lits of states to use when geocoding
		$state = new cStateList;
		$state_list = $state->MakeStateArray();
		$state_list[0]="---";

		// Build a GET request for the geocoding server
		$first = false;
		$address = "";
		/* Skip postcodes that have only zeros, by failing if at least one
		   address component is all zero. */
		foreach ($address_components as $component)
			if (only_zero($component))
				throw new Exception("Addresses with zero are not geocoded");
			else
				if ($first)
					$address .= $component;
				else
					$address .= ",". $component;

		// su = safe string for URL
		$su_address = urlencode($address);
		$su_geocode_request = sprintf(self::$url_template, $su_address);

		// Send request
		$response = http_parse_message(http_get($geocode_request));

		if ($response->responseCode != 200) 
			throw new Exception("ERROR HTTP error; response code was ". $response->responseCode);

		ProcessGeocode($su_person['person_id'], $response->body, $status, $msg);
	if ($status != "OK") {
		error_log("Error processing request $geocode_request for person {$su_person['id']}: $msg");
		print "ERROR ". $su_person['person_id'] ." ". $msg ."\n";
		if (!$continue)
			break;
	}
	else {
		print "OK ". $su_person['person_id'] ."\n";
	}

	$geocode_count++;

	// Throttle requests by waiting 200ms to prevent Google from blocking us
	usleep(200000);
	}

	static function GenerateMap() {
		return <<<HTML
			<div id="map_canvas" style="width:100%;"></div>
			<script type="text/javascript"
				src="http://maps.googleapis.com/maps/api/js?key=AIzaSyA5n7eMkwocdSFXiGrPNJPz32CLxzDYpGk&sensor=false">
			</script>
			<script type="text/javascript">
				var map;
				var infowindow = new google.maps.InfoWindow();

				function initializeMap() {
					var myOptions = {
						center: new google.maps.LatLng(59.931624, 10.741882),
						zoom: 12,
						mapTypeId: google.maps.MapTypeId.ROADMAP
					};
					map = new google.maps.Map(document.getElementById("map_canvas"),
							myOptions);
					loadMarkers();

					google.maps.event.addListener(map, 'click', function() {
							infowindow.close();
					});
				}

				var markerRequest = new XMLHttpRequest();

				function loadMarkers() {
					var url = "http://lex.localhost/ajax/map.php";
					markerRequest.onload = addMarkers;
					// TODO How wide is browser support for onload?
					// TODO Also listen for failure
					markerRequest.open("GET", url, true); 
					markerRequest.send();
				}
				
				function addMarkers() {
					// TODO Use a compatibility shim (such as jQuery) for JSON.parse
					var markers = JSON.parse(markerRequest.responseText);
					for (var i = 0; i < markers.length; i++) {
						var marker = new google.maps.Marker({
							position: new google.maps.LatLng(markers[i].latitude, markers[i].longitude),
							map: map,
							title:"id = " + markers[i].id
						});
						var text = "<h1>"+ markers[i].id +"</h1>";
						// TODO More lightweight method? (without a separate function for each marker)
						google.maps.event.addListener(marker, 'click', (function(marker, text) {
							return function() {
								infowindow.content = text;
								infowindow.open(map,marker);
							}
						})(marker, text));
					}
				}
				
				// TODO: More robust mechanism for onload. If more than one script sets
				// onload, one of them will break.
				window.onload = initializeMap;
			</script>
HTML;
	}

	static function AllMarkers() {
		global $cDB;
		// TODO When user is not logged in, return fuzzy data.
		$c = get_defined_constants();
		$result = $cDB->Query(<<<SQL
			SELECT person_id,
				latitude,
				longitude
			FROM {$c['DATABASE_PERSONS']} NATURAL JOIN {$c['DATABASE_MEMBERS']}
			WHERE
				`latitude` IS NOT NULL AND `longitude` IS NOT NULL
				AND
				status = '{$c['ACTIVE']}'
SQL
		);
		$out = array();
		while($marker = mysql_fetch_array($result))
			array_push($out, array(
				'id' => $marker['person_id'],
				'latitude' => $marker['latitude'],
				'longitude' => $marker['longitude']
				));
		return $out;
	}
}
