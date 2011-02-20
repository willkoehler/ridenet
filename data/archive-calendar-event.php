<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();
$calendarID = SmartGetInt("ID");

$eventInPast = ($oDB->DBCount("calendar", "CalendarID=$calendarID AND CalendarDate <= NOW()") > 0);
$addedBy = $oDB->DBLookup("AddedBy", "calendar", "CalendarID=$calendarID");

if(!CheckSession())
{
    $result['success'] = false;
    $result['message'] = "You are not logged in";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
elseif($eventInPast || ($addedBy!=GetUserID() && !isSystemAdmin()))
{
    $result['success'] = false;
    $result['message'] = "Unauthorized access";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
else
{
    $oDB->query("UPDATE calendar SET Archived=1 WHERE CalendarID=$calendarID", __FILE__, __LINE__);
    $oDB->RecordActivityIfOK("Archive [calendar]", $calendarID);
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
