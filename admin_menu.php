<?php
include_once("includes/inc.global.php");
$p->site_section = ADMINISTRATION;
$p->page_title = $lng_admin_menu;

$cUser->MustBeLevel(1);

$query = $cDB->Query("SELECT sum(balance) from ". DATABASE_MEMBERS .";");
		
if($row = mysql_fetch_array($query)) {
		$balance = $row[0];
}			
			
$list = "<STRONG>".$lng_current_balance_is." ".$balance.": </STRONG><P>";

$list .= "<table border=0 cellpadding=5>";

$list .= "<tr valign=top>";
$list .= "<td>";
$list .= "<STRONG>".$lng_accounts."</STRONG><P>";
$list .= "<A HREF=member_create.php><FONT SIZE=2>".$lng_create_new_member_account."</FONT></A><BR>";
$list .= "<A HREF=member_to_edit.php><FONT SIZE=2>".$lng_edit_a_member_account."</FONT></A><BR>";
if ($cUser->member_role > 1) {
	$list .= "<A HREF=photo_to_edit.php><FONT SIZE=2>".$lng_edit_a_member_photo."</FONT></A> <font color=red>".$lng_new."</font><BR>";
}
if ($cUser->member_role > 1) {
	$list .= "<A HREF=member_choose.php?action=member_status_change&inactive=Y><FONT SIZE=2>".$lng_inactivate_reactivate_member_account."</FONT></A><BR>";
}
$list .= "<A HREF=member_contact_create.php?mode=admin><FONT SIZE=2>".$lng_add_joint_member_to_existing_account."</FONT></A><BR>";
$list .= "<A HREF=member_contact_to_edit.php><FONT SIZE=2>".$lng_edit_delete_joint_member."</FONT></A><BR>";

if ($cUser->member_role > 1) {
	$list .= "<A HREF=member_unlock.php><FONT SIZE=2>".$lng_unlock_account_reset_pwd."</FONT></A><BR>";
}
echo OVRIDE_BALANCES;

if (OVRIDE_BALANCES==true && $cUser->member_role > 1) // Only display Override Balance link if it is turned on in config file
	$list .= "<A HREF=balance_to_edit.php><FONT SIZE=2>".$lng_override_member_account_balance."</FONT></A><BR>";

if ($cUser->member_role>1)
	$list .= "<a href=manage_restrictions.php><font size=2>".$lng_manage_account_restrictions."</font></a> <font color=red>".$lng_new."</font>";
	
$list .= "</td><td>";


if ($cUser->member_role > 1) {
	$list .= "<STRONG>".$lng_exchanges."</STRONG><P>";
	$list .= "<A HREF=member_choose.php?action=trade><FONT SIZE=2>".$lng_record_exchange_for_member."</FONT></A><BR>";
	$list .= "<A HREF=trade_reverse.php><FONT SIZE=2>".$lng_reverse_exchange_made_in_error."</FONT></A><BR>";
	$list .= "<A HREF=member_choose.php?action=feedback_choose><FONT SIZE=2>".$lng_record_feedback_for_member."</FONT></A><P>";
}
$list .= "</td></tr>";
$list .= "<tr valign=top><td>";
$list .= "<strong>".$lng_listings."</strong><p>";

$list .= "<em>".$lng_offers."</em><P>";
$list .= "<A HREF=listing_create.php?type=Offer&mode=admin><FONT SIZE=2>".$lng_create_new_offer_listing_for_member."</FONT></A><BR>";
$list .= "<A HREF=member_choose.php?action=listing_to_edit&get1=type&get1val=Offer><FONT SIZE=2>".$lng_edit_members_offered_listing."</FONT></A><BR>";
$list .= "<A HREF=member_choose.php?action=listing_delete&get1=type&get1val=Offer><FONT SIZE=2>".$lng_delete_members_offered_listing."</FONT></A><P>";

$list .= "<em>".$lng_wants."</em><P>";
$list .= "<A HREF=listing_create.php?type=Want&mode=admin><FONT SIZE=2>".$lng_create_new_want_listing_for_member."</FONT></A><BR>"; // replaced $lng_create_new_want_listing by $lng_create_new_want_listing_for_member by ejkv
$list .= "<A HREF=member_choose.php?action=listing_to_edit&get1=type&get1val=Want><FONT SIZE=2>".$lng_edit_members_wanted_lisitng."</FONT></A><BR>";
$list .= "<A HREF=member_choose.php?action=listing_delete&get1=type&get1val=Want><FONT SIZE=2>".$lng_delete_members_wanted_lisitng."</FONT></A><P>";

$list .= "<em>".$lng_miscellaneous."</em><P>";
$list .= "<A HREF=member_choose.php?action=holiday><FONT SIZE=2>".$lng_member_going_holiday."</FONT></A>";
if ($cUser->member_role > 1) {
	$list .= "<BR><A HREF=category_create.php><FONT SIZE=2>".$lng_create_new_listing_category."</FONT></A><BR>";
	$list .= "<A HREF=category_choose.php><FONT SIZE=2>".$lng_edit_delete_listing_category."</FONT></A>";
	$list .= "<BR><A HREF=state_create.php><FONT SIZE=2>".$lng_create_new_listing_state."</FONT></A><BR>"; // added by ejkv
	$list .= "<A HREF=state_choose.php><FONT SIZE=2>".$lng_edit_listing_state."</FONT></A>"; // added by ejkv
}
$list .= "<P>";
$list .= "</td><td>";

$list .= "<p><STRONG>".$lng_content."</STRONG><P>";
$list .= "<em>".$lng_information_pages."</em><p>";
$list .= "<A HREF=create_info.php><FONT SIZE=2>".$lng_create_new_info_page."</FONT></A><BR>";
$list .= "<A HREF=edit_info.php><FONT SIZE=2>".$lng_edit_info_pages."</FONT></A><BR>";
$list .= "<A HREF=delete_info.php><FONT SIZE=2>".$lng_delete_info_pages."</FONT></A><BR>";
$list .= "<A HREF=info_permissions.php><FONT SIZE=2>".$lng_edit_info_page_permissions."</FONT></A> <font size=2 color=red>".$lng_new."</font> <BR>";
$list .= "<A HREF=info_url.php><FONT SIZE=2>".$lng_see_info_page_urls."</FONT></A><p>";

$list .= "<em>".$lng_news_and_events."</em><p>";
$list .= "<A HREF=news_create.php><FONT SIZE=2>".$lng_create_news_item."</FONT></A><BR>";
$list .= "<A HREF=news_to_edit.php><FONT SIZE=2>".$lng_edit_news_item."</FONT></A><BR>";
$list .= "<A HREF=newsletter_upload.php><FONT SIZE=2>".$lng_upload_newsletter."</FONT></A><BR>";
$list .= "<A HREF=newsletter_delete.php><FONT SIZE=2>".$lng_delete_newsletter."</FONT></A><BR>";

$list .= "</td></tr>";

$list .= "<tr valign=top><td>";

$list .= "<strong>".$lng_admin_fees."</strong><p>";

if (TAKE_MONTHLY_FEE && $cUser->member_role > 1) {
    $ts = time();

   // $list .= "<strong>Monthly fee</strong><p>";
   
   // File missing??
 //   $list .= "<a href='monthly_fee_list.php'>List of monthly fees</a><br>";
    // CID = Confirmation ID.
    $list .= "<a href='take_monthly_fee.php?CID=$ts'>
                <font size=2>".$lng_take_monthly_fee."</font></a><br>";
    $list .= "<a href='refund_monthly_fee.php'>
                <font size=2>".$lng_refund_monthly_fee."</font></a><p>";
}

if (TAKE_SERVICE_FEE==true && $cUser->member_role > 1) {
	
	$list .= "<p><a href='service_charge.php?CID=$ts'>
                <font size=2>".$lng_take_service_charge."</font></a> <font color=red>".$lng_new."</font><br>
                <a href='refund_service_charge.php'>
                <font size=2>".$lng_refund_service_charge."</font></a> <font color=red>".$lng_new."</font><p>";
}
$list .= "</td><td>";

$list .= "<STRONG>".$lng_system_and_reporting."</STRONG><P>";
if ($cUser->member_role > 1) {
	$list .= "<A HREF=settings.php><FONT SIZE=2>".$lng_site_settings."</FONT></A> <font color=red>".$lng_new."</font><BR>";
	$list .= "<A HREF=mysql_backup.php><FONT SIZE=2>".$lng_mysql_backup."</FONT></A> <font color=red>".$lng_new."</font><BR>";

	$list .= "<A HREF=contact_all.php><FONT SIZE=2>".$lng_send_mail_to_all_members."</FONT></A><BR>";
}
$list .= "<A HREF=report_no_login.php><FONT SIZE=2>".$lng_view_members_not_logged_in."</FONT></A><BR><p>";

$list .= "</td></tr></table>";

$p->DisplayPage($list);

?>
