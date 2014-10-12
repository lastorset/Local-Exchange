<?php
include 'ajax.inc';
/**
 * Find the file that translates to the current language, and transmit it.
 */

global $translation;

$translation_file = '../'. $translation->currentLanguageDir() .'/messages.js';
if (!file_exists($translation_file)) {
	// No translation file could be found. Return a valid JSON document with an error message.
	print 'define('. json_encode(array(
		'error' => "No translation file found",
		'language' => $translation->current_language,
		'file' => $translation_file,
	)) .');';
	exit;
}
$documentMIME = 'application/json';

/* TODO: Send the file with X-Sendfile if possible.
   http://www.brighterlamp.com/2010/10/send-files-faster-better-with-php-mod_xsendfile/
*/

// Otherwise, use the traditional PHP way.
header ('Content-Type: ' . $documentMIME);
@ob_end_clean();
@ob_end_flush();
readfile($translation_file);
exit;
