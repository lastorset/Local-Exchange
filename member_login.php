<?php
include_once("includes/inc.global.php");
$p->site_section = 0;

if($cUser->IsLoggedOn())
{
	$list = _("Welcome to "). SITE_LONG_TITLE .", ". $cUser->PrimaryName() ."!";
	
	if ($cUser->AccountIsRestricted())
		$list .= _("hi")."<p>".LEECH_NOTICE;
}
else 
{
	$list = $cUser->UserLoginPage();
}

$p->DisplayPage($list);

?>
