<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();
$calendarID = SmartGetInt('CalendarID');

$addedBy = $oDB->DBLookup("AddedBy", "calendar", "CalendarID=$calendarID");

if(!CheckSession())
{
    $result['success'] = false;
    $result['message'] = "You are not logged in";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
elseif($calendarID!=-1 && $addedBy!=GetUserID() && !isSystemAdmin())
{
    $result['success'] = false;
    $result['message'] = "Unauthorized access";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
else
{
    $values['CalendarDate'] = "'" . date_create($_REQUEST['CalendarDate'] . ' ' . $_REQUEST['CalendarTime'])->format("Y-m-d H:i") . "'";
    $values['EventName'] = SmartGetString('EventName');
    $values['Location'] = SmartGetString('Location');
    $values['Comments'] = SmartGetString('Comments');
    $values['ZipCodeID'] = SmartGetInt('ZipCodeID');
    $values['MapURL'] = SmartGetString('MapURL');
    $values['ClassX'] = SmartGetCheckBox('ClassX');
    $values['ClassA'] = SmartGetCheckBox('ClassA');
    $values['ClassB'] = SmartGetCheckBox('ClassB');
    $values['ClassC'] = SmartGetCheckBox('ClassC');
    $values['ClassD'] = SmartGetCheckBox('ClassD');
    $values['Archived'] = 0;
    if($calendarID==-1)
    {
        // Set PostedBy and TeamID if we are creating a new event
        // Figure out which team to post this ride under
        $pt = GetPresentedTeamID($oDB);
        $teamInfo = GetRiderTeamInfo($oDB, GetUserID());
        if($pt==$teamInfo['RacingTeamID'] || $pt==$teamInfo['CommutingTeamID'])
        {
            // user is on their own team site, post ride under the presented team
            $postingTeamID = $pt;
        }
        else
        {
            // user is on someone else's team site, post ride under user's racing team
            $postingTeamID = $teamInfo['RacingTeamID'];
        }
        $values['AddedBy'] = GetUserID();
        $values['TeamID'] = $postingTeamID;
    }

    $result = InsertOrUpdateRecord2($oDB, "calendar", "CalendarID", $calendarID, $values);

    // update calendar_attendance for rider saving this ride
    if($result['success'])
    {
        // check to see if there is already a calendar_attendance record for this user and ride
        $attendanceID = $oDB->DBLookup("AttendanceID", "calendar_attendance", "CalendarID={$result['CalendarID']} AND RiderID=" . GetUserID(), -1);
        // add/update calendar_attendance record
        $attvalues['RiderID'] = GetUserID();
        $attvalues['CalendarID'] = $result['CalendarID'];
        $attvalues['Attending'] = SmartGetCheckbox("Attending");
        $attvalues['Notify'] = SmartGetCheckbox("Attending");
        $result = InsertOrUpdateRecord2($oDB, "calendar_attendance", "AttendanceID", $attendanceID, $attvalues);
    }
}

// --- Encode response and send back to form
Echo json_encode($result);
?>
