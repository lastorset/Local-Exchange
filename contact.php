<?php
include_once("includes/inc.global.php");
$p->site_section = SECTION_EMAIL;
$p->page_title = _("Contact Us");

include("includes/inc.forms.php");

//
// First, we define the form
//
$form->addElement("static", null, _("For more information on the")." ". SITE_LONG_TITLE ." "._("or to find out how to become a member, please fill out our information request. Someone will get back to you soon. Please check our")." <A HREF=news.php>"._("Events")."</A> "._("page for our next New Member's Meeting if you would like to join our group!"), null);
$form->addElement("static", null, null, null);
$form->addElement("text", "name", _("Name"));
$form->addElement("text", "email", _("Email"));
$form->addElement("text", "phone", _("Phone"));
$form->addElement("static", null, null, null);
$form->addElement("textarea", "message", _("Your Message"), array("cols"=>55, "rows"=>10, "wrap"=>"soft")); // colls changed from 65 to 55 by ejkv
$form->addElement("static", null, null, null);
$heard_from = array ("0"=>_("(Select One)"), "1"=>_("Newspaper"), "2"=>_("Radio"), "3"=>_("Search Engine"), "4"=>_("Friend"), "5"=>_("Local Business"), "6"=>_("Article"), "7"=>_("Other"));
$form->addElement("select", "how_heard", _("How did you hear about us?"), $heard_from);

$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", _("Send"));

//
// Define form rules
//
$form->addRule("name", _("Enter your name"), "required");
$form->addRule("email", _("Enter your email address"), "required");
$form->addRule("phone", _("Enter your phone number"), "required");


if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	global $p, $heard_from;
	
	$mailed = mail(EMAIL_ADMIN, SITE_SHORT_TITLE ." "._("Contact Form"), _("From").": ". $values["name"]. "\n". _("Phone").": ". $values["phone"] ."\n". _("Heard From").": ". $heard_from[$values["how_heard"]] ."\n\n". wordwrap($values["message"], 64) , "From:". $values["email"] ."\nContent-type: text/plain; charset=UTF-8");
	
	if($mailed)
		$output = _("Thank you.");
	else
		$output = _("There was a problem sending the email.  Are your sure you entered your email address  correctly?");	
	$p->DisplayPage($output);
}

?>
