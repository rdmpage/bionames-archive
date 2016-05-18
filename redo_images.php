<?php

require_once (dirname(__FILE__) . '/pdf.php');
require_once (dirname(__FILE__) . '/utils.php');

/*

$json = file_get_contents('dump2.json');
$obj = json_decode($json);

$obj = json_decode($json);

foreach ($obj->rows as $row)
{
	echo $row->id . "\n";
	
	if (have_pdf_images($row->id, 'pdf'))
	{
		echo "done already\n";
	}
	else
	{
		pdf_to_images($row->id, 'pdf');
	}
}
*/

//$sha1 = '8697961e501340f7eb27162d4db48ab0ce779b7c';



$shas = array('01857b01563fd7886227b12881e3386a3e93f6c7');

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
			pdf_to_images($sha1, 'pdf');
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
				pdf_to_images($sha1, 'pdf');
			}
		}
	}
	
}

?>
