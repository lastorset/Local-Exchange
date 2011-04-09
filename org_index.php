<?php

include_once("includes/inc.global.php");
$p->site_section = 0;

print $p->MakePageHeader();
print $p->MakePageMenu();

print $lng_home_text;

print $p->MakePageFooter();

?>
