<?php	
	//require('../includes/setup.php');
	define('WP_USE_THEMES', false);
	require('../../../../wp-load.php');	
	
	$file = sanitize_text_field($_REQUEST['file']);
	$title = sanitize_text_field($_REQUEST['title']);
	$postid = intval($_REQUEST['postid']);
	$ssdmsg = intval($_REQUEST['ssdmsg']);	
?>
<div id="ss-downloads">
	<h3>Enter your email address to download <em><?php echo $title; ?></em></h3>
    <?php
		if($ssdmsg == 1)
		{
		?>
		<p class="message error">Please enter a valid email address.</p>
		<?php
		}
	?>
    
    <form action="<?php echo SSD_PLUGIN_URL; ?>/services/addemail.php" method="post">
        <input class="input-text" size="50" placeholder="Enter your email address..." type="text" name="email" value="" />
        <input type="hidden" name="title" value="<?php echo esc_attr($title); ?>" />
        <input type="hidden" name="file" value="<?php echo esc_attr($file); ?>" />
        <input type="submit" value="SUBMIT" />
        <input type="hidden" name="postid" value="<?php echo esc_attr($postid); ?>" />
    </form>
</div>