<?php
include_once("includes/inc.global.php");

if (isset($_GET["action"]))
	$action = $_GET["action"];

if (isset($_POST["action"]))
	$action = $_POST["action"];

if ($action=="logout")
{
	$cUser->Logout();
}

if ($action=="login")
{
	if (isset($_POST["location"]))
		$redir_url = $_POST["location"];

	$user="";
	$pass="";
	if (isset($_POST["user"]))
		$user = $_POST["user"];

	if (isset($_POST["pass"]))
		$pass = $_POST["pass"];

	if ($user=="" || $pass=="")
	{
		if ($user=="")
		{
			$cErr->Error(_("Please enter a user name to log in."));
		} else {
			$cErr->Error(_("Please enter a password to log on with this account.  If you've forgotten your password, you can request a new one."));
		}

	} else {
		$cUser->Login($user,$pass);
	}


}

include("redirect.php");	// if nothing in particular is set, will redirect to home, but this allows the user login
				// process to potentially set an alternate location.

?>
