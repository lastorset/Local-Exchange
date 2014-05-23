<?php
/**
 * TODO: Is this actually needed?
 * @backupGlobals disabled
 */
class cDatabaseTest extends PHPUnit_Framework_TestCase {
	function testScreenHTML() {
		global $cUser, $allowedHTML;

		// Fake configuration settings
		$allowedHTML = array('b', 'i');
		define('STRIP_JSCRIPT', true);
		$cUser = new cMember(array('member_role' => 0)); // 0: plain member

		$testString = 'This could be javascript: <script src=evil></script> <b>bold</b> <i>italic</i> <u>underlined</u>';
		$expected = 'This could be    <b>bold</b> <i>italic</i>  underlined ';

		$this->assertEquals(cDatabase::ScreenHTML($testString), $expected);
	}
}
