<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");

$oDB = oOpenDBConnection();
$uid = SmartGetInt('id');
$pw = SmartGetString('pw');
// ride log data
$rideLogID = SmartGetInt('ride-log-id');
$source = SmartGetInt('Source');
$date = SmartGetDate('Date');
$rideLogTypeID = SmartGetInt('RideLogTypeID');
$weatherID = SmartGetInt('WeatherID');
$distance = SmartGetInt('Distance');
$duration = SmartGetInt('Duration');
$comment = SmartGetString('Comment');
$link = SmartGetString('Link');
$hasmap = (isset($_REQUEST['Map']) && $_REQUEST['Map']!="") ? 1 : 0;

$duration = round($duration / 60.0);
$distance = round($distance / 1609.344);

if(!isset($_REQUEST['ride-log-id']) || !isset($_REQUEST['id']) || !isset($_REQUEST['pw']) ||
          !isset($_REQUEST['Source']) || !isset($_REQUEST['Date']) || !isset($_REQUEST['RideLogTypeID']))
{
    header("HTTP/1.1 400 Bad Request");
    $result['error'] = "Missing parameters";
}
elseif($source!=2)    // 2 = iPhone - this is hardcoded for now
{
    header("HTTP/1.1 400 Bad Request");
    $result['error'] = "Invalid source";
}
elseif(strtotime(SmartGet('Date'))==false)
{
    header("HTTP/1.1 400 Bad Request");
    $result['error'] = "Invalid date format";
}
elseif(($rideLogTypeID!=="NULL" && $rideLogTypeID < 1 || $rideLogTypeID > 6) || ($weatherID!=="NULL" && $weatherID < 1 || $weatherID > 8) ||
       $distance > 1000 || $duration > 2880 || strlen($comment) > 142)
{
    header("HTTP/1.1 400 Bad Request");
    $result['error'] = "Parameter out of range";
}
elseif($oDB->DBCount("rider", "RiderID=$uid AND Password=$pw") == 0)
{
    header("HTTP/1.1 403 Forbidden");
    $result['error'] = "Login credentials are not valid";
}
elseif($rideLogID!=-1 && $oDB->DBLookup("RiderID", "ride_log", "RideLogID=$rideLogID")!=$uid)
{
    header("HTTP/1.1 403 Forbidden");
    $result['error'] = "Not authorized to modified this ride log entry";
}
else
{
    // log the ride
    $values['RiderID'] = $uid;
    $values['Date'] = $date;
    $values['RideLogTypeID'] = $rideLogTypeID;
    $values['WeatherID'] = $weatherID;
    $values['Duration'] = $duration;
    $values['Comment'] = $comment;
    $values['Link'] = $link;
    $values['Distance'] = $distance;
    // convert 0 distance to NULL
    $values['Distance'] = ($values['Distance']==0) ? "NULL" : $values['Distance'];
    // set source and date created for new ride log entries
    if($rideLogID==-1)
    {
        $values['HasMap'] = $hasmap;
        $values['Source'] = $source;
        $values['DateCreated'] =  "'" . date("Y-m-d") . "'";
    }
    // save the ride log
    $post = InsertOrUpdateRecord2($oDB, "ride_log", "RideLogID", $rideLogID, $values);
    if($post['success'])
    {
        UpdateRiderStats($oDB, $uid);   // Update stats for this rider
        $result['RideLogID'] = $post['RideLogID'];
        if($hasmap && $rideLogID==-1)
        {
          // When adding a new ride, parse and store the map data if it's present
            $decoded_map = json_decode($_REQUEST['Map'], true);
            foreach ($decoded_map as $key => $point)
            {
                // sanitize the record and add to the database
                $alt = intval($point['alt']);
                $lon = intval($point['lon']);
                $lat = intval($point['lat']);
                $time = addslashes($key);
                $sql = "INSERT INTO ride_log_map (RideLogID, DateTime, Latitude, Longitude, Altitude)
                        VALUES({$result['RideLogID']}, '$time', $lat, $lon, $alt)";
                $oDB->query($sql, __FILE__, __LINE__);
                if($oDB->errno!=0)
                {
                    header("HTTP/1.1 500 Internal System Error");
                    $result['error'] = "[" . $oDB->errno . "] SQL Error while saving map data";
                    break;
                }
            }
        }
    }
    else
    {
        header("HTTP/1.1 500 Internal System Error");
        $result['error'] = $post['message'];
    }
}

echo json_encode($result);
?>
