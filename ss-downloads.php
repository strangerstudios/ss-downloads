<?php
/*
Plugin Name: SS Downloads
Plugin URI: http://www.strangerstudios.com/wordpress-plugins/ss-downloads/
Description: Email capture for content.
Version: 1.5
Author: Jason Coleman
Author URI: http://www.strangerstudios.com
*/

//version
define("SSDOWNLOADS_VERSION", "1.5");
add_option("ssd_db_version", "1.0");

//an include with some defines and functions
require_once("includes/setup.php");

//since we're in WordPress, we can better calculate the plugins directory

//setup the DB
require_once(ABSPATH . '/wp-admin/upgrade-functions.php');

global $table_prefix, $wpdb;
$wpdb->hide_errors();
$wpdb->justemails = $table_prefix . 'justemails';
$wpdb->ss_downloads = $table_prefix . 'ss_downloads';
$installed = $wpdb->get_results("SELECT id FROM $wpdb->ss_downloads");

if (mysql_errno() == 1146) 
{
	$sql = "CREATE TABLE " . $wpdb->ss_downloads . " (
			  `id` int(11) NOT NULL auto_increment,			 
			  `name` varchar(128) NOT NULL,
			  `email` varchar(128) NOT NULL,
			  `file` varchar(255) NOT NULL,
			  `ip` varchar(16) NOT NULL,
			  `referrer` varchar(255) NOT NULL,
			  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			  PRIMARY KEY  (`id`),
			  KEY `email` (`email`),
			  KEY `file` (`file`)
			)";
	$wpdb->query($sql);
	
	$sql = "CREATE TABLE " . $wpdb->justemails . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			`name` varchar(255) NOT NULL,
			email VARCHAR(128) NOT NULL,
			timestamp TIMESTAMP NOT NULL,
			UNIQUE KEY id (id), UNIQUE KEY `email` (`email`)
			);";	
	$wpdb->query($sql);		
}
else
{
	//add name col to tables if name column is missing
	$installed = $wpdb->get_results("SELECT name FROM $wpdb->justemails");
		
	if(mysql_errno() == 1054)
	{
		$sql = "ALTER TABLE " . $wpdb->justemails . " ADD `name` VARCHAR(255) NOT NULL";
		$wpdb->query($sql);
		$sql = "ALTER TABLE " . $wpdb->ss_downloads . " ADD `name` VARCHAR(255) NOT NULL";
		$wpdb->query($sql);
	}
}

//activation
function ssd_activation()
{
	//default settings
	$require = ssd_getOption("require");
	$delivery = ssd_getOption("delivery");
	$templatemethod = ssd_getOption("templatemethod");
	$ssdshortcode = ssd_getOption("ssdshortcode");
	if(!$require)
	{
		$require = "email";
		ssd_setOption("require", $require);
	}	
	if(!$delivery)
	{
		$delivery = "link";
		ssd_setOption("delivery", $delivery);
	}	
	if(!$templatemethod)
	{
		$templatemethod = "";	//let WP choose
		ssd_setOption("templatemethod", $templatemethod);
	}	
	if(!$ssdshortcode)
	{
		$ssdshortcode = "download";
		ssd_setOption("ssdshortcode", $ssdshortcode);
	}	
}
register_activation_hook(__FILE__, "ssd_activation");

//function to delete db tables
function ssd_init()
{
	global $wpdb, $msg, $msgt;
	
	if(is_admin())
	{
		//resetting the DB?
		$reset = $_REQUEST['ssdreset'];
		if($reset && current_user_can("manage_options"))
		{
			$sqlQuery = "DELETE FROM $wpdb->ss_downloads ";
			if($wpdb->query($sqlQuery) !== false)
			{
				wp_redirect("?page=ssdownloads");
				exit;
			}
			else
			{
				$msg = "There was an error clearing the SS Downloads table.";
				$msgt = "ssd_error";
			}
		}
		elseif($reset)
		{
			die("Only administrators can do this.");
		}
		
		//resetting the justemails table?
		$reset = $_REQUEST['ssdemailreset'];
		if($reset && current_user_can("manage_options"))
		{
			$sqlQuery = "DELETE FROM $wpdb->justemails ";
			if($wpdb->query($sqlQuery) !== false)
			{
				wp_redirect("?page=ssdownloads");
				exit;
			}
			else
			{
				$msg = "There was an error clearing the emails table.";
				$msgt = "ssd_error";
			}
		}
		elseif($reset)
		{
			die("Only administrators can do this.");
		}

	}
}
add_action("init", "ssd_init");

//shortcode function
function ssd_shortcode_handler($atts, $content=null, $code="") {
	global $current_user, $post;
	
	// $atts    ::= array of attributes
	// $content ::= text within enclosing form of shortcode element
	// $code    ::= the shortcode found, when == callback name
	// examples: [download title="title" file="/wp-content/uploads/filename.pdf"]
		
	extract(shortcode_atts(array(
		'title' => NULL,
		'file' => NULL,		
	), $atts));
	
	//no filename, just remove the shortcode
	if(!$file)
		return "[missing filename in your download code]";
		
	//no title, set it equal to the filename
	if(!$title)
		$title = basename($file);
	
	//settings
	$require = ssd_getOption("require");
	$delivery = ssd_getOption("delivery");
	$templatemethod = ssd_getOption("templatemethod");
		
	if((($require == "email" || $require == "emailandname") && $_SESSION['ssd_email_validates']) || ($require == "user" && $current_user->ID))
	{				
		if($delivery == "link")
		{		
			//show download box
			$tpath = str_replace('"', '\"', SSD_DOWNLOAD_URL . "?file=" . urlencode(ssd_swapChars($file)) . "&title=" . urlencode($title));
			if($templatemethod == "cURL")
			{
				$curl_handle=curl_init();
				curl_setopt($curl_handle, CURLOPT_URL, $tpath);
				curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
				$r = curl_exec($curl_handle);
				curl_close($curl_handle);
			}
			elseif($templatemethod == "file_get_contents")
				$r = file_get_contents($tpath);				
			else
			{								
				$r = wp_remote_retrieve_body(wp_remote_get($tpath));
				/*
				$file = ssd_swapChars($file);						
				include(dirname(__FILE__) . "/templates/r_download.php");
				*/
			}
		}
		else
		{
			//email_link and email_attachment			
			if($require == "email")
				$theemail = $_SESSION['ssd_email'];
			else
				$theemail = $current_user->user_email;
			
			$tpath = str_replace('"', '\"', SSD_EMAILSENT_URL . "?email=" . urlencode($theemail) . "&file=" . urlencode(ssd_swapChars($file)) . "&title=" . urlencode($title) . "&postid=" . $post->ID . "&require=" . $require . "&delivery=" . $delivery);
			
			if($templatemethod == "cURL")
			{
				$curl_handle=curl_init();
				curl_setopt($curl_handle, CURLOPT_URL, $tpath);
				curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
				$r = curl_exec($curl_handle);
				curl_close($curl_handle);
			}
			elseif($templatemethod == "file_get_contents")
				$r = file_get_contents($tpath);				
			else
			{		
				$r = wp_remote_retrieve_body(wp_remote_get($tpath));
				/*
				$email = $theemail;
				$file = ssd_swapChars($file);
				$postid = $post->ID;				
				include(dirname(__FILE__) . "/templates/r_emailsent.php");
				*/
			}
		}		
	}
	else
	{				
		if($_SESSION['ssd_email_failed'])
		{
			unset($_SESSION['ssd_email_failed']);
			$ssdmsg = 1;
		}
	
		//show register or email form		
		if($require == "email")
		{			
			$tpath = str_replace('"', '\"', SSD_EMAIL_FORM_URL . "?file=" . urlencode(ssd_swapChars($file)) . "&title=" . urlencode($title) . "&postid=" . $post->ID . "&ssdmsg=" . $ssdmsg);
			if($templatemethod == "cURL")
			{
				$curl_handle=curl_init();
				curl_setopt($curl_handle, CURLOPT_URL, $tpath);
				curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
				$r = curl_exec($curl_handle);
				curl_close($curl_handle);
			}
			elseif($templatemethod == "file_get_contents")
				$r = file_get_contents($tpath);				
			else
			{				
				$r = wp_remote_retrieve_body(wp_remote_get($tpath));
				/*
				$file = ssd_swapChars($file);
				$postid = $post->ID;				
				include(dirname(__FILE__) . "/templates/r_emailform.php");
				*/
			}
		}
		elseif($require == "emailandname")
		{
			$tpath = str_replace('"', '\"', SSD_EMAILANDNAME_FORM_URL . "?file=" . urlencode(ssd_swapChars($file)) . "&title=" . urlencode($title) . "&postid=" . $post->ID . "&ssdmsg=" . $ssdmsg);
			if($templatemethod == "cURL")
			{
				$curl_handle=curl_init();
				curl_setopt($curl_handle, CURLOPT_URL, $tpath);
				curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
				$r = curl_exec($curl_handle);
				curl_close($curl_handle);
			}
			elseif($templatemethod == "file_get_contents")
				$r = file_get_contents($tpath);				
			else
			{				
				$r = wp_remote_retrieve_body(wp_remote_get($tpath));				
			}
		}
		else
		{			
			$tpath = str_replace('"', '\"', SSD_REGISTER_URL . "?login_url=" . urlencode(wp_login_url()) . "&redirect_to=" . urlencode(get_permalink($post->ID)));
			if($templatemethod == "cURL")
			{
				$curl_handle=curl_init();
				curl_setopt($curl_handle, CURLOPT_URL, $tpath);
				curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
				$r = curl_exec($curl_handle);
				curl_close($curl_handle);
			}
			elseif($templatemethod == "file_get_contents")
				$r = file_get_contents($tpath);				
			else
			{
				$r = wp_remote_retrieve_body(wp_remote_get($tpath));
				/*
				$login_url = wp_login_url();
				$redirect_to = get_permalink($post->ID);
				include(dirname(__FILE__) . "/templates/r_register.php");
				*/
			}			
		}		
	}
		
	return $r;
}

$ssdshortcode = ssd_getOption("ssdshortcode");
if(!$ssdshortcode)
{
	$ssdshortcode = "download";
	ssd_setOption("ssdshortcode", $ssdshortcode);
}
add_shortcode($ssdshortcode, 'ssd_shortcode_handler');

//add stylesheet on front end
function ssd_print_styles() {
	//check for a copy of the css in the current theme folder
	$css_file = get_theme_root() . "/" . basename(get_bloginfo("template_url")) . '/ssd-ss-downloads.css';
	if(file_exists($css_file))
	{
		$myStyleUrl = get_bloginfo("template_url") . "/ssd-ss-downloads.css";
		$myStyleFile = $css_file;
	}
	else
	{	
		$myStyleUrl = WP_PLUGIN_URL . '/ss-downloads/css/ss-downloads.css';
		$myStyleFile = WP_PLUGIN_DIR . '/ss-downloads/css/ss-downloads.css';		
	}
	
	//load it up
	if ( file_exists($myStyleFile) ) {
		wp_register_style('ss-downloads', $myStyleUrl);
		wp_enqueue_style( 'ss-downloads');
	}
}
add_action('wp_print_styles', 'ssd_print_styles');

//setup and function for mailing list tab
function ssd_add_pages() 
{	
	add_management_page('SS Downloads', 'SS Downloads', 8, 'ssdownloads', 'ssd_ss_downloads');
}

function ssd_ss_downloads()
{
	global $wpdb;		
	
	$sql = "SELECT je.email, je.name, UNIX_TIMESTAMP(je.timestamp) as timestamp FROM $wpdb->justemails je ";		
	$collected_emails = $wpdb->get_results($sql);			
	$just_emails = array();
	$emails_and_names = array();
	if(!empty($collected_emails))
	{
		foreach($collected_emails as $ce)
		{
			$just_emails[] = $ce->email;
			if(!empty($ce->name))
				$emails_and_names[] = $ce->name . ' <' . $ce->email . '>';
			else
				$emails_and_names[] = $ce->email;
		}
	}		
	?>
	<div class="wrap">				
		
		<div id="ssdownloads_notifications">
		</div>
		<style>
			.ssd_message {background-color: #D5E4F7; background-repeat: no-repeat; margin: .5em 0; padding: 6px 6px 6px 6px; color: #345395; font-size: 11px; font-weight: bold; line-height: 1.3em; }

			.ssd_success {background-color: #CFEECA; color: #208A1B; }
			.ssd_error {background-color: #F9D6CB; color: #E36154; }
			.ssd_alert {background-color: #FFF6CC; color: #CF8516; }

			.ssd_message a {color: #345395; }
			.ssd_success a {color: #208A1B; }
			.ssd_error a {color: #E36154; }
			.ssd_alert a {color: #CF8516; }
		</style>
		<script>
			jQuery.get('http://www.strangerstudios.com/ss-downloads-notifications/?v=<?php echo SSDOWNLOADS_VERSION; ?>', function(data) {
			  jQuery('#ssdownloads_notifications').html(data);		 
			});
		</script>
		
		<h2>
			Collected Email Addresses
			<small>
				(<a target="_blank" href="<?php echo plugins_url('services/exportemails.php', __FILE__);?>">Download Full Report</a>)	
				(<a id="clear_ssd_emails" href="#" style="color: #CC0000;">Clear Table</a>)
			</small>
		</h2>	
		<script>
			jQuery('#clear_ssd_emails').click(function(){ 
			   if(window.confirm("This will delete *all names and email addresses* from the 'justemails' table. Press OK to continue and reset the emails database."))
				 window.location='?page=ssdownloads&ssdemailreset=1';
			});
		</script>		
		<textarea style="width: 500px; height: 100px;"><?php echo esc_textarea(implode(", ", $emails_and_names)); ?></textarea>		
        
		<?php if($msg) { ?>
			<p class="ssd_message <?php echo $msgt; ?>"><?php echo $msg; ?></p>
		<?php } ?>
		
        <h2>
        	File Downloads
            <small>
				(<a target="_blank" href="<?php echo plugins_url('services/exportdownloads.php', __FILE__);?>">Download Full Report</a>)
				(<a id="clear_ssd_db" href="#" style="color: #CC0000;">Clear Table</a>)
			</small>
			<script>
				jQuery('#clear_ssd_db').click(function(){ 
				   if(window.confirm("This will delete *all download information including email addresses* from the SS Downloads table. Press OK to continue and reset the download database."))
					 window.location='?page=ssdownloads&ssdreset=1';
				});
			</script>
        </h2>
        <table class="widefat page fixed" cellspacing="0">
        	<thead>
            	<tr>
                	<th class="manage-column" scope="col" width="70%">File</th>
                    <th class="manage-column" scope="col"># Downloads</th>
                    <th class="manage-column" scope="col">Last Download</th>
                </tr>
            </thead>
            <tbody>
            	<?php
					$sql = "SELECT file as filename, COUNT(id) as num, MAX(UNIX_TIMESTAMP(timestamp)) as timestamp FROM $wpdb->ss_downloads GROUP BY file ORDER BY timestamp DESC";
					$files = $wpdb->get_results($sql);
					
					if(count($files))
					{
						foreach($files as $file)
						{
						?>
						<tr>
							<td><a target="_blank" href="<?php echo $file->filename;?>"><?php echo $file->filename; ?></a></td>
							<td><?php echo $file->num; ?></td>
							<td><?php echo date("n/d/Y g:i A e", $file->timestamp); ?></td>
						</tr>
						<?php
						}
					}
					else
					{
					?>
                    <tr>
                    	<td colspan="3"><p>No downloads yet.</p></td>
                    </tr>
                    <?php
					}
				?>
            </tbody>
        </table>        		
		
        <h2>Settings</h2>
        <form action="" method="post" enctype="multipart/form-data">   
        <?php
			//get/set settings
			if($_REQUEST['savesettings'])
			{
				ssd_setOption("require");
				ssd_setOption("delivery");
				ssd_setOption("templatemethod");
				ssd_setOption("ssdshortcode");
			}

			$require = ssd_getOption("require");
			$delivery = ssd_getOption("delivery");
			$templatemethod = ssd_getOption("templatemethod");
			$ssdshortcode = ssd_getOption("ssdshortcode");						
		?>
            <table class="form-table">
            <tbody>                
                <tr>
                    <th scope="row" valign="top" colspan="2">
                        <label for="require">Required For Downloads:</label><br />
                        <select name="require">
                        	<option value="email" <?php if($require == "email") { ?>selected="selected"<?php } ?>>Properly Formatted Email Address</option>
							<option value="emailandname" <?php if($require == "emailandname") { ?>selected="selected"<?php } ?>>Email Address and Name</option>
                            <option value="user" <?php if($require == "user") { ?>selected="selected"<?php } ?>>User Signup</option>
                        </select>                       
                    </th>
                </tr>                 
                <tr>
                    <th scope="row" valign="top" colspan="2">
                        <label for="delivery">File Delivery Method:</label><br />
                        <select name="delivery">
                        	<option value="link" <?php if($delivery == "link") { ?>selected="selected"<?php } ?>>Show Link to File</option>
                            <option value="email_attachment" <?php if($delivery == "email_attachment") { ?>selected="selected"<?php } ?>>Send File as Email Attachment</option>
                            <option value="email_link" <?php if($delivery == "email_link") { ?>selected="selected"<?php } ?>>Send Link to File by Email</option>
                        </select>                        
                    </th>
                </tr>
                <tr>
                    <th scope="row" valign="top" colspan="2">
                        <label for="templatemethod">Template Method:</label><br />
                        <select name="templatemethod">
                        	<option value="" <?php if($templatemethod == "") { ?>selected="selected"<?php } ?>>Let WordPress Choose</option>
							<option value="file_get_contents" <?php if($templatemethod == "file_get_contents") { ?>selected="selected"<?php } ?>>file_get_contents()</option>
							<option value="cURL" <?php if($templatemethod == "cURL") { ?>selected="selected"<?php } ?>>cURL</option>
                        </select>    						
                        <br /><small>If you don't see the download form and/or see errors, changing this might help.</small>                   
                    </th>
                </tr>
                <tr>
                    <th scope="row" valign="top" colspan="2">
                        <label for="ssdshortcode">Shortcode:</label><br />
                        <input type="text" name="ssdshortcode" value="<?php echo esc_attr($ssdshortcode); ?>" />    
                        <br /><small>Can change this to resolve plugin conflicts.</small>
                        
                       	<p>
                        	With your current settings, your shortcode to embed a download form would be something like:<br />
                            <strong>[<?php echo sanitize_text_field($ssdshortcode); ?> file="filename.txt" title="title"]</strong>
                        </p>
                    </th>
                </tr>
            </tbody>
         	</table>
            
            <p class="submit">            
                <input name="savesettings" type="submit" value="Save Settings" /> 		                			
            </p> 
         </form>
	</div>
	<?php
}

add_action('admin_menu', 'ssd_add_pages');
?>
