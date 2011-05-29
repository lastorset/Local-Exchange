<?php
include_once("includes/inc.global.php");
$p->site_section = 0;

$output = _("In order to access this section of the site, you need to be logged on.")."<BR><BR>"._("If you already have an account, please log in below").":<BR><BR><CENTER><DIV STYLE='width=60%; padding: 5px;'><FORM ACTION=".SERVER_PATH_URL."/login.php METHOD=POST><INPUT TYPE=HIDDEN NAME=action VALUE=login><INPUT TYPE=HIDDEN NAME=location VALUE='".$_SESSION["REQUEST_URI"]."'><TABLE class=NoBorder><TR><TD ALIGN=RIGHT>"._("Member ID").":</TD><TD ALIGN=LEFT><INPUT TYPE=TEXT SIZE=12 NAME=user></TD></TR><TR><TD ALIGN=RIGHT>"._("Password").":</TD><TD ALIGN=LEFT><INPUT TYPE=PASSWORD SIZE=12 NAME=pass></TD></TR></TABLE><DIV align='right'><INPUT TYPE=SUBMIT VALUE="._("Login")."></DIV></FORM></DIV></CENTER><BR>"._("If you don't have an account, please")." <A HREF=contact.php>"._("contact")."</A> "._("us to join").".<BR>";

$p->DisplayPage($output);

?>
