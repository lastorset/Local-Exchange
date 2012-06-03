<?php
if (!function_exists('http_get'))
	die("Error: pecl_http extension with libcurl required");

include_once("includes/inc.global.php");

// Make absolutely sure error reporting is invisible
ini_set('display_errors', 0); 
header('Content-type: application/json');

if (!$cUser->HasLevel(1))
	die("ERROR Must have administrator permissions");

// 1: Get list of addresses that need geocoding

$c = get_defined_constants();
// us_ = unsafe strings
$us_result = $cDB->Query(<<<SQL
	SELECT person_id,
		address_street1,
		address_street2,
		address_city,
		address_state_code,
		address_post_code,
		address_country
	FROM {$c['DATABASE_PERSONS']} NATURAL JOIN {$c['DATABASE_MEMBERS']}
	WHERE
		(`latitude` IS NULL OR `longitude` IS NULL)
		AND status = '{$c['ACTIVE']}'
		AND address_post_code NOT LIKE "0000aa" -- Special accounts
SQL
);

$url_template = "https://maps.googleapis.com/maps/api/geocode/json?address=%s,%s,%s,%s,%s,%s&sensor=false";

$state = new cStateList;
$state_list = $state->MakeStateArray();
$state_list[0]="---";

$geocode_count = 0;
while($us_person = mysql_fetch_array($us_result))
{
	// Skip postcodes that have only zeros
	if (only_zero($us_person['address_post_code']))
		continue;

	// URL-encode address
	// su = safe string for URL
	$su_person = array();
	foreach ($us_person as $i => $us)
		$su_person[$i] = urlencode($us);

	// 2: Send requests to Google. Will PHP automatically throttle the number of connections? Otherwise, throttle yourself.
	$geocode_request = sprintf($url_template,
		$su_person['address_street1'],
		$su_person['address_street2'],
		$su_person['address_city'],
		$state_list[$su_person['address_state_code']],
		$su_person['address_post_code'],
		$su_person['address_country']
	);

	$response = http_parse_message(http_get($geocode_request));
	if ($response->responseCode != 200) {
		print "ERROR HTTP error; response code was ". $response->responseCode ."\n";
	}

	$continue = process_geocode($su_person['person_id'], $response->body, $status, $msg);
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

print "FINISHED Geocoding completed. $geocode_count responses processed.";

// 2.1: Callback function stores in database. If no coordinate can be found, mark as invalid in database.
// 2.2: If Google says that the daily quota is exceeded, signal to abort.
// 2.3: If Google says that the match is partial or otherwise returns multiple matches, try to use the postcode type.

function only_zero($string) {
	$len = mb_strlen($string);
	for ($i = 0; $i < $len; $i++)
		if ($string[$i] != '0')
			return false;

	return true;
}

/** @return true if geocoding can continue, or false if OVER_QUERY_LIMIT was received. */
function process_geocode($id, $response, &$result, &$msg) {
	global $cDB;

	$json = json_decode($response);
	$result = $json->status;

	if ($json->status == "ZERO_RESULTS") {
		$msg = "No results were found";
		return true;
	}
	else if ($json->status == "OVER_QUERY_LIMIT") {
		$msg = "Daily quota exceeded. Geocoding aborted";
		return false;
	}
	else if ($json->status == "REQUEST_DENIED" || $json->status == "INVALID_REQUEST") {
		$msg = "Invalid request or request denied";
		return true;
	}
	else if ($json->status != "OK") {
		$msg = "Unknown error";
		return true;
	}

	if (count($json->results) == 1) {
		$coord = $json->results[0]->geometry->location;
		$lat = $coord->lat;
		$lng = $coord->lng;
		$query = "UPDATE person SET latitude = $lat, longitude = $lng WHERE person_id = $id";

		if ($cDB->query($query))
			return true;
		else {
			$result = "ERROR";
			$msg = "Could not update database";
		}
	}
	else {
		$result = "ERROR";
		$msg = "Partial matches not supported";
	}

	return true;
}

?>
