<?php

include_once("../includes/inc.global.php");
$p->site_section = SECTION_INFO;
$p->page_title = "Learn More";

print $p->MakePageHeader();
print $p->MakePageMenu();
print $p->MakePageTitle();

?>

<a href="what_is_local_currency.php"><font size="3">What is a local currency?</font></a>

<ul><li><a href="how_does_it_work.php"><font size="2">How does a local currency work?</font></a></li>

<li><a href="what_does_it_do.php"><font size="2">What does a local currency do?</font></a></li>

<li><a href="how_do_i_join.php"><font size="2">How do I join?</font></a></li></ul>

<a href="what_are_time_dollars.php"><font size="3">What is an 'Hour' or 'Time Dollar'?</font></a>

<p><a href="agreement.php"><font size="3">Members Agreement</font></a></p>

<p><a href="history.php"><font size="3">Local currency history in Whatcom County</font></a></p>

<p><a href="reading.php"><font size="3">Recommended Reading List - Great Books</font></a></p>

<p><a href="links.php"><font size="3">Links to more Information</font></a></p>

<p><a href="amusingstory.php"><font size="3">An Amusing Story - The Harvard MBA and the Mexican Fisherman</font></a></p>


<?

print $p->MakePageFooter();

?>
