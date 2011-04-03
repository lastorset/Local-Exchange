<?php
include_once("includes/inc.global.php");

global $cErr;

$cErr->SaveErrors();

if (!isset($redir_url))
{
	if (isset($_GET['location']))
		$redir_url = $_GET['location'];
	if (isset($_POST['location']))
		$redir_url = $_POST['location'];
}

if (!isset($redir_type))
{
	if (isset($_GET['type']))
		$redir_type = $_GET['type'];
}

if (!isset($redir_item))
{
	if (isset($_GET['item']))
		$redir_item = $_GET['item'];
}

if (isset($redir_url))	// a specific URL was requested.  Go there regardless of other variables.
{
	header("location:".$redir_url);
	exit;
}

if (isset($redir_type) && isset($redir_item))
{
	header("location:http://".HTTP_BASE.$GLOBALS['SITE_SECTION_URL'][$redir_type]."?item=".$redir_item);
	exit;
}

if (isset($redir_type))	// $item not specified
{
	header("location:http://".HTTP_BASE.$GLOBALS['SITE_SECTION_URL'][$redir_type]);
	exit;
}

// dunno where to go.  Go home.
header("location:http://".HTTP_BASE);
exit;


?>
