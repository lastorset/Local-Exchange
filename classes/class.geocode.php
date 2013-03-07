<?php

include_once("class.listing.php");
include_once("class.member.php");

/** Signals that we're over our daily quota and must stop for today. */
class HaltGeocodingException extends Exception { }

/** Signals that the user likely has entered an invalid address. */
class AddressException extends Exception { }

/** Contains functions that transmit geocoding requests to Google and process
    the result. */
class cGeocode {
	static $url_template = "https://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false";

	/** User-visible string that identifies our geocoding provider. */
	static function GeocodingProvider() {
		return "Google Maps";
	}

	/** Administrator-visible URL for obtaining an API key. */
	static function GeocodingProviderAPIRequest() {
		return "https://developers.google.com/maps/documentation/javascript/tutorial#api_key";
	}

	static function OnlyZero($string) {
		$len = mb_strlen($string);

		if ($len === 0)
			return false;

		for ($i = 0; $i < $len; $i++)
			if ($string[$i] != '0')
				return false;

		return true;
	}

	static function Geocode(array $address_components) {
		if (!function_exists('http_get'))
			throw new Exception("pecl_http extension with libcurl required");

		// Input validation
		if (count($address_components) == 0)
			throw new Exception("Need an address in order to geocode");

		// Build a GET request for the geocoding server
		/* Skip postcodes that have only zeros, by failing if at least one
		   address component is all zero. */
		foreach ($address_components as $component)
			if (self::OnlyZero($component))
				throw new AddressException("Addresses with zero (\"$component\") are not geocoded");
		$address = implode(",", $address_components);

		// su = safe string for URL
		$su_address = urlencode($address);
		$su_geocode_request = sprintf(self::$url_template, $su_address);

		// Send request
		$response = http_parse_message(http_get($su_geocode_request, array('timeout' => 15)));

		// TODO Authentication error with incorrect key (wrong domain, invalid key)
		if (!$response)
			throw new Exception("Could not connect to geocoding server");
		else if ($response->responseCode != 200)
			throw new Exception("HTTP error; response code was ". $response->responseCode);

		return self::ProcessGeocode($su_person['person_id'], $response->body);
	}

	/** @return an array with the latitude and the longitude.
	 *  @throws HaltGeocodingException if the daily quota was exceeded, AddressException
	 *          if the address is suspected malformed, or Exception on other errors. */
	static function ProcessGeocode($id, $response) {
		$json = json_decode($response);
		$result = $json->status;

		if ($json->status == "ZERO_RESULTS")
			throw new AddressException("No results were found");
		else if ($json->status == "OVER_QUERY_LIMIT")
			throw new HaltGeocodingException("Quota exceeded. Please abort geocoding");
		else if ($json->status == "REQUEST_DENIED" || $json->status == "INVALID_REQUEST")
			throw new Exception("Invalid request or request denied");
		else if ($json->status != "OK")
			throw new Exception("Unknown error: \"$json->status\"");

		if (count($json->results) == 1) {
			$coord = $json->results[0]->geometry->location;
			$lat = $coord->lat;
			$lng = $coord->lng;
			return array($lat, $lng);
		} else
			throw new AddressException("Partial matches not supported");
	}

	static function UserMap($coordinates) {
		if (!is_array($coordinates) ||
			!is_numeric($coordinates[0]) || !is_numeric($coordinates[1]))
			return "<!-- No coordinates exist for member -->";

		$latitude = $coordinates[0];
		$longitude = $coordinates[1];
		$map_api_key = urlencode(MAP_API_KEY);

		return <<<HTML
			<div id="map_canvas"></div>
			<script type="text/javascript" src="ajax/lib/replace_alert.js"></script>
			<script type="text/javascript"
				src="http://maps.googleapis.com/maps/api/js?key=$map_api_key&sensor=false">
			</script>
			<script type="text/javascript">
				var map;

				function initializeMap() {
					var myOptions = {
						center: new google.maps.LatLng($latitude, $longitude),
						zoom: 14,
						mapTypeId: google.maps.MapTypeId.ROADMAP
					};
					map = new google.maps.Map(document.getElementById("map_canvas"),
							myOptions);
					var marker = new google.maps.Marker({
						position: new google.maps.LatLng($latitude, $longitude),
						map: map,
					});
				}

				window.addEventListener('DOMContentLoaded', initializeMap, false);
			</script>
HTML;
	}

	static function GenerateMap() {
		global $cUser, $_;
		// Used to influence caching by giving the private map its own cache key
		$is_logged_on = $cUser->IsLoggedOn() ? "?logged_on" : "";
		$map_api_key = urlencode(MAP_API_KEY);
		$center = self::ParseCoordinates(MAP_CENTER);
		$zoom = MAP_ZOOM;
		if (is_null($center) || !is_numeric($zoom)) {
			$center = array(0, 0);
			$zoom = 1;
		}

		return <<<HTML
			<div id="map_canvas" style="width:100%;"></div>
			<script type="text/javascript" src="ajax/lib/replace_alert.js"></script>
			<script type="text/javascript"
				src="http://maps.googleapis.com/maps/api/js?key=$map_api_key&sensor=false">
			</script>
			<script type="text/javascript">
				var map;
				var infowindow = new google.maps.InfoWindow();

				function initializeMap() {
					var myOptions = {
						center: new google.maps.LatLng($center[0], $center[1]),
						zoom: $zoom,
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
					var url = "ajax/map.php$is_logged_on";
					markerRequest.onreadystatechange = addMarkers;
					markerRequest.open("GET", url, true);
					markerRequest.send();
				}

				function addMarkers() {
					if (markerRequest.readyState === 4) {
						if (markerRequest.status === 200) {
							// TODO Use a compatibility shim (such as jQuery) for JSON.parse
							var markers = JSON.parse(markerRequest.responseText);
							for (var i = 0; i < markers.length; i++) {
								var marker = new google.maps.Marker({
									position: new google.maps.LatLng(markers[i].latitude, markers[i].longitude),
									map: map,
								});
								var text;
								if (markers[i].name)
									// TODO Some way to get internationalized text
									text = "<h1>"+ markers[i].name +"</h1>"
										 + "<a href=member_summary.php?member_id="+ markers[i].id +">"+ "{$_("See offers and wants")}" +"</a>";
									// TODO Display listings directly in info window
								else
									text = "{$_("Log in to see offers, wants and precise location")}";
								// TODO More lightweight method? (without a separate function for each marker)
								google.maps.event.addListener(marker, 'click', (function(marker, text) {
									return function() {
										infowindow.setContent(text);
										infowindow.open(map,marker);
									}
								})(marker, text));
							}
						} else {
							var failedP = document.createElement("p");
							failedP.innerHTML = "Failed to load map. <!-- HTTP "+ markerRequest.status +" -->";
							var map_canvas = document.getElementById("map_canvas");
							map_canvas.parentElement.replaceChild(failedP, map_canvas);
						}
					} // else: not ready
				}

				// Since this is used for the front page map, let's make sure even old IE gets it
				if (window.addEventListener) {
					window.addEventListener('DOMContentLoaded', initializeMap, false);
				} else if (window.attachEvent)  { // IE<9
					window.attachEvent('DOMContentLoaded', initializeMap);
				}
			</script>
HTML;
	}

	static function AllMarkers($fetch_listings = /* true */ false /* TODO until listings can be shown properly and anonymously */) {
		global $cDB, $cUser;

		if ($cUser->IsLoggedOn())
			header("Cache-control: max-age=3600, private");
		else
			header("Cache-control: max-age=3600, public");

		function getListings(&$listing_group) {
			$listings = array();
			if ($listing_group->listing)
				foreach ($listing_group->listing as $l) {
					$listing = array(
						'title' => $l->title,
						'url' => $l->GetURL(),
					);
					array_push($listings, $listing);
				}
			return $listings;
		}

		/** Generates a hash-based coefficient between -1 and 1 to use when obfuscating location
			We hash on a combination of the member id and the database password.
			The member id makes each location uniquely obfuscated; the database password
			makes the obfuscation unique for each Local Exchange installation. */
		function member_id_obfuscate($member_id) {
			$prime = 31;
			$result = 1;
			foreach(array($member_id, DATABASE_PASSWORD) as $string)
				for ($i = 0; $i < strlen($string); $i++)
					$result = $prime * $result + ord($string[$i]);
			return ($result % 226) / 113 - 1;
		}

		$c = get_defined_constants();
		$result = $cDB->Query(<<<SQL
			SELECT person_id,
				member_id,
				first_name,
				mid_name,
				last_name,
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
		// TODO Lazy loading of listings (such as when hovering before clicking on an infowindow). With that in place, the $fetch_listings parameter becomes unnecessary.
		while($marker = mysql_fetch_array($result))
		{
			if ($fetch_listings) {
				$listing_group = new cListingGroup(OFFER_LISTING_CODE);
				$listing_group->LoadListingGroup(null, null, $marker['member_id'], null, false);
				// TODO Cleaner way of getting out the listings, including getting both types in one call
				$listings_offered = getListings($listing_group);
				$listing_group = new cListingGroup(WANT_LISTING_CODE);
				$listing_group->LoadListingGroup(null, null, $marker['member_id'], null, false);
				$listings_wanted = getListings($listing_group);
				$listings = array('offered' => $listings_offered, 'wanted' => $listings_wanted);
			} else
				$listings = null;

			if ($cUser->IsLoggedOn())
				array_push($out, array(
					'id' => $marker['member_id'],
					'name' => $marker['first_name'] ." ".
							  $marker['mid_name'] ." ".
							  $marker['last_name'] ." ",
					'listings' => $listings,
					'latitude' => $marker['latitude'],
					'longitude' => $marker['longitude']
					));
			else
			{
				$obf = member_id_obfuscate($marker['member_id']);
				array_push($out, array(
					'id' => $marker['member_id'],
					'name' => null,
					'listings' => $listings,
					'latitude' => $marker['latitude'] + $obf * 0.005,
					'longitude' => $marker['longitude'] + $obf * 0.005
					));
			}
		}
		return $out;
	}

	/** Parses a coordinate string (like "(59.123, 12.30)") into an array such as "array(59.123, 12.30)".
	 *  @returns an array with two items, or NULL if the coordinate cannot be parsed. */
	static function ParseCoordinates($string) {
		$coords = sscanf($string, "(%f, %f)");
		if ($coords == -1)
			return null;
		return $coords;
	}

	/** Select all persons that can be geocoded, or that are missing geocoding data. */
	static function GeocodablePersons($only_missing=false) {
		global $cDB;
		$c = get_defined_constants();
		$result = $cDB->Query(<<<SQL
			SELECT DISTINCT person_id
			FROM {$c['DATABASE_PERSONS']} NATURAL JOIN {$c['DATABASE_MEMBERS']}
			WHERE
				status = '{$c['ACTIVE']}'
				AND address_post_code NOT LIKE "0000aa" -- Special accounts
SQL
				. ($only_missing ? "\n AND (`latitude` IS NULL OR `longitude` IS NULL)" : "")
		);
		$people = array();
		while($person_row = mysql_fetch_array($result))
		{
			$person = new cPerson();
			$person->LoadPerson($person_row['person_id']);
			array_push($people, $person);
		}
		return $people;
	}

	static function MissingPersons() {
		return self::GeocodablePersons(true);
	}
}
