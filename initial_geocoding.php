<?php

include_once("includes/inc.global.php");
$p->site_section = ADMINISTRATION;

print $p->MakePageHeader();
print $p->MakePageMenu();

print "<noscript>You do not have JavaScript enabled. Please <a href=initial_geocoding_ajax.php>run the script manually</a>.</noscript>";
print <<<HTML
<p id=status style="background-color: lightgoldenrodyellow">Waiting for results…</p>
<pre id=log></pre>
<script>
var xhr = new XMLHttpRequest();
var url = "http://lex.localhost/initial_geocoding_ajax.php";
 xhr.open("GET", url, true); 
 xhr.send();
var log = document.getElementById("log");
var status = document.getElementById("status");

// Define a method to parse the partial response chunk by chunk
var last_index = 0;
function parse() {
	var curr_index = xhr.responseText.length;
	if (last_index == curr_index) return; // No new data
	status.textContent = "Retrieving results…";
	var s = xhr.responseText.substring(last_index, curr_index);
	last_index = curr_index;
	log.textContent += s;
	if (s.indexOf("FINISHED") != -1) {
		status.textContent = "Done.";
	}
}

// Check for new content every half second
var interval = setInterval(parse, 500);
</script>
HTML;

print $p->MakePageFooter();

?>
  
