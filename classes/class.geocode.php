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

	/**
	 * Generate a map for a user's profile.
	 *
	 * @param $member cMember the member whose coordinates to show.
	 * @return string the HTML code for the map, or an HTML comment indicating that one is not drawn.
	 */
	static function UserMap($member) {
		$has_coordinates = false;
		$persons = array();
		foreach($member->person as $person) {
			$coordinates = $person->GetCoordinates();
			if ($coordinates) {
				$has_coordinates = true;
				$persons[] = array(
					'name' => $person->Name(),
					'latitude' => $coordinates[0],
					'longitude' => $coordinates[1],
				);
			}
		}
		if ($has_coordinates === false)
			return "<!-- No coordinates exist for member -->";

		// Set a good bounding box for the first marker, so that the zoom isn't excessive
		$bbox = self::RadialBoundingBox($persons[0]['latitude'], $persons[0]['longitude'], 0.5 /* km */);

		$listings = $member->FetchListings();

		$member_data = array(
			'persons' => $persons,
			'listing_count' => $listings['count'],
			'karma' => $member->GetKarma(),
			'minimum_bounding_box' => $bbox,
		);
		$member_data_string = json_encode($member_data, JSON_NUMERIC_CHECK);
		$map_api_key = urlencode(MAP_API_KEY);
		$c = get_defined_constants();

		return <<<HTML
			<div id="map_canvas"></div>
			<script type="text/javascript" src="ajax/lib/replace_alert.js"></script>
			<script type="text/javascript"
				src="http://maps.googleapis.com/maps/api/js?key=$map_api_key&sensor=false">
			</script>
			<script type="text/javascript" src="ajax/lib/maps.js"></script>
			<script type="text/javascript">
				var map;
				var infowindow = new google.maps.InfoWindow();
				var member = $member_data_string;

				function initializeMap() {
					var p0 = member.persons[0];
					var myOptions = {
						center: new google.maps.LatLng(p0.latitude, p0.longitude),
						zoom: 14,
						mapTypeId: google.maps.MapTypeId.ROADMAP
					};
					map = new google.maps.Map(document.getElementById("map_canvas"),
							myOptions);

					var b = member.minimum_bounding_box;
					var bounds = new google.maps.LatLngBounds(
						new google.maps.LatLng(b[0][0], b[0][1]),
						new google.maps.LatLng(b[1][0], b[1][1])
					);
					for (var i = 1; i < member.persons.length; i++)
						bounds.extend(new google.maps.LatLng(member.persons[i].latitude, member.persons[i].longitude));

					map.fitBounds(bounds);
					createMarkers(map, member, {$c['GAME_MECHANICS']}, infowindow)
					google.maps.event.addListener(map, 'click', function() {
							infowindow.close();
					});
				}

				window.addEventListener('DOMContentLoaded', initializeMap, false);
			</script>
HTML;
	}

	static function GenerateMap() {
		global $cUser, $_, $replace_tags;
		if($cUser->IsLoggedOn()) {
			// Used to influence caching by giving the private map its own cache key
			$is_logged_on = "?logged_on";
			$center = $cUser->person[0]->coordinates;
			if (!is_array($center))
				// Some people had errors in geocoding
				$center = self::ParseCoordinates(MAP_CENTER);
		} else {
			$is_logged_on = "";
			$center = self::ParseCoordinates(MAP_CENTER);
		}
		$map_api_key = urlencode(MAP_API_KEY);
		$zoom = MAP_ZOOM;
		if (is_null($center) || !is_numeric($zoom)) {
			$center = array(0, 0);
			$zoom = 1;
		}

		$out = <<<HTML
			<div id="map_canvas" style="width:100%;"></div>
			<div id="map_legend">
				<!-- Translation hint: Legend for front page map -->
				<h3>{$_("Legend:")}</h3>
HTML;
		$out .= (GAME_MECHANICS ?
				"<img src='images/marker_gold.png'>". replace_tags(
					_('Member with <a>experience</a>'), array('a' => 'a href=karma_explanation.php')
				) : "");
		$out .= <<<HTML
				<img src="images/marker.png">{$_("Member")}
				<img src="images/marker_gray.png">{$_("Member without listings")}
			</div>
			<script type="text/javascript" src="ajax/lib/replace_alert.js"></script>
			<script type="text/javascript"
				src="http://maps.googleapis.com/maps/api/js?key=$map_api_key&sensor=false">
			</script>
			<script type="text/javascript" src="ajax/lib/maps.js"></script>
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
					loadMembers();

					google.maps.event.addListener(map, 'click', function() {
							infowindow.close();
					});
				}

				var memberRequest = new XMLHttpRequest();

				function loadMembers() {
					var url = "ajax/map.php$is_logged_on";
					memberRequest.onreadystatechange = addMarkers;
					memberRequest.open("GET", url, true);
					memberRequest.send();
				}

				function addMarkers() {
					if (memberRequest.readyState === 4) {
						if (memberRequest.status === 200) {
							// TODO Use a compatibility shim (such as jQuery) for JSON.parse
							var received = JSON.parse(memberRequest.responseText);
							var flags = received.flags;
							var persons = received.persons;
							for (var i = 0; i < persons.length; i++) {
								var text;
								if (persons[i].name)
									// TODO Some way to get internationalized text
									text = "<h1>"+ persons[i].name +"</h1>"
										 + "<a href=member_summary.php?member_id="+ persons[i].id +">"+ "{$_("See offers and wants")}" +"</a>";
									// TODO Display listings directly in info window
								else
									text = "{$_("Log in to see offers, wants and precise location")}";
								createMarker(map, persons[i], flags.GAME_MECHANICS, text, infowindow);
							}
						} else {
							var failedP = document.createElement("p");
							failedP.innerHTML = "Failed to load map. <!-- HTTP "+ memberRequest.status +" -->";
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
		return $out;
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
			makes the obfuscation unique for each Local Exchange installation.

		    @param spice prime number to use as basis. Used if you want several obfuscations, such as one for each
		                coordinate.
		 */
		function member_id_obfuscate($member_id, $spice = 113) {
			$prime = 31;
			$result = 1;
			foreach(array($member_id, DATABASE_PASSWORD) as $string)
				for ($i = 0; $i < strlen($string); $i++)
					$result = $prime * $result + ord($string[$i]);
			return fmod($result, $spice * 2) / $spice - 1;
		}

		$result = $cDB->Query("
			SELECT person_id,
				m.member_id,
				first_name,
				mid_name,
				last_name,
				latitude,
				longitude,
				COUNT(listing_id) AS listing_count
				". (GAME_MECHANICS ? ", karma" : "") ."
			FROM
				". DATABASE_PERSONS ." p
				NATURAL JOIN ". DATABASE_MEMBERS ." m
				LEFT JOIN ". DATABASE_LISTINGS ." l ON m.member_id = l.member_id
				". (GAME_MECHANICS ? "LEFT JOIN karma k ON m.member_id = k.member_id" : "") ."
			WHERE
				`latitude` IS NOT NULL AND `longitude` IS NOT NULL
				AND
				m.status = '". ACTIVE ."'
			GROUP BY
				person_id;
		");

		// Radius of Earth
		$R = 6371;
		$obf_distance = .5; // km

		$out = array(
			"flags" => array("GAME_MECHANICS" => GAME_MECHANICS),
			"persons" => array()
		);
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
				array_push($out['persons'], array(
					'id' => $marker['member_id'],
					'name' => $marker['first_name'] ." ".
							  $marker['mid_name'] ." ".
							  $marker['last_name'] ." ",
					'listings' => $listings,
					'latitude' => $marker['latitude'],
					'longitude' => $marker['longitude'],
					'listing_count' => $marker['listing_count'],
					'karma' => $marker['karma']
					));
			else
			{
				// Anonymous users get obfuscated coordinates
				$obf = array(
					member_id_obfuscate($marker['member_id'], 113),
					member_id_obfuscate($marker['member_id'], 157)
				);
				array_push($out['persons'], array(
					'id' => $marker['member_id'],
					'name' => null,
					'listings' => $listings,
					'latitude' => $marker['latitude'] + $obf[0] * rad2deg($obf_distance/$R),
					'longitude' => $marker['longitude'] + $obf[1] * rad2deg($obf_distance/$R/cos(deg2rad($marker['latitude']))),
					'listing_count' => $marker['listing_count'],
					'karma' => $marker['karma']
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

	/// Radius of Earth
	static $R = 6371;

	/**
	 * Finds the bounding box that will exactly fit the given radius.
	 *
	 * @param $lat number latitude.
	 * @param $lon number longitude.
	 * @param $radius number the distance in km from the coordinate to each edge.
	 *
	 * @returns number[][] the south-west and north-east points as a two-dimensional array.
	 */
	static function RadialBoundingBox($lat, $lon, $radius) {
		// first-cut bounding box (in degrees)
		$maxLat = $lat + rad2deg($radius/self::$R);
		$minLat = $lat - rad2deg($radius/self::$R);
		// compensate for degrees longitude getting smaller with increasing latitude
		$maxLon = $lon + rad2deg($radius/self::$R/cos(deg2rad($lat)));
		$minLon = $lon - rad2deg($radius/self::$R/cos(deg2rad($lat)));

		return array(array($minLat, $minLon), array($maxLat, $maxLon));
	}
}
