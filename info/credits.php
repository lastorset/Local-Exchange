<?php

include_once("../includes/inc.global.php");
$p->site_section = LISTINGS;
$p->page_title = "Credits: Local Exchange UK Ver. ".LOCALX_VERSION;

print $p->MakePageHeader();
print $p->MakePageMenu();
print $p->MakePageTitle();

?>
<p> <b>This website is based on the one designed for Fourth Corner Exchange, in 
  Bellingham, Washington State, USA, as per the credits below, and was developed, 
  with permission, for use in the UK, by Mary and Progga under the auspices of 
  LETSlink London during summer 2007. Enhancements developed by <a href="http://cdmweb.co.uk" target="_blank">Chris McDonald</a> and <a href="http://rofo.co.uk" target="_blank">Rob Follett</a> of <a href="http://falmouthlets.org.uk" target="_blank">Falmouth LETS</a> and added for UK version 1.0 Nov 2008 - June 2009</b></p> 
<p><b>Further software developments under the auspices of LETSlink UK are being recorded 
  on <a href="http://www.cxss.info" target"_blank">www.cxss.info</a></b></p>
<p> PHP development for the <A HREF="http://sourceforge.net/projects/local-exchange/" target="_blank">Local 
  Exchange</A> system was by Calvin Priest. Original informational content was 
  by Francis Ayley & Cheryl Niles. Graphics were contributed by Calvin Priest from 
  several other free software projects, the Gnome project in particular. Thanks 
  to Laurie & Scott Shultis for their generous assistance with the original Fourth 
  Corner Exchange website.</p>

<p> 
<?

print $p->MakePageFooter();

?>
