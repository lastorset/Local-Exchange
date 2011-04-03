<?php

class cInfo {
		
		function LoadOne($id) {

			global $cDB, $cErr;
		
			$query = $cDB->Query("SELECT * FROM cdm_pages where id=".$cDB->EscTxt($id)." limit 0,1");
	
			if ($query)
				$row = mysql_fetch_array($query);
	
			if ($row)
				return $row;
			else
				return false;
		}
		
		function LoadPages() {

			global $cDB, $cErr;
		
			$query = $cDB->Query("SELECT * FROM cdm_pages;");
			
			$i = 0;				
			
			$pgs = array();
			
			$num_results = mysql_num_rows($query);
			
			for ($i=0;$i<$num_results;$i++) {
					
					$row = mysql_fetch_array($query);	
					$pgs[$i] = $row;
			}
			
			return $pgs;
		}
}
