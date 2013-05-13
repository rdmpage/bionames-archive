<?php

// http://localhost/~rpage/archive/zt02653p059.pdf

require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/utils.php');
require_once (dirname(__FILE__) . '/pdf.php');

//--------------------------------------------------------------------------------------------------
function update_pdf_request(&$pdf, $request)
{
	$timestamp = time();
	$pdf->request->{$timestamp} = new stdclass;
	$pdf->request->{$timestamp}->url = $request->url;
	$pdf->request->{$timestamp}->header = $request->header;
	
	if (!in_array($request->url, $pdf->urls))
	{	
		$pdf->urls[] = $request->url;
	}
	
	// handle case 302 and hence there being another URL for PDF
	if ($request->curl_info['url'] != $request->url)
	{
		if (!in_array($request->curl_info['url'], $pdf->urls))
		{	
			$pdf->urls[] = $request->curl_info['url'];
		}
	}		
		
	// known special cases, e.g. where there is a Javascript block
}

//--------------------------------------------------------------------------------------------------
// URL may be rewritten to create PDF URL, and this may be behind a Javascript script, or we may
// have an older URL that we want to include the mapping
function add_alternative_url(&$pdf, $url)
{

	if (!in_array($url, $pdf->urls))
	{	
		$pdf->urls[] = $url;
	}
}


//--------------------------------------------------------------------------------------------------
// Wrapper for fetching PDF
class http_request
{
	public $url;
	public $data = null;
	public $header;
	public $http_code;
	public $ch;
	public $curl_error;
	public $curl_result;
	public $curl_info;
	public $error_no = 0;
	public $error_message = '';
	
	public $destination = '';
	
	//----------------------------------------------------------------------------------------------
	function __construct () {}
	
	//----------------------------------------------------------------------------------------------
	function http_code_valid()
	{
		if ( ($this->http_code == '200') || ($this->http_code == '302') || ($this->http_code == '403'))
		{
			return true;
		}
		else{
			return false;
		}
	}
	
	//----------------------------------------------------------------------------------------------
	function get($url)
	{
		global $config;
		
		$this->url = $url;
		
		$this->data = null;
		
		$this->ch = curl_init(); 
		curl_setopt ($this->ch, CURLOPT_URL, $url); 
		curl_setopt ($this->ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($this->ch, CURLOPT_FOLLOWLOCATION,	1); 
		curl_setopt ($this->ch, CURLOPT_HEADER,		  1);  

		curl_setopt ($this->ch, CURLOPT_TIMEOUT,	600);  
		
		//curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
		
		curl_setopt ($this->ch, CURLOPT_COOKIEJAR, 'cookie.txt');
				
		if ($config['proxy_name'] != '')
		{
			curl_setopt ($this->ch, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
		}
		
		$this->curl_result = curl_exec ($this->ch); 
		
		if (curl_errno ($this->ch) != 0 )
		{
			$this->error_no = curl_errno ($this->ch);
			$this->error_message = curl_error($this->ch);
		}
		else
		{
			$this->curl_info = curl_getinfo($this->ch);
			
			$this->header = substr($this->curl_result, 0, $this->curl_info['header_size']);
			
			//echo $url;
			//echo $this->header;

			$this->http_code = $this->curl_info['http_code'];
			
			if ($this->http_code_valid())
			{
				$this->data = substr($this->curl_result, $this->curl_info['header_size']);
				
				
				// Is it a PDF?
				$pos = strpos ($this->data, '%PDF');
				if ($pos === false)
				{
					$this->data = '';
				}
				else
				{
					$this->data = substr($this->data, $pos);
				}
			}
		}
		curl_close($this->ch);
	}
}


//--------------------------------------------------------------------------------------------------
function default_display()
{
	echo '<form method="GET" action="pdfstore.php">
			<input type="url" name="url" style="font-size:24px;" id="url" placeholder="URL for PDF file" value="" size="60"><br/>
			<input type="url" name="canonical_url" style="font-size:24px;" id="canonical_url" placeholder="Canonical PDF URL" value="" size="60"><br/>
			<input type="checkbox" name="noredirect" value="1">No redirect<br />
	<select name="format">
		<option value="PDF">PDF</option>
		<option value="json">JSON</option>
	</select><br />
			
			<input type="submit" value="Go" style="font-size:24px;">	
	</form>';
}


//--------------------------------------------------------------------------------------------------
function main()
{	
	global $config;
	global $couch;
	
	// If no query parameters 
	if (count($_GET) == 0)
	{
		default_display();
		exit(0);
	}
	
	$format = 'pdf';
	if (isset($_GET['format']))
	{	
		switch (strtolower($_GET['format']))
		{
			case 'json':
				$format = 'json';
				break;
				
			default:
				$format = 'pdf';
				break;				
		}
	}
	
			
	// url
	if (isset($_GET['url']))
	{	
		$url = $_GET['url'];
		
		// 
		//$url = urldecode($url);
		//$url = str_replace(' ', '%20', $url);
		
		$redirect = true;
		
		if (isset($_GET['noredirect']))
		{
			$redirect = false;
		}
		
		// We may supply another URL, say one we know is broken, or gets rewritten by the server
		// in a way CURL can't cope with. This is the canonical URL for the PDF, but we fetch it from
		// the url parameter
		$canonical_url = '';
		if (isset($_GET['canonical_url']))
		{
			$canonical_url = $_GET['canonical_url'];
		}
		
		$pdf = null;
		
		$sha1 = pdf_with_url($url, $canonical_url);
				
		if (!$sha1)
		{
			// try and fetch PDF
			$request = new http_request();
			$request->get($url);
			
			//print_r($request);
					
			// Got something and it's a PDF (need to check this...)
			if (($request->http_code == 200) && ($request->data != ''))
			{
				$pdf = new stdclass;
				$pdf->urls = array();
				
				// sha1
				$pdf->sha1 = sha1($request->data);
				$pdf->_id = $pdf->sha1;
				
				// Do we have a file with this sha1?
				$sha1 = pdf_with_sha1($pdf->sha1);
				if ($sha1)
				{
					//echo "have";
					// Have this already, but not with this URL, so update URLs
					
					$resp = $couch->send("GET", "/" . $config['couchdb'] . "/" . $sha1);	
					$pdf = json_decode($resp);
					
					update_pdf_request($pdf, $request);
					
					if ($canonical_url != '')
					{
						add_alternative_url ($pdf, $canonical_url);
					}
					$resp = $couch->send("PUT", "/" . $config['couchdb'] . "/" . $sha1, json_encode($pdf));
					
					//echo $resp;
				}
				else
				{
					
					// New PDF
					$pdf->relative_path = sha1_to_path_string($pdf->sha1);
					$pdf->filepath = create_path_from_sha1($pdf->sha1, $config['pdf_file_root']);
					$pdf->filename = $pdf->sha1 . '.pdf';
					
					file_put_contents($pdf->filepath . '/' . $pdf->filename, $request->data);
					
					// Details of this request
					update_pdf_request($pdf, $request);
					
					if ($canonical_url != '')
					{
						add_alternative_url ($pdf, $canonical_url);
					}
					//print_r($pdf);
					
					// New PDF, so add to database					
					$resp = $couch->send("POST", "/" . $config['couchdb'], json_encode($pdf));	
					
					$sha1 = $pdf->sha1;
				}
			}			
			
			
			
		}
		
		if ($sha1)
		{
			// we now have PDF (either we had it or we've successfully fetched it)
			if ($redirect)
			{
				// go to original PDF URL
				header("HTTP/1.1 302 Found");
				header("Location: " . $url);
				exit(0);
			}
			else
			{
				switch ($format)
				{
					case 'json':
						header("HTTP/1.1 200 Found");
						$obj = new stdclass;
						$obj->url = $url;
						$obj->http_code = 200;
						$obj->sha1 = $sha1;
						echo json_encode($obj);
						exit(0);					
						break;
						
					case 'pdf':
					default:						
						// display cached PDF
						header("HTTP/1.1 302 Found");
						$location = $config['pdf_web_root'] . sha1_to_path_string($sha1) . '/' . $sha1 . '.pdf';
						header("Location: " . $location);
						exit(0);
						break;
				}
			}
		}
		else
		{
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');
			$_SERVER['REDIRECT_STATUS'] = 404;

			$obj = new stdclass;
			$obj->http_code = 404;
			$obj->url = $url;
			echo json_encode($obj);
			exit(0);
		}
	}	
	
	// Display cached PDF
	if (isset($_GET['sha1']))
	{	
		$sha1 = $_GET['sha1'];
		
		header("HTTP/1.1 302 Found");
		$location = $config['pdf_web_root'] . sha1_to_path_string($sha1) . '/' . $sha1 . '.pdf';
		header("Location: " . $location);
		exit(0);
	}

	
	// fall through
	default_display();	
}

main();

?>