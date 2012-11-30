<?php	
	//require('../includes/setup.php');
	define('WP_USE_THEMES', false);
	require('../../../../wp-load.php');	
	
	$file = $_REQUEST['file'];
	$title = $_REQUEST['title'];
	$postid = $_REQUEST['postid'];
	$ssdmsg = $_REQUEST['ssdmsg'];	
?>
<div id="ss-downloads">
	<h3>Enter your name and email address to download <em><?php echo $title; ?></em></h3>
    <?php
		if($_REQUEST['ssdmsg'] == 1)
		{
		?>
		<p class="message error">Please enter a name and valid email address.</p>
		<?php
		}
	?>
    
    <form action="<?php echo SSD_PLUGIN_URL; ?>/services/addemail.php" method="post">
        <input type="hidden" name="title" value="<?php echo $title; ?>" />
        <input type="hidden" name="file" value="<?php echo $file; ?>" />        
        <input type="hidden" name="postid" value="<?php echo $postid; ?>" />
		<table>
		<tr>
			<td>Name:</td>
			<td>Email:</td>
			<td></td>
		</tr>
		<tr>
			<td><input class="input-text" size="30" type="text" id="name" name="name" value="" /></td>
			<td><input class="input-text" size="30" type="text" name="email" value="" /></td>
			<td><input type="submit" value="SUBMIT" /></td>
		</tr>
		</table>		     
    </form>
</div>