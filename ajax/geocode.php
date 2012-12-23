<?php
/** @file Procedures for geocoding the members that need it */
include_once("ajax.inc");
include_once("../classes/class.geocode.php");
include_once("../classes/class.person.php");

include_once("../includes/inc.global.php");

if (isset($_GET['progress']))
{
	// Return all coordinates found so far
	echo json_encode(cGeocode::AllMarkers(false), JSON_NUMERIC_CHECK);
	exit();
}

// TODO Figure out how to report that PHP has timed out the script.

$general_errors = array();
$address_errors = array();
$other_errors = array();
if (!$cUser->HasLevel(1)) {
	$general_errors[] = array('message' => "Must have administrator permissions");
	print makeResponse();
	exit();
}

// Get list of addresses that need geocoding
$people = cGeocode::MissingPersons();

$geocode_count = 0;
foreach ($people as $person) {
	try {
		$person->Geocode();
		$person->SavePerson();
	} catch (AddressException $e) {
		$address_errors[] = array('member' => $person->member_id, 'message' => $e->getMessage());
	} catch (HaltGeocodingException $e) {
		$general_errors[] = array('message' => "Quota exceeded. Geocoding aborted");
		break;
	} catch (Exception $e) {
		$other_errors[] = array('member' => $person->member_id, 'message' => $e->getMessage());
	}

	$geocode_count++;

	// Throttle requests by waiting 300ms to prevent Google from blocking us
	usleep(300000);
}

function makeResponse() {
	global $geocode_count, $address_errors, $other_errors, $general_errors;
	return json_encode(array(
		'processedCount' => $geocode_count,
		'addressErrors' => $address_errors,
		'otherErrors' => $other_errors,
		'generalErrors' => $general_errors
	));
}

print makeResponse();
?>
