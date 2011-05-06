<?
require("script/app-master.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
  <title>Route Map</title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("/map.js")?>
<!-- google maps API -->
  <script type="text/javascript"src="http://maps.google.com/maps/api/js?sensor=false"></script>
<!-- pass ride log ID to javascript -->
  <script type="text/javascript">
    g_rideLogID = <?=SmartGetInt('RideLogID')?>;
  </script>
  

  <style type="text/css">
    html { height: 100% }
    body { height: 100%; margin: 0px; padding: 0px }
    #map_canvas { height: 100% }
  </style>
</head>


<body>
  <div id="map_canvas" style="width:100%; height:100%"></div>
</body>
</html>