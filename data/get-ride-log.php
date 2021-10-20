<?
require("../script/app-master.php");

// store query/post values in local variables
$rideLogID = SmartGetInt('RideLogID');

$oDB = oOpenDBConnection();
$rs = $oDB->query("SELECT RideLogID, Date, RideLogTypeID, Distance, WeatherID, Comment, Link,
                          IF(Duration IS NULL OR Duration=0, NULL, CONCAT(FLOOR(Duration/60), ':', LPAD(MOD(Duration,60), 2, '0'))) AS Duration
                   FROM ride_log
                   WHERE RideLogID=$rideLogID");
$result['results'] = $rs->fetch_object();
$result['success'] = true;

// --- Dump output.
Echo json_encode($result);
?>
