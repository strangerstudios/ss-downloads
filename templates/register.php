<?php	
	require('../includes/setup.php');
	
	$login_url = $_REQUEST['login_url'];
	$redirect_to = $_REQUEST['redirect_to'];
?>
<div id="ss-downloads">
	<h3>You must be logged in to download <em><?php echo $title; ?></em></h3>
    <p><a href="<?php echo esc_url($login_url); ?>?redirect_to=<?php echo esc_url($redirect_to); ?>">Login here</a> or <a href="<?php echo esc_url($login_url); ?>?action=register&redirect_to=<?php echo esc_url($redirect_to); ?>">click here to register</a>.</p>
</div>