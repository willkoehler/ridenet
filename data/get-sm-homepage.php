<?
require("../script/app-master.php");

$oDB = oOpenDBConnection();
// store query/post values in local variables
$teamID = SmartGetInt('TeamID');

if(!CheckSession())
{
    $result['results'][] = array();   // (dummy results array is required)
    $result['success'] = false;  
}
elseif(!isDesigner() && !isSystemAdmin() && !isTeamAdmin($oDB, $teamID))
{
    $result['results'][] = array();   // (dummy results array is required)
    $result['success'] = false;  
}
else
{
    $rs = $oDB->query("SELECT TeamID, TeamName, IFNULL(HomePageType,1) AS HomePageType, HomePageText, HomePageHTML, HomePageTitle, TeamTypeID
                       FROM teams WHERE TeamID=$teamID", __FILE__, __LINE__);
    $result['results'] = $rs->fetch_object();
    if(is_null($result['results']->HomePageHTML))
    {
        $result['results']->HomePageHTML = SampleHomePageHTML($result['results']->TeamName);
    }
    if(is_null($result['results']->HomePageText))
    {
        $result['results']->HomePageText = SampleHomePageText($result['results']->TeamTypeID);
    }
    if(is_null($result['results']->HomePageTitle))
    {
        $result['results']->HomePageTitle = SampleHomePageTitle($result['results']->TeamTypeID, $result['results']->TeamName);
    }
    $result['success'] = true;
}

// --- Dump output.
Echo json_encode($result);
?>
