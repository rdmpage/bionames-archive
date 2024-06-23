<?php

require_once('config.inc.php');
require_once('utils.php');

//--------------------------------------------------------------------------------------------------
// Convert a PDF to images
function pdf_to_images_xpdf($sha1, $root = ".")
{
	global $config;

	$sha1_path = create_path_from_sha1($sha1, $root);
	
	// Images folder
	$images_path = $sha1_path . '/images';
	if (!file_exists($images_path))
	{
		$oldumask = umask(0); 
		mkdir($images_path, 0777);
		umask($oldumask);
	}

	// Thumbnails folder (within images folder)
	$thumbnails_path = $images_path . '/thumbnails';
	if (!file_exists($thumbnails_path))
	{
		$oldumask = umask(0); 
		mkdir($thumbnails_path, 0777);
		umask($oldumask);
	}

	$pdf_filename = $sha1_path . '/' . $sha1 . '.pdf';	
	
	$command = '/Users/rpage/Desktop/xpdf-tools-mac-4.00/bin64/pdftopng'
		. ' ' . $pdf_filename . ' page';
	echo $command . "\n";
	system($command);
	
	$files = scandir(dirname(__FILE__));

	$n = 0;
	foreach ($files as $fname)
	{
		if (preg_match('/\.png$/', $fname))
		{	
			echo $fname . "\n";
			
			$n++;
			
			$new_name = $fname;
			$new_name = preg_replace('/page-0+/', 'page-', $new_name);	
			
			if (preg_match('/page-(?<id>\d+)\./', $new_name, $m))
			{
				$new_name = 'page-' . ($m['id'] - 1) . '.png';
			}	
			
			$old_name = dirname(__FILE__) . '/' . $fname;
			$new_name = $images_path . '/' . $new_name;
			
			echo "$old_name $new_name\n";
			
			copy($old_name, $new_name);
			unlink($old_name);
			
			
		}
	}

		

	//----------------------------------------------------------------------------------------------
	// Resize page (700 pixels wide best for documentcloud viewer)
	for ($i = 0; $i < $n; $i++)
	{	
		$command = $config['convert']
			. ' -resize 700x ' 
			. ' -depth 8 '		
			. $images_path  . '/page-' . $i . '.png ' . $images_path . '/page-' . $i . '.png';
		echo $command . "\n";
		system($command);	
	}	
	
	//----------------------------------------------------------------------------------------------
	// Check for case where images are long and thin (typically page is twice as high as should be)
	for ($i = 0; $i < $n; $i++)
	{	
		$details = getimagesize($images_path  . '/page-' . $i . '.png');
		//print_r($details);
		
		$image_width = $details[0];
		$image_height = $details[1];
		
		if ($image_height > (2 * $image_width))
		{
			$command = $config['convert']
				. ' ' . $images_path  . '/page-' . $i . '.png '  
				. ' -crop ' . $image_width . 'x' . ($image_height/2) . '+0+0 '				
				. $images_path . '/page-' . $i . '.png';
			echo $command . "\n";
			system($command);	
		}
	}
	
		
	//----------------------------------------------------------------------------------------------
	// Optimise
	if (1)
	{
		$command = $config['optipng'] . ' ' . $images_path . '/*.png';
		echo $command . "\n";
		system($command);
	}
		
		
	//----------------------------------------------------------------------------------------------
	// Thumbnails (100 pixels wide)
	for ($i = 0; $i < $n; $i++)
	{	
		$command = $config['convert']
			. ' -resize 100x ' 
			. ' -depth 8 '		
			. $images_path  . '/page-' . $i . '.png ' . $thumbnails_path . '/page-' . $i . '.png';
		echo $command . "\n";
		system($command);	
	}	
	
	//----------------------------------------------------------------------------------------------
	// text folder
	$text_path = $sha1_path . '/text';
	if (!file_exists($text_path))
	{
		$oldumask = umask(0); 
		mkdir($text_path, 0777);
		umask($oldumask);
	}
		
	for ($i = 0; $i < $n; $i++)
	{	
		// Attempt to extract text
		//$command = $config['pdftotext']
		$command = '/Users/rpage/Desktop/xpdf-tools-mac-4.00/bin64/pdftotext'
			. ' -f ' . ($i + 1)
			. ' -l ' . ($i + 1)
			. ' -raw'
			. ' -enc UTF-8 '
			. $pdf_filename  . ' ' . $text_path . '/page-' . $i . '.txt';
		echo $command . "\n";
		system($command);
	}	
	
	
	
	
}

if (0)
{
	$sha1 = 'ae3c38d17055a7b7006ce271c8f3150e0c0b46a1';

	pdf_to_images_xpdf($sha1, 'pdf');
}

