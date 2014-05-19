<?php

$running_upgrade_script = true;
include_once("includes/inc.global.php");

$query = $cDB->Query("SHOW VARIABLES LIKE 'have_innodb';");
$row = mysql_fetch_array($query);
if($row[1] != "YES")	die("Your database does not have InnoDB support. See the installation instructions for more information about InnoDB. Installation aborted.");

if($cDB->Query("SELECT * FROM " . DATABASE_MEMBERS))	die("Error - database already exists! If you want to create a new database delete the old one first. You may also get this error if you are trying to install the program and your database userid or password in inc.config.php is incorrect.");

	
$cDB->Query("CREATE TABLE " . DATABASE_MEMBERS . "( member_id varchar(15) NOT NULL default '', password varchar(50) NOT NULL default '', member_role char(1) NOT NULL default '', security_q varchar(25) default NULL, security_a varchar(15) default NULL, status char(1) NOT NULL default '', member_note varchar(100) default NULL, admin_note varchar(100) default NULL, join_date date NOT NULL default '0000-00-00', expire_date date default NULL, away_date date default NULL, account_type char(1) NOT NULL default '', email_updates int(3) unsigned NOT NULL default '0', balance decimal(8,2) NOT NULL default '0.00', PRIMARY KEY (member_id)) ENGINE=InnoDB;") or die("Error - database already exists! If you want to create a new database delete the old one first.");
	
$cDB->Query("CREATE TABLE " . DATABASE_PERSONS . "( person_id mediumint(6) unsigned NOT NULL auto_increment, member_id varchar(15) NOT NULL default '', primary_member char(1) NOT NULL default '', directory_list char(1) NOT NULL default '', first_name varchar(20) NOT NULL default '', last_name varchar(30) NOT NULL default '', mid_name varchar(20) default NULL, dob date default NULL, mother_mn varchar(30) default NULL, email varchar(40) default NULL, phone1_area char(5) default NULL, phone1_number varchar(30) default NULL, phone1_ext varchar(4) default NULL, phone2_area char(5) default NULL, phone2_number varchar(30) default NULL, phone2_ext varchar(4) default NULL, fax_area char(3) default NULL, fax_number varchar(30) default NULL, fax_ext varchar(4) default NULL, address_street1 varchar(50) default NULL, address_street2 varchar(50) default NULL, address_city varchar(50) NOT NULL default '', address_state_code smallint(4) unsigned default '0', address_post_code varchar(20) NOT NULL default '', address_country varchar(50) NOT NULL default '', PRIMARY KEY (person_id)) ENGINE=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first."); // changed address_state_code char(50) NOT NULL default '' by address_state_code smallint(4) unsigned NOT NULL default '0' - by ejkv

$cDB->Query("CREATE TABLE " . DATABASE_LISTINGS . "( listing_id mediumint(8) unsigned NOT NULL auto_increment, title varchar(60) NOT NULL default '', description text, category_code smallint(4) unsigned NOT NULL default '0', member_id varchar(15) NOT NULL default '', rate varchar(30) default NULL, status char(1) NOT NULL default '', posting_date timestamp(14) NOT NULL, expire_date date default NULL, reactivate_date date default NULL, type char(1) NOT NULL default '', PRIMARY KEY (listing_id), UNIQUE (title, category_code, member_id, type)) ENGINE=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_CATEGORIES . "( category_id smallint(4) unsigned NOT NULL auto_increment, parent_id smallint(4) unsigned default NULL, description varchar(30) NOT NULL default '', PRIMARY KEY (category_id)) ENGINE=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_TRADES . "( trade_id mediumint(8) unsigned NOT NULL auto_increment, trade_date timestamp(14) NOT NULL, status char(1) default NULL, member_id_from varchar(15) NOT NULL default '', member_id_to varchar(15) NOT NULL default '', amount decimal(8,2) NOT NULL default '0.00', category smallint(4) unsigned NOT NULL default '0', description varchar(255) default NULL, type char(1) NOT NULL default '', PRIMARY KEY (trade_id)) ENGINE=InnoDB;") or die("Error - database already exists! If you want to create a new database delete the old one first.");
$cDB->Query("CREATE INDEX trades_from ON ". DATABASE_TRADES ." (member_id_from)");
$cDB->Query("CREATE INDEX trades_to ON ". DATABASE_TRADES ." (member_id_to)");

// Karma points

// TODO This implementation of karma, which seemed like a good idea at the time,
// is rather complex (it involves a Cartesian join over members and trades) and incohesive
// (member_directory joins with a view, whereas LoadMember sums using a different query).
// Consider rewriting it to work more like balances do. See comments at
// https://trello.com/card/game-mechanics/4f2af6dc418686231b0f75e4/21

// First, a cartesian view of all members paired with all trades.
$cDB->Query(<<<SQL
CREATE view member_x_trades AS
SELECT m.member_id, t.trade_id
		FROM member m, trades t
SQL
);

// Then, views for all spent and earned credits.
$cDB->Query(<<<SQL
CREATE VIEW trades_from AS
SELECT member_id, trade_id, amount
FROM member m, trades t
WHERE member_id = member_id_from
	AND type NOT IN ('M', 'N')
	AND t.status NOT IN ('M', 'N')
SQL
);

$cDB->Query(<<<SQL
CREATE VIEW trades_to AS
SELECT member_id, trade_id, amount
FROM member m, trades t
WHERE member_id = member_id_to
	AND type NOT IN ('M', 'N')
	AND t.status NOT IN ('M', 'N')
SQL
);

// Then, a calculation of karma.

$cDB->Query(<<<SQL
CREATE VIEW karma AS
SELECT mt.member_id, SUM(t_from.amount) AS spent, SUM(t_to.amount) AS earned, LEAST(SUM(t_from.amount), SUM(t_to.amount)) AS karma
FROM member_x_trades AS mt
	LEFT JOIN trades_from t_from
	ON mt.trade_id = t_from.trade_id AND mt.member_id = t_from.member_id
	LEFT JOIN trades_to t_to
	ON mt.trade_id = t_to.trade_id AND mt.member_id = t_to.member_id
GROUP BY member_id
ORDER BY karma DESC
SQL
);

$cDB->Query("CREATE TABLE " . DATABASE_LOGGING . "( log_id mediumint(8) unsigned NOT NULL auto_increment, log_date timestamp(14) NOT NULL, admin_id varchar(15) NOT NULL default '', category char(1) NOT NULL default '', action char(1) NOT NULL default '', ref_id varchar(15) NOT NULL default '', note varchar(100) default NULL, PRIMARY KEY (log_id)) ENGINE=InnoDB;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_LOGINS . "( member_id varchar(15) NOT NULL default '', total_failed mediumint(6) unsigned NOT NULL default '0', consecutive_failures mediumint(3) unsigned NOT NULL default '0', last_failed_date timestamp(14) NOT NULL, last_success_date timestamp(14) NOT NULL default '00000000000000', PRIMARY KEY (member_id)) ENGINE=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_FEEDBACK . "( feedback_id mediumint(8) unsigned NOT NULL auto_increment, feedback_date timestamp(14) NOT NULL, status char(1) NOT NULL default '', member_id_author varchar(15) NOT NULL default '', member_id_about varchar(15) NOT NULL default '', trade_id mediumint(8) unsigned NOT NULL default '0', rating char(1) NOT NULL default '', comment text, PRIMARY KEY (feedback_id)) ENGINE=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_REBUTTAL . "( rebuttal_id mediumint(6) unsigned NOT NULL auto_increment, rebuttal_date timestamp(14) NOT NULL, feedback_id mediumint(8) unsigned default NULL, member_id varchar(15) NOT NULL default '', comment varchar(255) default NULL, PRIMARY KEY (rebuttal_id)) ENGINE=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_NEWS . "( news_id mediumint(6) unsigned NOT NULL auto_increment, title varchar(100) NOT NULL default '', description text NOT NULL, sequence decimal(6,4) NOT NULL default '0.0000', expire_date date default NULL, PRIMARY KEY (news_id)) ENGINE=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_UPLOADS . "( upload_id mediumint(6) unsigned NOT NULL auto_increment, upload_date timestamp(14) NOT NULL, title varchar(100) NOT NULL default '', type char(1) NOT NULL default '', filename varchar(100) default NULL, note varchar(100) default NULL, PRIMARY KEY (upload_id)) ENGINE=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");


// Special admin account.
$city = DEFAULT_CITY;
$state = DEFAULT_STATE;
$postcode = DEFAULT_ZIP_CODE;
$country = DEFAULT_COUNTRY;
$date = strftime("%Y-%m-%d", time());

$cDB->Query("INSERT INTO " . DATABASE_MEMBERS . "(member_id, password, member_role, security_q, security_a, status, member_note, admin_note, join_date, expire_date, away_date, account_type, email_updates, balance) VALUES ('ADMIN','5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', '9',NULL,NULL,'A',NULL,'Special account created during install. Ok to inactivate once an Admin Level 2 acct has been created.', '$date', NULL,NULL,'S',7,0.00);") or die("Error - Could not insert row into member table.");

$cDB->Query("INSERT INTO " . DATABASE_PERSONS . "(person_id, member_id, primary_member, directory_list, first_name, last_name, mid_name, dob, mother_mn, email, phone1_area, phone1_number, phone1_ext, phone2_area, phone2_number, phone2_ext, fax_area, fax_number, fax_ext, address_street1, address_street2, address_city, address_state_code, address_post_code, address_country) VALUES (1,'admin','Y','Y','Special Admin','Account',NULL,NULL,NULL, NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL, NULL, NULL, '$city', '$state', '$postcode','$country');") or die("Error - Could not insert row into person table.");


// System account.
if (defined("SYSTEM_ACCOUNT_ID")) {
    $cDB->Query("
        INSERT INTO " .
            DATABASE_MEMBERS . "(member_id, password, member_role, security_q,
                security_a, status, member_note, admin_note, join_date,
                expire_date, away_date, account_type, email_updates, balance)
            VALUES ('system', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', '0',
                NULL, NULL, 'A', NULL, 'System account created during install.',
                '$date', NULL, NULL, 'O', 7, 0.00)")
    or die("Error - Could not insert row into member table.");

    $system_account_id = SYSTEM_ACCOUNT_ID;
    $cDB->Query("
        INSERT INTO " .
            DATABASE_PERSONS . "(person_id, member_id, primary_member,
                directory_list, first_name, last_name, mid_name, dob, mother_mn,
                email, phone1_area, phone1_number, phone1_ext, phone2_area,
                phone2_number, phone2_ext, fax_area, fax_number, fax_ext,
                address_street1, address_street2, address_city,
                address_state_code, address_post_code, address_country)
            VALUES (2, '$system_account_id', 'Y', 'Y', 'system', 'system', NULL,
                NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
                NULL, NULL, NULL, NULL, '$city', '$state', '$postcode',
                '$country')")
    or die("Error - Could not insert row into person table.");
}


$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . "(parent_id, description) VALUES (null,'" . _("Arts & Crafts") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Building Services") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Business & Administration") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Children & Childcare") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Computers") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Counseling & Therapy") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Food") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Gardening & Yard Work") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Goods") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Health & Personal") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Household") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Miscellaneous") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Music & Entertainment") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Pets") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Sports & Recreation") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Teaching") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null, '" . _("Transportation") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'" . _("Freebies") . "');") or die("Error - Could not insert row into categories table.");

$cDB->Query("CREATE TABLE " . DATABASE_SESSION . "(id CHAR(32) NOT NULL, data TEXT, ts TIMESTAMP, PRIMARY KEY(id), KEY(ts))") or
    die("Error - Cannot create session table.");
 
/* BEGIN upgrade to 0.4.0 */

$cDB->Query("ALTER TABLE `person` ADD `about_me` text") or die ("Error altering person table. Does the web user account have alter table permission?");

$cDB->Query("ALTER TABLE `person` ADD `age` varchar(20) default NULL") or die ("Error altering person table. Does the web user account have alter table permission?");

$cDB->Query("ALTER TABLE `person` ADD `sex` varchar(1) default NULL") or die ("Error altering person table. Does the web user account have alter table permission?");

$cDB->Query("ALTER TABLE `member` ADD `confirm_payments` int(1) default '0'") or die ("Error altering member table. Does the web user account have alter table permission?");

$cDB->Query("CREATE TABLE cdm_pages (
  id int(11) NOT NULL auto_increment,
  `date` int(30) default NULL,
  title varchar(255) default NULL,
  body text,
  active int(1) default '1',
  PRIMARY KEY  (id)
) ENGINE=MyISAM AUTO_INCREMENT=6;")
 or die("Error creating cdm_pages table.  Does the web user account have add table permission?");

$cDB->Query("CREATE TABLE trades_pending (
  id mediumint(8) unsigned NOT NULL auto_increment,
  trade_date timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  member_id_from varchar(15) NOT NULL default '',
  member_id_to varchar(15) NOT NULL default '',
  amount decimal(8,2) NOT NULL default '0.00',
  category smallint(4) unsigned NOT NULL default '0',
  description varchar(255) default NULL,
  typ varchar(1) default NULL,
  `status` varchar(1) default 'O',
  member_to_decision varchar(2) default '1',
  member_from_decision varchar(2) default '1',
  PRIMARY KEY  (id)
) ENGINE=MyISAM AUTO_INCREMENT=17")
	or die("Error creating trades_pending table.  Does the web user account have add table permission?");

/* END upgrade to 0.4.0 */

/* BEGIN upgrade to 1.01 */


// Some alterations to existing tables...
$cDB->Query("ALTER TABLE `cdm_pages` add permission int(2)") or die("Error altering cdm_pages table.  Does the web user account have alter table permission?");


$cDB->Query("ALTER TABLE `member` add restriction int(1)") or die("Error altering member table.  Does the web user account have alter table permission?");

$cDB->Query("alter table member change admin_note admin_note text") or die("Error altering member table.  Does the web user account have alter table permission?");

// Create the new tables...
$cDB->Query("CREATE TABLE `income_ties` (
  `id` int(11) NOT NULL auto_increment,
  `member_id` varchar(15) default NULL,
  `tie_id` varchar(15) default NULL,
  `percent` int(3) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12") or die("Error creating income_ties table.  Does the web user account have add table permission?");


$cDB->Query("CREATE TABLE `settings` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `display_name` varchar(255) default NULL,
  `typ` varchar(10) default NULL,
  `current_value` text,
  `options` varchar(255) default NULL,
  `default_value` text,
  `max_length` varchar(5) default '99999',
  `descrip` text,
  `section` int(1) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=35") or die("Error creating settings table.  Does the web user account have add table permission?");

// Populate the settings table...
$cDB->Query("INSERT INTO `settings` VALUES ('8', 'LEECH_EMAIL_URUNLOCKED', '" . _("&quot;Account Restriction Lifted&quot; Email") . "', 'longtext', '', '', '" . _("Restrictions on your account have been lifted.") . "', '', '" . _("Define email that is sent out when restrictions are lifted on an account.") . "', '3')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('6', 'LEECH_EMAIL_URLOCKED', '" . _("&quot;Account Restricted&quot; Email") . "', 'longtext', '', '', '" . _("Dear Member<br /><br />We have been reviewing members balances as we are concerned to ensure that trading goes back and forth on an equitable basis so that members are able to keep their accounts close to zero. We recognise that situations sometimes occur that lead to things getting out of balance. Therefore to assist you, we have restricted expenditure on your account for the time being. If have any queries about this, or if we can assist you in any particular way, please let us know, and we will review the situation in due course. The LETS Administrator ") . "', '', '" . _("Define email that is sent out when restrictions are imposed on an account.") . "', '3')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('10', 'MEM_LIST_DISPLAY_BALANCE', '" . _("Display Member Balance") . "', 'bool', '', '', 'TRUE', '', '" . _("Do you want to display member balances in the Members List? (Balances are always visible to Admins and Committee members regardless of what is set here.)") . "', '7')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('11', 'TAKE_SERVICE_FEE', '" . _("Enable Take Service Charge") . "', 'bool', '', '', 'TRUE', '', '" . _("Do you want the option of taking a service charge from members as and when?") . "', '2')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('12', 'SHOW_INACTIVE_MEMBERS', '" . _("Show Inactive Members in Members List") . "', 'bool', '', '', 'FALSE', '', '" . _("Do you want to display Inactive members in the Member List?") . "', '7')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('13', 'SHOW_RATE_ON_LISTINGS', '" . _("Show Rate on Listings") . "', 'bool', '', '', 'TRUE', '', '" . _("Do you want to display the Rate alongside the offers/wants in the main listings?") . "', '7')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('14', 'SHOW_POSTCODE_ON_LISTINGS', '" . _("Show Postcode on Listings") . "', 'bool', '', '', 'TRUE', '', '" . _("Do you want to display the PostCode alongside the offers/wants in the main listings?") . "', '7')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('15', 'NUM_CHARS_POSTCODE_SHOW_ON_LISTINGS', '" . _("Postcode Length (in chars)") . "', 'int', '', '', '4', '', '" . _("If you have elected to display the postcode on offers/wants listings, how much of the PostCode do you want to show? (the number you enter will be the number of characters displayed, so for eg if you just want to show the first 3 characters of the postcode then put 3.") . "', '7')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('16', 'OVRIDE_BALANCES', '" . _("Enable Balance Override") . "', 'bool', '', '', 'FALSE', '', '" . _("Do you want admins to have the option to override Balances on a per member basis? This can be useful during the initial site set-up for inputting existing balances. Link will appear in admin panel if set to TRUE.  Use with CAUTION to avoid the database going out of balance") . "', '6')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('17', 'MEMBERS_CAN_INVOICE', '" . _("Enable Member-to-Member Invoicing") . "', 'bool', '', '', 'TRUE', '', '" . _("Do you want to allow members to invoice one-another via the site? (The recipient is always given the option to confirm/reject payment of the invoice)") . "', '2')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('18', 'ALLOW_IMAGES', '" . _("Allow Members to Upload Images") . "', 'bool', '', '', 'TRUE', '', '" . _("Do you want to allow members to upload an image of themselves, to be displayed with their personal profile?") . "', '4')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('19', 'SOC_NETWORK_FIELDS', '" . _("Enable Social Networking Fields") . "', 'bool', '', '', 'TRUE', '', '" . _("Do you want to enable the Social Networking profile fields (Age, Sex, etc)?") . "', '4')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('20', 'OOB_ACTION', '" . _("Out Of Balance Behaviour") . "', 'multiple', '', 'FATAL,SILENT', 'SILENT', '', ' " . _("If, whilst processing a trade, the database is found to be out of balance, what should the system do?

FATAL = Aborts the trade and informs the user why.

SILENT = Continues with trade, displays no notifications whatsoever (NOTE: you can still set the option below to have an email notification sent to the admin)") . ")', '6')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('21', 'OOB_EMAIL_ADMIN', '" . _("Email Admin on Out Of Balance") . "', 'bool', '', '', 'TRUE', '', '" . _("Should the system send the Admin an email when the database is found to be out of balance?") . "', '6')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('24', 'EMAIL_FROM', '" . _("Email From Address") . "', '', '', '', '" . _("reply@my-domain.org") . "', '', '" ._("Email sent from this site will show as coming from this address") . "', '1')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('25', 'USE_RATES', '" . _("Use Rates Fields") . "', 'bool', '', '', 'TRUE', '', '" . _("If turned on, listings will include a &quot;Rate&quot; field") . "', '7')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('26', 'TAKE_MONTHLY_FEE', '" . _("Enable Monthly Fee") . "', 'bool', '', '', 'TRUE', '', '" . _("Do you want to enable Monthly Fees") . "', '2')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('27', 'MONTHLY_FEE', '" . _("Monthly Fee Amount") . "', 'int', '', '', '1', '', '" . _("How much should the Monthly Fee be?") . "', '2')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('28', 'EMAIL_LISTING_UPDATES', '" . _("Send Listing Updates via Email") . "', 'bool', '', '', 'FALSE', '', '" . _("Should users receive automatic updates for new and modified listings?") . "', '1')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('29', 'DEFAULT_UPDATE_INTERVAL', '" . _("Default Email Listings Update Interval") . "', 'multiple', '', 'NEVER,DAILY,WEEKLY,MONTHLY', 'NEVER', '', '" . _("If automatic updates are sent, this is the default interval.") . "', '1')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('34', 'ALLOW_INCOME_SHARES', '" . _("Allow Income Sharing") . "', 'bool', '', null, 'TRUE', '99999', '" . _("Do you want to allow members to share a percentage of any income they generate with another account of their choosing? The member can specify the exact percentage they wish to donate.") . "', '2')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('35', 'LEECH_NOTICE', '" . _("Message Displayed to Leecher who tries to trade") . "', 'longtext', '', '', '" . _("Restrictions have been imposed on your account which prevent you from trading outwards, Please contact the administrator for more information.") . "', '', '" . _("Leecher sees this notice when trying to send money.") . "', '3')") or die("Error - Could not insert row into settings table.");

$cDB->Query("INSERT INTO `settings` VALUES ('36', 'SHOW_GLOBAL_FEES', '" . _("Show monthly fees and service charges in global exchange view") . "', 'bool', '', null, 'FALSE', '', '" . _("Do you want to show monthly fees and service charges in the global exchange view? (Note: individual members will still be able to see this in their own personal exchange history).") . "', '7')") or die("Error - Could not insert row into settings table.");


/* END upgrade to 1.01 */

/* BEGIN upgrade 1.01 for Multilingual version

the following was added to handle state_address_code for mutilingual purposes - by ejkv
for new Database table states, with similar structure and handling as categories
added for NL to be used for district or neighbourhood
could be used in UK for county, and in the USA for state
 */

// Create the new DATABASE_STATES table ...
$cDB->Query("CREATE TABLE " . DATABASE_STATES . "( 
	state_id smallint(4) unsigned NOT NULL auto_increment,
	description varchar(30) NOT NULL,
	PRIMARY KEY (state_id)
) ENGINE=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

// Now populate DATABASE_STATES table with a few initial records
// State name could be 'Florida', County could be 'Sussex', or Neighbourhood could be 'Centre'
$cDB->Query("INSERT INTO " . DATABASE_STATES . "(description) VALUES ('" . _("Sample: Downtown") . "');") or die("Error - Could not insert row into states table.");
$cDB->Query("INSERT INTO " . DATABASE_STATES . "(description) VALUES ('" . _("Sample: North") . "');") or die("Error - Could not insert row into states table.");
$cDB->Query("INSERT INTO " . DATABASE_STATES . "(description) VALUES ('" . _("Sample: East") . "');") or die("Error - Could not insert row into states table.");
$cDB->Query("INSERT INTO " . DATABASE_STATES . "(description) VALUES ('" . _("Sample: South") . "');") or die("Error - Could not insert row into states table.");
$cDB->Query("INSERT INTO " . DATABASE_STATES . "(description) VALUES ('" . _("Sample: West") . "');") or die("Error - Could not insert row into states table.");

/* END upgrade 1.01 for Multilingual version */

// Upgrade to Gettext version
include_once("upgrade/upgrade_1.01multilang_to_1.XXgettext.php");

// Upgrade to support Google map
include_once("upgrade/upgrade_1.01multilang_to_1.XXgoogle_map.php");

// Karma points and some bug fixes
include_once("upgrade/upgrade_1.01multilang_to_1.1oslo.php");
$p->DisplayPage(_("Database has been created. Click <A HREF=member_login.php>here</A> to login."));

?>
