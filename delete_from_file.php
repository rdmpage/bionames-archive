<?php

// Read PDFs that we want to delete from archive

require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/utils.php');
require_once (dirname(__FILE__) . '/pdf.php');


$filename = 'extra.txt';

$sha1s = array();

$file_handle = fopen($filename, "r");

while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));
	
	if (preg_match('/^#/', $line))
	{
		// skip
	}
	else
	{
		$empty = false;
		$parts = explode("\t", $line);
		
		$filename = $parts[0];
		$url = $parts[1];
		
		
		if (!file_exists($filename))
		{
			echo "*** $filename not found *** \n";
			exit();		
		}
		
		
		// Do we have this already?
		$sha1 = pdf_with_url($url);
		
		echo "sha1='$sha1'\n"; //exit();
		
		$sha1s[] = $sha1;
	}

}

print_r($sha1s);

foreach ($sha1s as $sha1)
{
	echo $sha1 . "\n";
	$couch->add_update_or_delete_document(null, $sha1, 'delete');
}


?>
