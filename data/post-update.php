<?
require("../script/app-master.php");
require("../script/email-notifications.php");
require(SHAREDBASE_DIR . "BufferHelpers.php");
$oDB = oOpenDBConnection();

if(!CheckSession())
{
    $result['success'] = false;
    $result['message'] = "You are not logged in";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
else
{
// --- save the message
    $values['RiderID'] = GetUserID();
    $values['TeamID'] = $oDB->DBLookup("RacingTeamID", "rider", "RiderID=" . GetUserID());
    $values['Date'] =  "'" . date("Y-m-d H:i:s") . "'";
    $values['PostType'] = SmartGetInt("PostType");
    $values['Text'] = SmartGetString("Message");
    $values['PostedToID'] = SmartGetInt("PostedToID");
    $result = InsertOrUpdateRecord2($oDB, "posts", "PostID", -1, $values);
}

// Encode response, send to the browser, and close the connection.
FlushAndClose(json_encode($result));

// Send email notifications silently after page connection is closed
if($result['success'])
{
    switch($values['PostType']) {
        case 1:
            CalendarUpdateEmail($oDB, $result['PostID']);
            break;
        case 2:
            EventUpdateEmail($oDB, $result['PostID']);
            break;
    }
}
?>
