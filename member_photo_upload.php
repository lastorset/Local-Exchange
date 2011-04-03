<?
require_once("includes/inc.global.php");
require_once("includes/inc.forms.php");
include_once("classes/class.uploads.php");
require_once('Image/Transform.php');

if (ALLOW_IMAGES!=true) 
	header("location:http://".HTTP_BASE."/");
	
if(!extension_loaded('gd')) {
	$cErr->Error("The GD extension is required for photo uploads!");
	include("redirect.php");
}

$p->site_section = EVENTS;

$member = new cMember;

if($_REQUEST["mode"] == "admin") {
	$cUser->MustBeLevel(1);
	$member->LoadMember($_REQUEST["member_id"]);
	
	$p->page_title = $lng_upload_photo_for." ".$member->member_id;
} else {
	$cUser->MustBeLoggedOn();
	$member = $cUser;
	$p->page_title = $lng_upload_photo;

}

$query = $cDB->Query("SELECT filename FROM ".DATABASE_UPLOADS." WHERE title=".$cDB->EscTxt("mphoto_".$member->member_id)." limit 0,1;");
		
$num_results = mysql_num_rows($query);
$mIMG = cMember::DisplayMemberImg($member->member_id);

if ($mIMG!=false) {
			
		$form->addElement("html", $mIMG."<p>");
		$submitTxt = $lng_replace_image;		
}
else
	$submitTxt = $lng_upload_image;
		
$form->addElement('hidden', 'member_id', $member->member_id);
$form->addElement('hidden', 'mode', $_REQUEST["mode"]);

$form->addElement('file', 'userfile', $lng_select_file_upload.':', array("MAX_FILE_SIZE"=>MAX_FILE_UPLOAD));
$form->addElement('submit', 'btnSubmit', $submitTxt);

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process('process_data', false);
} else {
   $p->DisplayPage($form->toHtml());  // just display the form
}

function process_data ($values) {
	global $p, $member,$cDB,$cErr, $lng_size_uploaded_file_null, $lng_file_uploaded, $lng_problem_resizing_file, $lng_problem_uploading_file;

	if ($_FILES['userfile']['size']==0) {
			
			$cErr->Error($lng_size_uploaded_file_null);
			$output = $lng_size_uploaded_file_null;
			$p->DisplayPage($output);
			exit;
	}
	
	$name = "mphoto_".$member->member_id;
	
	$query = $cDB->Query("SELECT upload_date, type, title, filename, note FROM ".DATABASE_UPLOADS." WHERE title=".$cDB->EscTxt($name)." limit 0,1;");
	
	if ($query)
		$num_results = mysql_num_rows($query);

	if($num_results>0) { // Member already has a pic		
	
		$row = mysql_fetch_array($query);
		
		$fileLoc = UPLOADS_PATH . stripslashes($row["filename"]);
		
		@unlink($fileLoc);
		
		$query = "DELETE FROM ". DATABASE_UPLOADS ." WHERE filename = ". $cDB->EscTxt($row["filename"]) .";";
	
		$delete = $cDB->Query($query);

	}
  
	$upload = new cUpload("P", $name, null, $name);
	
	if($upload->SaveUpload(true)) {
		$image = Image_Transform::factory("GD"); // Need to shrink photo
		$image->load(UPLOADS_PATH . $upload->filename);
		
		$x = $image->getImageWidth();
		$y = $image->getImageHeight();
		
		if ($x>=MEMBER_PHOTO_WIDTH || UPSCALE_SMALL_MEMBER_PHOTO==true) {
			
			$y = @(MEMBER_PHOTO_WIDTH/$x) * $y; // Keep proportions
			$x = MEMBER_PHOTO_WIDTH;
			$image->resize($x,$y); 
		}
		
		if($image->save(UPLOADS_PATH . $upload->filename, null, 100))
			$output = $lng_file_uploaded;
		else
			$output = $lng_problem_resizing_file;
	} else {
		$output = $lng_problem_uploading_file;
	}

	$p->DisplayPage($output);
}
?>
