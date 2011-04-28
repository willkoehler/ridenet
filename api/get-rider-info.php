<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
define("SHAREDBASE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/Shared/");
require(SHAREDBASE_DIR . "DBConnection.php");
require(SHAREDBASE_DIR . "RequestHelpers.php");
require(dirname(__FILE__) . "/../script/data-helpers.php");

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
