<?php

/************************************************************
This file includes necesary class files and other include files.
It also defines global constants, and kicks off the session. 
It should be included by all pages in the site.  It does not
need to be edited for site installation, and in fact should
only be modified with care.
************************************************************/

/*********************************************************/
/******************* GLOBAL CONSTANTS ********************/

// These constants should only be changed with extreme caution
define("LOGGED_OUT","!");
define("GO_BACK","< Back");
define("GO_NEXT","Next >");
define("GO_FINISH","Finish");
define("REDIRECT_ON_ERROR", true);
define("FIRST", true);
define("LONG_LONG_AGO", "1970-01-01");
define("FAR_FAR_AWAY", "2030-01-01");
define("ACTIVE","A");
define("INACTIVE","I");
define("EXPIRED","E");
define("DISABLED","D");
define("LOCKED","L");
define("BUYER","B");
define("SELLER","S");
define("POSITIVE","3");
define("NEGATIVE","1");
define("NEUTRAL","2");
define ("OFFER_LISTING", "Offer");
define ("OFFER_LISTING_CODE", "O");
define ("WANT_LISTING", "Want");
define ("WANT_LISTING_CODE", "W");
define("DAILY",1);
define("WEEKLY",7);
define("MONTHLY",30);
define("NEVER",0);

// The following constants are used for logging. Add new categories if
// needed, but edit existing ones with caution.
define("TRADE","T"); // Logging event category
define("TRADE_BY_ADMIN","A");
define("TRADE_ENTRY","T");
define("TRADE_REVERSAL","R");
define("TRADE_MONTHLY_FEE", "M");
define("TRADE_MONTHLY_FEE_REVERSAL", "N");
define("FEEDBACK","F"); // Logging event category
define("FEEDBACK_BY_ADMIN","A");
define("ACCOUT_EXPIRATION","E"); // Logging event category - System Event
define("DAILY_LISTING_UPDATES","D"); // Logging event category - System Event
define("WEEKLY_LISTING_UPDATES","W"); // Logging event category - System Event
define("MONTHLY_LISTING_UPDATES","M"); // Logging event category - System Event

/*********************************************************/
define("LOCALX_VERSION", "1.01");

/**********************************************************/
/***************** DATABASE VARIABLES *********************/

define ("DATABASE_LISTINGS","listings");
define ("DATABASE_PERSONS","person");
define ("DATABASE_MEMBERS","member");
define ("DATABASE_TRADES","trades");
define ("DATABASE_LOGINS","logins");
define ("DATABASE_LOGGING","admin_activity");
define ("DATABASE_USERS","member");
define ("DATABASE_CATEGORIES", "categories");
define ("DATABASE_FEEDBACK", "feedback");
define ("DATABASE_REBUTTAL", "feedback_rebuttal");
define ("DATABASE_NEWS", "news");
define ("DATABASE_UPLOADS", "uploads");
define ("DATABASE_SESSION", "session");
define ("DATABASE_STATES", "states");  // added by ejkv

$global = ""; 	// $global lets other includes know that 
					// inc.global.php has been included

include_once("inc.config-database.php");

/* Initial session handling code starts */
require_once("session_handler.php");
session_name("LOCAL_EXCHANGE");
session_start();
/* Initial session handling code ends */

// Translation is required to read config file
include_once("inc.translation.php");
include_once("inc.config.php");

include_once(CLASSES_PATH ."class.datetime.php");
include_once(CLASSES_PATH ."class.error.php");
include_once(CLASSES_PATH ."class.database.php");
include_once(CLASSES_PATH ."class.login_history.php");
include_once(CLASSES_PATH ."class.member.php");
include_once(CLASSES_PATH ."class.page.php");
include_once(CLASSES_PATH ."class.logging.php");
include_once(CLASSES_PATH ."class.settings.php");
include_once(CLASSES_PATH ."class.state_address.php"); // added by ejkv

global $site_settings;


// The following is necessary because of a PHP 4.4 bug with passing references
error_reporting( E_ALL & ~E_NOTICE );

// For maintenance, see inc.config.php
if(DOWN_FOR_MAINTENANCE and !$running_upgrade_script) {
	$p->DisplayPage(MAINTENANCE_MESSAGE);
	exit;
}

// [chris] Uncomment this line to surpress non-fatal Warning and Notice errors
//error_reporting(E_ALL &~ (E_NOTICE | E_WARNING));	

// [ejkv] Uncomment to show all errors including Pear errors if DEBUG
/* if (DEBUG) {
 error_reporting(E_ALL); 
 ini_set('display_errors', 1); 
 require_once PEAR_PATH .'/PEAR.php'; 
 PEAR::setErrorHandling(PEAR_ERROR_DIE);
}
 else error_reporting(E_ALL ^ E_NOTICE);
*/

?>
