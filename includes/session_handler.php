<?php

/**
 * Custom session handling functions.
 * Session is saved in a MySQL database table.
 * When session_regenerate_id() is not used, there's no need to use
 * OLD_SESSION_ID. 
 * The DATABASE_SESSION constant is defined in includes/inc.global.php file of
 * Local Exchange.
 *
 * Code based on http://shiflett.org/articles/storing-sessions-in-a-database.
 * Session regeneration part taken from chapter 6 (Session security) of
 * "PHP architects guide to security" by Ilia Alshanetsky.
 *
 * SQL for session table creation:
 *     CREATE TABLE session (
 *         id char(32) NOT NULL,
 *         data TEXT,
 *         ts timestamp,
 *
 *         PRIMARY KEY(id),
 *         KEY(ts)
 *     );
 *
 *
 * Last update: 2007-08-06 1254 +0100
 */


session_set_save_handler("m_open", "m_close", "m_read", "m_write",
                                                  "m_destroy", "m_clean");


/* [chris] Wrapping some functions here to accomodate newer/older versions of PHP */
if(!function_exists("mysql_real_escape_string")) {
	
	function mysql_real_escape_string($var) {
		
		return mysql_escape_string($var);
	}
}

if(!function_exists("session_regenerate_id")) {
	
	function session_regenerate_id() {
		
		return true;
	}
}

if (!function_exists("ctype_alnum")) {
	
	function ctype_alnum($var) {
		return $var;
	}
}

if (!function_exists("ctype_digit")) {
	
	function ctype_digit($var) {
		return $var;
	}
}


function m_open($a, $b)
{
    global $_sess_db;

    if ($_sess_db = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME,
                                                           DATABASE_PASSWORD))
    {
        return mysql_select_db(DATABASE_NAME, $_sess_db);
    }
    else
    {
        return false;
    }
}


function m_close()
{
    global $_sess_db;

    return mysql_close($_sess_db);
}


function m_read($id)
{
    // The next line is for session_regenerate_id().
    define("OLD_SESSION_ID", $id);

    global $_sess_db;
  
    $id = mysql_real_escape_string($id);
    $sql = "SELECT data FROM " . DATABASE_SESSION . " WHERE id = '$id'";
  
    if ($result = mysql_query($sql, $_sess_db))
    {
        if (mysql_num_rows($result))
        {
            $record = mysql_fetch_assoc($result);
  
            return $record['data'];
        }
        else
        {
            return "";
        }
    }
    else
    {
        return "";
    }
}


function m_write($id, $data)
{
    // The next line is for session_regenerate_id().
    OLD_SESSION_ID != $id ? m_destroy(OLD_SESSION_ID) : false;

    global $_sess_db;
  
    $id = mysql_real_escape_string($id);
    $access = mysql_real_escape_string($access);
    $data = mysql_real_escape_string($data);
  
    $sql = "REPLACE INTO " . DATABASE_SESSION . " VALUES ('$id', '$data', default)";
  
    return mysql_query($sql, $_sess_db);
}


/*
 * This handler is executed instead of the write handler when session_destroy()
 * is called.
 */
function m_destroy($id)
{
    // The next line is for session_regenerate_id().
    OLD_SESSION_ID != $id ? m_destroy(OLD_SESSION_ID) : false;

    global $_sess_db;
    $id = mysql_real_escape_string($id);
    $sql = "DELETE FROM " . DATABASE_SESSION  . " WHERE id = '$id'";
  
    return mysql_query($sql, $_sess_db);
}


function m_clean($max_life_time)
{
    global $_sess_db;
  
    $sql = "DELETE FROM " . DATABASE_SESSION . " WHERE (ts + $max_life_time) < now()";
  
    return mysql_query($sql, $_sess_db);
}


