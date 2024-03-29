<?
require("../script/app-master.php");

// Reject requests that are missing required parameters (to handle bots scanning this page)
CheckRequiredParameters(Array('limit', 'start', 'dir', 'sort', 'Range'));

// store query/post values in local variables
$limit = SmartGetInt('limit');     // Number of records to retrieve (used for grid object paging)
$start = SmartGetInt('start');     // Starting record (used for grid object paging)
$dir = SmartGet('dir');            // Sort direction desc, asc (used for remoteSort)
$sort = SmartGet('sort');          // name of sort row (used for remoteSort)
$searchFor = SmartGet('SearchFor', "");
$range = addslashes(SmartGet('Range'));

// --- open connection to database
$oDB = oOpenDBConnection();

// -- build WHERE clause based on search terms
$teamFilter = "TeamID<>" . SANDBOX_TEAM_ID;
$teamFilter .= ($searchFor != "") ?  " AND TeamName LIKE \"%$searchFor%\"" : "";

// --- Get team stats
$sql = "SELECT TeamID, TeamName, TeamType, Domain, CONCAT(City, ', ', State, ' ', Zipcode) AS Location,
               SUM(Miles) AS Miles,
               SUM(CEMiles) AS CEMiles,
               COUNT(IF(CEDaysMonth >= 2, RiderID, NULL)) AS StarRiders,
               SUM(CERides) AS CERides
        FROM (SELECT RacingTeamID AS TeamID, RiderID, CEDaysMonth, {$range}_Miles AS Miles, {$range}_CEMiles AS CEMiles, {$range}_CERides AS CERides
              FROM rider JOIN rider_stats USING (RiderID)
              WHERE IFNULL(rider.Archived,0)=0
              UNION
              SELECT CommutingTeamID AS TeamID, RiderID, CEDaysMonth, {$range}_Miles AS Miles, {$range}_CEMiles AS CEMiles, {$range}_CERides AS CERides
              FROM rider JOIN rider_stats USING (RiderID)
              WHERE IFNULL(rider.Archived,0)=0) d1
        LEFT JOIN teams USING (TeamID)
        LEFT JOIN ref_team_type USING (TeamTypeID)
        LEFT JOIN ref_zipcodes USING (ZipCodeID)
        WHERE $teamFilter
        GROUP BY TeamID
        ORDER BY $sort $dir LIMIT $start, $limit";
$rs = $oDB->query($sql);

// --- Loop through all the records and add the contents of each record to the output array
$result['results'] = array();
while($row = $rs->fetch_object())
{
	  $result['results'][] = $row;
}

// --- Dump output.
Echo json_encode($result);
?>
