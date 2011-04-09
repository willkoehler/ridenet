<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();

$teamID = SmartGetInt('TeamID');
$domainCheck=$oDB->DBLookup("TeamName", "teams", "TeamID<>$teamID AND Domain=" . SmartGetString('Domain'));

if(!CheckSession())
{
    $result['success'] = false;
    $result['message'] = "You are not logged in";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
// Make sure user is authorized to modify team accounts
else if(!isSystemAdmin())
{
    $result['success'] = false;
    $result['message'] = "You do not have rights to modify team accounts";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
else if($domainCheck!="")
{
    $result['success'] = false;
    $result['message'] = "The chosen domain name is taken by \"$domainCheck\"";
    $result['errors'][] = array('id' => 'Domain', 'msg' => 'This domain name is already taken' );     // server-side error message
}
else
{
    $values['TeamName'] = SmartGetString("TeamName");
    $values['Domain'] = SmartGetString("Domain");
    $values['Archived'] = SmartGetInt("Archived");
    $values['bRacing'] = SmartGetCheckbox("bRacing");
    $values['bCommuting'] = SmartGetCheckbox("bCommuting");
    $values['SiteLevelID'] = SmartGetInt("SiteLevelID");
    $values['TeamTypeID'] = SmartGetInt("TeamTypeID");
    $values['ZipCodeID'] = SmartGetInt("ZipCodeID");
    $result = InsertOrUpdateRecord2($oDB, "teams", "TeamID", $teamID, $values);
}
// --- Encode response and send back to form
Echo json_encode($result);
?>
