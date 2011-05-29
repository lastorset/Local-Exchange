<?php
include_once("includes/inc.global.php");
$p->site_section = 0;

$cUser->MustBeLoggedOn();

$list = "<H2>"._("Welcome to "). SITE_SHORT_TITLE .", ". $cUser->person[0]->first_name ."!</H2>";
$list .= _("Please choose from the following options, or navigate using the buttons on the sidebar to the left.")."<P>";

$list .= "<STRONG>"._("Member Settings")."</STRONG><P>";
$list .= "<A HREF=password_change.php><FONT SIZE=2>"._("Change My Password")."</FONT></A><BR>";
$list .= "<A HREF=member_edit.php?mode=self><FONT SIZE=2>"._("Edit My Personal Information")."</FONT></A><BR>";
$list .= "<A HREF=member_contact_create.php?mode=self><FONT SIZE=2>"._("Add a Joint Member to My Account")."</FONT></A><BR>";
$list .= "<A HREF=member_contact_choose.php><FONT SIZE=2>"._("Edit a Joint Member")."</FONT></A><P>";

$list .= "<STRONG>"._("Offered Listings")."</STRONG><P>";
$list .= "<A HREF=listings.php?type=Offer><FONT SIZE=2>"._("View Offered Listings")."</FONT></A><BR>";
$list .= "<A HREF=listing_create.php?type=Offer><FONT SIZE=2>"._("Create New Offer Listing")."</FONT></A><BR>";
$list .= "<A HREF=listing_to_edit.php?type=Offer><FONT SIZE=2>"._("Edit Offered Listings")."</FONT></A><BR>";
$list .= "<A HREF=listing_delete.php?type=Offer><FONT SIZE=2>"._("Delete Offered Listings")."</FONT></A><P>";

$list .= "<STRONG>"._("Wanted Listings")."</STRONG><P>";
$list .= "<A HREF=listings.php?type=Want><FONT SIZE=2>"._("View Wanted Listings")."</FONT></A><BR>";
$list .= "<A HREF=listing_create.php?type=Want><FONT SIZE=2>"._("Create New Want Listing")."</FONT></A><BR>";
$list .= "<A HREF=listing_to_edit.php?type=Want><FONT SIZE=2>"._("Edit Wanted Listings")."</FONT></A><BR>";
$list .= "<A HREF=listing_delete.php?type=Want><FONT SIZE=2>"._("Delete Wanted Listings")."</FONT></A><P>";

$list .= "<STRONG>"._("Exchanges")."</STRONG><P>";
$list .= "<A HREF=trade.php><FONT SIZE=2>"._("Record an Exchange")."</FONT></A><BR>";
$list .= "<A HREF=trade_history.php?mode=self><FONT SIZE=2>"._("View My Balance and Exchange History")."</FONT></A><BR>";
$list .= "<A HREF=trades_to_view.php><FONT SIZE=2>"._("View Another Member's Exchange History")."</FONT></A><P>";

if ($cUser->member_role > 0) {
	$list .= "<STRONG>"._("Administration")."</STRONG><P>";
	$list .= "<A HREF=member_create.php><FONT SIZE=2>"._("Create a New Member Account")."</FONT></A><BR>";
	$list .= "<A HREF=member_to_edit.php><FONT SIZE=2>"._("Edit a Member Account")."</FONT></A><BR>";
	$list .= "<A HREF=member_contact_create.php?mode=admin><FONT SIZE=2>"._("Add a Joint Member to an Existing Account")."</FONT></A><BR>";
	$list .= "<A HREF=member_contact_to_edit.php><FONT SIZE=2>"._("Edit a Joint Member")."</FONT></A><BR>";
}
if ($cUser->member_role > 1) {
	$list .= "<A HREF=trade_reverse.php><FONT SIZE=2>"._("Reverse an Exchange that was Made in Error")."</FONT></A><BR>";
}

$p->DisplayPage($list);

?>
