<?php	
	//require('../includes/setup.php');	
	define('WP_USE_THEMES', false);
	require('../../../../wp-load.php');
	
	$file = sanitize_text_field($_REQUEST['file']);
	$title = sanitize_text_field($_REQUEST['title']);
?>
<div id="ss-downloads">	
	<h3>Your download is ready &raquo;</h3>
	<div class="btn-ss-downloads"><a target="_blank" href="<?php echo SSD_PLUGIN_URL; ?>/services/getfile.php?file=<?php echo $file; ?>"><?php echo $title; ?></a></div>
</div>