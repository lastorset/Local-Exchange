<?php
// Upgrades from multilingual version to Gettext version

// User's preferred language
$cDB->Query("ALTER TABLE ". DATABASE_MEMBERS ." ADD COLUMN  `preferred_language` VARCHAR(20) DEFAULT NULL AFTER `confirm_payments`") or die("Error altering member table.  Does the web user account have alter table permission?");
?>
