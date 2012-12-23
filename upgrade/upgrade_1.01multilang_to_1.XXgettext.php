<?php
// Upgrades from multilingual version to Gettext version
$running_upgrade_script = true;
include_once("../includes/inc.global.php");
$c = get_defined_constants();

$cUser->MustBeLevel(1);

// Administrator's enabled and default languages (autofilled at runtime from site settings)
$cDB->Query(<<<SQL
CREATE TABLE `languages` (
	`langcode` VARCHAR(20) PRIMARY KEY,
	`available` BOOLEAN NOT NULL DEFAULT TRUE
)
SQL
) or die("Error altering member table.  Does the web user account have alter table permission?");

$cDB->Query("INSERT INTO `settings` VALUES ('37', 'DEFAULT_LANGUAGE', '" . _("Available and default languages") . "', 'language', NULL, '', 'en_US', '', '" . _("Which languages are available, and which is the default") . "', 8)") or die("Error - Could not insert row into settings table.");
$cDB->Query("INSERT INTO `settings` VALUES ('38', 'ENABLE_TRANSLATION', '" . _("Enable translation of user interface") . "', 'bool', '', '', 'TRUE', '', '" . _("Enable translation of user-interface text. This only translates text that is built into Local Exchange; news, listings etc. are not automatically translated.") . "', 8)") or die("Error - Could not insert row into settings table.");

// User's preferred language
$cDB->Query("ALTER TABLE ". DATABASE_MEMBERS ." ADD COLUMN  `preferred_language` VARCHAR(20) DEFAULT NULL AFTER `confirm_payments`") or die("Error altering member table.  Does the web user account have alter table permission?");

echo "Finished Gettext upgrade.";
?>
