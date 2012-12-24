<?php

include_once("includes/inc.global.php");
$p->site_section = ADMINISTRATION;
$p->page_title = _("Initial geocoding");

$cUser->MustBeLevel(1);

print $p->MakePageHeader();
print $p->MakePageMenu();

$geocodable_count = count(cGeocode::GeocodablePersons());
$provider = cGeocode::GeocodingProvider();

?>
<h2><?= _("Initial geocoding") ?></h2>
<?= // Translation hint: %1$s is the name of a geocoding provider
sprintf(_('This script contacts %1$s to place every member on a map.'), $provider) ?>
<noscript><p><?= // Translation hint: %s are HTML tags making a link.
sprintf(_("You do not have JavaScript enabled. Please %s run the script manually%s (takes a long time and may be interrupted by PHP)."), "<a href=ajax/geocode.php>", "</a>") ?></p></noscript>
<p id=status style="background-color: lightgoldenrodyellow"><?= _("Waiting for results…") ?></p>
<progress max=<?= $geocodable_count ?>></progress>
<div id="map_canvas" style="width:100%; margin: 1em 0;"></div>
<div id=log style="display: none">
	<span id=processed_count></span>
	<h3>Errors</h3>
	<ul id=general_errors>
	</ul>

	<h4>Address errors</h4>
	<ul id=address_errors>
	</ul>

	<h4>Other errors</h4>
	<ul id=other_errors>
	</ul>
</div>
<script type="text/javascript"
	src="http://maps.googleapis.com/maps/api/js?key=AIzaSyA5n7eMkwocdSFXiGrPNJPz32CLxzDYpGk&sensor=false">
</script>
<script type="text/javascript" src="ajax/lib/sprintf.js"></script>
<script>
	// Initialize XHR
	var geocodingRequest = new XMLHttpRequest();
	var url = "http://lex.localhost/ajax/geocode.php";
	geocodingRequest.open("POST", url, true);
	geocodingRequest.onreadystatechange = finish;
	geocodingRequest.send();

	// Various elements and variables
	var log = document.getElementById("log");
	var status = document.getElementById("status");
	var progress = document.getElementsByTagName("progress")[0];
	var interval;

	// Finish up
	function finish() {
		if (geocodingRequest.readyState === 4) {
			if (geocodingRequest.status === 200) {
				status.innerText = "<?= _("Done") ?>";
				progress.value = <?= $geocodable_count ?>;
				outputLog(log, JSON.parse(geocodingRequest.responseText));
				window.clearInterval(interval);
			} else {
				status.innerText = "<?= _("Request failed.") ?>";
				progress.value = 0;
				window.clearInterval(interval);
			}
		}
	}

	function outputLog(log, response) {
		if (response.processedCount)
			document.getElementById('processed_count').innerText = sprintf("<?= _("%(number)s responses processed.") ?>", { number: response.processedCount });

		addItem(response.generalErrors, document.getElementById('general_errors'))
		addItem(response.addressErrors, document.getElementById('address_errors'))
		addItem(response.otherErrors, document.getElementById('other_errors'))

		function addItem(errors, elm) {
			if (errors)
				for (var i = 0; i < errors.length; i++) {
					var item = document.createElement("li");

					var html = "";
					if (errors[i].member)
						html = "<a href='member_summary.php?member_id="+ errors[i].member +"'>"+ errors[i].member +"</a>: ";
					item.innerHTML = html + errors[i].message;
					elm.appendChild(item);
				}
		}
		log.style.display = 'block';
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
		markerRequest.addEventListener('load', addMarkers, false);
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
<?php

print $p->MakePageFooter();

?>
