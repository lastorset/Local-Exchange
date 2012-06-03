<?php
// Upgrade to use geocoding

$cDB->Query("ALTER TABLE ". DATABASE_PERSONS ."
	ADD COLUMN `latitude` DOUBLE DEFAULT NULL,
	ADD COLUMN `longitude` DOUBLE DEFAULT NULL")
	or die("Error altering member table.  Does the web user account have alter table permission?");
?>
