<?php
include_once("includes/inc.global.php");
include("classes/class.uploads.php");
include("includes/inc.forms.php");

$cUser->MustBeLevel(1);

$p->site_section = EVENTS;
$p->page_title = $lng_delete_newsletter;

// First, need to change the default form template so checkbox comes before the label
$renderer->setElementTemplate('<TR><TD>{element}<!-- BEGIN required --><font> *</font><!-- END required --></FONT><!-- BEGIN error --><font color=RED size=2>   *{error}*</font><br /><!-- END error -->&nbsp;<FONT SIZE=2>{label}</FONT></TD></TR>');  

$newsletters = new cUploadGroup("N");

if($newsletters->LoadUploadGroup()) {
	foreach($newsletters->uploads as $newsletter) {
		$form->addElement('checkbox', $newsletter->upload_id, $newsletter->title);
		$message = "";
	}
	$form->addElement('static', null, null);
	$form->addElement('submit', 'btnSubmit', $lng_delete);
} else {
	$message = $lng_no_newsletters_in_system;
}

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process('process_data', false);
} else {
   $p->DisplayPage($form->toHtml() ."<BR>". $message);  // just display the form
}

function process_data ($values) {
	global $p, $cErr, $newsletters, $lng_one_newsletter_deleted, $lng_newsletters_deleted, $lng_error_deleting_newsletter;

	$deleted = 0;
	
	while (list ($id, $text) = each ($values)) {
		if(is_numeric($id)) {
		// if it's not numeric it's not one of the checkbox fields, so skip
			$newsletter = new cUpload;
			$newsletter->LoadUpload($id);
			if($newsletter->DeleteUpload())
				$deleted += 1;
		}
	}
	
	if($deleted == 1) 
		$output = $lng_one_newsletter_deleted;
	elseif($deleted > 1)
		$output = $deleted . " ".$lng_newsletters_deleted;	
	else
		$cErr->Error($lng_error_deleting_newsletter);
		
   $p->DisplayPage($output);
}

?>
