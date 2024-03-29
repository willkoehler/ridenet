<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");

$oDB = oOpenDBConnection();
$uid = SmartGetInt('id');
$pw = SmartGetString('pw');
$rideLogID = SmartGetInt('ride-log-id');

if(!isset($_REQUEST['ride-log-id']) || !isset($_REQUEST['id']) || !isset($_REQUEST['pw']))
{
    header("HTTP/1.1 400 Bad Request");
    $result['error'] = "Missing parameters";
}
elseif($oDB->DBCount("rider", "RiderID=$uid AND Password=$pw") == 0)
{
    header("HTTP/1.1 403 Forbidden");
    $result['error'] = "Login credentials are not valid";
}
elseif($rideLogID!=-1 && $oDB->DBLookup("RiderID", "ride_log", "RideLogID=$rideLogID")!=$uid)
{
    header("HTTP/1.1 403 Forbidden");
    $result['error'] = "Not authorized to delete this ride log entry";
}
else
{
    // delete the ride log entry
    $oDB->query("DELETE FROM ride_log WHERE RideLogID=$rideLogID");
    $oDB->query("DELETE FROM ride_log_map WHERE RideLogID=$rideLogID");
    $oDB->RecordActivityIfOK("Delete [ride_log] ID=$rideLogID", $uid);
    UpdateRiderStats($oDB, $uid);   // update rider stats
    $result=null;
}

if($result)
{
    echo json_encode($result);
}
?>
