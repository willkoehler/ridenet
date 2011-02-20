<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
define("SHAREDBASE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/Shared/");
require(SHAREDBASE_DIR . "DBConnection.php");
require("../script/data-helpers.php");

// store query/post values in local variables
$query = $_REQUEST['query'];           // partial string typed into combo box

// --- open connection to database
$oDB = oOpenDBConnection();

// --- split query string into $lastName, $firstName. It is much faster to search on names separately
// --- then to search on a combined "Name" field because the separate seaches can take advantage
// --- of the LastName, FirstName indexes
$names = explode(" ", $query);
$firstName = $names[0];
$lastName = isset($names[1]) ? trim($names[1]) : "";

// -- build WHERE clause based on search terms
$whereFilter1 = "((FirstName LIKE \"$firstName%\" OR LastName LIKE \"$firstName%\") AND LastName LIKE \"$lastName%\") AND IFNULL(rider.Archived,0)=0";
$whereFilter2 = "TeamName LIKE \"%$query%\" AND IFNULL(Archived,0)=0";

// --- Get User records
$rs = $oDB->query("SELECT 'rider' AS Type, CONCAT(FirstName, ' ', LastName) AS DisplayText, TeamName AS InfoText, IFNULL(RiderType,'') AS InfoText2,
                           RiderID, RacingTeamID AS TeamID, Domain, CONCAT(FirstName, ' ', LastName) AS Sort
                   FROM rider LEFT JOIN teams ON (RacingTeamID=TeamID) LEFT JOIN ref_rider_type USING (RiderTypeID)
                   WHERE $whereFilter1
                   UNION
                   SELECT 'team' AS Type, TeamName AS DisplayText,
                          IF(bRacing=0 AND bCommuting=0, 'Recreational Team', IF(bRacing=1 AND bCommuting=0, 'Racing Team', IF(bRacing=0 AND bCommuting=1, 'Commuting Team', IF(bRacing=1 AND bCommuting=1, 'Racing and Commuting Team', '')))) AS InfoText,
                          '' AS InfoText2, 0 AS RiderID, TeamID, Domain, TeamName AS Sort
                   FROM teams
                   WHERE $whereFilter2
                   ORDER BY Sort LIMIT 9", __FILE__, __LINE__);

// --- Loop through all the records and add the contents of each record to the output array
$result['results'] = array();
while($row = $rs->fetch_object())
{
	  $result['results'][] = $row;
}

// --- Dump output.
Echo json_encode($result);
?>
