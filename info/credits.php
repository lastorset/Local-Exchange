<?php

include_once("../includes/inc.global.php");
$p->site_section = LISTINGS;
$p->page_title = "Credits: Local Exchange Oslo Ver. ".LOCALX_VERSION;

print $p->MakePageHeader();
print $p->MakePageMenu();
print $p->MakePageTitle();

?>
The software has been built in several stages, each built on the previous version. All versions are most likely still maintained. This list is in reverse chronological order:

<h3>Oslo fork</h3>

<p>Project led by Douwe Beerda. Software development by Leif Arne Storset.

<p>Source code available at <a href=https://github.com/lastorset/Local-Exchange/>GitHub</a>.

<h3>Internationalized version</h3>

Developed by Evert Jan Klein Velderman.

<h3>UK version</h3>
<p>This website is based on the one designed for Fourth Corner Exchange, in 
  Bellingham, Washington State, USA, as per the credits below, and was developed, 
  with permission, for use in the UK, by Mary and Progga under the auspices of 
  LETSlink London during summer 2007. Enhancements developed by <a href="http://cdmweb.co.uk" target="_blank">Chris McDonald</a> and <a href="http://rofo.co.uk" target="_blank">Rob Follett</a> of <a href="http://falmouthlets.org.uk" target="_blank">Falmouth LETS</a> and added for UK version 1.0 Nov 2008 - June 2009
</p>
<p>Further software developments under the auspices of LETSlink UK are being recorded 
  on <a href="http://www.cxss.info" target"_blank">www.cxss.info</a></p>

<h3>Original version</h3>
<p> PHP development for the <A HREF="http://sourceforge.net/projects/local-exchange/" target="_blank">Local 
  Exchange</A> system was by Calvin Priest. Original informational content was 
  by Francis Ayley &amp; Cheryl Niles. Graphics were contributed by Calvin Priest from 
  several other free software projects, the Gnome project in particular. Thanks 
  to Laurie &amp; Scott Shultis for their generous assistance with the original Fourth 
  Corner Exchange website.</p>

<h3>License</h3>

Licensed under the <a href=http://www.gnu.org/copyleft/gpl.html>GNU General Public License</a> with one exception:

<ul>
<li>The "Handshake" icon is created by <a href="http://www.dragonartz.net">DragonArtz</a> and licensed under the <a href="http://www.dragonartz.net/license-2/">Creative
Commons Attribution-Noncommercial-Share Alike 3.0 United States License</a>.
</ul>

<?

print $p->MakePageFooter();

?>
