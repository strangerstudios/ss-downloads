<?php
	define('WP_USE_THEMES', false);
	require('../../../../wp-load.php');
	
	$postid = $_REQUEST['postid'];
	
	unset($_SESSION['ssd_email']);
	unset($_SESSION['ssd_email_validates']);
	unset($_SESSION['ssd_email_failed']);	
		
	session_write_close();
	
	//go back to where you came
	if($postid)
		wp_redirect(get_permalink($postid));
	else
		wp_redirect(get_bloginfo("home"));
?>