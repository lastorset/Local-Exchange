<?php
include_once("includes/inc.global.php");
$p->site_section = ADMINISTRATION;
$p->page_title = $lng_site_settings;

$cUser->MustBeLevel(3); // changed level 2 to level 3 - by ejkv

global $cDB, $site_settings;

echo "Updating allowance to ". $_GET['value'];
cAllowanceLender::UpdateAllowance($_GET['value']);

?>
