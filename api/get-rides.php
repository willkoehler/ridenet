<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");

$oDB = oOpenDBConnection();
$riderID = SmartGetInt('rider-id');
$uid = SmartGetInt('id');
$pw = SmartGetString('pw');
$start = SmartGetInt('start');
$limit = SmartGetInt('limit');

if(!isset($_REQUEST['rider-id']) || !isset($_REQUEST['id']) || !isset($_REQUEST['pw']) ||
   !isset($_REQUEST['start']) || !isset($_REQUEST['limit']))
{
    header("HTTP/1.1 400 Bad Request");
    $result['error'] = "Missing parameters";
}
elseif($oDB->DBCount("rider", "RiderID=$uid AND Password=$pw") == 0)
{
    header("HTTP/1.1 403 Forbidden");
    $result['error'] = "Login credentials are not valid";
}
elseif($riderID!=$uid)
{
    header("HTTP/1.1 403 Forbidden");
    $result['error'] = "Not authorized to view ride log for this rider";
}
else
{
    $rs = $oDB->query("SELECT RideLogID, Date, RideLogTypeID, Distance, Duration, WeatherID, Comment, Link, DateCreated
                       FROM ride_log
                       WHERE RiderID=$riderID
                       ORDER BY Date DESC
                       LIMIT $start, $limit", __FILE__, __LINE__);
    $result= array();
    while($row = $rs->fetch_object())
    {
    	  $result[] = $row;
    }

}

echo json_encode($result);
?>
