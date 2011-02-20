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
    $rs = $oDB->query("SELECT TeamID, Archived, bRacing, bCommuting, SiteLevelID, OrganizationID, TeamName,
                              IFNULL(Domain, CONCAT('club', TeamID)) AS Domain
                       FROM teams
                       WHERE TeamID=$teamID", __FILE__, __LINE__);
    $result['results'] = $rs->fetch_object();
    $result['success'] = true;
}

// --- Dump output.
Echo json_encode($result);
?>
