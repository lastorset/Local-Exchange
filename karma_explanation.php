<?php

include_once("includes/inc.global.php");
$p->site_section = SECTION_INFO;
$p->page_title = _("What are Karma points?");

print $p->MakePageHeader();
print $p->MakePageMenu();
print $p->MakePageTitle();

if (GAME_MECHANICS) {
	$balance = $cUser->balance;
	$balance_text = sprintf("%+.2f", $balance);
	if ($balance > 0)
		// Translation hint: "it" refers to the member's account balance.
		$balance_hint .= _("Spend some of them to gain Karma points!");
	else if ($balance < 0)
		$balance_hint .= _("Do something for other members to gain Karma points!");

	$c = get_defined_constants();
	print <<<HTML
		<article class=karma-explanation>
			<table id=karma-explanation>
				<tr>
					<th/>
					<th>
						Karma points
					<th/>
					<th>
						Balance
				<tr class=main-numbers>
					<th>
						Numbers
					<td class=karma>
						{$cUser->GetKarma()}
					<td rowspan=2 class=picture />
						<!-- Picture inserted via CSS -->
					<td class=balance>
						{$balance_text}
				<tr class=exposition>
					<th>
						Explanation
					<td class=karma>
						{$_("These are your Karma points. They reflect your activity in the LETS system, and the equilibrium between how much you have done for others and how much others have done for you.")}
					<td>
HTML;
						// Translation hint: %s is the currency name, which may be "hours".
						printf(_("This is your account balance. It shows how many %s you have earned minus what you have spent."), $site_settings->getUnitString());
					print <<<HTML
						<strong>$balance_hint</strong>
			</table>
		</article>
HTML;
}
else {
	printf(_("Karma points are disabled in %s. If they were enabled, they would reflect your activity in the LETS system, and the equilibrium between how much you have done for others and how much others have done for you. Contact the administrators if you wish to request that they be enabled."), SITE_SHORT_TITLE);
	print "<img src=//". HTTP_BASE ."/images/handshake-color.svgz style='display: block; margin: 5em auto; width: 150px' width=75>";
}

print $p->MakePageFooter();

?>
