<?php

// This include file is for processes that only need to be run periodically, 
// using the cSystemEvent class's TimeForEvent() method to limit their
// execution so that they don't bog the system down.
//
// This file is meant to be included in one or more pages in the system
// which is regularly visited by users.  It's best to include it AFTER a page has
// been displayed, also to prevent excessive page load times.


// The following will expire listings for inactive members as set
// in inc.config.php.  

include_once("classes/class.listing.php");

$e = new cSystemEvent(ACCOUT_EXPIRATION);
if(EXPIRE_INACTIVE_ACCOUNTS and $e->TimeForEvent()) {
	$members = new cMemberGroup;
	$members->ExpireListings4InactiveMembers();
	$e->LogEvent();
}


// The following three events are for automatic email updates regarding new modified 
// listings

$e = new cSystemEvent(MONTHLY_LISTING_UPDATES, MONTHLY*1440);
if(EMAIL_LISTING_UPDATES and $e->TimeForEvent()) {
	$members = new cMemberGroup;
	$members->EmailListingUpdates(MONTHLY);
	$e->LogEvent();
}

$e = new cSystemEvent(WEEKLY_LISTING_UPDATES, WEEKLY*1440);
if(EMAIL_LISTING_UPDATES and $e->TimeForEvent()) {
	$members = new cMemberGroup;
	$members->EmailListingUpdates(WEEKLY);
	$e->LogEvent();
}

$e = new cSystemEvent(DAILY_LISTING_UPDATES, DAILY*1440);
if(EMAIL_LISTING_UPDATES and $e->TimeForEvent()) {
	$members = new cMemberGroup;
	$members->EmailListingUpdates(DAILY);
	$e->LogEvent();
}

?>
