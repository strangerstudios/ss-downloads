<?php	
	define('WP_USE_THEMES', false);
	require('../../../../wp-load.php');
		
	//get the filename
	$file = ssd_unswapChars(sanitize_text_field($_REQUEST['file']));		
	
	//fix it if there is no leading http, etc
	if(substr($file, 0, 1) == "/")
		$file = "http://" . $_SERVER['HTTP_HOST'] . $file;
	elseif(substr($file, 0, 4) != "http")
		$file = "http://" . $_SERVER['HTTP_HOST'] . "/" . $file;
		
	//serverfile
	$serverfile = str_replace("http://" . $_SERVER['HTTP_HOST'], $_SERVER['DOCUMENT_ROOT'], $file);	
	$require = ssd_getOption("require");
	global $current_user;
	//if user has session flag, fetch and return the file	
	if($file && ((($require == "email" || $require == "emailandname") && $_SESSION['ssd_email_validates']) || ($require == "user" && $current_user->ID)))
	{
		//save file in database	
		if($require == "user")
			$wpdb->insert($wpdb->ss_downloads, array('name' => trim($current_user->first_name . " " . $current_user->last_name), 'email' => $current_user->email, 'file' => $file, 'ip' => $_SERVER['REMOTE_ADDR'], 'referrer' => $_SERVER['HTTP_REFERER']), array('%s', '%s', '%s', '%s', '%s'));				
		else
			$wpdb->insert($wpdb->ss_downloads, array('name' => $_SESSION['ssd_name'], 'email' => $_SESSION['ssd_email'], 'file' => $file, 'ip' => $_SERVER['REMOTE_ADDR'], 'referrer' => $_SERVER['HTTP_REFERER']), array('%s', '%s', '%s', '%s', '%s'));				
				
		if(GETFILE_REDIRECT === "cURL")
		{
			$curl_handle=curl_init();
			curl_setopt($curl_handle, CURLOPT_URL, $file);
			curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
			$r = curl_exec($curl_handle);
			curl_close($curl_handle);
			
			session_write_close();
			$mimetype = new ssd_mimetype();       		
			header("Content-type: " . $mimetype->getType($file)); 
			header("Content-Transfer-Encoding: Binary"); 
			header("Content-Length: ".filesize($serverfile)); 
			header("Content-Disposition: attachment; filename=\"" . basename($file) . "\""); 
							
			echo $r; 
			exit(0);
		}
		elseif(GETFILE_REDIRECT)
		{
			header("Location: " . $file);
			exit(0);
		}
		else
		{		
			//output file
			session_write_close();
			$mimetype = new ssd_mimetype();       		
			header("Content-type: " . $mimetype->getType($file)); 
			header("Content-Transfer-Encoding: Binary"); 
			header("Content-Length: ".filesize($serverfile)); 
			header("Content-Disposition: attachment; filename=\"" . basename($file) . "\""); 
			
			//close db connection (because it can cause issues if it remains open while downloading)
			global $wpdb;
			mysql_close($wpdb->dbh);
			
			if(GETFILE_REDIRECT == "file_get_contents")
				echo file_get_contents($file); 
			else
				echo wp_remote_retrieve_body(wp_remote_get($file)); 
			
			exit(0);
		}
	}
	else
	{	
		//else Something is wrong.
		?>
        <p>This file is protected. You need to enter an email address to gain access. Please contact us if this is unexpected. <a href="/">Back to homepage</a>.</p>
        <?php
	}
?>