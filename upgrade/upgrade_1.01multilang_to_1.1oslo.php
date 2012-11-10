<?php
// Various fixes implemented in the Oslo fork of Local Exchange
$running_upgrade_script = true;
include_once("../includes/inc.global.php");

// Indexes on the trades table
$cDB->Query("CREATE INDEX trades_from ON ". DATABASE_TRADES ." (member_id_from)");
$cDB->Query("CREATE INDEX trades_to ON ". DATABASE_TRADES ." (member_id_to)");

// Missing option for DAILY updates (supported by the newsletter code)
$cDB->Query("UPDATE `settings` SET options='NEVER,DAILY,WEEKLY,MONTHLY'
	WHERE name='DEFAULT_UPDATE_INTERVAL'");

$p->DisplayPage(_("Database has been updated to version ")."1.1oslo.");
?>
