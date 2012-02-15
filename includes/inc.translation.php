<?php

/* Initialize gettext */

global $supported_languages, $current_language;

// All supported languages by this version of Local Exchange.
$supported_languages = array(
	'en_US',     // English (no translation)
	'nb_NO.utf8' // Norwegian Bokmål
);

// Set the language cookie, if the user posted a preference.
setLanguageCookie();

// Select the locale based on a variety of factors, and save it in this global variable.
$current_language = selectLocale();
$ret = setlocale(LC_MESSAGES, $current_language);

// Gettext invocations
bindtextdomain("messages", "./includes/lang");
bind_textdomain_codeset("messages", "UTF-8");
textdomain("messages");

// TODO These functions can probably go into a class.
/** Pick the locale to use based on the following prioritized factors:

	- TODO: The user's preferred language, as stored in his profile.
	- What the user picked in this session using the dropdown.
	- TODO: What the user agent suggests in the HTTP request.
	- The site's default language. */
function selectLocale() {
	global $supported_languages;

	// The fallback locale, used when nothing else works.
	// TODO: Move to settings
	$default_locale = "nb_NO.utf8";

	// First, if the user is logged in, check preferred language.
	// TODO

	// If not logged in, or language is not available, check the cookie.
	if (isset($_SESSION['preferred_language']))
	{
		$idx = array_search($_SESSION['preferred_language'], $supported_languages);
		if ($idx !== false)
			return $supported_languages[$idx];
	}

	// If no cookie is set, accept from HTTP.
	// TODO doesn't work, http.so crashes
	// $http_locale = http_negotiate_language($supported_languages);

	// If all else fails, use the default language.
	return $default_locale;
}

/** Validate the language setting and store it in a cookie. */
function setLanguageCookie() {
	global $supported_languages;

	if (!isset($_POST['set_language']))
		return;

	$idx = array_search($_POST['set_language'], $supported_languages);
	if ($idx === false)
		return;

	$_SESSION['preferred_language'] = $supported_languages[$idx];
}

/** Custom translations for strings in inc.config.php that do not appear in other
	source code. Configure using $customTranslations. */
function translate($string) {
	global $current_language, $customTranslations;

	if (isset($customTranslations[$string]) &&
		isset($customTranslations[$string][$current_language]))
		return $customTranslations[$string][$current_language];
	else
		return $string;
}
?>
