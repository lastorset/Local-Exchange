<?php

include_once("includes/inc.global.php");
$p->site_section = 0;

print $p->MakePageHeader();
print $p->MakePageMenu();

print "<div class=homepage>";

$c = get_defined_constants();
print <<<HTML
<div class=logoheader>
	<img src='images/localx_home.png'>
	<div>
		<h1>{$c['SITE_HOME_TITLE']}</h1>
		<div class=tagline>{$c['SITE_HOME_TAGLINE']}</div>
	</div>
</div>
HTML;

print "<h2>" ._("Why have a Local, Sustainable Currency?"). "</h2><ul><li>" ._("Enhance Your Prosperity"). "</li><li>" ._("Build a Sustainable Community"). "</li><li>" ._("Utilize Your Talents"). "</li><li> " ._("Nurture the Unique Quality of Your Hometown"). "</li><li>" ._("Have Fun"). "</li></ul>";

print "</div>";

print $p->MakePageFooter();

?>
  
