<?php
	//wp includes
	define('WP_USE_THEMES', false);
	require('../../../../wp-load.php');
	
	if ( !current_user_can('manage_options') ) 
	{ 
		exit(0);
	}
	
	$sql = "SELECT *, file as filename, UNIX_TIMESTAMP(timestamp) as timestamp FROM $wpdb->ss_downloads ORDER BY id";	
	$downloads = $wpdb->get_results($sql);	
	
	$csvoutput = "id\tname\temail\tfile\tip\treferrer\tdate\n";
	
	if($downloads)
	{
		foreach($downloads as $download)
		{						
			$csvoutput .= enclose($download->id) . "\t" .
						  enclose($download->name) . "\t" .
						  enclose($download->email) . "\t" .
						  enclose($download->filename) . "\t" .
						  enclose($download->ip) . "\t" .
						  enclose($download->referrer) . "\t" .
						  enclose(date("n/d/Y g:i A e", $download->timestamp)) . "\n";				
		}
	}
	
	$size_in_bytes = strlen($csvoutput);
	//header("Content-type: text/csv");
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=downloads_" . date("Y-m-d") . ".xls; size=$size_in_bytes");
	
	print $csvoutput;
?>