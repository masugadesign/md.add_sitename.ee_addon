<?php
/* ===========================================================================
ext.md_add_sitename.php ---------------------------
Add name of current site to the CP header.
            
INFO ---------------------------
Developed by: Ryan Masuga, masugadesign.com
Created:   Jun 25 2007
Last Mod:  Jan 12 2009


Related Thread: http://expressionengine.com/forums/viewthread/54996/

CHANGELOG ---------------------------
1.2.2 - Old school update checking removed to help solve CP homepage errors
1.2.1 - Testing Subversion...
1.2.0 - Fixed bug where styles could be rendered in head more than once; added docs URL;
        added version checking (Thanks Leevi Graham); fixed default settings problem; added
        check to see if info is in session or cache to keep things speedy (again, thanks, Leevi.)
1.1.5 - Fixed in case you use a .png instead of .ico
1.1.4 - Updated the way $siteurl is created
1.1.3 - Added ability to style text as a normal or italic font
1.1.2 - Added ability to put a site's favicon next to the text
1.1.1 - Added Override setting, to put whatever text you want at the upper left
1.1.0 - Added settings, optimized some PHP
1.0.2 - Added 'stripslashes' for site names with apostrophes
1.0.1 - Small style tweaks
1.0.0 - Initial release 

http://expressionengine.com/docs/development/extensions.html
=============================================================================== */
if ( ! defined('EXT')) exit('Invalid file request');




class Md_add_sitename {

	var $settings			= array();

	var $name            = 'MD Add Sitename';
	var $version         = '1.2.2';
	var $description     = 'Adds the site name and favicon to the header of the EE Control Panel.';
	var $settings_exist  = 'y';
	var $docs_url        = 'http://www.masugadesign.com/the-lab/scripts/add-sitename/';
	var $addon_name      = 'MD Add Sitename';
  
  //var $cache_name      = "mdesign_cache";
  //var $cache_expired   = TRUE;
  //var $log             = array();
  //
	//var $debug           = FALSE;


// --------------------------------
//  PHP 4 Constructor
// --------------------------------
	function Md_add_sitename($settings='')
	{
		$this->__construct($settings);
	}

// --------------------------------
//  PHP 5 Constructor
// --------------------------------
	function __construct($settings='')
	{
		global $SESS;
		$this->settings = $settings;

	 //if(isset($SESS->cache['mdesign']) === FALSE)
	 //{
	 //	$SESS->cache['mdesign'] = array();
	 //}
	}

// --------------------------------
//  Settings
// --------------------------------
	function settings()
	{
		$settings = array();
      $settings['font_size'] = "";
			$settings['font_family'] = '';
			$settings['font_weight'] = array('s', array('normal' => "normal", 'bold' => "bold"), 'normal');
			$settings['font_style'] = array('s', array('normal' => "normal", 'italic' => "italic"), 'normal');
			$settings['padding_top'] = array('s', array('0' => "0", '1' => "1", '2' => "2", '3' => "3", '4' => "4", '5' => "5", '6' => "6", '7' => "7", '8' => "8", '9' => "9", '10' => "10", ), '0');
			$settings['padding_bottom'] = array('s', array('0' => "0", '1' => "1", '2' => "2", '3' => "3", '4' => "4", '5' => "5", '6' => "6", '7' => "7", '8' => "8", '9' => "9", '10' => "10", ), '0');
			$settings['color'] = '';
    	$settings['is_link'] = array('r', array('1' => "yes", '0' => "no"), '0');
			$settings['text_override'] = '';
			$settings['show_favicon'] = array('r', array('1' => "yes", '0' => "no"), '0');
			$settings['favicon_path'] = '';

		return $settings;
	}

// --------------------------------
//  Activate Extension
// --------------------------------  
	function activate_extension ()
	{
		global $DB, $PREFS, $FNS;
		$default_settings = serialize(
			array(
			'font_size' => 14,
			'font_family' => 'arial, helvetica, sans-serif',
			'font_weight' => 'normal',
			'font_style' => 'normal',
			'padding_top' => 4,
			'padding_bottom' => 4,
			'color' => "dadada",
    	'is_link' => 0,
			'text_override' => '',
			'show_favicon'  => 0,
			'favicon_path' => $FNS->set_realpath('favicon.ico')
			)
		);

		$hooks = array(
			'show_full_control_panel_end' 			=> 'show_full_control_panel_end'
		);

		foreach ($hooks as $hook => $method)
		{
			$sql[] = $DB->insert_string( 'exp_extensions', 
							array('extension_id' 	=> '',
								'class'			=> get_class($this),
								'method'		=> $method,
								'hook'			=> $hook,
								'settings'		=> $default_settings,
								'priority'		=> 10,
								'version'		=> $this->version,
								'enabled'		=> "y"
							)
						);
		}
		
		// run all sql queries
		foreach ($sql as $query)
		{
			$DB->query($query);
		}
		return TRUE;
	}

// --------------------------------
//  Update Extension
// --------------------------------  
	function update_extension($current='')
	{
		global $DB;
		
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		$DB->query("UPDATE exp_extensions
		            SET version = '".$DB->escape_str($this->version)."'
		            WHERE class = '".get_class($this)."'");
	}
	// END

// --------------------------------
//  Disable Extension
// -------------------------------- 
	function disable_extension()
	{
		global $DB;
		$DB->query("DELETE FROM `exp_extensions` WHERE class = '" . get_class($this) . "'");
	}

	/**
	* Takes the control panel html
	*
	* @param	string $out The control panel html
	* @return	string The modified control panel html
	*/
	function show_full_control_panel_end( $out )
	{
		global $EXT, $PREFS, $FNS;

		// -- Check if we're not the only one using this hook
		if($EXT->last_call !== FALSE)
			$out = $EXT->last_call;

		$textoverride = isset($this->settings['text_override']) ? $this->settings['text_override'] : "";
		$thesitename = "";
		
		if ($textoverride != "") {
			$thesitename = $textoverride;
		} else {
			$thesitename = stripslashes($PREFS->ini('site_name'));
		}
	
		$siteurl = "http://".$_SERVER["HTTP_HOST"]."/";
		$fontsize = isset($this->settings['font_size']) ? $this->settings['font_size'] : "14";
		$islink = isset($this->settings['is_link']) ? $this->settings['is_link'] : "0";
		$color = isset($this->settings['color']) ? $this->settings['color'] : "dadada";		
		$fontweight = isset($this->settings['font_weight']) ? $this->settings['font_weight'] : "normal";
		$fontstyle = isset($this->settings['font_style']) ? $this->settings['font_style'] : "normal";	
		$fontfamily = isset($this->settings['font_family']) ? $this->settings['font_family'] : 'arial, helvetica, sans-serif';	
		$paddingtop = isset($this->settings['padding_top']) ? $this->settings['padding_top'] : "4";
		$paddingbottom = isset($this->settings['padding_bottom']) ? $this->settings['padding_bottom'] : "4";
		// favicon
		$showfavicon = isset($this->settings['show_favicon']) ? $this->settings['show_favicon'] : "0";
		$favicon_path = isset($this->settings['favicon_path']) ? $this->settings['favicon_path'] : $FNS->set_realpath("favicon.ico");
		// might be a PNG!
		$faviconfile = basename($favicon_path);
	  
	  $look_for = "<div class='helpLinksLeft' >";
	  
	  $cpsitename_styles = '<!-- booey --><style type="text/css">';
	
		$cpsitename_styles .= "#cpsitename { 
			font-family: ".$fontfamily.";
			font-weight: ".$fontweight.";
			font-style: ".$fontstyle."; 
			font-size: ".$fontsize."px;
			color: #".$color.";
			border-right: 1px solid #313E45;
			padding:".$paddingtop."px 10px ".$paddingbottom."px 0px;
			margin: 0 10px 0 0;
			display: inline;
			float: left;";
			
			if ($showfavicon == "yes") {
					if (file_exists($favicon_path)) {
    					//$cpsitename_styles .= "background: transparent url('".$siteurl."favicon.ico') 0px ".$paddingtop."px no-repeat;	
    					$cpsitename_styles .= "background: transparent url('".$siteurl.$faviconfile."') 0px ".$paddingtop."px no-repeat;	
							padding-left: 20px;";
					}
			}
			
		$cpsitename_styles .= "}
			div.helpLinksLeft a {
				padding-top: 7px;
				display: block;
				float: left;}
				#cpsitename a.cpname { 
			padding-top:0;
			}";
		$cpsitename_styles .= '</style>';
		
		$out = str_replace("</head>", $cpsitename_styles . "</head>", $out);
		
		if (trim($islink) == "1") 
		{
			$replace_with = "<div id='cpsitename'><a class='cpname' href='$siteurl'>$thesitename</a></div>";
		} else {          
			$replace_with = "<div id='cpsitename'>$thesitename</div>";
		} 
			
			$out = str_replace($look_for, $look_for . $replace_with, $out);
	
		return $out;
	}


}
?>