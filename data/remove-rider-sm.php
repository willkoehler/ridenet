<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();
$riderID = SmartGetInt('RiderID');
$teamID = SmartGetInt('TeamID');
$teamInfo = GetRiderTeamInfo($oDB, $riderID);

if(!CheckSession())
{
    $result['success'] = false;
    $result['message'] = "You are not logged in";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
// Make sure logged-in rider is authorized to modify riders on the presented team
elseif(!isSystemAdmin() && !isTeamAdmin($oDB, $teamID))
{
    $result['success'] = false;
    $result['message'] = "You do not have rights to modify this rider";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
// Make sure rider being removed is a member of the presented team
else if($teamInfo['CommutingTeamID']!=$teamID && $teamInfo['RacingTeamID']!=$teamID)
{
    $result['success'] = false;
    $result['message'] = "You do not have rights to modify this rider";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
else
{
    $newRacingTeamID = 3;         // move rider to sandbox team
    $newCommutingTeamID = 3;      // move rider to sandbox team
    if($teamInfo['CommutingTeamID']!=$teamInfo['RacingTeamID'])
    {
        // Rider has two teams, move them to the team that is not removing them
        if($teamInfo['CommutingTeamID']==$teamID)
        {
            $newRacingTeamID = $teamInfo['RacingTeamID'];
            $newCommutingTeamID = $teamInfo['RacingTeamID'];
        }
        if($teamInfo['RacingTeamID']==$teamID)
        {
            $newRacingTeamID = $teamInfo['CommutingTeamID'];
            $newCommutingTeamID = $teamInfo['CommutingTeamID'];
        }
    }
    $result = ChangeRiderTeam($oDB, $riderID, $newRacingTeamID, $newCommutingTeamID);
    $oDB->RecordActivityIfOK("Remove Rider", $riderID);
}

// --- Encode response and send back to form
Echo json_encode($result);

?>
