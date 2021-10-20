<?
// This page appears to the browser as an image file containing the specified team's page banner.
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");

$oDB = oOpenDBConnection();
$teamID = intval($_REQUEST['T']);
$teamTypeID = $oDB->DBLookup("TeamTypeID", "teams", "TeamID=$teamID", 1);
$showLogo = $oDB->DBLookup("IF(ShowLogo=1 AND Logo IS NOT NULL, 1, 0)", "teams JOIN team_images USING (TeamID)", "TeamID=$teamID", 0);

// Check if image has been modified since browser cached it.
$lastModified = strtotime($oDB->DBLookup("IFNULL(LastModified, '1/1/2000')", "team_images", "TeamID=$teamID", "1/1/2000"));
CheckLastModified($lastModified);

ob_start();   // buffer the output so it's sent as a single chunk
$rs = $oDB->query("SELECT Banner FROM team_images WHERE TeamID=$teamID");
if(($record = $rs->fetch_array())!=false && !is_null($record['Banner']))
{
    // display page banner from database
    header("Content-type: image/jpeg");
    echo $record['Banner'];
}
else
{
    // This team does not have a page banner. Display default banner
    // Default banner depends on the team type and team ID (hard-code for now)
    if($teamID==0)
    {
        // banner for RideNet pages
        $bannerFile = "ridenetbanner.png";
    }
    elseif($teamTypeID==2)
    {
        // banner for 2BY2012 teams
        $bannerFile = "2by2012banner.png";
    }
    else
    {
        // banner for racing/recreational teams depends on weather team is showing logo in the center of the banner
        $bannerFile = ($showLogo) ? "ridenetbanner2.png" : "ridenetbanner.png";
    }
    $picData=file_get_contents(dirname(__FILE__) . "/../images/$bannerFile");
    header("Content-type: image/png");
    echo $picData;
}
header("Content-Length: " . ob_get_length());   // tell the browser the size of the image
ob_end_flush();                                 // flush and close buffer
?>