<?php

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/couchsimple.php');

//--------------------------------------------------------------------------------------------------
function count_images($image_path)
{
	$num_pages = 0;
	$files = scandir($image_path);
	foreach ($files as $filename)
	{
		if (preg_match('/.png$/', $filename))
		{
			$num_pages++;
		}
	}

	return $num_pages;
}

//--------------------------------------------------------------------------------------------------
// Do we have a PDF with this SHA1?
function pdf_with_sha1($sha1)
{
	global $config;
	global $couch;

	$id = null;
	
	$resp = $couch->send("GET", "/" . $config['couchdb']  . "/" . $sha1);
			
	$r = json_decode($resp);
	
	//print_r($r);
	
	if (isset($r->error))
	{
	}
	else
	{
		$id = $sha1;
	}
	return $id;
}

//--------------------------------------------------------------------------------------------------
function pdf_with_url($url, $canonical_url = '')
{
	global $config;
	global $couch;

	$sha1 = null;
	
	$resp = $couch->send("GET", "/" . $config['couchdb'] 
			. "/_design/pdf/_view/url?key=" . urlencode('"' . $url . '"')
			);
	$r = json_decode($resp);
	
	
	//echo $config['couchdb'] 
	//		. "/_design/pdf/_view/url?key=" . urlencode('"' . $url . '"') . "\n";
	//print_r($r);	
		
	if (count($r->rows) == 1)
	{
		$sha1 = $r->rows[0]->id;
	}
	
	return $sha1;
}


// PDF-specific functions

//--------------------------------------------------------------------------------------------------
// Convert a PDF to images
function have_pdf_images($sha1, $root = ".")
{
	global $config;

	$sha1_path = create_path_from_sha1($sha1, $root);
	
	// Images folder
	$images_path = $sha1_path . '/images';
	return file_exists($images_path);
}

//--------------------------------------------------------------------------------------------------
// Convert a PDF to images
function pdf_to_images($sha1, $root = ".")
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
	
	if (1)
	{
		// http://stackoverflow.com/questions/977540/convert-a-pdf-to-a-transparent-png-with-ghostscript
		// Make images bigger to start with then resize to get better text quality
		$dpi = 288;
	
		//----------------------------------------------------------------------------------------------
		// Pages 2-n
		$command = $config['ghostscript']
			. ' -dNOPAUSE '
			. ' -sDEVICE=pngalpha '
			. ' -sOutputFile=' . $images_path . '/page-%d.png'
			. ' -r' . $dpi
			. ' -dFirstPage=2'
			. ' -q ' . $pdf_filename
			. ' -c quit';	
			
		echo $command . "\n";
		system($command);
	
		//----------------------------------------------------------------------------------------------
		// http://stackoverflow.com/questions/2449486/can-ghostscript-to-start-numbering-pages-from-zero	
		// First page (ensures it has zero offset)
		$command = $config['ghostscript']
			. ' -dNOPAUSE '
			. ' -sDEVICE=pngalpha '
			. ' -sOutputFile=' . $images_path . '/page-0.png'
			. ' -r' . $dpi
			. ' -dLastPage=1'
			. ' -q ' . $pdf_filename
			. ' -c quit';	
			
		echo $command . "\n";
		system($command);
	
	}
	else
	{
		// Imagemagick
		$command = $config['convert']
			. ' -density 300'
			. ' ' . $pdf_filename
			. ' ' . $images_path . '/page-%d.png';
		
		echo $command . "\n";
		system($command);
		
	}
	
	
	// count number of pages
	$n = count_images($images_path);
			

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
		$command = $config['pdftotext']
			. ' -f ' . ($i + 1)
			. ' -l ' . ($i + 1)
			. ' -raw'
			. ' -enc UTF-8 '
			. $pdf_filename  . ' ' . $text_path . '/page-' . $i . '.txt';
		echo $command . "\n";
		system($command);
	}	
	
	
}

//--------------------------------------------------------------------------------------------------
// Generate thumbnails (assume images already created)
function pdf_to_thumbnails($sha1, $root = ".")
{
	global $config;

	$sha1_path = create_path_from_sha1($sha1, $root);
	
	// Images folder
	$images_path = $sha1_path . '/images';

	// Thumbnails folder (within images folder)
	$thumbnails_path = $images_path . '/thumbnails';
	if (!file_exists($thumbnails_path))
	{
		$oldumask = umask(0); 
		mkdir($thumbnails_path, 0777);
		umask($oldumask);
	}
	
	// count number of pages
	$n = count_images($images_path);
					
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
	
	
}

//--------------------------------------------------------------------------------------------------
// Generate thumbnails (assume images already created)
function pdf_to_text($sha1, $root = ".")
{
	global $config;

	$sha1_path = create_path_from_sha1($sha1, $root);
	
	$pdf_filename = $sha1_path . '/' . $sha1 . '.pdf';
	
	
	// Images folder
	$images_path = $sha1_path . '/images';

	
	// count number of pages
	$n = count_images($images_path);
	
	
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
		$command = $config['pdftotext']
			. ' -f ' . ($i + 1)
			. ' -l ' . ($i + 1)
			. ' -raw'
			. ' -enc UTF-8 '
			. $pdf_filename  . ' ' . $text_path . '/page-' . $i . '.txt';
		echo $command . "\n";
		system($command);
	}	
}


?>
