<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

require_once("HTML/Table.php");

class cTable extends HTML_Table {
	var $curr_row=0;

	function cTable ($attrs=null) {
		global $CONTENT_TABLE;
	
		if(!$attrs)
			$attrs = $CONTENT_TABLE;
			
		$this->HTML_Table($attrs);
		$this->setAutoGrow = true;
	}

	function AddSimpleHeader($fields) {
		$this->setRowAttributes(0, array("id"=>"tableheader"), true);
	
		$i=0;
		foreach($fields as $field) {
			$this->setHeaderContents(0, $i, $field); 
			$i += 1;
		}
	}
	
	function AddSimpleRow($fields) {
		$this->curr_row += 1;
		$i=0;
		foreach($fields as $field) {
			$this->setCellContents($this->curr_row, $i, $field); 
			$i += 1;
		}
	}
	
	function DisplayTable() {
		$this->altRowAttributes (1, array("id"=>"tablerow"), array("id"=>"tablealtrow"));
		return "<P><BR>". $this->toHTML();
	}

}


?>
