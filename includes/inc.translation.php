<?php
/* Initialize gettext */

class cTranslationSupport {
	/// Whether string translation is enabled
	// TODO Need we really have all these fields static?
	static $translation_enabled = false;
	/// The fallback locale, used when nothing else works.
	static $default_locale;

	/// All languages supported by this version of Local Exchange.
	static $supported_languages = array(
		'en_US',     // English (no translation)
		'nb_NO.utf8' // Norwegian Bokmål // TODO Is '.utf8' really needed, if we maintain a UTF-8 ecosystem?
	);

	/// Available languages in the drop-down menu, as decided by the administrator.
	static $available_languages = array();

	/** Aliases for HTTP header Accept-Language. Accept-Language expects the key; the value is
		the locale we use. The first entry has special significance: it is set to the default
		locale in selectLocale. */
	static $http_language_map = array(
		'first_entry' => 'none',
		'en' => 'en_US',
		'nb' => 'nb_NO.utf8',
		'no' => 'nb_NO.utf8'
	);

	public $current_language = 'en_US';

	function connect_to_db() {
		// PHP will automatically reuse the link when the database class connects.
		mysql_connect(DATABASE_SERVER,DATABASE_USERNAME,DATABASE_PASSWORD)
			or die("Could not connect to database for language selection");
		mysql_selectdb(DATABASE_NAME)
			or die("Could not select database");
	}

	function initialize() {
		$this->connect_to_db();
		$this->retrieveAvailableLanguages();

		// Set the language cookie, if the user posted a preference.
		$this->storeLanguageChoice();

		if (self::$translation_enabled)
		{
			// Select the locale based on a variety of factors, and save it this member.
			$this->current_language = $this->selectLocale();
			$ret = setlocale(LC_MESSAGES, $this->current_language);

			// Gettext invocations
			bindtextdomain("messages", "./includes/lang");
			bind_textdomain_codeset("messages", "UTF-8");
			textdomain("messages");
		}
	}

	/** The directory containing the files supporting the current language.
	 */
	function currentLanguageDir() {
		$lang_no_encoding = explode('.', $this->current_language)[0];
		$dir = "includes/lang/$lang_no_encoding/LC_MESSAGES";
		return $dir;
	}

	/** Pick the locale to use based on the following prioritized factors:

		- The user's preferred language, as stored in his profile.
		- What the user picked in this session using the dropdown.
		- What the user agent suggests in the HTTP request.
		- The site's default language.

		@note This code does not cancel a user's choice of a language that previously was
		      available but no longer is. This is simpler, more respectful, arguably less
		      surprising, and not less secure. */
	function selectLocale() {
		// First, if the user is logged in, check preferred language.
		$preference = $this->retrieveLanguagePreference();
		if ($preference)
			return $preference;

		// If not logged in, or language is not available, check the cookie.
		if (isset($_SESSION['preferred_language']))
		{
			$idx = array_search($_SESSION['preferred_language'], self::$supported_languages);
			if ($idx !== false)
				return self::$supported_languages[$idx];
		}

		// If no cookie is set, accept from HTTP.
		if (extension_loaded('http'))
		{
			// Prepare the first member of the map as the default for http_negotiate_language
			self::$http_language_map['first_entry'] = self::$default_locale;

			$http_locale = http_negotiate_language(array_keys(self::$http_language_map), $result);
			return self::$http_language_map[$http_locale];
		}

		// If all else fails, use the default language.
		return self::$default_locale;
	}

	/**	If the user is logged in, get their language preference.

		@return the string stored by storeLanguageChoice, or false if the user is logged in
				or has not chosen a language. */

	/*	This code (and the database portion of storeLanguageChoice) should really have been with the other
		code in the cMember class, but because of an unfortunate circular dependency chain, it cannot be
		there: this file would depend on the cMember class, which depends on inc.config.php, which
		depends on selecting a language, which is done in this file.

		The future solution is likely to translate customizable strings at display time, so that the
		configuration file no longer dependent on language.  Alternatively, use a more sophisticated
		configuration mechanism, where default strings can be set programmatically (or some other way
		Gettext can pick them up) and webmaster can override. */
	static function retrieveLanguagePreference() {
		if (!isset($_SESSION["user_login"]))
			return false;
		$user = $_SESSION["user_login"];

		$resource = mysql_query("SELECT preferred_language FROM ". DATABASE_MEMBERS ." WHERE member_id = '". $user ."'");
		if (!$resource)
			return false;

		$row = mysql_fetch_array($resource);
		return $row[0];
	}

	/**	Validate the language setting and store it in a cookie and in the database. */

	/*	The direct database access performed by this function is unfortunate; see note at
		retrieveLanguagePreference for more information. */
	function storeLanguageChoice() {
		// If user chose a language, store it in a cookie
		if (!isset($_POST['set_language']))
			return;

		$idx = array_search($_POST['set_language'], self::$available_languages);
		if ($idx === false)
			return;

		$_SESSION['preferred_language'] = self::$available_languages[$idx];

		// If user is logged in, store their choice in the database
		if (!isset($_SESSION["user_login"]))
			return;
		$user = $_SESSION["user_login"];

		// No escaping needed, since user is not in control of string
		mysql_query("UPDATE ". DATABASE_MEMBERS ."
			SET preferred_language = '". $_SESSION['preferred_language'] ."'
			WHERE member_id = '". $user. "'");
	}

	static function retrieveAvailableLanguages() {
		// Enable translation?
		$resource = mysql_query("SELECT current_value FROM `settings` WHERE name = 'ENABLE_TRANSLATION'");

		$row = mysql_fetch_array($resource);
		if ($row['current_value'] == 'TRUE')
			self::$translation_enabled = true;
		else
			return;

		// Default language
		$resource = mysql_query("SELECT current_value, default_value FROM `settings` WHERE name = 'DEFAULT_LANGUAGE'");

		$row = mysql_fetch_array($resource);
		if ($row['current_value'])
			self::$default_locale = $row['current_value'];
		else
			self::$default_locale = $row['default_value'];

		// Available languages
		$resource = mysql_query("SELECT langcode FROM `languages` WHERE available = TRUE");
		self::$available_languages = array(); // Empty in case this is called several times

		while($row = mysql_fetch_array($resource))
		{
			array_push(self::$available_languages, $row['langcode']);
		}
	}

	/** Custom translations for strings in inc.config.php that do not appear in other
		source code. Configure using $customTranslations. */
	function translate($string) {
		global $customTranslations;
		if (isset($customTranslations[$string]) &&
			isset($customTranslations[$string][$this->current_language]))
			return $customTranslations[$string][$this->current_language];
		else
			return $string;
	}
}

$translation = new cTranslationSupport();
if (!isset($running_upgrade_script) || !$running_upgrade_script)
	$translation->initialize();

function translate($string) {
	global $translation;
	return $translation->translate($string);
}

/** Replaces placeholder HTML tags in the string with real HTML tags.
 * For example, the string "See your <a>profile</a> or <j>recent
 * trades</j>" translated to Norwegian with the dictionary
 *
 * @code{.php}
 * array(
 *     'a' => 'a href=profile.php',
 *     'j' => 'a href=recent.php'
 * )
 * @endcode
 *
 * will be turned into "Se <a href=profile.php>profilen</a> din eller <a
 * href=recent.php>nylige handler</a>.".
 *
 * This is useful when you don't want to enshrine HTML attributes in your
 * translatable strings.
 *
 * No error is given if the given tags don't exist in the input string.
 *
 * @param string string the string whose tags to replace.
 * @param tags string[] a dictionary (associative array) from placeholder tags to actual
 *             tags with attributes. Each placeholder tag should be unique, and
 *             they don't have to have the name of a real tag.
 */
function replace_tags($string, $tags) {
	foreach($tags as $tag => $attrs) {
		if(strpos($attrs, " ") === FALSE)
			$realtag = $attrs;
		else
			$realtag = substr($attrs, 0, strpos($attrs, " "));

		$string = str_replace(array(
			"<$tag>",
			"</$tag>"
		), array(
			"<$attrs>",
			"</$realtag>"
		), $string);
	}
	return $string;
}

/** Renders a template.
 *
 * For example, the template "Dear {{ member_name }},"
 */
function render_template($template, $replacements) {
	return preg_replace_callback("/\{\{\s*([a-zA-Z_]*?)\s*\}\}/",
		function ($matches) use ($replacements) {
			if (array_key_exists($matches[1], $replacements)) {
				return $replacements[$matches[1]];
			} else {
				return $matches[0];
			}
		}, $template);
}
?>
