<?
require("../script/app-master.php");

// store query/post values in local variables
$limit = SmartGetInt('limit');     // Number of records to retrieve (used for grid object paging)
$start = SmartGetInt('start');     // Starting record (used for grid object paging)
$dir = SmartGet('dir');            // Sort direction desc, asc (used for remoteSort)
$sort = SmartGet('sort');          // name of sort row (used for remoteSort)
$startDate = SmartGetDate('StartDate');
$endDate = SmartGetDate('EndDate');
$tolerance = SmartGetInt('Tolerance');
$teamID = SmartGetInt('T');

// --- open connection to database
$oDB = oOpenDBConnection();

// --- Get Team data
$rs = $oDB->query("SELECT RiderID, LastName, FirstName,
                   Count(DISTINCT IF(RideLogTypeID=1 AND Date BETWEEN $startDate AND $endDate, Date, NULL)) AS CDays
                   FROM rider
                   LEFT JOIN ride_log USING (RiderID)
                   WHERE (RacingTeamID=$teamID OR CommutingTeamID=$teamID) AND rider.Archived=0
                   GROUP BY RiderID
                   ORDER BY $sort $dir", __FILE__, __LINE__);

// --- Loop through all the records and add the contents of each record to the output array
$result['results'] = array();
while($row = $rs->fetch_object())
{
	  $result['results'][] = $row;
}

// --- Dump output.
Echo json_encode($result);
?>
