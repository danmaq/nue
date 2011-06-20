<?php

if(isset($attrs['title']))
{
	$this->createAttribute($paragraph, 'title', $attrs['title']);
}
if(isset($attrs['class']))
{
	$this->createAttribute($paragraph, 'class', $attrs['class']);
}

?>
