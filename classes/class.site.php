<?php

// Not currently using this class.  Started it with the intention of replacing
// site configuration constants with classes, but thought better of it, at
// least for the moment...

class cSite {
	var $server_domain;
	var $server_path_url;
	var $server_filesystem_base;
	var $redirect_url;
	var $magic_quotes_gpc_status;
	var $magic_quotes_runtime_status;
	var $site_sections  // An array of cSiteSection objects
	
	function SetMagicQuotesGPC ($turn_on) {
		if($turn_on) {
			if(set_magic_quotes_gpc ($turn_on))
				$this->magic_quotes_gpc_status = true;
			else
				$this->magic_quotes_gpc_status = false;
		} else {
			set_magic_quotes_gpc (0);
			$this->magic_quotes_gpc_status = false;
		}
		
	}
	
	function SetMagicQuotesRuntime ($turn_on) {
		if($turn_on) {
			if(set_magic_quotes_runtime (1))
				$this->magic_quotes_runtime_status = true;
			else
				$this->magic_quotes_runtime_status = false;
		} else {
			set_magic_quotes_runtime (0);
			$this->magic_quotes_runtime_status = false;
		}
		
	}
	
	function BasePath () {
		return $this->server_domain . $this->server_path_url;
	}
	
	function IncludesPath () {
		return $server_filesystem_base . $server_path_url ."/includes/";
	}
	
	function ClassesPath () {
		return $server_filesystem_base . $server_path_url ."/classes/";
	}

	function AddSiteSection ($description, $url) {
		$this->site_sections[] = new cSiteSection($description, $url);
	}

}

class cSiteSection {
	var $description;
	var $url;
	
	function cSiteSection($description, $url) {
		$this->description = $description;
		$this->url = $url;
	}
}

$cSite = new cSite;

?>
