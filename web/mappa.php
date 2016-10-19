<?php
require_once('../class2.php');
require_once(HEADERF);

$db = mysql_connect($mySQLserver, $mySQLuser, $mySQLpassword) or die(mysql_error());

mysql_select_db($mySQLdefaultdb,$db) or die(mysql_error());


/*
if (@$_GET['insertNew'] == 'yes')
{
 $acquireMax = "SELECT MAX(id) FROM e107_temperatures";
 $result = mysql_query($acquireMax) or die(mysql_error());
 $row = mysql_fetch_array($result);
 $inId = $row[0];

 $inTMin = @$_GET['inTMin'];
 $inTMax = @$_GET['inTMax'];
 $inCrono = @$_GET['inCrono'];
 $inTopo = @$_GET['inTopo'];
 $inNote = @$_GET['inNote'];
 $insertStmt = "INSERT INTO e107_temperatures (id, tmin, tmax, crono, topo, note) "
             . "VALUES (1+$inId, $inTMin, $inTMax, '$inCrono ', '$inTopo ', '$inNote ')";
 $result = mysql_query($insertStmt) or die(mysql_error());
}

$inputForm =
"<form action=\"".$_SERVER['PHP_SELF']."\">
  <input type=\"hidden\" name=\"insertNew\" value=\"yes\" />
  crono  = <input type=\"text\" name=\"inCrono\" size=\"25\" value=\"$inCrono\" /><br>
  topo  = <input type=\"text\" name=\"inTopo\" size=\"25\" value=\"$inTopo\" /><br>
  tmin  = <input type=\"text\" name=\"inTMin\" size=\"10\" value=\"$inTMin\" /><br>
  tmax  = <input type=\"text\" name=\"inTMax\" size=\"10\" value=\"$inTMax\" /><br>
  note  = <input type=\"text\" name=\"inNote\" size=\"50\" value=\"$inNote\" /><br>
  <input type=\"submit\" value=\"aggiungi dato\"/>
</form>";
*/

$select_a = "SELECT id, label, ordinale FROM ".MPREFIX."maplocations_annata ORDER BY ordinale";
$sql_a = mysql_query($select_a) or die(mysql_error());

while($row_a = mysql_fetch_array($sql_a))
{
  $a_id[]   = $row_a[0];
  $a_label[] = $row_a[1];
  // per memorizzare l'annata corrente, ovvero l'ultima
  $a_max   = $row_a[0];
}

if (isset($_POST['inAnnata']))
{
 $selectedAnnataId = @$_POST['inAnnata'];
}
else
{
 $selectedAnnataId = $a_max;   // stagione corrente (l'ultima, sarebbe)
}

$select = "SELECT id, lat, lon, descr, marker, label, casa FROM ".MPREFIX."maplocations WHERE active=1 AND annata_id = ".$selectedAnnataId." ORDER BY id";
$result = @mysql_query($select);
$pointTextSnippet = "\nvar point; var marker;\n";
while($row = mysql_fetch_array($result))
{
//  $pointTextSnippet .= "point = new GLatLng(" . $row['lat'] . "," . $row['lon'] . ");\n";
//  $pointTextSnippet .= "map.addOverlay(new GMarker(point));\n\n";

//  $pointTextSnippet .= "point = new GLatLng(" . $row['lat'] . "," . $row['lon'] . ");\n";
//  $pointTextSnippet .= "map.addOverlay(createMarker(point, '" . $row['descr'] . "'));\n\n";
  
  
//   var point = new GLatLng(43.65654,-79.90138);
//   var marker = createMarker(point,"This place","Some stuff to display in the<br>First Info Window")
//   map.addOverlay(marker);
  $pointTextSnippet .= "point = new GLatLng(" . $row['lat'] . "," . $row['lon'] . ");\n";
  $pointTextSnippet .= "marker = createMarker(point, '" . $row['label'] . "', '" . $row['descr'] . "', " . $row['id'] . ", " . $row['casa'] . ");\n";
  $pointTextSnippet .= "map.addOverlay(marker);\n\n";
    
}
mysql_close($link);

$inputFormAnnata =
"<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">
  <input type=\"hidden\" name=\"selectAnnata\" value=\"yes\" />
Scegli la stagione agonistica: <select name=\"inAnnata\" onChange=\"this.form.submit();\">";

for ($i=0; $i<count($a_id); $i++)
{
  $inputFormAnnata .= "<OPTION VALUE=\"" . $a_id[$i] . "\" " . (($selectedAnnataId==$a_id[$i])?"SELECTED":"") . "> " . $a_label[$i] . " </OPTION>";
}

$inputFormAnnata .=
"               </select>
</form>";

$text = "
<div  style=\"text-align:center\">
<table style=\"width:99%; border:0;\">" .

$inputFormAnnata .

"<tr><td>

<script src=\"http://maps.google.com/maps?file=api&v=2&key=ABQIAAAAm9n4G6baH8kcPaljkkBuBxQo66_12ATYlJr4f9ReSyL5eRGsvhTPUA-zauDGX51fC8IvwvwzbSc-xg\" type=\"text/javascript\"></script>
<div  style=\"text-align:center\">

<!--
<div id=\"map\" style=\"width: 600px; height: 500px\"></div>
-->

    <table border=0>
      <tr>
        <td>
           <div id=\"map\" style=\"width: 600px; height: 500px\"></div>
        </td>
        <td width = 150 valign=\"top\" style=\"text-decoration: underline; color: #4444ff;\">
           <div id=\"side_bar\"></div>
        </td>
      </tr>
    </table>



<script type=\"text/javascript\">
//<![CDATA[

var map = new GMap2(document.getElementById(\"map\"));
//  map.addControl(new GSmallMapControl());
map.addControl(new GLargeMapControl());
map.addControl(new GMapTypeControl());
// map.centerAndZoom(new GPoint(-0.14110, 51.512161), 6);
// map.setCenter(new GLatLng(37.4419, -122.1419), 13);
// map.setCenter(new GLatLng(46.1040, 11.1138), 9);
map.setCenter(new GLatLng(46.118941, 11.11122), 12);

// Create our 'tiny' marker icon
var tinyIcon = new GIcon();
tinyIcon.image = \"http://labs.google.com/ridefinder/images/mm_20_red.png\";
tinyIcon.shadow = \"http://labs.google.com/ridefinder/images/mm_20_shadow.png\";
tinyIcon.iconSize = new GSize(12, 20);
tinyIcon.shadowSize = new GSize(22, 20);
tinyIcon.iconAnchor = new GPoint(6, 20);
tinyIcon.infoWindowAnchor = new GPoint(5, 1);

// Create our 'tiny' marker icon
var griffIcon = new GIcon();
griffIcon.image = \"http://maps.gstatic.com/mapfiles/ms2/micons/homegardenbusiness.png\";
griffIcon.shadow = \"http://maps.gstatic.com/mapfiles/ms2/micons/homegardenbusiness.shadow.png\";
griffIcon.iconSize = new GSize(32, 32);
griffIcon.shadowSize = new GSize(59, 32);
griffIcon.iconAnchor = new GPoint(6, 20);
griffIcon.infoWindowAnchor = new GPoint(5, 1);
 
"
.

(ADMIN ? "
GEvent.addListener(map, \"click\", function(marker, point) {
//  if (marker) {
//    map.removeOverlay(marker);
//  } else {
   document.getElementById(\"mapMessage\").innerHTML = point.toString();
//  }
});
" : "")

. "
      // this variable will collect the html which will eventually be placed in the side_bar
      var side_bar_html = \"\";
    
      // arrays to hold copies of the markers and html used by the side_bar
      // because the function closure trick doesnt work there
      var gmarkers = [];


      // A function to create the marker and set up the event window
      function createMarker(point, name, html, id, casa) {
      
        var marker;
        if (casa == 1)
        {
          marker = new GMarker(point, {icon:griffIcon});
        }
        else
        {
          marker = new GMarker(point, {icon:tinyIcon});
        }
          
        GEvent.addListener(marker, \"click\", function() {
          marker.openInfoWindowHtml(html);
        });
        // save the info we need to use later for the side_bar
        gmarkers.push(marker);
        // add a line to the side_bar html
        if (casa == 1)
        {
        	side_bar_html += '<br><a href=\"javascript:myclick(' + (gmarkers.length-1) + ')\"><b>' + name + '<\/b><\/a><br>';
        }
        else
        {
        	side_bar_html += '<br><a href=\"javascript:myclick(' + (gmarkers.length-1) + ')\">' + name + '<\/a><br>';
        }
        return marker;
      }

      // This function picks up the click and opens the corresponding info window
      function myclick(i) {
        GEvent.trigger(gmarkers[i], \"click\");
      }

      // add the points    
      $pointTextSnippet   
      
      // put the assembled side_bar_html contents into the side_bar div
	  document.getElementById(\"side_bar\").innerHTML = side_bar_html;


// // Creates a marker at the given point with the given label
// function createMarker(point, label) {
//  var marker = new GMarker(point);
//  GEvent.addListener(marker, \"click\", function() {
//    marker.openInfoWindowHtml(label);
//  });
//  return marker;
//}
// 
// qui il vecchio \$pointTextSnippet

/*
GEvent.addListener(map, \"click\", function(marker, point) {
  if (marker) {
    map.removeOverlay(marker);
  } else {
    map.addOverlay(new GMarker(point));
  }
});
*/

// // Add a polyline with five random points. Sort the points by
// // longitude so that the line does not intersect itself.
// var points = [];
// for (var i = 0; i < 5; i++) {
//   points.push(new GLatLng(southWest.lat() + latSpan * Math.random(),
//                           southWest.lng() + lngSpan * Math.random()));
// }
// points.sort(function(p1, p2) {
//   return p1.lng() - p2.lng();
// });
// map.addOverlay(new GPolyline(points));



// // Creates a marker whose info window displays the given number
// function createMarker(point, number)
// {
// var marker = new GMarker(point);
// // Show this markers index in the info window when it is clicked
// var html = number;
// GEvent.addListener(marker, \"click\", function() {marker.openInfoWindowHtml(html);});
// return marker;
// };

    //]]>
    </script>

<!--
    Messaggio: <div id=\"mapMessage\"></div>
-->    
";

if(ADMIN)
{
  $text .=  "Messaggio: <div id=\"mapMessage\"></div>\n";
}


/*
if(ADMIN)
{
  $text .= $inputForm;
  $text .= $insertStmt;
}
*/

$text .= "</div>";

$text .= "</td></tr>

</table>

</div>
";



$ns->tablerender("Mappe palestre (et al.) - GMaps", $text);
require_once(FOOTERF);
?>
