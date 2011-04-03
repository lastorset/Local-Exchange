<?php

include_once("includes/inc.global.php");
include("classes/class.news.php");
include("includes/inc.forms.php");

$cUser->MustBeLevel(1);

$p->site_section = EVENTS;

$news = new cNews;
$news->LoadNews($_REQUEST["news_id"]);
$p->page_title = $lng_edit." '". $news->title ."'";



//
// First, we define the form
//

$form->addElement("hidden","news_id",$_REQUEST["news_id"]);
$form->addElement("text", "title", $lng_title, array("size" => 35, "maxlength" => 100));
$today = getdate();
$options = array("language"=> $lng_language, "format" => "dFY", "minYear" => $today["year"],"maxYear" => $today["year"]+5); // changed "en" by $lng_language by ejkv
$form->addElement("date","expire_date", $lng_expires, $options);
$sequence = new cNewsGroup();
$sequence->LoadNewsGroup();
$form->addElement("select", "sequence",$lng_sequence, $sequence->MakeNewsSeqArray($news->sequence));
//$form->addElement("static", null, "Description", null);
$form->addElement("textarea", "description", $lng_description, array("cols"=>65, "rows"=>5, "wrap"=>"soft"));

$form->addElement("submit", "btnSubmit", $lng_submit);

//
// Set up validation rules for the form
//
$form->addRule("title",$lng_enter_title,"required");
$form->addRule("description",$lng_enter_description,"required");
$form->registerRule("verify_valid_date","function","verify_valid_date");
$form->addRule("expire_date",$lng_date_invalid,"verify_valid_date");

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
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	global $p, $news, $cErr, $lng_changes_saved, $lng_problem_saving_news;
	
	$date = $values['expire_date'];
	$expire_date = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];
	$news->title = $values["title"];
	$news->description = $values["description"];
	$news->expire_date->Set($expire_date);
	$news->sequence = $values["sequence"];
	$success = $news->SaveNews();	
	
	if ($success)
		$output = $lng_changes_saved;
	else
		$output = $lng_problem_saving_news;
		
	$p->DisplayPage($output);
	
}


//
// Custom validation functions
//

function verify_valid_date ($element_name,$element_value) {
	$date = $element_value;
	return checkdate($date["F"],$date["d"],$date["Y"]);
}
