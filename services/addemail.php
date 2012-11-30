<?php
	define('WP_USE_THEMES', false);
	require('../../../../wp-load.php');
	
	$email = $_REQUEST['email'];
	$postid = $_REQUEST['postid'];
	$title = $_REQUEST['title'];
	$file = $_REQUEST['file'];
	
	if(isset($_REQUEST['name']))
		$name = $_REQUEST['name'];
	else
		$name = "";
		
	if(is_email($email) && (!isset($_REQUEST['name']) || !empty($name)))
	{
		//update session vars for form use
		$_SESSION['ssd_email'] = $email;		
		$_SESSION['ssd_name'] = $name;		
		$_SESSION['ssd_email_validates'] = true;
		
		//save the email to the db
		$wpdb->replace($wpdb->justemails, array('email' => $email, 'name' => $name), array('%s', '%s'));	
		
		//if we're set to email file, email the file
		$delivery = ssd_getOption("delivery");
		if($delivery == "email_link" || $delivery == "email_attachment")
		{
			require_once(ABSPATH . "/wp-includes/class-phpmailer.php");
			//send email
			$to = $email;				
			$subject = "Your Requested File From " . get_bloginfo("name");
			$from = get_bloginfo('name') . "<" . get_bloginfo('admin_email') . ">";		
			
			if($delivery == "email_attachment")
			{
				$body = "Your requested file is attached.";
				
				//get the filename
				$file = ssd_unswapChars($file);
				
				//fix it if there is no leading http, etc
				if(substr($file, 0, 1) == "/")
					$file = "http://" . $_SERVER['HTTP_HOST'] . $file;
				elseif(substr($file, 0, 4) != "http")
					$file = "http://" . $_SERVER['HTTP_HOST'] . "/" . $file;
					
				//serverfile
				$serverfile = str_replace("http://" . $_SERVER['HTTP_HOST'], $_SERVER['DOCUMENT_ROOT'], $file);					
				$attachment = $serverfile;								
			}
			else
			{
				$body .= "Download your file here: <a href=\"" . SSD_PLUGIN_URL . "/services/getfile.php?file=" . $file . "\">" . $title . "</a>";
				$body .= "<br /><br /><small>Note: You must use the same computer and browser that you submitted your email address from to access the file.</small>";
			}
			
			$mail             = new PHPMailer(); // defaults to using php "mail()"			
			$body             = eregi_replace("[\]",'',$body);	
			$mail->From       = get_bloginfo('admin_email');
			$mail->FromName   = get_bloginfo('name');
			$mail->Subject    = $subject;	
			$mail->MsgHTML($body);	
			$mail->AddAddress($email);				
			if($delivery == "email_attachment")
				$mail->AddAttachment($attachment);             // attachment	
				
			if(!$mail->Send()) {
			  //echo "Mailer Error: " . $mail->ErrorInfo;
			} else {
			  //echo "Message sent!";
			}			
		}
	}
	else
	{
		$_SESSION['ssd_email'] = "";
		$_SESSION['ssd_name'] = "";
		$_SESSION['ssd_email_validates'] = false;	
		$_SESSION['ssd_email_failed'] = true;
	}
		
	session_write_close();
	
	//go back to where you came
	if($postid)
		wp_redirect(get_permalink($postid));
	else
		wp_redirect(get_bloginfo("home"));
?>