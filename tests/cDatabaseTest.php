<?php
require_once('../classes/class.database.php');

class cDatabaseTest extends PHPUnit_Framework_TestCase {
	function testScreenHTML() {
		global $cUser,$allowedHTML;

		$allowedHTML = array('b', 'i');
		define('STRIP_JSCRIPT', true);
		$cUser = new cMember(array('member_role' => 0)); // 0: plain member

		// TODO Make config file for unit tests and use it instead of this hack
		define(HTML_PERMISSION_LEVEL, 2);

		$testString = 'This could be javascript: <script src=evil></script> <b>bold</b> <i>italic</i> <u>underlined</u>';
		$expected = 'This could be    <b>bold</b> <i>italic</i>  underlined ';

		$this->assertEquals(cDatabase::ScreenHTML($testString), $expected);
	}
}
