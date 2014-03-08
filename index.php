<?php

include_once("includes/inc.global.php");
include_once("classes/class.geocode.php");
$p->site_section = 0;

print $p->MakePageHeader();
print $p->MakePageMenu();

?>
<div class=homepage>

<div class=logoheader>
	<img src='images/localx_home.png'>
	<div>
		<h1><?= SITE_HOME_TITLE ?></h1>
		<div class=tagline><?= SITE_HOME_TAGLINE ?></div>
	</div>
</div>
<?php

if (!HOME_PAGE_MAP) { ?>
	<h2><?= _("Why have a Local, Sustainable Currency?") ?></h2>
	<ul>
	<li><?= _("Enhance Your Prosperity") ?></li>
	<li><?= _("Build a Sustainable Community") ?></li>
	<li><?= _("Utilize Your Talents") ?></li>
	<li><?= _("Nurture the Unique Quality of Your Hometown") ?></li>
	<li><?= _("Have Fun") ?></li></ul>
	<p><?= HOME_PAGE_MESSAGE ?>
<?php } else {
	print "<p>". HOME_PAGE_MESSAGE;
	print cGeoCode::GenerateMap();
} ?>
</div>
<?php

print $p->MakePageFooter();

?>
