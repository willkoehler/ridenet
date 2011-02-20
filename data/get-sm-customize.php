<?
require("../script/app-master.php");

$oDB = oOpenDBConnection();
// store query/post values in local variables
$teamID = SmartGetInt('TeamID');
$organizationID = $oDB->DBLookup("OrganizationID", "teams", "TeamID=$teamID", 2);

// default colors depend on the team type
$defaultPrimaryColor = ($organizationID==2) ? PRIMARY_COLOR_CMT : PRIMARY_COLOR;
$defaultSecondaryColor = ($organizationID==2) ? SECONDARY_COLOR_CMT : SECONDARY_COLOR;
$defaultPageBGColor = ($organizationID==2) ? PAGE_BG_COLOR_CMT : PAGE_BG_COLOR;
$defaultBodyBGColor = ($organizationID==2) ? BODY_BG_COLOR_CMT : BODY_BG_COLOR;
$defaultLinkColor = ($organizationID==2) ? LINK_COLOR_CMT : LINK_COLOR;

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
    $rs = $oDB->query("SELECT TeamID, TeamName, Domain, bRacing, bCommuting,
                              IFNULL(ShowLogo, 1) AS ShowLogo,
                              IFNULL(PrimaryColor, '$defaultPrimaryColor') AS PrimaryColor,
                              IFNULL(SecondaryColor, '$defaultSecondaryColor') AS SecondaryColor,
                              IFNULL(PageBGColor, '$defaultPageBGColor') AS PageBGColor,
                              IFNULL(BodyBGColor, '$defaultBodyBGColor') AS BodyBGColor,
                              IFNULL(LinkColor, '$defaultLinkColor') AS LinkColor
                       FROM teams WHERE TeamID=$teamID", __FILE__, __LINE__);
    $result['results'] = $rs->fetch_object();
    $result['success'] = true;
}

// --- Dump output.
Echo json_encode($result);
?>
