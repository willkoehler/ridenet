<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");

$oDB = oOpenDBConnection();
$riderEmail = SmartGetString('email');

$rs = $oDB->query("SELECT RiderID, Password FROM rider WHERE RiderEmail=$riderEmail", __FILE__, __LINE__);
if(($record = $rs->fetch_array())==false)
{
    header("HTTP/1.1 404 Not Found");
    $result['error'] = "Rider not found";
}
else
{
    $result['RiderID'] = $record['RiderID'];
    $result['Salt'] = GetPasswordSalt($record['Password']);
}

echo json_encode($result);
?>
