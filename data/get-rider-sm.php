<?
require("../script/app-master.php");

$oDB = oOpenDBConnection();
// store query/post values in local variables
$riderID = SmartGetInt('RiderID');      // Rider ID
$teamID = SmartGetInt('TeamID');
$teamInfo = GetRiderTeamInfo($oDB, $riderID);

if(!CheckSession())
{
    $result['results'][] = array();   // (dummy results array is required)
    $result['success'] = false;  
}
// Make sure logged-in rider is authorized to view riders on the team
else if(!isSystemAdmin() && !isTeamAdmin($oDB, $teamID))
{
    $result['results'][] = array();   // (dummy results array is required)
    $result['success'] = false;  
}
// Make sure requested rider is a member of the team
else if($teamInfo['CommutingTeamID']!=$teamID && $teamInfo['RacingTeamID']!=$teamID)
{
    $result['results'][] = array();   // (dummy results array is required)
    $result['success'] = false;  
}
else
{   // (photo is always based on racing team ID)
    $rs = $oDB->query("SELECT RiderID, FirstName, LastName, RiderEmail, '********' AS PwUnencrypted,
                       (IF(CommutingTeamID=$teamID, sCommutingTeamAdmin, 0) OR IF(RacingTeamID=$teamID, sRacingTeamAdmin, 0)) AS sTeamAdmin
                       FROM rider
                       WHERE RiderID=$riderID");
    $result['results'] = $rs->fetch_object();
    $result['success'] = true;
}

// --- Dump output.
Echo json_encode($result);
?>
