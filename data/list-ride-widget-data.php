<?
require("../script/app-master-min.php");

// Reject requests that are missing required parameters (to handle bots scanning this page)
CheckRequiredParameters(Array('team', 'rider', 'maxage', 'callback'));

$teamFilter = addslashes($_REQUEST['team']);
$riderFilter = addslashes($_REQUEST['rider']);
$maxAge = intval($_REQUEST['maxage']);

$whereFilter = "Comment IS NOT NULL AND (DATEDIFF(NOW(), Date) BETWEEN 0 AND $maxAge)";
$whereFilter .= ($teamFilter != '*') ? " AND (TeamName LIKE '%$teamFilter%')" : "";
$whereFilter .= ($riderFilter != '*') ? " AND (CONCAT(FirstName, ' ', LastName) LIKE '%$riderFilter%')" : "";

// --- open data connection
$oDB = oOpenDBConnection();

// --- get ride log data
$sql = "SELECT RiderID, CONCAT(FirstName, ' ', LastName) AS RiderName, RideLogID, TeamID, RacingTeamID, TeamName,
               Domain, DATE_FORMAT(Date, '%a %c/%e') AS ShortDate, RideLogType, RideLogTypeImage, Distance, Duration,
               Comment, IFNULL(Weather, 'N/A') AS Weather, IFNULL(WeatherImage, 'none.png') AS WeatherImage,
               DATEDIFF(NOW(), Date) AS Age, IF(Comment IS NULL, 0, 1) AS HasComment
        FROM ride_log
        LEFT JOIN rider USING (RiderID)
        LEFT JOIN teams ON (CommutingTeamID = TeamID)
        LEFT JOIN ref_ride_log_type USING (RideLogTypeID)
        LEFT JOIN ref_weather USING (WeatherID)
        WHERE $whereFilter
        ORDER BY HasComment Desc, RAND() Desc
        LIMIT 0,50";
$rs = $oDB->query($sql, __FILE__, __LINE__);

$result = array();
while($row = $rs->fetch_object())
{
	  $result[] = $row;
}

// --- Dump output. This will be sent to the server as javascript. We are using the script
// --- tag hack to work around the same origin barrier so we can pull data from our server
// --- from sites hosted on other servers
header("Content-Type: text/javascript");
echo $_REQUEST['callback'] . "(" . json_encode($result). ");";
?>
