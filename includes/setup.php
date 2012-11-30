<?php
	//start the session if it's not there already	
	if(session_id() == "")
	{	  
	  session_start();
	}

	//using these guys to figure out the plugin URL
	$filepath = dirname(__FILE__);
	$rootpath = $_SERVER['DOCUMENT_ROOT'];
	$baseroot = basename($rootpath);
	$bpos = strpos($filepath, $baseroot) + strlen($baseroot);	

	//some definiations for later					
	/*
		Optional Fix:
		Change this (via the ssdownloads_getfile_redirect filter/hook) if the getfile.php script is not working on your server.
		Your files may be too large for your server memory.
		
		false = the file contents will be loaded via the WordPress HTTP API after checking creds
		true = (default) the user will be redirected to the loaction of the file after checking creds
		"file_get_contents" = use file_get_contents specifically to load the file (works on some setups when having errors)
		"cURL" = use cURL specifically to load the file (works on some setups when having errors)
		
		Notes:
		1. Your files will need to be stored on a publically accessible portion of your website.
		2. Savvy web users will be able to see the direct URL to your file in their server log/etc.		
	*/
	if(function_exists("apply_filters"))
		define("GETFILE_REDIRECT", apply_filters("ssdownloads_getfile_redirect", true));
	else
		define("GETFILE_REDIRECT", true);
	
	/*
		Define the URL base for services/templates in the plugin (can override below)
				
		Optional Fix:
		If you are getting 404 or include errors loading the template files, use the ssdownloads_plugins_url filter/hook
		to set the location of the plugin on the serve.
	*/
	if(function_exists("plugins_url"))
	{
		//loaded from WordPress, let's use WP functions to get the URL for the plugin
		define("SSD_PLUGIN_URL", apply_filters("ssdownloads_plugins_url", plugins_url("ss-downloads")));		
	}
	else
	{
		//loaded outside of WP, let's do our best
		define("SSD_PLUGIN_URL", "http://" . $_SERVER['HTTP_HOST'] . str_replace("/includes", "", substr($filepath, $bpos, strlen($filepath) - $bpos)));
	}
			
	//check for a template files in the theme folder, otherwise fall back on the templates in the plugin directory
	if(function_exists("get_theme_root"))
	{		
		//email form template
		$email_form_template_file = get_theme_root() . "/" . basename(get_bloginfo("template_url")) . '/ssd-emailform.php';
		if(file_exists($email_form_template_file))
			define("SSD_EMAIL_FORM_URL", get_bloginfo("template_url") . "/ssd-emailform.php");			
		else
			define("SSD_EMAIL_FORM_URL", SSD_PLUGIN_URL . "/templates/emailform.php");	

		//email and name form template
		$email_and_name_form_template_file = get_theme_root() . "/" . basename(get_bloginfo("template_url")) . '/ssd-emailandnameform.php';
		if(file_exists($email_and_name_form_template_file))
			define("SSD_EMAILANDNAME_FORM_URL", get_bloginfo("template_url") . "/ssd-emailandnameform.php");			
		else
			define("SSD_EMAILANDNAME_FORM_URL", SSD_PLUGIN_URL . "/templates/emailandnameform.php");	
		
		//register notice template
		$email_form_template_file = get_theme_root() . "/" . basename(get_bloginfo("template_url")) . '/ssd-register.php';
		if(file_exists($email_form_template_file))
			define("SSD_REGISTER_URL", get_bloginfo("template_url") . "/ssd-register.php");			
		else
			define("SSD_REGISTER_URL", SSD_PLUGIN_URL . "/templates/register.php");
			
		//download form template
		$email_form_template_file = get_theme_root() . "/" . basename(get_bloginfo("template_url")) . '/ssd-download.php';
		if(file_exists($email_form_template_file))
			define("SSD_DOWNLOAD_URL", get_bloginfo("template_url") . "/ssd-download.php");			
		else
			define("SSD_DOWNLOAD_URL", SSD_PLUGIN_URL . "/templates/download.php");
			
		//email sent template
		$email_form_template_file = get_theme_root() . "/" . basename(get_bloginfo("template_url")) . '/ssd-emailsent.php';
		if(file_exists($email_form_template_file))
			define("SSD_EMAIL_FORM_URL", get_bloginfo("template_url") . "/ssd-emailsent.php");			
		else
			define("SSD_EMAILSENT_URL", SSD_PLUGIN_URL . "/templates/emailsent.php");		
	}
	
	//paths to the services used
	define("SSD_ADD_EMAIL_URL", SSD_PLUGIN_URL . "/services/addemail.php");	
	define("SSD_RESET_URL", SSD_PLUGIN_URL . "/services/reset.php");		
	
	//vars and functions for our "encryption" of the filenames, which is pretty basic
	/*
		Optional Security:
		Change the SSD_SHUFFLED_CHARS constant to some random order or 0-9A-Za-z for further security.
	*/
	define("SSD_CHARS", "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklm nopqrstuvwxyz");
	define("SSD_SHUFFLED_CHARS", "TJdgK7EqYUN20oDiHA1MyQ LVl35nSbwuIFPOWzBcxCXZGma8j6Rvstrf9p4ekh ");
	
	function ssd_swapChars($s)
	{
		return strtr($s, SSD_CHARS, SSD_SHUFFLED_CHARS);
	}
	
	function ssd_unswapChars($s)
	{
		return strtr($s, SSD_SHUFFLED_CHARS, SSD_CHARS);
	}	
	
	require_once($filepath . "/functions.php");
	
	if(!class_exists("ssd_mimetype"))
		require_once("class.mimetype.php");
?>
