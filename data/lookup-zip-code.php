<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
define("SHAREDBASE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/Shared/");
require(SHAREDBASE_DIR . "DBConnection.php");
require("../script/data-helpers.php");

// store query/post values in local variables
$limit = intval($_REQUEST['limit']);   // Number of records to retrieve (used for paging)
$start = intval($_REQUEST['start']);   // Starting record (used for paging)
$query = $_REQUEST['query'];           // partial string typed into combo box

// if no paging information is provided, just pull first 20 records
/*$paging = true;
if($limit=="NULL")
{
    $limit = 20;
    $start = 0;
    $paging = false;
}*/

// --- open connection to database
$oDB = oOpenDBConnection();

// --- split query string into $city, $state. It is much faster to search on names separately
// --- then to search on a combined "City, St" field because the separate seaches can take advantage
// --- of the LastName, FirstName indexes in the patients table
$location = explode(",", $query);
$city = $location[0];
$state = isset($location[1]) ? trim($location[1]) : "";

$whereFilter = "(City LIKE \"$city%\" AND State LIKE \"$state%\") OR ZipCode LIKE \"$query%\"";

// --- Count total records in table. "rowcount" tells the combo-box the total number of rows available in recordset
//if($paging)
{
    $rs = $oDB->query("SELECT count(*) as TotalRows FROM ref_zipcodes WHERE $whereFilter", __FILE__, __LINE__);
    $record = $rs->fetch_array();
    $result['rowcount'] = $record['TotalRows'];
    $rs->free();
}

// --- Get zip code data
$rs = $oDB->query("SELECT ZipCodeID as id, CONCAT(City, ', ', State, ' ', ZipCode) as text
                   FROM ref_zipcodes
                   WHERE $whereFilter
                   ORDER BY ZipCodeID LIMIT $start, $limit", __FILE__, __LINE__);

// --- Loop through all the records and add the contents of each record to the output array
$result['results'] = array();
while($row = $rs->fetch_object())
{
	  $result['results'][] = $row;
}

// --- Dump output. "rowcount" tells the combobox the total number of rows available in recordset
Echo json_encode($result);

?>
