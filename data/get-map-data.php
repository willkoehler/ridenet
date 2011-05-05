<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
define("SHAREDBASE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/Shared/");
require(SHAREDBASE_DIR . "DBConnection.php");
require(SHAREDBASE_DIR . "RequestHelpers.php");
require("../script/data-helpers.php");

$rideLogID = SmartGetInt('RideLogID');

// --- open data connection
$oDB = oOpenDBConnection();

// --- get map data
$sql = "SELECT Longitude, Latitude
        FROM ride_log_map
        WHERE RideLogID=$rideLogID
        ORDER BY DateTime ASC";
$rs = $oDB->query($sql, __FILE__, __LINE__);

$result = array();
while($row = $rs->fetch_object())
{
	  $result[] = array($row->Latitude/1e6, $row->Longitude/1e6);
}

echo json_encode($result);
?>