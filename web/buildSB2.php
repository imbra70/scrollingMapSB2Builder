<?php
	// acquisisco le coordinate della mappa
	$mCeLa = @$_POST['mCeLa'];
	$mCeLo = @$_POST['mCeLo'];
	$mZoom = @$_POST['mZoom'];
	// e quelle del rettangolo
	$rLaLo = @$_POST['rLaLo'];
	$rLoLo = @$_POST['rLoLo'];
	$rLaHi = @$_POST['rLaHi'];
	$rLoHi = @$_POST['rLoHi'];
	$rZoom = @$_POST['rZoom'];
	// tipo mappa	
	$tmsType = @$_POST['tmsType'];
	// variabili "globali"
	$NUM_TILES_Y = @$_POST['NUM_TILES_Y'];
	$NUM_TILES_X = @$_POST['NUM_TILES_X'];
	
  include('utils.php');
 
	$tmpDir = createTmpDir();

  // print $tmpDir; // IMBRA TODO
  
  smartCopy(getcwd() . "/sb2projectTemplate" , $tmpDir);
  
  // sostituisce NUM_TILES_Y con il valore di $NUM_TILES_Y (cioè 10)
  replaceTagsInTemplate($tmpDir, $NUM_TILES_Y);

  // calcola gli indici X e Y della tile centrale
  list($ZOOM, $ARG_BASE_X, $ARG_BASE_Y) = getCentralTileCoordinates($rZoom, ($rLoLo+$rLoHi)/2.0, ($rLaLo+$rLaHi)/2.0, $tmsType);

//print "<a target=\"tile\" href=\"".getTileURL($ZOOM, $ARG_BASE_X, $ARG_BASE_Y, $tmsType)."\">$tmsType: $ZOOM - $ARG_BASE_X, $ARG_BASE_Y</a>";
	 
	$TILES_Y_SHIFT = floor($NUM_TILES_Y/2);
	$TILES_X_SHIFT = floor($NUM_TILES_X/2);
	
	$fhOut = fopen($tmpDir."/".'project.json','a');
	
	for ($i=1; $i<=$NUM_TILES_X; $i++)
	{
		for ($j=1; $j<=$NUM_TILES_Y; $j++)
		{
			$X = $ARG_BASE_X + $i - $TILES_X_SHIFT - 1;
			$Y = $ARG_BASE_Y + $j - $TILES_Y_SHIFT - 1;
			$file_num = 4 + ($i-1) + ($j-1)*$NUM_TILES_X;
			$file_name = $tmpDir."/".$file_num."."."png"; // ($tmsType=="TN")?"jpg":"png"; // imbra TODO: per ora rename a .png anche se sono .jpg
			copy(getTileURL($ZOOM, $X, $Y, $tmsType), $file_name);    
			// se serve la conversione:
			// mime_content_type()
			// imagepng(imagecreatefromstring(file_get_contents($filename)), "output.png");
			// http://stackoverflow.com/questions/8550015/convert-jpg-gif-image-to-png-in-php
			$checksum = md5_file($file_name);
			$indexUpDown = ($tmsType == "TN") ? $j : $NUM_TILES_Y - $j + 1;
			fwrite($fhOut,"    			{\n");
			fwrite($fhOut,"					\"costumeName\": \"".$i."_".$indexUpDown."\",\n");
			fwrite($fhOut,"					\"baseLayerID\": ".$file_num.",\n");
			fwrite($fhOut,"					\"baseLayerMD5\": \"".$checksum.".png\",\n");
			fwrite($fhOut,"					\"bitmapResolution\": 1,\n");
			fwrite($fhOut,"					\"rotationCenterX\": 128,\n");
			fwrite($fhOut,"					\"rotationCenterY\": 128\n");
			fwrite($fhOut,"				}");
			if (($i<$NUM_TILES_X)||($j<$NUM_TILES_Y))
				fwrite($fhOut,",\n");
			else
				fwrite($fhOut,"\n");
		}
	}
	
	appendTail($tmpDir, $fhOut);

	fclose($fhOut);
	
	$projectName = "scrollingMap.sb2";
	createProjectFromFolder($tmpDir, $projectName);
	
	header("Content-type: application/zip"); 
	header("Content-Disposition: attachment; filename=$projectName");
	header("Content-length: " . filesize($tmpDir."/".$projectName));
	header("Pragma: no-cache"); 
	header("Expires: 0"); 
	readfile($tmpDir."/".$projectName);

	//unlink($tmpDir."/".$projectName);
	//unlink($tmpDir);
?>
