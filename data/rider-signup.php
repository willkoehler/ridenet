<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
define("SHAREDBASE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/Shared/");
require(SHAREDBASE_DIR . "DBConnection.php");
require(SHAREDBASE_DIR . "SendMail.php");
require("../script/data-helpers.php");
$oDB = oOpenDBConnection();
$riderName = $_REQUEST['RiderName'];
$riderEmail = $_REQUEST['RiderEmail'];
$riderDescription = $_REQUEST['RiderDescription'];
$teamName = $_REQUEST['TeamName'];

$msg = "Signup Request:\n\n" .
       "NAME: $riderName\n" .
       "EMAIL: $riderEmail\n" .
       "DESCRIPTION: $riderDescription\n\n" .
       "TEAM: $teamName";

if(SendMail("signup@ridenet.net", "RideNet Signup Request!", $msg, "info@ridenet.net"))
{
    $result['success'] = true;
}
else
{
    $result['success'] = false;
    $result['message'] = "Failed to send signup email";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}

// --- Encode response and send back to form
Echo json_encode($result);
?>
