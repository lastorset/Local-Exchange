This software is licensed under the GPL (see GPL.txt for more information). It comes with no warranty or guarantees of any kind, to the extent allowed by applicable law.

Requirements:
	- PHP 4.1.0 or higher
	- MySQL 3.23 or higher with InnoDB table support*
	- Pear system and the libraries specified below 

==== New version 1.01 UK ====
N.B. - This package (localx-UK-1.01.zip) is for installing a NEW site using version 1.01 from scratch. 
We suggest you read RELEASENOTES.txt before attempting to install for an overview and understanding of new features and bug fixes introduced since 0.3.2 

Upgrading from version 0.3.2:
If you already have a version of Local Exchange 0.3.2 installed, please 

1) use the UPGRADE package (localexchange-upgrade-0.4.0.zip) and instructions contained within it instead of this archive to upgrade to version 0.4.0.  

Upgrading from version 0.4.0:
Once you have Local Exchange version 0.4.0 installed

2) use the UPGRADE package (localx-UK-upgrade-0.4-to-1.0.zip) to upgrade to version 1.0 UK

Upgrading from version 1.0:
Once you have Local Exchange UK version 1.0 installed

3) use the UPGRADE package (localx-UK-upgrade-1.0-to-1.01.zip) to upgrade to version 1.01 UK

Please note that the current upgrade package (localx-UK-upgrade-1.0-to-1.01.zip)is ONLY for upgrading an existing site running version 1.0 to version 1.01 (version is defined as LOCALX_VERSION in includes/inc.global.php).

Chris Macdonald and Rob Follett
==============================


To install a new site:

1) Upload the LocalExchange files to your web server.

2) Install PHP Pear libraries. There are a number of ways to do this.  Following are two of them.  

a) Using The Pear Installer
If Pear is installed on your web server and you have admin access (see pear.php.net for more info) you can simply copy and paste the following command into a shell and then proceed to step 3: 
"pear install -f File_PDF HTML_Common HTML_QuickForm HTML_Table HTTP HTTP_Download HTTP_Header Mail_Mime OLE Spreadsheet_Excel_Writer Text_Password".  

If you do not have admin access, your website administrator may be willing to run the above command for you. A simple call or email to your web host tech support should confirm one way or the other.

b) Download Pear Package from Sourceforge
Since many inexpensive web hosting services will be unwilling to install anything for you, or give you access to do so, we have provided the complete set of required Pear libraries as a package on Soureforge that you can download.  Go to http://sourceforge.net/project/showfiles.php?group_id=136704 and download the pear-libraries package.  Uncompress the package.  The simplest option at this point is to ftp the contents of the "pear" folder (but not the "pear" folder itself) directly into the same folder you have uploaded the Local Exchange files.  This is messy and there may be security risks associated with it, but it will get the job done.  If you have done this, you can now proceed to step 3.

The better option is to upload to a different location on the server that is not accessible by HTTP (but is accessible by FTP).  Often the default directory you are put into after a successful FTP connection will be such a location.  If so, you can upload the pear files (including the "pear" folder itself) here.  The final step is to edit the text configuration file "includes/inc.config.php" and set the PEAR_PATH value.  This value needs to be set to the full path of the new "pear" folder on the server.  You can find the path structure on your server by creating a file called info.php with the following contents:
<?php
echo dirname(__FILE__);
?>

After the above file has been uploaded to your server you need to go to http://your-domain-name/info.php in a browser and it will reveal the location of your web files on the server, maybe "/home/username/htdocs", for example.  Using this example, you would then set PEAR_PATH to "/home/username/pear".

Ok, onward and upward.

3) Create a new database in MySQL and create at least one user account with full access to it.  You can call the database and user whatever you want, but you'll need to enter that information into the text configuration file "includes/inc.config.php".  

4) Edit includes/inc.config.php.  This file contains lots of optional settings.  The domain name and database login settings are *required*.  You'll also want to set the email address values.

5) Open a web browser.  Go to http://your-domain-name/your-path-if-any/create_db.php.  This will create the database tables and insert initial data.

6) You should now be able to login with the userid "admin" and password "password".  Go into the Member Profile section and change the password for this account (for security).

7) Also for security reasons, you should delete the create_db.php file at this point, or change the file permissions so that it can't be run by the web user account. 

8) In order to use file uploading features (such as uploading a Newsletter), you will need to set the permissions on the "uploads" directory such that the web user account (often www-data) has access to write to it.

Further Configuration:
1) Edit style.css, print.css, inc.config.php and add graphics files to the "images" folder as needed to personalize the site.  The main site graphics can be modified be editing inc.config.php.
2) In the "info" folder are a number of essentially static html files that help to explain what local currency is.  Included among them is some information specific to the area the original developer of the system lives in (me, that is).  You may use these files as you like, of course, but you may want to tailor them to your needs.  The files themselves need to be edited with a text editor, there is no content editor included in the system currently. (RF: Update ver 0.4.0 - there *is* now an (optional) content editor built in for creating additional info pages which can be edited online - see includes/inc.config.php )
3) Default listing categories were created by the create_db script.  You can edit these categories from the Administration menu.


The system has been run on Linux and FreeBSD.  It should of course run on a Windows server too, but this has not seen any testing. 

For questions, comments, or miscellaneous verbage for versions up to and including ver 0.3.2 you can email calvinpriest@yahoo.com - (RF: for ver 0.4.0 and above you can email chris@cdmweb.co.uk or robfol@gmail.com).


* InnoDB tables are used to help keep the database in balance.  It is possible to use the system without InnoDB support, but not recommended.  To do so, you will need to comment out the lines at the top of the create_db.php file which check for InnoDB.  Better yet, contact your database administrator and ask if it can be turned on.  For reference: http://dev.mysql.com/doc/mysql/en/innodb-overview.html
