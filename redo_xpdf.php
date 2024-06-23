<?php

// xpdf redo

require_once (dirname(__FILE__) . '/xpdf.php');
require_once (dirname(__FILE__) . '/utils.php');



if (0)
{
	$force = true;
	foreach ($shas as $sha1)
	{
		if ($force = false)
		{
			if (have_pdf_images($sha1, 'pdf'))
			{
				echo "Done!\n";
			}
			else
			{
				pdf_to_images($sha1, 'pdf');
			}
		}
		else
		{
			pdf_to_images($sha1, 'pdf');
		}
	}
}
else
{
	
	
	$filename = 'sha1.txt';
	$filename = 'file_sha.txt';
	$file_handle = fopen($filename, "r");
	
	$force = true;
	//$force = false;
	
	while (!feof($file_handle)) 
	{
		$sha1 = trim(fgets($file_handle));
		
		
			if ($force)
			{
				pdf_to_images_xpdf($sha1, 'pdf');
			}
			else
			{
				if (have_pdf_images($sha1, 'pdf'))
				{
					echo "Done!\n";
				}
				else
				{
					//echo "$sha1\n";
					pdf_to_images_xpdf($sha1, 'pdf');
				}
			}
		
	}
	
}

?>
