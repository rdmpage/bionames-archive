<?php

//--------------------------------------------------------------------------------------------------
//http://www.php.net/manual/en/function.rmdir.php#107233
function rrmdir($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
 }

//--------------------------------------------------------------------------------------------------
// http://stackoverflow.com/questions/247678/how-does-mediawiki-compose-the-image-paths
function sha1_to_path_array($sha1)
{
	preg_match('/^(..)(..)(..)/', $sha1, $matches);
	
	$sha1_path = array();
	$sha1_path[] = $matches[1];
	$sha1_path[] = $matches[2];
	$sha1_path[] = $matches[3];

	return $sha1_path;
}

//--------------------------------------------------------------------------------------------------
// Return path for a sha1
function sha1_to_path_string($sha1)
{
	$sha1_path_parts = sha1_to_path_array($sha1);
	
	$sha1_path = '/' . join("/", $sha1_path_parts) . '/' . $sha1;

	return $sha1_path;
}

//--------------------------------------------------------------------------------------------------
// Create nested folders in folder "root" based on sha1
function create_path_from_sha1($sha1, $root = '.')
{	
	$sha1_path_parts 	= sha1_to_path_array($sha1);
	$sha1_path 			= sha1_to_path_string($sha1);
	$filename 			= $root . $sha1_path;
				
	// If we dont have file, create directory structure for it	
	if (!file_exists($filename))
	{
		$path = $root;
		$path .= '/' . $sha1_path_parts[0];
		if (!file_exists($path))
		{
			$oldumask = umask(0); 
			mkdir($path, 0777);
			umask($oldumask);
		}
		$path .= '/' . $sha1_path_parts[1];
		if (!file_exists($path))
		{
			$oldumask = umask(0); 
			mkdir($path, 0777);
			umask($oldumask);
		}
		$path .= '/' . $sha1_path_parts[2];
		if (!file_exists($path))
		{
			$oldumask = umask(0); 
			mkdir($path, 0777);
			umask($oldumask);
		}
		$path .= '/' . $sha1;
		if (!file_exists($path))
		{
			$oldumask = umask(0); 
			mkdir($path, 0777);
			umask($oldumask);
		}
	}
	
	return $filename;
}


?>