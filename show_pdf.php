<?php

require_once (dirname(__FILE__) . '/pdf.php');
require_once (dirname(__FILE__) . '/utils.php');

//print_r($config);

$sha1 = 'adb5d616e36dd2489bc2fc4eff2f3c878f29d8e1';

if (isset($_GET['sha1']))
{
	$sha1 = $_GET['sha1'];
}

$sha1 = pdf_with_sha1($sha1);

$pdf_path = '';

if ($sha1)
{
	
	// Count pages
	$sha1_path = sha1_to_path_string($sha1);
	$pdf_path = $config['pdf_web_root'] .  $sha1_path . '/' . $sha1 . '.pdf';
}

if ($pdf_path != '')
{
	// redirect to PDF
	header("Location: $pdf_path", true, 301);
}
else
{
	header('HTTP/1.1 404 Not Found');
}

?>
