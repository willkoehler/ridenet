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
$whereFilter = "IFNULL(rider.Archived,0)=0";
if($searchFor != "")
{
    // --- split query string into $lastName, $firstName. It is much faster to search on names separately
    // --- then to search on a combined "Name" field because the separate seaches can take advantage
    // --- of the LastName, FirstName indexes
    $names = explode(" ", $searchFor);
    $firstName = $names[0];
    $lastName = isset($names[1]) ? trim($names[1]) : "";
    $whereFilter .= " AND (((FirstName LIKE \"$firstName%\" OR LastName LIKE \"$firstName%\") AND LastName LIKE \"$lastName%\")";
    $whereFilter .= " OR t1.TeamName LIKE \"%$searchFor%\" OR t2.TeamName LIKE \"%$searchFor%\")";
}

// --- Get rider stats
$sql = "SELECT RiderID, CONCAT(FirstName, ' ', LastName) AS RiderName, RiderType, t1.TeamID, t1.TeamName, t1.Domain,
               {$range}_Miles AS Miles, {$range}_Days AS Days, CEDaysMonth, {$range}_CEDays AS CEDays
        FROM rider
        LEFT JOIN rider_stats USING (RiderID)
        LEFT JOIN ref_rider_type USING (RiderTypeID)
        LEFT JOIN teams t1 ON (t1.TeamID = RacingTeamID)
        LEFT JOIN teams t2 ON (t2.TeamID = CommutingTeamID)
        WHERE $whereFilter
        GROUP BY RiderID
        ORDER BY $sort $dir LIMIT $start, $limit";
        //exit($sql);
$rs = $oDB->query($sql, __FILE__, __LINE__);

// --- Loop through all the records and add the contents of each record to the output array
$result['results'] = array();
while($row = $rs->fetch_object())
{
	  $result['results'][] = $row;
}

// --- Dump output.
Echo json_encode($result);
?>
