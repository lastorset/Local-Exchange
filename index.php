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

print _("<p><strong> <font size='4'>Why have a Local, Sustainable Currency?</font></strong></p><ul><li><strong>Enhance Your Prosperity<br><br></strong></li><li><strong> Build a Sustainable Community<br><br></strong></li><li><strong>Utilize Your Talents<br><br></strong></li><li><strong> Nurture the Unique Quality<br>of Your Hometown <br><br></strong></li><li><strong> Have Fun</strong></li></ul>");

print "</div>";

print $p->MakePageFooter();

?>
  
