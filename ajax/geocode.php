<?php
/** @file Procedures for geocoding the members that need it */
include_once("ajax.inc");
include_once("../classes/class.geocode.php");
include_once("../classes/class.person.php");

include_once("../includes/inc.global.php");

class GeocodingError {
	var $member_id;
	var $message;
	function __construct($person, $message) {
		$this->member_id = $person->member_id;
		$this->message = $message;
	}
}

if (isset($_GET['progress']))
{
	// Return all coordinates found so far
	echo json_encode(cGeocode::AllMarkers(false), JSON_NUMERIC_CHECK);
	exit();
}

// TODO Figure out how to report that PHP has timed out the script.

if (!$cUser->HasLevel(1))
	die("ERROR Must have administrator permissions\nFINISHED");

// Get list of addresses that need geocoding
$people = cGeocode::MissingPersons();

$geocode_count = 0;
$address_errors = array();
$other_errors = array();
foreach ($people as $person) {
	try {
		$person->Geocode();
		$person->SavePerson();
	} catch (AddressException $e) {
		echo "ERROR $person->member_id {$e->getMessage()}\n";
		array_push($address_errors, $e->getMessage());
	} catch (HaltGeocodingException $e) {
		print "ERROR Daily quota exceeded. Geocoding aborted\n";
		break;
	} catch (Exception $e) {
		echo "ERROR $person->member_id {$e->getMessage()}\n";
		array_push($other_errors, $e->getMessage());
	}

	$geocode_count++;

	// Throttle requests by waiting 300ms to prevent Google from blocking us
	usleep(300000);
}

print "FINISHED Geocoding completed. $geocode_count responses processed.";

?>
