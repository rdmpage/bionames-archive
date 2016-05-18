<?php

// fetch text for PDF

require_once (dirname(__FILE__) . '/config.inc.php');
require_once (dirname(__FILE__) . '/utils.php');

$callback = '';

if (isset($_GET['sha1']))
{
	$sha1 = $_GET['sha1'];
}


if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}

$sha1_path = sha1_to_path_string($sha1);


$text_path = $config['pdf_file_root'] .  $sha1_path . '/text';


$obj = new stdclass;
$obj->status = 404;
$obj->sha1 = $sha1;
$obj->pages = array();

$files = scandir($text_path);

// Need to sort pages in true numeric order
$textfiles = array();

foreach ($files as $filename)
{
	if (preg_match('/page-(?<page>\d+)\.txt$/', $filename, $m))
	{	
		$textfiles[] = 'page-' . str_pad($m['page'], 3, '0', STR_PAD_LEFT) . '.txt';
	}
}

asort($textfiles);

//print_r($files);
//print_r($textfiles);


foreach ($textfiles as $filename)
{
	$filename = preg_replace('/page-[0]+/', 'page-', $filename);
	
	if ($filename == 'page-.txt')
	{
		$filename = 'page-0.txt';
	}
	$textfilename = $text_path . '/' . $filename;
	
	//echo $textfilename;
	if (file_exists($textfilename))
	{
		//echo '*ok*';
		$text = file_get_contents($textfilename);
		
		// do any cleaning here	
		$text = addcslashes($text, "\\");
		
		// store
		$obj->status = 200;
		$obj->pages[] = $text;
	}
}

header("Content-type: text/plain");
if ($callback != '')
{
	echo $callback . '(';
}	
echo json_encode($obj);
if ($callback != '')
{
	echo ')';
}

?>