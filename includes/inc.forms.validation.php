<?php

require_once("inc.forms.php");

function verify_max255($z, $text) {
	if(strlen($text) > 255)
		return false;
	else
		return true;
}
$form->registerRule('verify_max255','function','verify_max255');

function verify_selection ($z, $selection) {
	if($selection == "0")
		return false;
	else
		return true;
}
$form->registerRule('verify_selection','function','verify_selection');

?>
