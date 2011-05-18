<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");

// Reject requests that are missing required parameters (to handle bots scanning this page)
CheckRequiredParameters(Array('RideLogID'));

$rideLogID = SmartGetInt('RideLogID');

$oDB = oOpenDBConnection();
$sql = "SELECT Longitude, Latitude
        FROM ride_log_map
        WHERE RideLogID=$rideLogID AND Altitude>0
        ORDER BY DateTime ASC";
$rs = $oDB->query($sql, __FILE__, __LINE__);

$result = array();
while($row = $rs->fetch_object())
{
	  $result[] = array($row->Latitude/1e6, $row->Longitude/1e6);
}

echo json_encode($result);
?>