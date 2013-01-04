<?php

include_once("includes/inc.global.php");
$p->site_section = ADMINISTRATION;
$p->page_title = _("Initial geocoding");

$cUser->MustBeLevel(1);

print $p->MakePageHeader();
print $p->MakePageMenu();

// Receive form input

if ($_POST['saved-geocode-apikey'] == true) {
	$site_settings->set("GEOCODE", isset($_POST['GEOCODE']) ? "TRUE" : "FALSE");
	$site_settings->set("MAP_API_KEY", $_POST['MAP_API_KEY']);
}

if ($_POST['saved-viewport'] == true) {
	$site_settings->set("MAP_CENTER", $_POST['MAP_CENTER']);
	$site_settings->set("MAP_ZOOM", $_POST['MAP_ZOOM']);
}

if ($_POST['saved-home-page-map'] == true) {
	$site_settings->set("HOME_PAGE_MAP", isset($_POST['HOME_PAGE_MAP']) ? "TRUE" : "FALSE");
}

$site_settings->getCurrent();

// Set up some variables

$geocodable_count = count(cGeocode::GeocodablePersons());
$missing_count = count(cGeocode::MissingPersons());

$provider = cGeocode::GeocodingProvider();
$provider_api_key_request = cGeocode::GeocodingProviderAPIRequest();
$api_key = $site_settings->current['MAP_API_KEY'];
if ($api_key == "FALSE")
	$api_key = "";

$map_center = cGeocode::ParseCoordinates($site_settings->current['MAP_CENTER']);
if (is_null($map_center))
	$map_center = array(0, 0);

?>
<script type="text/javascript" src="ajax/lib/replace_alert.js">
</script>
<?php if ($api_key == "") { ?>
	<script>
	window.addEventListener('DOMContentLoaded', function() {
		document.getElementById('map_canvas').innerText = "<?= _("(A map will be displayed here as soon as an API key is entered.)") ?>";
	}, false);
	</script>
<?php } else { ?>
	<script type="text/javascript"
		src="http://maps.googleapis.com/maps/api/js?key=<?= urlencode($api_key) ?>&sensor=false">
	</script>
	<script type="text/javascript" src="ajax/lib/sprintf.js"></script>

	<script>
		<? /* TODO: Disable map if key is empty and catch authentication errors
		https://groups.google.com/forum/?fromgroups=#!topic/google-maps-api/oHfKEazKd0M */ ?>

		// Set up map
		var map;
		var members_seen = new Array();

		function initializeMap() {
			var myOptions = {
				center: new google.maps.LatLng(<?= $map_center[0] ?>, <?= $map_center[1] ?>),
				zoom: <?= $site_settings->current['MAP_ZOOM'] ?>,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			map = new google.maps.Map(document.getElementById("map_canvas"),
					myOptions);
			loadMarkers();
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
<?php } ?>

<div id=initial_geocoding>
<h1><?= _("Geocoding setup") ?></h1>
<p><?= _("This page guides you through the steps required to start geocoding members.") ?>
<p><?= _("<i>Geocoding</i> means finding the latitude/longitude coordinates of a member's address. We store these coordinates in the database and use them to show members on a map.") ?>

<h2 id=api-key><?= _("API key") ?></h2>
<form method=post action=#api-key>
	<p><?= // Translation hint: %s is the name of a geocoding provider
	sprintf(_('%s requires sites like yours to obtain an API key. Please visit the following site to obtain a key.'), $provider) ?>
	<p><a href=<?= $provider_api_key_request ?>><?= $provider_api_key_request ?></a>

	<p><?= _("Then, paste the key in the following box.") ?>
	<p><input size=39 style="font-family: monospace" name=MAP_API_KEY value="<?= htmlspecialchars($api_key) ?>"/>

	<p><?= _("Check this box to enable geocoding. Then save your settings. This causes new members and members who modify their profile to be geocoded automatically.") ?>
	<p>
		<input type=checkbox id=GEOCODE name=GEOCODE <?= $site_settings->current['GEOCODE'] === true ? "checked" : "" ?>><label for=GEOCODE><?= _("Enable geocoding") ?></label>
		<input type=hidden name=saved-geocode-apikey value=true>
	<p>
		<input type=submit value="<?= _("Save") ?>">
</form>

<h2 id="center-viewport">Map viewport</h2>
	<?= _("Choose the map's default view by panning and zooming.") ?>
	<div id="map_errors"></div>
	<div id="map_canvas" style="width:100%; margin: 1em 0;"></div>
	<form method=post action=#center-viewport>
		<input type=hidden name=saved-viewport value=true>
		<input type=hidden name=MAP_CENTER id=viewport-coord value="<?= $site_settings->current['MAP_CENTER'] ?>">
		<input type=hidden name=MAP_ZOOM   id=viewport-zoom  value="<?= $site_settings->current['MAP_ZOOM'] ?>">
		<input type=submit value="<?= _("Save") ?>" onclick="
			var center = map.getCenter();
			document.getElementById('viewport-coord').value = '('+ center.lat() +', '+ center.lng() +')';
			document.getElementById('viewport-zoom').value = map.getZoom();
		">
	</form>

<h2><?= _("Geocode all members") ?></h2>
<?
if (!$site_settings->current['GEOCODE'])
	print _("Enable geocoding to continue.");
else if ($missing_count === 0) {
	print _("All members have been geocoded. You may skip this step.");
} else {
	printf(_('Although you have enabled geocoding for the future, %d existing members also need to be geocoded.'), $missing_count);
	?>
	<noscript><p><?= // Translation hint: %s are HTML tags making a link.
	sprintf(_("You do not have JavaScript enabled. Please %s run the script manually%s (takes a long time and may be interrupted by PHP)."), "<a href=ajax/geocode.php>", "</a>") ?></p></noscript>
	<p><button onclick="startGeocoding(); this.parentNode.removeChild(this);"><?= _("Start geocoding") ?></button>

	<p id=status style="background-color: lightgoldenrodyellow; visibility: hidden;"><?= _("Not started") ?></p>
	<progress max=<?= $geocodable_count ?> style="visibility: hidden;"></progress>

	<div id=log style="display: none">
		<span id=processed_count></span>

		<p><?= _("You may wish to re-center the map viewport above to show the newly geocoded members.") ?>

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

	<script>
		// Initialize XHR
		var geocodingRequest = new XMLHttpRequest();
		var url = "http://<?= HTTP_BASE ?>/ajax/geocode.php";
		geocodingRequest.open("POST", url, true);
		geocodingRequest.onreadystatechange = finish;

		// Various elements and variables
		var log = document.getElementById("log");
		var status = document.getElementById("status");
		var progress = document.getElementsByTagName("progress")[0];
		var interval;

		function startGeocoding() {
			geocodingRequest.send();

			// Check for new progress every half second
			interval = setInterval(loadMarkers, 1000);

			progress.style.visibility = "visible";
			status.style.visibility = "visible";
			status.innerText = "<?= _("Waiting for results…") ?>";
		}

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
			var errorCount = response.generalErrors.length
				+ response.addressErrors.length
				+ response.otherErrors.length;
			if (response.processedCount)
				document.getElementById('processed_count').innerText = errorCount > 0
					? sprintf("<?= _("%(response_count)s responses processed with %(error_count)s errors.") ?>", { response_count: response.processedCount, error_count: errorCount })
					: sprintf("<?= _("%(response_count)s responses processed.") ?>", { response_count: response.processedCount });

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
	</script>
	<?php
} ?>

<h2 id=home-page-map><?= _("Home page map") ?></h2>
<form method=post action="#home-page-map">
	<p><?= _("If you wish to display a map on the front page, check this box. Other maps will display regardless of this setting.") ?>
	<p>
		<input type=checkbox id=HOME_PAGE_MAP name=HOME_PAGE_MAP <?= $site_settings->current['HOME_PAGE_MAP'] === true ? "checked" : "" ?>><label for=HOME_PAGE_MAP><?= _("Show map on home page") ?></label>
		<input type=hidden name=saved-home-page-map value=true>
	<p>
		<input type=submit value="<?= _("Save") ?>">
</form>

<?php

print $p->MakePageFooter();

?>
