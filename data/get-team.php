<?
require("../script/app-master.php");

$oDB = oOpenDBConnection();
// store query/post values in local variables
$teamID = SmartGetInt('TeamID');      // Team ID

if(!CheckSession())
{
    $result['results'][] = array();   // (dummy results array is required)
    $result['success'] = false;  
}
else if(!isSystemAdmin())
{
    $result['results'][] = array();   // (dummy results array is required)
    $result['success'] = false;  
}
else
{
    $rs = $oDB->query("SELECT TeamID, Archived, bRacing, bCommuting, SiteLevelID, TeamTypeID, TeamName,
                              ZipCodeID, CONCAT(City, ', ', State, ' ', ZipCode) AS ZipCodeText,
                              IFNULL(Domain, CONCAT('club', TeamID)) AS Domain
                       FROM teams LEFT JOIN ref_zipcodes USING (ZipCodeID)
                       WHERE TeamID=$teamID");
    $result['results'] = $rs->fetch_object();
    $result['success'] = true;
}

// --- Dump output.
Echo json_encode($result);
?>
