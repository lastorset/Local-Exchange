<?php
include_once("includes/inc.global.php");
$p->site_section = 0;

if($cUser->IsLoggedOn())
{
	$list = $lng_welcome_to. SITE_LONG_TITLE .", ". $cUser->PrimaryName() ."!";
	
	if ($cUser->AccountIsRestricted())
		$list .= $lng_hi."<p>".LEECH_NOTICE;
}
else 
{
	$list = $cUser->UserLoginPage();
}

$p->DisplayPage($list);

?>
