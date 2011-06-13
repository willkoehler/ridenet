<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");

// Reject requests that are missing required parameters (to handle bots scanning this page)
CheckRequiredParameters(Array('query'));

// store query/post values in local variables
$query = addslashes($_REQUEST['query']);           // partial string typed into combo box

// --- open connection to database
$oDB = oOpenDBConnection();

// --- split query string into Last Name (with possible middle name) and First Name. It is much faster
// --- to search on names separately then to search on a combined "Name" field because the separate
// --- seaches can take advantage of the LastName, FirstName indexes
$names = explode(" ", $query);
$name2 = trim((count($names) > 1) ? end($names) : "");
$name1 = trim(($name2=="") ? $query : substr($query, 0, -strlen($name2)));

// -- build WHERE clause based on search terms
$whereFilter1 = "(((FirstName LIKE \"$name1%\" OR LastName LIKE \"$name1%\") AND LastName LIKE \"$name2%\") OR
                  FirstName LIKE \"$name1 $name2%\") AND IFNULL(rider.Archived,0)=0";
$whereFilter2 = "TeamName LIKE \"%$query%\" AND IFNULL(Archived,0)=0";

// --- Get User records
$rs = $oDB->query("SELECT 'rider' AS Type, CONCAT(FirstName, ' ', LastName) AS DisplayText, TeamName AS InfoText, IFNULL(RiderType,'') AS InfoText2,
                           RiderID, RacingTeamID AS TeamID, Domain, CONCAT(FirstName, ' ', LastName) AS Sort
                   FROM rider LEFT JOIN teams ON (RacingTeamID=TeamID) LEFT JOIN ref_rider_type USING (RiderTypeID)
                   WHERE $whereFilter1
                   UNION
                   SELECT 'team' AS Type, TeamName AS DisplayText, TeamType AS InfoText, CONCAT(City, ', ', State, ' ', Zipcode) AS InfoText2,
                          0 AS RiderID, TeamID, Domain, TeamName AS Sort
                   FROM teams LEFT JOIN ref_team_type USING (TeamTypeID) LEFT JOIN ref_zipcodes USING (ZipCodeID)
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
