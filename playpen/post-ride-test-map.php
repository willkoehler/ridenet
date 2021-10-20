<?
////////////////////////////////////////////////////////////////////////////////////////////////////////
// Gets map data in json format that can be posted back to the iPhone api. For testing the iPhone api
////////////////////////////////////////////////////////////////////////////////////////////////////////
require("../script/app-master-min.php");

// Reject requests that are missing required parameters (to handle bots scanning this page)
CheckRequiredParameters(Array('RideLogID'));

$rideLogID = SmartGetInt('RideLogID');

$oDB = oOpenDBConnection();
$sql = "SELECT DateTime, Longitude, Latitude, Altitude
        FROM ride_log_map
        WHERE RideLogID=$rideLogID AND Altitude>0";
$rs = $oDB->query($sql);

$result = array();
while($row = $rs->fetch_object())
{
	  $result[$row->DateTime] = array("lat" => $row->Latitude, "lon" => $row->Longitude, "alt" => $row->Altitude);
}

echo json_encode($result);
?>