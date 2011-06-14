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

// --- update rider stats
UpdateAllRiderStats($oDB);


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

?>