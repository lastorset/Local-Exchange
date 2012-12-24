<?php
/** @file Procedures for displaying LETS data on a map */
include_once("ajax.inc");
include_once("../classes/class.geocode.php");

echo json_encode(cGeocode::AllMarkers(), JSON_NUMERIC_CHECK);
?>
