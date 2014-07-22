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
	'NEW_MEMBER_EMAIL_ADMIN'  => true,
	'CKEDITOR'                => true,
	'CKEDITOR_PATH'           => 'ckeditor',
	'KCFINDER_PATH'           => 'kcfinder',
	'GAME_MECHANICS'          => false,
	'EXPLAIN_KARMA'           => 10,
	'FAVICON'                 => "localx_logo.png",
	'HOME_PAGE_MESSAGE'       => replace_tags(_("<a>Learn more</a> about this community!"), array('a' => "a href=info/more.php")),
	'SPAM_WARNING'            => false,
	'LOG_EMAIL_UPDATES'       => false,
	'VIEW_OTHER_TRADES_PERMISSION_LEVEL' => 1,
);

foreach ($features as $feature => $default) {
	if (!defined($feature))
		define($feature, $default);
}

// Default welcome e-mail, in case custom text for a language is missing.

$welcome_email_default = _(<<<WELCOME
Dear {{ member_name }},

Thank you for registering with {{ site_shortname }}.

We greatly appreciate each new member! Your talents and desires will make the whole LETS network more interesting and lively.

To get the most out of the LETS network, we ask you to register what you can offer other members and what you'd like to receive from them. You can find inspiration by browsing the listings other members already have created:

- See what other members offer: {{ offered_listings }}
- See what other members want: {{ wanted_listings }}
- List your own offers and wants: {{ listings_menu }}

In the LETS system you are not limited to trading one-on-one with another member. You can do work for one person in the system, receive an hour for that and then spend an hour by enjoying the services of somebody else in the system. LETS is not a barter system, but a "normal" economic network in which all members can trade amongst each other. The difference is that we use our time instead of a currency.

Your new username and password are listed at the end of this message. You can change your password by going to the Member Profile section after you log in. If you have any questions, feel free to ask them by e-mail.

Welcome to the LETS! We hope you will enjoy it as much as many others already do.

With kind regards,

The {{ site_shortname }} team

- Your username: {{ username }}
- Your password: {{ password }}

Log in at {{ login_link }}
WELCOME
);

?>
