<?php
if (!isset($global))
{
	die(__FILE__." was included directly.  This file should only be included via inc.global.php.  Include() that one instead.");
}

class cDatabase
{
	var $isConnected;
	var $db_link;
	var $tx_count = 0;

	function Database()
	{
		$this->isConnected = false;
	}

	function Connect()
	{ 
		if ($this->isConnected)
			return;

		$this->db_link = mysql_connect(DATABASE_SERVER,DATABASE_USERNAME,DATABASE_PASSWORD)
		       or die("Could not connect");	// TODO: fix error messages
		mysql_selectdb(DATABASE_NAME)
		       or die("Could not select database");	// TODO: fix error messages
		$this->isConnected=true;
	}

	function Query($thequery)
	{
		// Intercept transaction-control statements to support nesting
		if (strpos(trim($thequery), "BEGIN") === 0
			|| strpos(trim($thequery), "START TRANSACTION") === 0)
		{
			if($this->tx_count++ > 0)
				// We are inside a transaction, so don't START, just increment.
				return TRUE;
		}
		else if (strpos(trim($thequery), "COMMIT") === 0)
			if(--$this->tx_count > 0)
				// There is another enclosing transaction, so don't COMMIT, just decrement.
				return TRUE;

		if (!$this->isConnected)
			$this->Connect();

		$ret = mysql_query($thequery);
		if (!$ret)
		{
			error_log("MySQL error ". mysql_errno() .": ". mysql_error());
			error_log("MySQL query was: ". $thequery);
		}
		return $ret;
	}

	function BeginTransaction()
	{
		return $this->Query("START TRANSACTION");
	}

	function Rollback()
	{
		return $this->Query("ROLLBACK");
	}

	function Commit()
	{
		return $this->Query("COMMIT");
	}

	function NumRows($thequery)
	{
		if (!$this->isConnected)
			$this->Connect();

		$result = mysql_query($thequery);

		return mysql_num_rows($result);
	}

	function MakeSimpleTable($theQuery)
	{
		$query = $this->Query($theQuery);

		/* Printing results in HTML */
		$table = "<TABLE>\n";
		while ($line = mysql_fetch_array($query, MYSQL_ASSOC)) {
			$table .= "\t<TR>\n";
			foreach ($line as $col_value)
			{
				$table .= "\t\t<TD>$col_value</TD>\n";
			}
			$table .= "\t</TR>\n";
		}
		$table .= "</TABLE>\n";

		return $table;
	}

/*
	function EscTxt($text) {
		if($text) {
			if(MAGIC_QUOTES_ON) 
				return "'". $text ."'";
			else 
				return "'". addslashes($text) ."'";
		} else {
			return "null";
		}
	}

	function EscTxt2($text) {  // TODO: Rename to EscQueryTxt() and update through site
		if($text) {
			if(MAGIC_QUOTES_ON) 
				return "='". $text ."'";
			else 
				return "='". addslashes($text) ."'";
		} else {
			return " IS NULL";
		}
	}
*/

	/* A HTML screening function, an optional additional security step for data being submitted for storage in MySQL */
	function ScreenHTML($var) {
		
		global $cUser,$allowedHTML;
		
		if (STRIP_JSCRIPT==true) { // Strip any obvious JavaScript
		
			$var = str_replace(array('javascript:','<script>','< script','</script'),' ',$var);
		}
		
		if ($cUser->member_role>=HTML_PERMISSION_LEVEL) // User has free reign to submit any and all HTML
			return $var;

		// This next bit is messy but ProcessHTMLTag works on the assumption of a 2-dimensional array so we have to convert our existing 1-dimensional array
		// Would be tidier to rewrite ProcessHTMLTag to work with 1-dimensional arrays but for now this will do
		$allow = array();
		
		if ($allowedHTML) {
			
			foreach($allowedHTML as $tag) {
				
				$allow[$tag] = $tag;
			}
		}
	
		// Screen all the tags in this $var
		$var = preg_replace("/<(.*?)>/e","cDatabase::ProcessHTMLTag(StripSlashes('\\1'), \$allow)",$var);
			
		return $var;
	}
	
	/* Takes an individual HTML tag and checks it
			$allowed - an Array containing exceptions (e.g. em, i) */
	function ProcessHTMLTag($data,$allowed) {
		
		// ending tags
		if (preg_match("/^\/([a-z0-9]+)/i", $data, $matches)){
			$name = StrToLower($matches[1]);
			if (in_array($name, array_keys($allowed))){
				return '</'.$name.'>';
			}else{
				
				return '';
			}
		}

		// starting tags
		if (preg_match("/^([a-z0-9]+)(.*?)(\/?)$/i", $data, $matches)){
			$name = StrToLower($matches[1]);
			$body = $matches[2];
			$ending = $matches[3];
			if (in_array($name, array_keys($allowed))){
				$params = "";
				preg_match_all("/ ([a-z0-9]+)=\"(.*?)\"/i", $body, 
					$matches_2, PREG_SET_ORDER);
				preg_match_all("/ ([a-z0-9]+)=([^\"\s]+)/i", $body,
					$matches_1, PREG_SET_ORDER);
				$matches = array_merge($matches_1, $matches_2);
			
				foreach($matches as $match){
					$pname = StrToLower($match[1]);
					//if (in_array($pname, $allowed[$name]) || in_array("*",$allowed[$name])){
						$params .= " $pname=\"$match[2]\"";
					//}
		
				}
				
				return '<'.$name.$params.$ending.'>';
		}
		else
			return '';
		}
	}
	
/*
 * Warning on mysql_escape_string()
 *
 * This function will escape the unescaped_string, so that it is safe to place
 * it in a mysql_query(). This function is deprecated.
 *
 * This function is identical to mysql_real_escape_string() except that
 * mysql_real_escape_string() takes a connection handler and escapes the string 
 * according to the current character set. mysql_escape_string() does not take a
 * connection argument and does not respect the current charset setting. 
 *
 * Despite this warning, mysql_real_escape_string() was not used becuase it
 * requires a database connection which is not always present when EscTxt() or
 * EscTxt2() is called.
 */

    function EscTxt($text) {
    		
    		// An optional security step in case someone tries to put something 'evil' in our SQL
    		$text = cDatabase::ScreenHTML($text);
    		
        if( !empty($text)) {
            if(get_magic_quotes_gpc()) {
                $text = stripslashes($text);
            }

            return "'" . mysql_escape_string($text) . "'";
        } else if(is_numeric($text)) {
            return "'$text'";
        } else {
            return "NULL";
        }
    }


    function EscTxt2($text) {
        if( !empty($text)) {
            if(get_magic_quotes_gpc()) {
                $text = stripslashes($text);
            }

            return "='" . mysql_escape_string($text) . "'";
        } else if(is_numeric($text)) {
            return "='$text'";
        } else {
            return " IS NULL";
        }
    }


	function UnEscTxt($text) {
		if(MAGIC_QUOTES_ON)
			return $text;
		else
			return stripslashes($text);
	}	

}


$cDB = new cDatabase;
?>
