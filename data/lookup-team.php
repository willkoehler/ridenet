<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
define("SHAREDBASE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/Shared/");
require(SHAREDBASE_DIR . "DBConnection.php");
require("../script/data-helpers.php");

// store query/post values in local variables
$limit = intval($_REQUEST['limit']);   // Number of records to retrieve (used for paging)
$start = intval($_REQUEST['start']);   // Starting record (used for paging)
$query = $_REQUEST['query'];           // partial string typed into combo box

// --- open connection to database
$oDB = oOpenDBConnection();

// -- build WHERE clause based on search terms
$whereFilter = "(TeamName LIKE \"%$query%\") AND IFNULL(Archived,0)=0";

// --- Count total records in table. "rowcount" tells the combo-box the total number of rows available in recordset
$rs = $oDB->query("SELECT count(*) as TotalRows FROM teams WHERE $whereFilter", __FILE__, __LINE__);
$record = $rs->fetch_array();
$result['rowcount'] = $record['TotalRows'];
$rs->free();

// --- Get User records
$rs = $oDB->query("SELECT TeamID, TeamName, Domain,
                   IF(bRacing=0 AND bCommuting=0, 'Recreational Team', IF(bRacing=1 AND bCommuting=0, 'Racing Team', IF(bRacing=0 AND bCommuting=1, 'Commuting Team', IF(bRacing=1 AND bCommuting=1, 'Racing and Commuting Team', '')))) AS TeamType
                   FROM teams
                   WHERE $whereFilter ORDER BY TeamName LIMIT $start, $limit", __FILE__, __LINE__);

// --- Loop through all the records and add the contents of each record to the output array
$result['results'] = array();
while($row = $rs->fetch_object())
{
	  $result['results'][] = $row;
}

// --- Dump output.
Echo json_encode($result);
?>
