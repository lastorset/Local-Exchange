<?php
/** New features are introduced with defines such as GAME_MECHANICS. Installations that upgrade will
 * not have these symbols defined, and they will be treated like text and consistently activated.
 * To avoid this, new features are given default values here.
 *
 * When adding to the list, be conservative and leave features disabled if they might come as a
 * surprise to users of the upgraded system, or if they require additional configuration.
 * Additions to inc.config.php.default can be less conservative, since new users of new
 * installations won't know what to expect.
 */

$features = array(
	'SELF_REGISTRATION'       => false,
	'REQUIRE_EMAIL'           => true,
	'PASSWORD_MIN_LENGTH'     => 7,
	'NEW_MEMBER_EMAIL_ADMIN'  => true,
	'CKEDITOR'                => false, // needs additional configuration
	'GAME_MECHANICS'          => false,
	'FAVICON'                 => "localx_logo.png"
);

foreach ($features as $feature => $default) {
	if (!defined($feature))
		define($feature, $default);
}

?>
