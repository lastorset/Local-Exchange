<?php

/* Initialize gettext */

$default_locale = "nb_NO.utf8";

if (extension_loaded('intl'))
	$default_locale = Locale::lookup(array("nb_NO.utf8", "en_US"), Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']), true, $default_locale);
echo "<dt>Detected:<dd>". $default_locale;

// Hardcoded for now. Later some prettier configuration.
$locale = "nb_NO.utf8";

$ret = setlocale(LC_MESSAGES, $locale);
echo "<dt>Set:<dd>". $ret;

bindtextdomain("messages", "./includes/lang");
bind_textdomain_codeset("messages", "UTF-8");
textdomain("messages");

?>
