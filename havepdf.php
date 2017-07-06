<?php

// Test to see whether we have this PDF URL in our database already

require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/utils.php');
require_once (dirname(__FILE__) . '/pdf.php');

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
		
		//echo $this->curl_result;
		
		if (curl_errno ($this->ch) != 0 )
		{
			$this->error_no = curl_errno ($this->ch);
			$this->error_message = curl_error($this->ch);
		}
		else
		{
			$this->curl_info = curl_getinfo($this->ch);
			
			//print_r($curl_info);
			
			$this->header = substr($this->curl_result, 0, $this->curl_info['header_size']);
			
			//echo $url;
			//echo $this->header;

			$this->http_code = $this->curl_info['http_code'];
			
			if ($this->http_code_valid())
			{
				$this->data = substr($this->curl_result, $this->curl_info['header_size']);
				
				
				//echo $data;
				//exit();
				
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
	echo '<form method="GET" action="havepdf.php">
			<input type="url" name="url" style="font-size:24px;" id="url" placeholder="URL for PDF file" value="" size="60"><br/>
			<input type="canonical_url" name="canonical_url" style="font-size:24px;" id="canonical_url" placeholder="Canonical PDF URL" value="" size="60"><br/>			
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
	
	//print_r($_GET);
	//exit();
	
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
				
		if ($sha1)
		{
			header("HTTP/1.1 200 Found");
			$obj = new stdclass;
			$obj->url = $url;
			$obj->http_code = 200;
			$obj->sha1 = $sha1;
			echo json_encode($obj);
			exit(0);					
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
	
	// fall through
	default_display();	
}

main();

?>