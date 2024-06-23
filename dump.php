<?php

require_once (dirname(__FILE__) . '/couchsimple.php');

$page = 100;
$offset = 0;


$done = false;

$rows_per_page = 10;
$skip = 0;

while (!$done)
{
	$url = '_design/housekeeping/_view/ids';
	$url .= '?limit=' . $rows_per_page . '&skip=' . $skip;
	$url .= '&stale=ok';
	$url .= '&include_docs=true';
	
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);
	
	//echo $resp;
	
	$obj = json_decode($resp);
	
	
	foreach($obj->rows as $row)
	{
		echo json_encode($row->doc) . "\n";
	}
	
	if (count($obj->rows) < $rows_per_page)
	{
		$done = true;
	}
	else
	{
		$page = ($obj->offset / $rows_per_page) + 1; // == 1
		$skip = $page * $rows_per_page; // == 10 for the first page, 20 for the second ...	
	}
	
	// debug
	//echo '[' . $skip . '(' . count($obj->rows) . ')]';
	//$done = ($skip > 100);
	
}

?>