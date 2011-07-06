<?php

$fsize = -1;
if(isset($attrs['path']))
{
	$fpath = NUE_ROOT . '/' . $attrs['path'];
	if(file_exists($fpath))
	{
		$fsize = filesize($fpath);
		if($fsize === false)
		{
			$fsize = -1;
		}
	}

	// 0.00MB –hŽ~ô
	if($fsize > 0 && $fsize < 10486)
	{
		$fsize = 10486;
	}
}

$result = $dom->createTextNode(sprintf('%.2F MB', $fsize / 1048576));

?>
