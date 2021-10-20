<?
// --- This script is run from the command line. The caller must pass in the path to
// --- the web server root folder so we can set $_SERVER['DOCUMENT_ROOT']. This is needed
// --- to find the "Shared" directory.

if(!isset($argv[0]))
{
    exit("The Nightly script can only be run from the command line");
}
if(!isset($argv[1]))
{
    exit("Usage: php -f Nightly.php PathToServerRoot\n");
}

$_SERVER['DOCUMENT_ROOT'] = $argv[1];
// --- (use app-master-min here so we don't start a session)
require(dirname(__FILE__) . "/app-master-min.php");

// --- open database connections
$oDB=oOpenDBConnection();

// --- nightly operations
UpdateAllRiderStats($oDB);
CheckDuplicateWayPoints($oDB);


//----------------------------------------------------------------------------------
//  UpdateAllRiderStats()
//
//  This function updates the stats for all the riders in the system. This is needed
//  to keeps the days/month stat current for riders that do not log rides daily.
//
//  PARAMETERS:
//    oDB   - the database connection object
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function UpdateAllRiderStats($oDB)
{
    $startTime = microtime(true);
    $count=0;
    $rs = $oDB->query("SELECT RiderID FROM rider");
    while(($record = $rs->fetch_array())!=false)
    {
        UpdateRiderStats($oDB, $record['RiderID']);
        $count++;
    }
    $rs->free();
    $elapsedTime = microtime(true) - $startTime;
    trigger_error("NIGHTLY: Rider stats updated for $count riders (" . number_format($elapsedTime,2) . " sec)", E_USER_NOTICE);
}


//----------------------------------------------------------------------------------
//  CheckDuplicateWayPoints()
//
//  Remove duplicate way points from all ride log maps.
//
//  PARAMETERS:
//    oDB         - the database connection object
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function CheckDuplicateWayPoints($oDB)
{
    $startTime = microtime(true);
    $rs = $oDB->query("SELECT DISTINCT RideLogID FROM ride_log_map");
    while(($record = $rs->fetch_array())!=false)
    {
        $rideLogID = $record['RideLogID'];
        $before = $oDB->DBCount("ride_log_map", "RideLogID=$rideLogID");
        RemoveDuplicateWayPoints($oDB, $rideLogID);
        $after = $oDB->DBCount("ride_log_map", "RideLogID=$rideLogID");
        $reduce = 100*($before-$after)/$after;
        if($reduce > 0)
        {
            trigger_error("NIGHTLY: Removed duplicate way points ID:$rideLogID  $before->$after (" . number_format($reduce,0) . "%)", E_USER_NOTICE);
        }
    }
    // reclaim unused space and defragment ride_log_map table
    $oDB->query("OPTIMIZE TABLE ride_log_map");
    $elapsedTime = microtime(true) - $startTime;
    trigger_error("NIGHTLY: Checked for duplicate way points (" . number_format($elapsedTime,2) . " sec)", E_USER_NOTICE);
}


//----------------------------------------------------------------------------------
//  RemoveDuplicateWayPoints()
//
//  Remove duplicate way points from a route. Hopefully this won't be needed at some
//  point. But currently about 40% of the way points sent from the iPhone app are
//  redundant.
//
//  NOTE: We cannot easily filter way points before inserting them into the
//  ride_log_map table because they arrive unsorted from the iPhone.
//
//  PARAMETERS:
//    oDB         - the database connection object
//    $rideLogID  - ID of ride log
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RemoveDuplicateWayPoints($oDB, $rideLogID)
{
    $lastPoint = array('Longitude' => 0, 'Latitude' => 0);
    $rs = $oDB->query("SELECT DateTime, Longitude, Latitude FROM ride_log_map WHERE RideLogID=$rideLogID ORDER BY DateTime");
    while(($wayPoint=$rs->fetch_array())!=false)
    {
        if($wayPoint['Longitude']==$lastPoint['Longitude'] && $wayPoint['Latitude']==$lastPoint['Latitude'])
        {
            $oDB->query("DELETE FROM ride_log_map WHERE RideLogID=$rideLogID AND DateTime='{$wayPoint['DateTime']}'");
        }
        else
        {
            $lastPoint = $wayPoint;
        }
    }
}

?>