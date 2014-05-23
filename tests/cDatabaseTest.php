<?php
/**
 * TODO: Is this actually needed?
 * @backupGlobals disabled
 */
class cDatabaseTest extends PHPUnit_Framework_TestCase {
	/** ScreenHTML should correctly weed out evil codes. */
	function testScreenHTML() {
		global $cUser, $allowedHTML;

		// Fake configuration settings
		$allowedHTML = array('b', 'i');
		define('STRIP_JSCRIPT', true);
		$cUser = new cMember(array('member_role' => 0)); // 0: plain member

		/* The stray expected '>' is because the '<script src=evil>' is replaced by ProcessHTMLTag, whereas
		   '</script' is textually replaced with a space, leaving behind a '>'. */
		$testString = 'This could be javascript: <script src=evil></script> <b>bold</b> <i>italic</i> <u>underlined</u>';
		$expected = 'This could be    > <b>bold</b> <i>italic</i> underlined';
		$safe = cDatabase::ScreenHTML($testString);

		$this->assertEquals($expected, $safe);
	}
}
