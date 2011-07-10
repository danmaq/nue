<?php

if(isset($attrs['videoid']))
{
	$shortUrl = 'http://www.nicovideo.jp/watch/' . $attrs['videoid'];
	$result = $this->simpleHMLMConvert('iframe', array(
		'width' => 312, 'height' => 192,
		'src' => 'http://www.nicovideo.jp/thumb/' . $attrs['videoid'],
		'srcdoc' => $shortUrl,
		'seamless' => 'seamless',
		'allowfullscreen' => 'true'));
	$this->createHTMLElement($result, 'a', array('href' => $shortUrl),
		isset($attrs['alt']) ? $attrs['alt'] : $shortUrl);
}

?>
