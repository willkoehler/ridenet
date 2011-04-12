<?
require("../script/app-master.php");

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
$sql = "SELECT TeamID, TeamName, Domain, COUNT(RiderID) AS TotalRiders, SUM(IF(CEDaysMonth>0,1,0)) AS ActiveRiders,
               SUM(CEDaysMonth) AS TotDaysMonth, AVG(CEDaysMonth) AS AvgDaysMonth, TIMESTAMPDIFF(WEEK, LastActivity, NOW()) AS LastActivity
        FROM teams
        LEFT JOIN rider ON (rider.CommutingTeamID=teams.TeamID)
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
