<?php

// Read extra.txt, check file is a PDF, delete if not

require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/utils.php');
require_once (dirname(__FILE__) . '/pdf.php');


$filename = 'extra.txt';

$sha1s = array();

$file_handle = fopen($filename, "r");

$ok_sha1s = array();

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
		
		
		// check file 
		$fs = filesize($filename);
		
		echo $filename . " " . $fs . "\n";
		
		if ($fs < 1000)
		{
			$sha1 = pdf_with_url($url);
			echo "$sha1\n";
			
			$sha1s[] = $sha1;
		}
		else
		{
			$ok_sha1s[] = pdf_with_url($url);
		}
		/*
		$pos = strpos ($this->data, '%PDF');
				if ($pos === false)
				{
					$this->data = '';
				}
				else
				{
					$this->data = substr($this->data, $pos);
				}		
		
		
		// Do we have this already?
		$sha1 = pdf_with_url($url);
		
		echo "sha1='$sha1'\n"; //exit();
		
		$sha1s[] = $sha1;
		*/
	}

}

print_r($sha1s);


// Delete files that aren't OK
foreach ($sha1s as $sha1)
{
	echo $sha1 . "\n";
	$couch->add_update_or_delete_document(null, $sha1, 'delete');
}

// Output  sha1s that are OK
echo join("\n", $ok_sha1s);



?>
