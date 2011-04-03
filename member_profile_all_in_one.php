<?php
include_once("includes/inc.global.php");
$p->site_section = SITE_SECTION_OFFER_LIST;

$cUser->MustBeLoggedOn();

$list = "<H2>".$lng_welcome_to. SITE_SHORT_TITLE .", ". $cUser->person[0]->first_name ."!</H2>";
$list .= $lng_pls_choose_options_or_navigate."<P>";

$list .= "<STRONG>".$lng_member_settings."</STRONG><P>";
$list .= "<A HREF=password_change.php><FONT SIZE=2>".$lng_change_my_pwd."</FONT></A><BR>";
$list .= "<A HREF=member_edit.php?mode=self><FONT SIZE=2>".$lng_edit_my_pers_info."</FONT></A><BR>";
$list .= "<A HREF=member_contact_create.php?mode=self><FONT SIZE=2>".$lng_add_joint_member_to_my_acc."</FONT></A><BR>";
$list .= "<A HREF=member_contact_choose.php><FONT SIZE=2>".$lng_edit_joint_member."</FONT></A><P>";

$list .= "<STRONG>".$lng_offered_listings."</STRONG><P>";
$list .= "<A HREF=listings.php?type=Offer><FONT SIZE=2>".$lng_view_offered_listings."</FONT></A><BR>";
$list .= "<A HREF=listing_create.php?type=Offer><FONT SIZE=2>".$lng_create_new_offer_listing."</FONT></A><BR>";
$list .= "<A HREF=listing_to_edit.php?type=Offer><FONT SIZE=2>".$lng_edit_offered_listings."</FONT></A><BR>";
$list .= "<A HREF=listing_delete.php?type=Offer><FONT SIZE=2>".$lng_delete_offered_listings."</FONT></A><P>";

$list .= "<STRONG>".$lng_wanted_listings."</STRONG><P>";
$list .= "<A HREF=listings.php?type=Want><FONT SIZE=2>".$lng_view_wanted_listings."</FONT></A><BR>";
$list .= "<A HREF=listing_create.php?type=Want><FONT SIZE=2>".$lng_create_new_want_listing."</FONT></A><BR>";
$list .= "<A HREF=listing_to_edit.php?type=Want><FONT SIZE=2>".$lng_edit_wanted_listings."</FONT></A><BR>";
$list .= "<A HREF=listing_delete.php?type=Want><FONT SIZE=2>".$lng_delete_wanted_listings."</FONT></A><P>";

$list .= "<STRONG>".$lng_exchanges."</STRONG><P>";
$list .= "<A HREF=trade.php><FONT SIZE=2>".$lng_record_exchange."</FONT></A><BR>";
$list .= "<A HREF=trade_history.php?mode=self><FONT SIZE=2>".$lng_view_balance_and_history."</FONT></A><BR>";
$list .= "<A HREF=trades_to_view.php><FONT SIZE=2>".$lng_view_members_history."</FONT></A><P>";

if ($cUser->member_role > 0) {
	$list .= "<STRONG>".$lng_administration."</STRONG><P>";
	$list .= "<A HREF=member_create.php><FONT SIZE=2>".$lng_create_new_member_account."</FONT></A><BR>";
	$list .= "<A HREF=member_to_edit.php><FONT SIZE=2>".$lng_edit_a_member_account."</FONT></A><BR>";
	$list .= "<A HREF=member_contact_create.php?mode=admin><FONT SIZE=2>".$lng_add_joint_member_to_existing_account."</FONT></A><BR>";
	$list .= "<A HREF=member_contact_to_edit.php><FONT SIZE=2>".$lng_edit_joint_member."</FONT></A><BR>";
}
if ($cUser->member_role > 1) {
	$list .= "<A HREF=trade_reverse.php><FONT SIZE=2>".$lng_reverse_exchange_made_in_error."</FONT></A><BR>";
}

$p->DisplayPage($list);

?>
