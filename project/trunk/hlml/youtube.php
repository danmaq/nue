<?php

if(isset($attrs['videoid']))
{
	$shortUrl = 'http://youtu.be/' . $attrs['videoid'];
	$result = $this->simpleHMLMConvert('iframe', array(
		'width' => 425, 'height' => 349,
		'src' => 'http://www.youtube.com/embed/' . $attrs['videoid'],
		'srcdoc' => $shortUrl,
		'seamless' => 'seamless',
		'allowfullscreen' => 'true'));
	$this->createHTMLElement($result, 'a', array('href' => $shortUrl),
		isset($attrs['alt']) ? $attrs['alt'] : $shortUrl);
}

?>
