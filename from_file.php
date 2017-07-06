<?php

// Load PDFs from local list of file path and URL

require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/utils.php');
require_once (dirname(__FILE__) . '/pdf.php');

$filename = 'test.txt';
$filename = 'hbs.txt';
//$filename = 'tssa.txt';
$filename = 'wash.txt';

$filename = 'extra.txt';

$shas=array();

$force = true;

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
		$parts = explode("\t", $line);
		
		$filename = $parts[0];
		$url = $parts[1];
		
		
		if (!file_exists($filename))
		{
			echo "*** $filename not found *** \n";
			exit();		
		}
	
		echo "filename=$filename\n";
		echo "url=$url\n";
		
		$sha1 = '';
		
		// Do we have this already?
		$sha1 = pdf_with_url($url);
		
		//print_r($sha1);
		
		echo "sha1='$sha1'\n"; //exit();
		
		if ((!$sha1 || ($sha1 == '')) || $force)
		{	
			// nope
			
			$pdf = new stdclass;
			$pdf->urls = array();
			$pdf->urls[] = $url;
			
			// sha1 is sha1 of local file
			$pdf->sha1 = sha1($filename);
			$pdf->_id = $pdf->sha1;
			
			echo $pdf->sha1 . "\n";
			
			$shas[] = $pdf->sha1;
			
			// Do we have a file with this sha1?
			$sha1 = pdf_with_sha1($pdf->sha1);
			if ($sha1 && !$force)
			{
				echo "have\n";
			}
			else
			{			
				// New PDF
				$pdf->relative_path = sha1_to_path_string($pdf->sha1);
				$pdf->filepath = create_path_from_sha1($pdf->sha1, $config['pdf_file_root']);
				$pdf->filename = $pdf->sha1 . '.pdf';
				
				// Copy PDF 
				echo $filename . ' ' .  $pdf->filepath. '/' . $pdf->filename. "\n";
				copy ($filename, $pdf->filepath. '/' . $pdf->filename);
							
				print_r($pdf);
				
				// New PDF, so add to database					
				if (0)
				{
					$resp = $couch->send("POST", "/" . $config['couchdb'], json_encode($pdf));	
				}
				else
				{
					$resp = $couch->add_update_or_delete_document($pdf, $pdf->sha1);
				}
				
				echo $resp . "\n";
				
			}
		}
		else
		{
			echo "done already!\n";
		}
	}
}

file_put_contents('file_sha.txt', join("\n", $shas));

?>
