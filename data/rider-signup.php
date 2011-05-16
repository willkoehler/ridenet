<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");
require(SHAREDBASE_DIR . "SendMail.php");

$riderName = $_REQUEST['RiderName'];
$riderEmail = $_REQUEST['RiderEmail'];
$riderDescription = $_REQUEST['RiderDescription'];
$teamName = $_REQUEST['TeamName'];
$source = $_REQUEST['Source'];

$msg = "Signup Request:\n\n" .
       "NAME: $riderName\n" .
       "EMAIL: $riderEmail\n" .
       "SOURCE: $source\n" .
       "DESCRIPTION: $riderDescription\n\n" .
       "TEAM: $teamName\n\n";

if(SendMail("signup@ridenet.net", "RideNet Signup Request", $msg, "info@ridenet.net"))
{
    $result['success'] = true;
}
else
{
    $result['success'] = false;
    $result['message'] = "Failed to send signup email";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}

// --- Encode response and send back to browser. The small delay caused by sending the email
// --- is desireable in this case so we don't flush the response before sending the email
Echo json_encode($result);
?>
