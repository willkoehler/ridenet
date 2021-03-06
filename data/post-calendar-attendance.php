<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();
$attendanceID = SmartGetInt("AttendanceID");

if(!CheckSession())
{
    $result['success'] = false;
    $result['message'] = "You are not logged in";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
else
{
// --- save the attendance record
    $values['RiderID'] = GetUserID();
    $values['CalendarID'] = SmartGetInt("CalendarID");
    $values['Attending'] = SmartGetCheckbox("Attending");
    $values['Notify'] = SmartGetCheckbox("Notify");
    $result = InsertOrUpdateRecord2($oDB, "calendar_attendance", "AttendanceID", $attendanceID, $values);
}

// --- Encode response and send back to form
Echo json_encode($result);
?>
