<?
require("../script/app-master.php");

// Reject requests that are missing required parameters (to handle bots scanning this page)
CheckRequiredParameters(Array('limit', 'start', 'dir', 'sort'));

// store query/post values in local variables
$limit = SmartGetInt('limit');     // Number of records to retrieve (used for grid object paging)
$start = SmartGetInt('start');     // Starting record (used for grid object paging)
$dir = SmartGet('dir');            // Sort direction desc, asc (used for remoteSort)
$sort = SmartGet('sort');          // name of sort row (used for remoteSort)
$searchFor = SmartGet('SearchFor', "");

// --- open connection to database
$oDB = oOpenDBConnection();

// -- build WHERE clause based on search terms
$whereFilter = "TeamTypeID=2";
$whereFilter .= ($searchFor != "") ? " AND (TeamName like \"%$searchFor%\")" : "";

// --- Count total records in table. "rowcount" tells the grid object the total number of rows available in recordset
$result['rowcount'] = $oDB->DBCount("teams", $whereFilter);;

// --- Get User records
$sql = "SELECT TeamID, TeamName, Domain, COUNT(RiderID) AS TotalRiders, COUNT(IF(CEDaysMonth >= 2, RiderID, NULL)) AS StarRiders,
               SUM(CERides) AS CERides, IFNULL(TIMESTAMPDIFF(WEEK, LastActivity, NOW()),1000) AS LastActivity
        FROM (SELECT RacingTeamID AS TeamID, RiderID, CEDaysMonth, Y0_CERides AS CERides
              FROM rider JOIN rider_stats USING (RiderID)
              WHERE IFNULL(rider.Archived,0)=0
              UNION
              SELECT CommutingTeamID AS TeamID, RiderID, CEDaysMonth, Y0_CERides AS CERides
              FROM rider JOIN rider_stats USING (RiderID)
              WHERE IFNULL(rider.Archived,0)=0) d1
        LEFT JOIN teams USING (TeamID)
        LEFT JOIN (SELECT CommutingTeamID, DATE(MAX(activity.Date)) AS LastActivity
                   FROM rider
                   JOIN logins USING(RiderID)
                   JOIN activity ON(LoginTableID = LoginID)
                   GROUP BY CommutingTeamID) dt ON (dt.CommutingTeamID=teams.TeamID)
        WHERE $whereFilter
        GROUP BY TeamID
        ORDER BY $sort $dir LIMIT $start, $limit";
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
