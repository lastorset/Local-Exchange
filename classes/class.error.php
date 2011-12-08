<?php
if (!isset($global))
{
	die(__FILE__." was included directly.  This file should only be included via inc.global.php.  Include() that one instead.");
}

define ("ERROR_ARRAY_SEVERITY", 0);
define ("ERROR_ARRAY_MESSAGE", 1);
define ("ERROR_ARRAY_FILE", 2);
define ("ERROR_ARRAY_LINE", 3);

define ("ERROR_SEVERITY_INFO",1);
define ("ERROR_SEVERITY_LOW",2);
define ("ERROR_SEVERITY_MED",3);
define ("ERROR_SEVERITY_HIGH",4);
define ("ERROR_SEVERITY_STOP",5);


class cError
{

	var $retval;
	var $retobj;

	var $arrErrors;

	function cError()
	{
		$this->arrErrors = array();

		if (isset($_SESSION["errors_saved"]))
		{
			$this->arrErrors = $_SESSION["errors_saved"];
			unset ($_SESSION["errors_saved"]);	// don't want the errors to keep appearing...
		}


	}

	/** Use this function to save an error for emitting later. Remember to call SaveErrors
		before redirecting; they will be saved as a session variable and restored when the
		next page loads. Emit the error using ErrorBox. */
	function Error($message, $severity=0, $file="", $line=0)
	{
		if ($severity==0)
			$severity = ERROR_SEVERITY_LOW;

		$this->arrErrors[]=array(ERROR_ARRAY_MESSAGE => $message,
					ERROR_ARRAY_SEVERITY => $severity,
					ERROR_ARRAY_FILE => $file,
					ERROR_ARRAY_LINE => $line);

		if ($severity==ERROR_SEVERITY_STOP)
			$this->DoStopError();
	}

	/** Use this function to log an error immediately. The caller is responsible for informing
		the user, if necessary. */
	function InternalError($message, $file="", $line=0)
	{
		global $cUser;
		error_log(SITE_SHORT_TITLE ." error (INTERNAL) (user ". $cUser->member_id ."): ". $message 
			. (strlen($file) > 0 ? " (". $file ." line ". $line .")" : ""));
	}

	function SaveErrors()
	{	// we're about to redirect, but want to remember the errors, so put them in session temporarily.

		$_SESSION["errors_saved"] = $this->arrErrors;
	}

	function DoStopError()
	{

		$box = $this->ErrorBox();

		die ($box);
	}

	function ErrorBox()
	{
		$output="";

		foreach($this->arrErrors as $oneErr)
		{
			$output.=$this->ErrorBoxError($oneErr);
		}

//		$msg = "<DIV class=ErrorBoxMsg>Errors occured on this page:</DIV>";
		$msg = "<FONT color=RED size=2>"._("Errors occurred on this page").":<BR>";

		if (strlen($output)>0)
			$output = $msg.$output."</FONT><BR>";
//			$output = "<CENTER><DIV class=ErrorBox>".$msg.$output."</DIV></CENTER>";

		return $output;
	}

	function ErrorBoxError($oneErr)
	{
		//if ($oneErr[ERROR_ARRAY_SEVERITY]==ERROR_SEVERITY_INFO && !DEBUG)
		//	return "";

		$output="<DIV class=ErrorBoxLine>".$this->SeverityNote($oneErr[ERROR_ARRAY_SEVERITY]).$oneErr[ERROR_ARRAY_MESSAGE];

		if  (DEBUG && $oneErr[ERROR_ARRAY_FILE] != "")
		{
			$output.="<DIV class=FileLine> ".$oneErr[ERROR_ARRAY_FILE];
			if ($oneErr[ERROR_ARRAY_LINE] != 0)
				$output.=" (".$oneErr[ERROR_ARRAY_LINE].")";
			$output.="</DIV>";
		}

		$output .= "</DIV>";

		return $output;
	}

	function SeverityNote($sev)
	{
		switch($sev)
		{
			case ERROR_SEVERITY_INFO:
//				return "(INFO) ";
				return "";
				break;
			case ERROR_SEVERITY_LOW:
				return "(LOW) ";
				break;
			case ERROR_SEVERITY_MED:
				return "(MED) ";
				break;
			case ERROR_SEVERITY_HIGH:
				return "(HIGH) ";
				break;
			case ERROR_SEVERITY_STOP:
				return "(STOP) ";
				break;
			default:
				return "";
				break;
		}

	}


	function ReturnValue($message, $obj="")
	{
		$this->retval = $message;
		$this->retobj = $obj;
	}
}


$cErr = new cError;
?>
