<?php
  function createTmpDir() 
  {
  	$dirName = tempnam(sys_get_temp_dir(), 'dir');
  	unlink($dirName);
  	mkdir($dirName);
  	return $dirName;
  }

	/**
	 * Copy file or folder from source to destination, it can do
	 * recursive copy as well and is very smart
	 * It recursively creates the dest file or directory path if there weren't exists
	 * Situtaions :
	 * - Src:/home/test/file.txt ,Dst:/home/test/b ,Result:/home/test/b -> If source was file copy file.txt name with b as name to destination
	 * - Src:/home/test/file.txt ,Dst:/home/test/b/ ,Result:/home/test/b/file.txt -> If source was file Creates b directory if does not exsits and copy file.txt into it
	 * - Src:/home/test ,Dst:/home/ ,Result:/home/test/** -> If source was directory copy test directory and all of its content into dest     
	 * - Src:/home/test/ ,Dst:/home/ ,Result:/home/**-> if source was direcotry copy its content to dest
	 * - Src:/home/test ,Dst:/home/test2 ,Result:/home/test2/** -> if source was directoy copy it and its content to dest with test2 as name
	 * - Src:/home/test/ ,Dst:/home/test2 ,Result:->/home/test2/** if source was directoy copy it and its content to dest with test2 as name
	 * @todo
	 *     - Should have rollback technique so it can undo the copy when it wasn't successful
	 *  - Auto destination technique should be possible to turn off
	 *  - Supporting callback function
	 *  - May prevent some issues on shared enviroments : http://us3.php.net/umask
	 * @param $source //file or folder
	 * @param $dest ///file or folder
	 * @param $options //folderPermission,filePermission
	 * @return boolean
	 */
	function smartCopy($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755))
	{
			$result=false;
		 
			if (is_file($source)) {
					if ($dest[strlen($dest)-1]=='/') {
							if (!file_exists($dest)) {
									cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
							}
							$__dest=$dest."/".basename($source);
					} else {
							$__dest=$dest;
					}
					$result=copy($source, $__dest);
					chmod($__dest,$options['filePermission']);
				 
			} elseif(is_dir($source)) {
					if ($dest[strlen($dest)-1]=='/') {
							if ($source[strlen($source)-1]=='/') {
									//Copy only contents
							} else {
									//Change parent itself and its contents
									$dest=$dest.basename($source);
									@mkdir($dest);
									chmod($dest,$options['filePermission']);
							}
					} else {
							if ($source[strlen($source)-1]=='/') {
									//Copy parent directory with new name and all its content
									@mkdir($dest,$options['folderPermission']);
									chmod($dest,$options['filePermission']);
							} else {
									//Copy parent directory with new name and all its content
									@mkdir($dest,$options['folderPermission']);
									chmod($dest,$options['filePermission']);
							}
					}

					$dirHandle=opendir($source);
					while($file=readdir($dirHandle))
					{
							if($file!="." && $file!="..")
							{
									 if(!is_dir($source."/".$file)) {
											$__dest=$dest."/".$file;
									} else {
											$__dest=$dest."/".$file;
									}
									//echo "$source/$file ||| $__dest<br />";
									$result=smartCopy($source."/".$file, $__dest, $options);
							}
					}
					closedir($dirHandle);
				 
			} else {
					$result=false;
			}
			return $result;
	}
	
  function replaceTagsInTemplate($tmpDir, $NUM_TILES_Y) 
  {
		$fhIn  = fopen($tmpDir."/".'project.json-template','r');
		$fhOut = fopen($tmpDir."/".'project.json','w');
		while ($line = fgets($fhIn)) {
			fwrite($fhOut, str_replace("NUM_TILES_Y", $NUM_TILES_Y, $line));
		}
		fclose($fhIn); // N.B. chiudo solo fhIn, perchè fhOut lo passo all'esterno ancora aperto 
		
		unlink($tmpDir."/".'project.json-template');
		
		return $fhOut;
  }
	
  function appendTail($tmpDir, $fhOut) 
  {
		$fhIn  = fopen($tmpDir."/".'project.json-tail','r');
		while ($line = fgets($fhIn)) {
			fwrite($fhOut, $line);
		}
		fclose($fhIn); // N.B. chiudo solo fhIn, perchè fhOut lo passo all'esterno ancora aperto 
		
		unlink($tmpDir."/".'project.json-tail');
  }
  
  function createProjectFromFolder($tmpDir, $projectName)
  {
  	$filesInFolder = scandir($tmpDir); // N.B. contiene anche "." e ".."
  	$filesInFolder = array_diff( $filesInFolder, [".", ".."] ); 
		$zip = new ZipArchive;
		$zip->open($tmpDir."/".$projectName, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		foreach ($filesInFolder as $file) 
		{
			$zip->addFile($tmpDir."/".$file, $file);
		}
		$zip->close();
		
		// dopo la chiusura dello zip, posso anche cancellare i files
		foreach ($filesInFolder as $file) 
		{
			unlink($tmpDir."/".$file);
		}
  }

  function getCentralTileCoordinates($zoom, $lon, $lat, $tmsType) 
  {
  	if ( $tmsType == "TN" )
  		return getCentralTileCoordinatesTN($zoom, $lon, $lat) ;
  	else
  		return getCentralTileCoordinatesOSM($zoom, $lon, $lat) ;
  }

  function getCentralTileCoordinatesTN($zoom, $lon, $lat) 
  {
  	// print "(".$lat.", ".$lon.")";
  	$out[0] = $zoom;
//	$out[1] = floor((4564.8664339168 * $lon - 50310.5334770127)/pow(2, 8 - $zoom));
    $out[1] = floor((4565.01 * $lon - 50312.16)/pow(2, 8 - $zoom)) + 1; // correzione ad occhio
//	$out[2] = floor((6546.321876555 * $lat - 300970.8906601214)/pow(2, 8 - $zoom));
  	$out[2] = floor((6546.32 * $lat - 300970.89)/pow(2, 8 - $zoom)) + 1; // correzione ad occhio
    return $out;
  }
	
  function getCentralTileCoordinatesOSM($zoom, $lon, $lat) 
  {
  	// print "(".$lat.", ".$lon.")";
    $zoom += 12;
  	$out[0] = $zoom;
  	$out[1] = floor((($lon + 180) / 360) * pow(2, $zoom));
  	$out[2] = floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom));
    return $out;
  }
  
  function getTileURL($zoom, $xtile, $ytile, $tmsType) 
  {
  	if ( $tmsType == "TN" )
  		return "http://webapps.comune.trento.it/gis/ogc/mapcache/tms/1.0.0/ortofoto_2015@comune/${zoom}/${xtile}/${ytile}.jpeg";
  	else
  		return "http://b.tile.openstreetmap.org/${zoom}/${xtile}/${ytile}.png";
  }
?>
