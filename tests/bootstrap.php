<?php
/**
 * Bootstrap for Local Exchange's unit tests. This loads configuration and globals to prepare for them.
 */

// Workaround for PHPUnit issue #325: Explicitly declare globals
// http://stackoverflow.com/questions/9672178/phpunit-and-globals/23575096#23575096
global $translation, $SIDEBAR, $cDB;

require_once('set_environment.php');
require_once('includes/inc.global.php');
