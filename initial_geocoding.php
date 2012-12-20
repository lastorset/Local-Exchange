<?php

include_once("includes/inc.global.php");
$p->site_section = ADMINISTRATION;

print $p->MakePageHeader();
print $p->MakePageMenu();

$geocodable_count = count(cGeocode::GeocodablePersons());

print "<noscript>You do not have JavaScript enabled. Please <a href=ajax/geocode.php>run the script manually</a> (takes a long time and may be interrupted by PHP).</noscript>";
print <<<HTML
<p id=status style="background-color: lightgoldenrodyellow">Waiting for results…</p>
<progress max=$geocodable_count></progress>
<div id="map_canvas" style="width:100%;"></div>
<pre id=log></pre>
<script type="text/javascript"
	src="http://maps.googleapis.com/maps/api/js?key=AIzaSyA5n7eMkwocdSFXiGrPNJPz32CLxzDYpGk&sensor=false">
</script>
<script>
	// Initialize XHR
	var geocodingRequest = new XMLHttpRequest();
	var url = "http://lex.localhost/ajax/geocode.php";
	geocodingRequest.open("GET", url, true);
	geocodingRequest.send();
	geocodingRequest.onload = finish;

	// Various elements and variables
	var log = document.getElementById("log");
	var status = document.getElementById("status");
	var progress = document.getElementsByTagName("progress")[0];
	var interval;

	// Finish up
	// TODO Also listen for failure
	function finish() {
		status.innerText = "Done";
		progress.value = $geocodable_count;
		log.innerText = geocodingRequest.responseText;
		clearInterval(interval);
	}

	// Set up map
	var map;
	var members_seen = new Array();

	function initializeMap() {
		var myOptions = {
			center: new google.maps.LatLng(59.931624, 10.741882),
			zoom: 12,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById("map_canvas"),
				myOptions);

		// Check for new progress every half second
		interval = setInterval(loadMarkers, 1000);
	}

	var markerRequest = new XMLHttpRequest();

	function loadMarkers() {
		// Don't send a request if one is already in progress
		if (markerRequest.readyState != 0 && markerRequest.readyState != 4)
			return;

		var url = "ajax/geocode.php?progress";
		// TODO Narrow browser support for onload
		markerRequest.onload = addMarkers;
		// TODO Also listen for failure
		markerRequest.open("GET", url, true);
		markerRequest.send();
	}

	// Add markers that haven't been seen yet
	function addMarkers() {
		var markers = JSON.parse(markerRequest.responseText);
		progress.value = markers.length;
		for (var i = 0; i < markers.length; i++) {
			var member = markers[i];
			if (members_seen.some(function(member_seen) { return member_seen == member.id; }))
				continue;
			else {
				var marker = new google.maps.Marker({
					position: new google.maps.LatLng(member.latitude, member.longitude),
					map: map,
					animation: google.maps.Animation.DROP
				});
				members_seen.push(member.id);
			}
		}
	}

	window.addEventListener('DOMContentLoaded', initializeMap, false);
</script>
HTML;

print $p->MakePageFooter();

?>
