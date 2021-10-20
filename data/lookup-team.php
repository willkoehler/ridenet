<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");

// Reject requests that are missing required parameters (to handle bots scanning this page)
CheckRequiredParameters(Array('limit', 'start', 'query'));

// store query/post values in local variables
$limit = intval($_REQUEST['limit']);   // Number of records to retrieve (used for paging)
$start = intval($_REQUEST['start']);   // Starting record (used for paging)
$query = $_REQUEST['query'];           // partial string typed into combo box

// --- open connection to database
$oDB = oOpenDBConnection();

// -- build WHERE clause based on search terms
$whereFilter = "(TeamName LIKE \"%$query%\") AND IFNULL(Archived,0)=0";

// --- Count total records in table. "rowcount" tells the combo-box the total number of rows available in recordset
$rs = $oDB->query("SELECT count(*) as TotalRows FROM teams WHERE $whereFilter");
$record = $rs->fetch_array();
$result['rowcount'] = $record['TotalRows'];
$rs->free();

// --- Get User records
$rs = $oDB->query("SELECT TeamID, TeamName, Domain, TeamType
                   FROM teams LEFT JOIN ref_team_type USING (TeamTypeID)
                   WHERE $whereFilter ORDER BY TeamName LIMIT $start, $limit");

// --- Loop through all the records and add the contents of each record to the output array
$result['results'] = array();
while($row = $rs->fetch_object())
{
	  $result['results'][] = $row;
}

// --- Dump output.
Echo json_encode($result);
?>
