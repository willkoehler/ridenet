<?
require("../script/app-master.php");
require("../script/email-notifications.php");
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
    $values['Date'] =  "'" . date("Y-m-d H:i") . "'";
    $values['PostType'] = 1;
    $values['Text'] = SmartGetString("Message");
    $values['PostedToID'] = SmartGetInt("CalendarID");
    $result = InsertOrUpdateRecord2($oDB, "posts", "PostID", -1, $values);
    
    if($result['success'])
    {
        CalendarUpdateEmail($oDB, $result['PostID']);
    }
}

// --- Encode response and send back to form
Echo json_encode($result);
?>
