<?php

require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/pdf.php');
require_once (dirname(__FILE__) . '/utils.php');

//print_r($config);


$sha1 = '';

if (isset($_GET['sha1']))
{
	$sha1 = $_GET['sha1'];
}

$obj = new stdclass;
$callback = '';

if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}

$sha1 = pdf_with_sha1($sha1);

if ($sha1)
{
	$obj->title 		= 'PDF';
	$obj->description 	= 'PDF';

	$obj->canonical_url = $config['archive_web_root'] . '/pdfstore?sha1=' . $sha1;
	$obj->id 			= $sha1;
	
	// Count pages
	$sha1_path = sha1_to_path_string($sha1);
	$image_path = $config['pdf_file_root'] .  $sha1_path . '/images';
	
	$obj->pages = count_images($image_path);
	
	// If we don't have images make them
	if ($obj->pages == 0)
	{
		pdf_to_images($sha1, 'pdf');
		$obj->pages = count_images($image_path);
	}
		
	$obj->resources = new stdclass;
	
	$obj->resources->page = new stdclass;
	$obj->resources->page->text 	= $config['archive_web_root'] . '/documentcloud/pages/' . $sha1 . '/{page}';
	$obj->resources->page->image 	= $config['archive_web_root'] . '/documentcloud/pages/' . $sha1 . '/{page}-{size}';
	
	$obj->sections = array();
	
	$obj->annotations = array();
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