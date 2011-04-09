<?php
include_once("includes/inc.global.php");
$p->site_section = 0;

$cUser->Logout();

$list = $lng_yre_logged_out.".<P>";
$list .= $lng_you_can_login;

$p->DisplayPage($list);

?>
