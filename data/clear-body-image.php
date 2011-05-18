<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();

$teamID = SmartGetInt('TeamID');

if(!CheckSession())
{
    $result['success'] = false;
    $result['message'] = "You are not logged in";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
// Make sure user is authorized to modify this team
else if(!isSystemAdmin() && !isDesigner() && !isTeamAdmin($oDB, $teamID))
{
    $result['success'] = false;
    $result['message'] = "You do not have rights to modify this team site";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
else
{
    $values['TeamID'] = $teamID;
    $values['BodyBG'] = "NULL";
    $values['LastModified'] = "'" . date("Y-m-d H:i:s") . "'";
    $result = InsertOrUpdateRecord2($oDB, "team_images", "TeamID", $teamID, $values);
}
// --- Encode response and send back to form
Echo json_encode($result);
?>
