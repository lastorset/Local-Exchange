<?php

class cUpload {
	var $upload_id;
	var $upload_date;
	var $type; // for example "N" for "newsletters"
	var $title;
	var $filename;
	var $note;

	function cUpload ($type=null, $title=null, $note=null, $filename=null) {
		global $cUser;

		if($type) {
			$this->type = $type;
			$this->title = $title;
			$this->note = $note;
			$this->filename = $filename; // For the sake of being thorough [chris]
		
		}
	}
	
	function SaveUpload() {
		// Copy file uploaded by UploadForm class to uploads directory and
		// save entry for it in the database
		global $cDB, $cErr;
		
		if($this->filename == null)
			$this->filename = $_FILES['userfile']['name'];
		
		$query = $cDB->Query("SELECT null from ". DATABASE_UPLOADS ." WHERE filename ='".$_FILES['userfile']['name']."';");
		
		if($row = mysql_fetch_array($query)) {
			$cErr->Error(_("A file with this name already exists on the server."));
			return false;
		}		
			
		if(move_uploaded_file($_FILES['userfile']['tmp_name'], UPLOADS_PATH . $this->filename)) {
			$insert = $cDB->Query("INSERT INTO ". DATABASE_UPLOADS ." (type, title, filename, note) VALUES (". $cDB->EscTxt($this->type) .", ". $cDB->EscTxt($this->title) .", ". $cDB->EscTxt($this->filename) .", ". $cDB->EscTxt($this->note) .");");
						
			if(mysql_affected_rows() == 1) {
				$this->upload_id = mysql_insert_id();	
				$query = $cDB->Query("SELECT upload_date FROM ".DATABASE_UPLOADS." WHERE  upload_id=". $this->upload_id.";");
				if($row = mysql_fetch_array($query))
					$this->upload_date = $row[0];					
				return true;
			} else {
				$cErr->Error(_("Could not save database row for uploaded file."));
				return false;
			}				
		} else {
			$cErr->Error(_("Could not save uploaded file. This could be because of a permissions problem.  Does the web user have permission to write to the uploads directory?  It could also be that the file is too large.  The current maximum size of file allowed is")." ".MAX_FILE_UPLOAD." "._("bytes").".");
			return false;
		}
	}
	
	function LoadUpload ($upload_id) {
		global $cDB, $cErr;
			
		$query = $cDB->Query("SELECT upload_date, type, title, filename, note FROM ".DATABASE_UPLOADS." WHERE upload_id=". $upload_id.";");
		
		if($row = mysql_fetch_array($query)) {		
			$this->upload_id = $upload_id;
			$this->upload_date = new cDateTime($row[0]);
			$this->type = $row[1];		
			$this->title = $row[2];
			$this->filename = $row[3];
			$this->note = $cDB->UnEscTxt($row[4]);
			return true;
		} else {
			$cErr->Error(_("There was an error accessing the uploads table.")." "._("Please try again later."));
			include("redirect.php");
		}
		
	}

	function DeleteUpload () {
		global $cDB, $cErr;
		
		if(unlink(UPLOADS_PATH . $this->filename)) {
			$delete = $cDB->Query("DELETE FROM ". DATABASE_UPLOADS ." WHERE upload_id = ". $this->upload_id .";");
			if(mysql_affected_rows() == 1) {
				return true;
			} else {
				$cErr->Error(_("File was deleted but could not delete row from database.  The row will have to removed manually.  Please contact your systems administrator."));
				include("redirect.php");
			}			
		} else {
			$cErr->Error(_("Could not delete file")." - ". $this->filename .".  "._("Please try again later").".");
			include("redirect.php");
		}
	}

	function DisplayURL ($text=null) {
		if($text == null)
			$text = $this->title;
		// RF: changed to open file in uploads in new window	
		return '<A HREF="uploads/'. $this->filename .'" target="_blank">'. $text .'</A>';
	}
}

class cUploadGroup {
	var $uploads; // will be object of class cUpload
	var $type;
	
	function cUploadGroup($type) {
		$this->type = $type;
	}
	
	function LoadUploadGroup () {
		global $cDB, $cErr;
	
		$query = $cDB->Query("SELECT upload_id FROM ".DATABASE_UPLOADS." WHERE type=". $cDB->EscTxt($this->type) ." ORDER BY upload_date DESC;");
		
		$i = 0;				
		while($row = mysql_fetch_array($query)) {
			$this->uploads[$i] = new cUpload;			
			$this->uploads[$i]->LoadUpload($row[0]);
			$i += 1;
		}

		if($i == 0)
			return false;
		else
			return true;
	}
	

}

class cUploadForm {
     
	function DisplayUploadForm($action, $text_fields=null) {
	$output = '<form enctype="multipart/form-data" action="'. $action.'" method="POST">';
	foreach($text_fields as $field)
		$output .= $field .' <input type="text" name="'. $field .'"><BR>';
		
	$output .= '<input type="hidden" name="MAX_FILE_SIZE" value="'.MAX_FILE_UPLOAD.'">'._("Select file to upload").' <input name="userfile" type="file"><input type="submit" value='._("Upload").'></form>';
	return $output;
	}

}

?>
