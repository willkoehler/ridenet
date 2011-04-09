<?
require("../script/app-master.php");

// store query/post values in local variables
$limit = SmartGetInt('limit');     // Number of records to retrieve (used for grid object paging)
$start = SmartGetInt('start');     // Starting record (used for grid object paging)
$dir = SmartGet('dir');            // Sort direction desc, asc (used for remoteSort)
$sort = SmartGet('sort');          // name of sort row (used for remoteSort)
$archivedFilter = SmartGetInt('Archived');
$searchFor = SmartGet('SearchFor', "");

// make sure user is authorized to edit users
if(!isSystemAdmin())
{
    $result['results'] = array();
    $result['rowcount'] = 0;
}
else
{
    // -- build WHERE clause based on search terms
    $whereFilter = "";
    $whereFilter .= ($archivedFilter != -1) ? " AND (Archived = $archivedFilter)" : "";
    $whereFilter .= ($searchFor != "") ? " AND (TeamName like \"%$searchFor%\" OR Domain like \"%$searchFor%\")" : "";

    // --- open connection to database
    $oDB = oOpenDBConnection();

    // --- Count total records in table. "rowcount" tells the grid object the total number of rows available in recordset
    $rs = $oDB->query("SELECT count(*) as TotalRows FROM teams WHERE 1 $whereFilter", __FILE__, __LINE__);
    $record = $rs->fetch_array();
    $result['rowcount'] = $record['TotalRows'];
    $rs->free();

    // --- Get Team data
    $rs = $oDB->query("SELECT TeamID, TeamType, Archived, bRacing, bCommuting, SiteLevelID, SiteLevel, TeamName,
                       IFNULL(Domain, CONCAT('club', TeamID)) AS Domain
                       FROM teams
                       LEFT JOIN ref_site_level USING (SiteLevelID)
                       LEFT JOIN ref_team_type USING (TeamTypeID)
                       WHERE 1 $whereFilter ORDER BY $sort $dir LIMIT $start, $limit", __FILE__, __LINE__);

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
