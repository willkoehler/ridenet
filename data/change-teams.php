<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();
$newCommutingTeamID = SmartGetInt('CommutingTeamID');
$newRacingTeamID = SmartGetInt('RacingTeamID');

if(!CheckSession())
{
    $result['success'] = false;
    $result['message'] = "You are not logged in";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
else
{
    $result = ChangeRiderTeam($oDB, GetUserID(), $newRacingTeamID, $newCommutingTeamID);
    $oDB->RecordActivityIfOK("Change Teams. RT=$newRacingTeamID CT=$newCommutingTeamID", GetUserID());
}

// --- Encode response and send back to form
Echo json_encode($result);

?>
