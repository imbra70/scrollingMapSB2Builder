<?php

// inizializzazione coordinate default della mappa (sovrascritte in 'buildSB2.php')
$mCeLa = 46.07087;
$mCeLo = 11.12108;
$mZoom = 16;

// e del rettangolo (sovrascritte in 'buildSB2.php')
$rLaLo = 46.06998;
$rLoLo = 11.11995;
//$rLaHi = 46.07303551769332;
//$rLoHi = 11.12126438675958;
$rZoom = 7;

// altre variabili globali
$NUM_TILES_Y = 10;
$NUM_TILES_X = 3;

// $submitted = false;
// if (isset($_POST['rLoLo']))
// {
//  include('buildSB2.php');
// }
	
$pageTextTop = "
<!DOCTYPE html>
<html>
<head>
	
	<title>Generatore di progetti Scratch - Festivolare (OSM, Leaflet, Comune TN)</title>

	<meta charset=\"utf-8\" />
	<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
	
	<link rel=\"shortcut icon\" type=\"image/x-icon\" href=\"docs/images/favicon.ico\" />

  <link rel=\"stylesheet\" href=\"./leaflet/leaflet.css\" />
  <script src=\"./leaflet/leaflet.js\"></script>
<!--  
  <link rel=\"stylesheet\" href=\"./leaflet.draw/leaflet.draw.css\" />
  <script src=\"./leaflet.draw/leaflet.draw.js\"></script>
-->	
</head>
<body>

<div id=\"mapid\" style=\"width: 600px; height: 450px;\"></div>
<script>

	var mymap = L.map('mapid').setView([".$mCeLa.", ".$mCeLo."], ".$mZoom.");

//	L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpandmbXliNDBjZWd2M2x6bDk3c2ZtOTkifQ._QA7i5Mpkd_m30IGElHziw', {
//		maxZoom: 19,
//		minZoom: 12,
//		attribution: 'Map data &copy; <a href=\"http://openstreetmap.org\">OpenStreetMap</a> contributors, ' +
//			'<a href=\"http://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>, ' +
//			'Imagery © <a href=\"http://mapbox.com\">Mapbox</a>',
//		id: 'mapbox.streets'
//	}).addTo(mymap);
	
	mapLink = 
			'<a href=\"http://www.esri.com/\">Esri</a>';
	wholink = 
			'other and the GIS User Community';
	L.tileLayer(
			'http://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
			attribution: '&copy; '+mapLink+', '+wholink,
			maxZoom: 18,
			minZoom: 12,
			}).addTo(mymap);
        
	var imageUrl = './maschera_ortofoto_TN.png',
			imageBounds = [[45.98, 10.99], [46.16, 11.245]];
	var layerMaschera = L.imageOverlay(imageUrl, imageBounds).addTo(mymap);	
	layerMaschera.setOpacity(0.7);
	
	function onMapClick(e) {
		mymap.panTo(e.latlng);
		rectLatitLow = e.latlng.lat - (".$NUM_TILES_Y." * refTileLatitSize * Math.pow(2, 8 - rectZoomLevel))/2;
		rectLongiLow = e.latlng.lng - (".$NUM_TILES_X." * refTileLongiSize * Math.pow(2, 8 - rectZoomLevel))/2;
		updateRect();
	}
	mymap.on('click', onMapClick);

	// variabili per individuare l'area selezionata
	var deltaLatit = 46.134134 - 46.017566; // a livello 8: 275	1038
	var deltaLongi = 11.171309 - 11.045566; // a livello 8: 111	685
	var refTileLatitSize = deltaLatit / (1038 - 275); // dimensione delle tiles calcolata a livello 8
	var refTileLongiSize = deltaLongi / (685 - 111);  // dimensione delle tiles calcolata a livello 8
	
	// Palazzina Liberty, tile di riferimento
	// 46.07083, 11.12112
	// 8/458/624
	// 6 114 156
	
	var rectZoomLevel = ".$rZoom.";
	var rectLatitLow = ".$rLaLo.";
	var rectLatitHig = rectLatitLow + ".$NUM_TILES_Y." * refTileLatitSize * Math.pow(2, 8 - rectZoomLevel);
	var rectLongiLow = ".$rLoLo.";
	var rectLongiHig = rectLongiLow + ".$NUM_TILES_X." * refTileLongiSize * Math.pow(2, 8 - rectZoomLevel);
	
	// define rectangle geographical bounds
	var bounds = [[rectLatitLow, rectLongiLow], [rectLatitHig, rectLongiHig]];
	// create an orange rectangle
	var rect = L.rectangle(bounds, {color: \"#ff7800\", weight: 1}).addTo(mymap);
	
// mappa         rettangolo
// maxZoom: 18
//          17      8
//          16      7
//          15      6
//          14      5
//          13      4
// minZoom: 12,

	mymap.on('zoomend', function(e) {
			rectZoomLevel = mymap.getZoom() - 9;
			if (rectZoomLevel>8) rectZoomLevel=8; 
			if (rectZoomLevel<4) rectZoomLevel=4; 
			updateRect();
	});	
	
	// gestione spostamento rettangolo
	function updateRect() {
		rectLatitHig = rectLatitLow + ".$NUM_TILES_Y." * refTileLatitSize * Math.pow(2, 8 - rectZoomLevel);
		rectLongiHig = rectLongiLow + ".$NUM_TILES_X." * refTileLongiSize * Math.pow(2, 8 - rectZoomLevel);
		bounds = [[rectLatitLow, rectLongiLow], [rectLatitHig, rectLongiHig]];
		rect.setBounds(bounds);
	}
	
	function selectionOK() {
	  var fo = document.forms['selectionData'];
	  // dati della mappa
		selectionData.elements[\"mCeLa\"].value = mymap.getCenter().lat;
		selectionData.elements[\"mCeLo\"].value = mymap.getCenter().lng;
		selectionData.elements[\"mZoom\"].value = mymap.getZoom();
	  // dati del rettangolo
		selectionData.elements[\"rLaLo\"].value = rectLatitLow;
		selectionData.elements[\"rLoLo\"].value = rectLongiLow;
		selectionData.elements[\"rLaHi\"].value = rectLatitHig;
		selectionData.elements[\"rLoHi\"].value = rectLongiHig;
		selectionData.elements[\"rZoom\"].value = rectZoomLevel;
		// submit
		selectionData.submit();
	}
	
</script>

<!-- <form id=\"selectionData\" action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\"> -->
<form id=\"selectionData\" action=\"./buildSB2.php\" method=\"POST\">
  <input type=\"hidden\" name=\"NUM_TILES_Y\" value=\"".$NUM_TILES_Y."\" />
  <input type=\"hidden\" name=\"NUM_TILES_X\" value=\"".$NUM_TILES_X."\" />

  <input type=\"hidden\" name=\"mCeLa\" value=\"undef\" />
  <input type=\"hidden\" name=\"mCeLo\" value=\"undef\" />
  <input type=\"hidden\" name=\"mZoom\" value=\"undef\" />

  <input type=\"hidden\" name=\"rLaLo\" value=\"undef\" />
  <input type=\"hidden\" name=\"rLoLo\" value=\"undef\" />
  <input type=\"hidden\" name=\"rLaHi\" value=\"undef\" />
  <input type=\"hidden\" name=\"rLoHi\" value=\"undef\" />
  <input type=\"hidden\" name=\"rZoom\" value=\"undef\" />
  
  <input type=\"radio\" name=\"tmsType\" value=\"TN\" checked> Ortofoto TN<br>
  <input type=\"radio\" name=\"tmsType\" value=\"OSM\"> OpenStreetMap<br>
</form>

<button onclick=\"selectionOK();\">Genera la mappa!</button>";

$pageTextBottom = "
</body>
</html>";

// $pageTextMiddle = "
// sottomesso!";

print $pageTextTop;
// if ($submitted) print $pageTextMiddle;
print $pageTextBottom;
?>
