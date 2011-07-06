<?php

$result = simpleHMLMConvert('object', array('width' => 425, 'height' => 344), null);
createHTMLElement($result, 'params', array('name' => 'movie', 'value' => 'http://www.youtube.com/v/' . $attrs['videoid']));
createHTMLElement($result, 'params', array('name' => 'allowFullScreen', 'value' => 'true'));
createHTMLElement($result, 'params', array('name' => 'allowscriptaccess', 'value' => 'always'));
$embed = createHTMLElement($result, 'embed', array(
	'type' => 'application/x-shockwave-flash',
	'allowscriptaccess' => 'always'));

?>
