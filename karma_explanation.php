<?php

include_once("includes/inc.global.php");
$p->site_section = SECTION_INFO;
$p->page_title = _("What are Karma points?");

print $p->MakePageHeader();
print $p->MakePageMenu();
print $p->MakePageTitle();

$balance = $cUser->balance;
$balance_text = sprintf("%+.2f", $balance);
if ($balance > 0)
	$balance_hint .= _("Spend some of it to gain Karma points!");
else if ($balance < 0)
	$balance_hint .= _("Do something for others to gain Karma points!");

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
				{$_("These are your Karma points. They reflect your activity in the system, and the equilibrium between how much you have done for others and how much others have done for you.")}
			<td>
				{$_("This is your account balance. It shows how many ". $site_settings->getUnitString() ." you have received minus what you have sent.")} <strong>$balance_hint</strong>
	</table>
</article>
HTML;

print $p->MakePageFooter();

?>
