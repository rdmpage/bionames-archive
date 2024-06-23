<?php

$source = dirname(__FILE__) . '/pdf';
$destination = '/Volumes/WD4TB/bionames-archive/pdf';

$hex = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'a','b','c','d','e','f');

$hex = array('f');
$hex = array('e');

$hex = array('a', 'b', 'c', 'd');

foreach ($hex as $i)
{
	foreach ($hex as $j)
	{
		$dir = $i . $j;
		
		echo $dir . "\n";
		
		$command = 'rsync -r -v \'' . $source . '/' . $dir . '\'  \'' . $destination . '\'';
		//echo $command . "\n";
		system($command);
	}
}

?>