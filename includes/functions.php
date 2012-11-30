<?php
	if(!function_exists("readfile_chunked"))
	{
		// from: http://cn2.php.net/manual/en/function.readfile.php#48683
		// Read a file and display its content chunk by chunk
		function readfile_chunked($filename, $retbytes = TRUE) {
			$buffer = '';
			$cnt =0;
			// $handle = fopen($filename, 'rb');
			$handle = fopen($filename, 'rb');
			if ($handle === false) {
				return false;
			}
			while (!feof($handle)) {
				$buffer = fread($handle, CHUNK_SIZE);
				echo $buffer;
				ob_flush();
				flush();
				if ($retbytes) {
					$cnt += strlen($buffer);
				}
			}
			$status = fclose($handle);
			if ($retbytes && $status) {
				return $cnt; // return num. bytes delivered like readfile() does.
			}
			return $status;
		}
	}
	
	if(!function_exists("enclose"))
	{
		function enclose($s)
		{
			return "\"" . str_replace("\"", "\\\"", $s) . "\"";
		}			
	}
	
	function ssd_getOption($s)
	{
		if($_REQUEST[$s])
			return $_REQUEST[$s];
		elseif(get_option("ssd_" . $s))
			return get_option("ssd_" . $s);
		else
			return "";
	}
	
	function ssd_setOption($s, $v = NULL)
	{
		//no value is given, set v to the request var
		if($v === NULL)
			$v = $_REQUEST[$s];
		
		return update_option("ssd_" . $s, $v);		
	}
?>