<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();
$raceID = SmartGetInt('RaceID');

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
    $values['EventName'] = SmartGetString('EventName');
    $values['WebPage'] = SmartGetString('WebPage');
    $values['City'] = SmartGetString('City');
    $values['StateID'] = SmartGetInt('StateID');
    $values['RideTypeID'] = SmartGetInt('RideTypeID');
    $values['RaceDate'] = SmartGetDate('RaceDate');
    $values['Archived'] = 0;
    if($raceID==-1)
    {
        // set AddedBy and DateAdded if we are creating a new event
        $values['AddedBy'] = GetUserID();
        $values['DateAdded'] = "'" . date("Y-m-d") . "'";
    }
    // --- remove "http://" from web address if it is present (we add this ourselves when rendering the race schedule)
    $values['WebPage'] = str_replace("http://", "", $values['WebPage']);
    $values['WebPage'] = str_replace("https://", "", $values['WebPage']);

    $result = InsertOrUpdateRecord2($oDB, "event", "RaceID", $raceID, $values);
}

// --- Encode response and send back to form
Echo json_encode($result);
?>
