<?php
// Upgrade to use geocoding

$running_upgrade_script = true;
include_once("../includes/inc.global.php");

$cUser->MustBeLevel(1);

$cDB->Query("ALTER TABLE ". DATABASE_PERSONS ."
	ADD COLUMN `latitude` DOUBLE DEFAULT NULL,
	ADD COLUMN `longitude` DOUBLE DEFAULT NULL")
	or die("Error altering member table.  Does the web user account have alter table permission?");

$cDB->Query("INSERT INTO `settings` VALUES (NULL, 'GEOCODE', '" . _("Enable geocoding and display a map") . "', 'bool', '', '', 'FALSE', '', '" . _("Geocodes members and displays them on a map. Requires an API key.") . "', 9)") or die("Error - Could not insert row into settings table.");
$cDB->Query("INSERT INTO `settings` VALUES (NULL, 'HOME_PAGE_MAP', '" . _("Display a map on the front page") . "', 'bool', '', '', 'FALSE', '', '" . _("Shows location of all members on a map on the front page. Requires geocoding.") . "', 9)") or die("Error - Could not insert row into settings table.");
$cDB->Query("INSERT INTO `settings` VALUES (NULL, 'MAP_CENTER', '" . _("Center of map") . "', 'smalltext', '', '', '(0, 0)', '', '" . _("The default center of the map. Please type coordinates on a form similar to \"(-12.3456, 78.90)\", or use <a href=geocoding_setup.php>geocoding setup</a>.") . "', 9)") or die("Error - Could not insert row into settings table.");
$cDB->Query("INSERT INTO `settings` VALUES (NULL, 'MAP_ZOOM', '" . _("Zoom level of map") . "', 'int', '', '', '1', '', '" . _("The default zoom level of the map. Enter a number, or use <a href=geocoding_setup.php>geocoding setup</a>.") . "', 9)") or die("Error - Could not insert row into settings table.");
$cDB->Query("INSERT INTO `settings` VALUES (NULL, 'MAP_API_KEY', '" . _("Google Maps API key") . "', 'smalltext', '', '', '', '40', '" . _("The API key allows you to use mapping services. Obtain one from Google Maps.") . "', 9)") or die("Error - Could not insert row into settings table.");

echo "Finished geocoding upgrade.";
?>
