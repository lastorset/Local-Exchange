<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

require_once ('Spreadsheet/Excel/Writer.php');

class cBackup {
	var $all_tables; // an array of all table names
	var $workbook; // an object of class Spreadsheet_Excel_Writer;
	
	function cBackup() {
		global $cUser, $cDB;
		
		$this->workbook = new Spreadsheet_Excel_Writer();
		$this->workbook->setTempDir('/tmp');
		
		// TODO: The following should be dynamically generated
		$this->all_tables = array(DATABASE_LISTINGS, DATABASE_PERSONS, DATABASE_MEMBERS, DATABASE_TRADES, DATABASE_LOGINS, DATABASE_LOGGING, DATABASE_CATEGORIES, DATABASE_FEEDBACK, DATABASE_REBUTTAL, DATABASE_NEWS);
		$this->workbook->send('export_'. $cUser->member_id .'.xls');
	}
	
	function PrintHeaders($table_name, &$worksheet) {
		global $cDB;
	
		$query = $cDB->Query("DESC ". $table_name);
		$i=0;
		while($row = mysql_fetch_array($query)) {
			$worksheet->write(0, $i, $row[0]);			
			$field_names[$i] = $row[0];
			$i += 1;
		}		
		return $field_names;
	}
	
	function BackupAll() {
		global $cDB;

		foreach ($this->all_tables as $table_name) {
			$worksheet =& $this->workbook->addWorksheet($table_name);
			
			$field_names = $this->PrintHeaders($table_name, $worksheet);
			
			$query = $cDB->Query("SELECT * FROM ". $table_name .";");
			
			$row_num=1;
			while($row = mysql_fetch_array($query)) {
				$col_num=0;
				foreach ($field_names as $field) {
					$worksheet->write($row_num, $col_num, $row[$field]);
					$col_num += 1;
				}
				$row_num += 1;
			}
		}
		// Let's send the file
		$this->workbook->close();		
	}


}

?>
