<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;
$p->page_title = "Test state codes from database";

include("includes/inc.forms.php");

//
// Show state codes from database
//
// $cUser->MustBeLevel(2);

$i=1;
$state = new cState();
$state->LoadState($i);

$states = new cStateList;
$state_list = $states->MakeStateArray();
$state_list[0]="---";

$output = "Lijst met wijk/buurt (bv. wijk-nr ".$i." = ".$state->description.") : <br>";

$i=0;
foreach($state_list as $states) {
	$output = $output . $i . " - " . $state_list[$i] . "<br>";
	$i++;
}

$p->DisplayPage($output);

?>
