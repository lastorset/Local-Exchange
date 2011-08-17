<?php

include_once("includes/inc.global.php");
$p->site_section = 0;

print $p->MakePageHeader();
print $p->MakePageMenu();

print _("<table cellpadding=0 cellspacing=2><tr><td><img src='images/localx_home.png' align=top></td><td><img src='images/localx_black.png' align=top><br><img src='images/mutual_time.png' align=top></td></tr></table><p><strong> <font size='4'>Why have a Local, Sustainable Currency?</font></strong></p><ul><li><strong>Enhance Your Prosperity<br><br></strong></li><li><strong> Build a Sustainable Community<br><br></strong></li><li><strong>Utilize Your Talents<br><br></strong></li><li><strong> Nurture the Unique Quality<br>of Your Hometown <br><br></strong></li><li><strong> Have Fun</strong></li></ul>");

print $p->MakePageFooter();

?>
