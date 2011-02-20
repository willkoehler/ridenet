<?
require("../script/app-master.php");

// store query/post values in local variables
$limit = SmartGetInt('limit');     // Number of records to retrieve (used for grid object paging)
$start = SmartGetInt('start');     // Starting record (used for grid object paging)
$dir = SmartGet('dir');            // Sort direction desc, asc (used for remoteSort)
$sort = SmartGet('sort');          // name of sort row (used for remoteSort)

// --- open connection to database
$oDB = oOpenDBConnection();

// make sure user is authorized to view the system log
if(!isSystemAdmin())
{
    $result['results'] = array();
    $result['rowcount'] = 0;
}
else
{
    // --- Count total records in table. "rowcount" tells the grid object the total number of rows available in recordset
    $result['rowcount'] = $oDB->DBCount("activity", "TRUE");;

    // --- Get User records
    $rs = $oDB->query("SELECT ActivityID, RiderID, Date, CONCAT(FirstName, ' ', LastName) AS RiderName,
                              TeamName, Domain, Description, ReferenceID, activity.IPAddress, LoginID
                       FROM activity
                       LEFT JOIN logins ON (LoginID = LoginTableID)
                       LEFT JOIN rider USING (RiderID)
                       LEFT JOIN teams ON (TeamID = RacingTeamID)
                       ORDER BY $sort $dir LIMIT $start, $limit", __FILE__, __LINE__);

    // --- Loop through all the records and add the contents of each record to the output array
    $result['results'] = array();
    while($row = $rs->fetch_object())
    {
    	  $result['results'][] = $row;
    }
}

// --- Dump output.
Echo json_encode($result);
?>
