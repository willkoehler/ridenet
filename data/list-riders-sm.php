<?
require("../script/app-master.php");

// store query/post values in local variables
$limit = SmartGetInt('limit');     // Number of records to retrieve (used for grid object paging)
$start = SmartGetInt('start');     // Starting record (used for grid object paging)
$dir = SmartGet('dir');            // Sort direction desc, asc (used for remoteSort)
$sort = SmartGet('sort');          // name of sort row (used for remoteSort)
$nameFilter = SmartGet('Name', "");
$teamID = SmartGetInt('TeamID');

// --- open connection to database
$oDB = oOpenDBConnection();

// make sure user is authorized to edit users
if(!isTeamAdmin($oDB, $teamID) && !isSystemAdmin())
{
    $result['results'] = array();
    $result['rowcount'] = 0;
}
else
{
    // -- build WHERE clause based on search terms
    $whereFilter = "Archived=0";
    $whereFilter .= ($teamID != -1) ? " AND (CommutingTeamID = $teamID OR RacingTeamID = $teamID)" : "";
    $whereFilter .= ($nameFilter != "") ? " AND (FirstName like \"%$nameFilter%\" OR LastName like \"%$nameFilter%\" OR RiderEmail like \"%$nameFilter%\")" : "";

    // --- Count total records in table. "rowcount" tells the grid object the total number of rows available in recordset
    $rs = $oDB->query("SELECT count(*) as TotalRows FROM rider WHERE $whereFilter");
    $record = $rs->fetch_array();
    $result['rowcount'] = $record['TotalRows'];
    $rs->free();

    // --- Get User records
    $rs = $oDB->query("SELECT RiderID, RiderEmail, LastName, FirstName, rider.Archived, RiderType,
                       (IF(CommutingTeamID=$teamID, sCommutingTeamAdmin, 0) OR IF(RacingTeamID=$teamID, sRacingTeamAdmin, 0)) AS sTeamAdmin
                       FROM rider LEFT JOIN ref_rider_type USING (RiderTypeID)
                       WHERE $whereFilter ORDER BY $sort $dir LIMIT $start, $limit");

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
