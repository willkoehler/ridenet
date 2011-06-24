<?
require("../script/app-master.php");
require("../script/email-notifications.php");
require(SHAREDBASE_DIR . "BufferHelpers.php");
$oDB = oOpenDBConnection();

$riderID = SmartGetInt('RiderID');
$teamID = SmartGetInt('TeamID');
$teamInfo = GetRiderTeamInfo($oDB, $riderID);
$existingRiders = $oDB->DBCount("rider", "RiderEmail=" . SmartGetString('RiderEmail'));

if(!CheckSession())
{
    $result['success'] = false;
    $result['message'] = "You are not logged in";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
// Make sure logged-in rider is authorized to modify riders on the team
else if(!isSystemAdmin() && !isTeamAdmin($oDB, $teamID))
{
    $result['success'] = false;
    $result['message'] = "You do not have rights to modify this rider";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
// Make sure rider being modified is a member of the team
else if($riderID!=-1 && $teamInfo['CommutingTeamID']!=$teamID && $teamInfo['RacingTeamID']!=$teamID)
{
    $result['success'] = false;
    $result['message'] = "You do not have rights to modify this rider";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
// Do not allow rider to use email of an existing rider
else if($existingRiders > 0 && strtolower(SmartGet('RiderEmail'))!=strtolower($oDB->DBLookup("RiderEmail", "rider", "RiderID=$riderID")))
{
    $result['success'] = false;
    $result['message'] = "There is already a RideNet member with this email address. You can ask this rider to join your team ".
                          "by sending them an email: " . SmartGet('RiderEmail');
    $result['errors'][] = array('id' => 'RiderEmail', 'msg' => 'There is already a RideNet rider with this email address' );
}
else
{
    $values['RiderEmail'] = SmartGetString('RiderEmail');
    $values['LastName'] = SmartGetString('LastName');
    $values['FirstName'] = SmartGetString('FirstName');
    $teamAdmin = SmartGetCheckbox('sTeamAdmin');
    if($riderID==-1)
    {
        $values['Archived'] = 0;
        $values['MapPrivacy'] = 1;
        // only store teamID when rider is first created
        $values['RacingTeamID'] = $teamID;
        $values['CommutingTeamID'] = $teamID;
        // set team admin flags
        $values['sCommutingTeamAdmin'] = $teamAdmin;
        $values['sRacingTeamAdmin'] = $teamAdmin;
        // set MustChangePW password for new users
        $values['MustChangePW'] = 1;
        // date created / who created
        $values['CreatedByID'] = GetUserID();
        $values['DateCreated'] =  "'" . date("Y-m-d") . "'";
    }
    else
    {
        // update team admin flags
        $teamInfo = GetRiderTeamInfo($oDB, $riderID);
        if($teamID == $teamInfo['CommutingTeamID'])
        {
            $values['sCommutingTeamAdmin'] = $teamAdmin;
        }
        if($teamID == $teamInfo['RacingTeamID'])
        {
            $values['sRacingTeamAdmin'] = $teamAdmin;
        }
    }
    // only update password if user has changed it, otherwise leave password as is
    if(SmartGet('PwUnencrypted') != "********")
    {
        $values['Password'] = chr(34) . MakePasswordHash(SmartGet('PwUnencrypted')) . chr(34);
    }
    $result = InsertOrUpdateRecord2($oDB, "rider", "RiderID", $riderID, $values);
}

// Encode response, send to the browser, and close the connection.
FlushAndClose(json_encode($result));

// Send email notification to new users silently after page connection is closed
if($result['success'] && $riderID==-1)
{
    AccountCreatedEmail($oDB, $result['RiderID'], GetUserID());
}
?>
