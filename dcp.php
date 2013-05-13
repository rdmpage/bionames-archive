<?php

require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/utils.php');

$sha1 = '';
$image = false;
$size = 'normal';

$callback = '';


if (isset($_GET['sha1']))
{
	$sha1 = $_GET['sha1'];
}

if (isset($_GET['page']))
{
	$page = $_GET['page'];
	$page--;
}

if (isset($_GET['size']))
{
	$size = $_GET['size'];
	$image = true;
}

if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}


$sha1_path = sha1_to_path_string($sha1);
$image_path = $config['pdf_web_root'] .  $sha1_path . '/images';


if ($image)
{	
	if ($size == 'small')
	{
		header("Location: " . $image_path . '/thumbnails/page-' . $page . '.png' . "\n\n");	
	}
	else
	{
		header("Location: " . $image_path . '/page-' . $page . '.png' . "\n\n");		
	}
}
else
{
	$text_path = $config['pdf_file_root'] .  $sha1_path . '/text';
	
	// text (may not be present if unable to extract text from PDF)	
	$textfilename = $text_path . '/page-' . $page . '.txt';
	if (file_exists($textfilename))
	{
		$text = file_get_contents($textfilename);
	}
	else
	{
		$text = '[text unavailable]';
	}
	
	header("Content-type: text/plain");
	if ($callback != '')
	{
		echo $callback . '(';
	}	
	echo json_encode($text);
	//echo $text;
	if ($callback != '')
	{
		echo ')';
	}	
}

?>