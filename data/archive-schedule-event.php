<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();
$raceID = SmartGetInt("ID");

$eventInThePast = ($oDB->DBCount("event", "RaceID=$raceID AND RaceDate <= NOW()") > 0);
$createdOver7DaysAgo = ($oDB->DBCount("event", "RaceID=$raceID AND DateAdded <= ADDDATE(NOW(), -7)") > 0);
$hasResults = ($oDB->DBCount("results", "RaceID=$raceID") > 0);
$addedBy = $oDB->DBLookup("AddedBy", "event", "RaceID=$raceID");

if(!CheckSession())
{
    $result['success'] = false;
    $result['message'] = "You are not logged in";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
elseif($hasResults)
{
    $result['success'] = false;
    $result['message'] = "Results are already posted";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
elseif(($eventInThePast && $createdOver7DaysAgo))
{
    $result['success'] = false;
    $result['message'] = "Event is in the past";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
elseif(($raceID!=-1 && $addedBy!=GetUserID() && !isSystemAdmin()))
{
    $result['success'] = false;
    $result['message'] = "Unauthorized access";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
else
{
    $oDB->query("UPDATE event SET Archived=1 WHERE RaceID=$raceID", __FILE__, __LINE__);
    $oDB->RecordActivityIfOK("Archive [event]", $raceID);
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
