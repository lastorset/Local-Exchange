<?php
include_once("includes/inc.global.php");
$p->site_section = 0;

$cUser->Logout();

$list = _("You are now logged out").".<P>";
$list .= _("You can login at any time by clicking on the 'Login' link at the bottom of the left menu.");

$p->DisplayPage($list);

?>
