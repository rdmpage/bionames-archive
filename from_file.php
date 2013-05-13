<?php

// Load PDFs from local list of file path and URL

require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/utils.php');
require_once (dirname(__FILE__) . '/pdf.php');

$filename = 'test.txt';
$filename = 'hbs.txt';
//$filename = 'tssa.txt';

$shas=array();

$file_handle = fopen($filename, "r");

while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));
	$parts = explode("\t", $line);
	
	$filename = $parts[0];
	$url = $parts[1];

	echo "filename=$filename\n";
	echo "url=$url\n";
	
	// Do we have this already?
	$sha1 = pdf_with_url($url);
	
	//print_r($sha1);
	
	if (!$sha1)
	{	
		// nope
		
		$pdf = new stdclass;
		$pdf->urls = array();
		$pdf->urls[] = $url;
		
		// sha1 is sha1 of local file
		$pdf->sha1 = sha1($filename);
		$pdf->_id = $pdf->sha1;
		
		$shas[] = $pdf->sha1;
		
		// Do we have a file with this sha1?
		$sha1 = pdf_with_sha1($pdf->sha1);
		if ($sha1)
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
			copy ($filename, $pdf->filepath. '/' . $pdf->filename);
						
			print_r($pdf);
			
			// New PDF, so add to database					
			$resp = $couch->send("POST", "/" . $config['couchdb'], json_encode($pdf));	
			
			echo $resp . "\n";
			
		}
	}
}

file_put_contents('file_sha.txt', join("\n", $shas));

?>
