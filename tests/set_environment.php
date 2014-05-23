<?php
// Set up the environment for the unit-test suite.

// Fake server paths
$_SERVER = array("DOCUMENT_ROOT" => realpath('.'));
define('LEX_CONFIG_FILE', 'tests/config.test.php');
define("DATABASE_CONFIG_FILE", "tests/config-database.test.php");
