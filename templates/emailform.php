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
	<h3>Enter your email address to download <em><?php echo $title; ?></em></h3>
    <?php
		if($_REQUEST['ssdmsg'] == 1)
		{
		?>
		<p class="message error">Please enter a valid email address.</p>
		<?php
		}
	?>
    
    <form action="<?php echo SSD_PLUGIN_URL; ?>/services/addemail.php" method="post">
        <input class="input-text" size="50" placeholder="Enter your email address..." type="text" name="email" value="" />
        <input type="hidden" name="title" value="<?php echo $title; ?>" />
        <input type="hidden" name="file" value="<?php echo $file; ?>" />
        <input type="submit" value="SUBMIT" />
        <input type="hidden" name="postid" value="<?php echo $postid; ?>" />
    </form>
</div>