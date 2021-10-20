<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();
$teamID = SmartGetInt("ID");

if(!CheckSession())
{
    $result['success'] = false;
    $result['message'] = "You are not logged in";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
elseif(!isSystemAdmin())
{
    $result['success'] = false;
    $result['message'] = "Unauthorized access";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
else
{
    $oDB->query("UPDATE teams SET Archived=1 WHERE TeamID=$teamID");
    $oDB->RecordActivityIfOK("Archive [team]", $teamID);
    if($oDB->errno!=0)
    {
        $result['success'] = false;
        $result['message'] = "[" . $oDB->errno . "] SQL Error";// . $oDB->error;
    // --- needed so Ext returns failureType of 'server' (FYI: could also be used to do server-side field validation)
        $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );
    }
    else
    {
        $result['success'] = true;
    }
}

// --- Encode response and send back to form
Echo json_encode($result);
?>
