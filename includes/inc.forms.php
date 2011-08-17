<?php
require_once('HTML/QuickForm.php');

$form = new HTML_QuickForm();
$renderer =& $form->defaultRenderer();

$renderer->setFormTemplate('<form{attributes}><table id="contentTable">{content}</table></form>');
$renderer->setHeaderTemplate('<tr><td><H2>{header}</H2></td></tr>');
$renderer->setElementTemplate('<TR><TD><FONT SIZE=2>{label}<!-- BEGIN required --><font> *</font><!-- END required --></FONT><!-- BEGIN error --><font color=RED size=2>   {error}</font><br /><!-- END error -->&nbsp;{element}</TD></TR>');
$form->setRequiredNote('<br><tr><td><font size=2>* '._("denotes a required field").'</font></td></tr>');

?>
