<?php

include_once("includes/inc.global.php");
include_once("classes/class.news.php");
include("includes/inc.forms.php");

$cUser->MustBeLevel(1);

$p->site_section = EVENTS;

$news = new cNews;
$news->LoadNews($_REQUEST["news_id"]);
$p->page_title = _("Edit")." '". $news->title ."'";



//
// First, we define the form
//

$form->addElement("hidden","news_id",$_REQUEST["news_id"]);
$form->addElement("text", "title", _("Title"), array("size" => 35, "maxlength" => 100));
$today = getdate();
$options = array("language"=> _("en"), "format" => "dFY", "minYear" => $today["year"],"maxYear" => $today["year"]+5); // changed "en" by _("en") by ejkv
$form->addElement("date","expire_date", _("Expires"), $options);
$sequence = new cNewsGroup();
$sequence->LoadNewsGroup();
$form->addElement("select", "sequence",_("Sequence"), $sequence->MakeNewsSeqArray($news->sequence));
//$form->addElement("static", null, "Description", null);
$form->addElement("textarea", "description", _("Description"), array("cols"=>65, "rows"=>5, "wrap"=>"soft"));

$form->addElement("submit", "btnSubmit", _("Submit"));

//
// Set up validation rules for the form
//
$form->addRule("title",_("Enter a title"),"required");
$form->addRule("description",_("Enter a description"),"required");
$form->registerRule("verify_valid_date","function","verify_valid_date");
$form->addRule("expire_date",_("Date is invalid"),"verify_valid_date");

//
// Then check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {
	$current_values = array ("title"=>$news->title, "description"=>$news->description, "expire_date"=>$news->expire_date->DateArray(), "sequence"=>$news->sequence);
	
	$form->setDefaults($current_values);
   $p->DisplayPage($form->toHtml());  // just display the form
   $p->InsertCKEditor("description");
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	global $p, $news, $cErr;
	
	$date = $values['expire_date'];
	$expire_date = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];
	$news->title = $values["title"];
	$news->description = $values["description"];
	$news->expire_date->Set($expire_date);
	$news->sequence = $values["sequence"];
	$success = $news->SaveNews();	
	
	if ($success)
		$output = _("Changes saved.");
	else
		$output = _("There was a problem saving the news item.");
		
	$p->DisplayPage($output);
	
}


//
// Custom validation functions
//

function verify_valid_date ($element_name,$element_value) {
	$date = $element_value;
	return checkdate($date["F"],$date["d"],$date["Y"]);
}
