<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();
$rideLogID = SmartGetInt('RideLogID');

// convert distance from kilometers to miles if distance value contains "k"
$values['Distance'] = (SmartGet('Distance')=="opt.") ? "NULL" : (strpos(SmartGet('Distance'), "k") ? SmartGetInt('Distance') * .62 : SmartGetInt('Distance'));
$values['Duration'] = (SmartGet('Duration')=="opt.") ? "NULL" : SmartGetDuration('Duration');

if(!CheckSession())
{
    $result['success'] = false;
    $result['message'] = "You are not logged in";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
if($values['Duration'] > 1500 || $values['Distance'] > 600)
{
    $result['success'] = false;
    $result['message'] = "Values out of range";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
else
{
// --- log the ride
    $values['RiderID'] = GetUserID();
    $values['Date'] = SmartGetDate("Date");
    $values['RideLogTypeID'] = SmartGetInt('RideLogTypeID');
    $values['WeatherID'] = SmartGetInt('WeatherID');
    $values['Comment'] = (SmartGet('Comment')=="140 characters max") ? "NULL" : SmartGetString('Comment');
    $values['Link'] = (SmartGet('Link')=="Link to something: Route map, Garmin Connect, TrainingPeaks, power file, etc.") ? "NULL" : SmartGetString('Link');
    // convert 0 distance and time to NULL
    $values['Distance'] = ($values['Distance']<=0) ? "NULL" : $values['Distance'];
    $values['Duration'] = ($values['Duration']<=0) ? "NULL" : $values['Duration'];
    // set source and date created for new ride log entries
    if($rideLogID==-1)
    {
        $values['HasMap'] = 0;
        $values['Source'] = 1;  // 1 = ridenet.net website
        $values['Created'] =  "'" . date("Y-m-d H:i:s") . "'";
    }
    // save the ride log
    $result = InsertOrUpdateRecord2($oDB, "ride_log", "RideLogID", $rideLogID, $values);
// --- Update stats for this rider and store new stats in the response
    if($result['success'])
    {
        $result['stats'] = UpdateRiderStats($oDB, GetUserID());
    }
}

// --- Encode response and send back to form
Echo json_encode($result);
?>
