<?
// This page appears to the browser as an image file containing the specified team's page banner.
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");

$oDB = oOpenDBConnection();
$teamID = intval($_REQUEST['T']);
$teamTypeID = $oDB->DBLookup("TeamTypeID", "teams", "TeamID=$teamID", 1);

// Check if image has been modified since browser cached it.
$lastModified = strtotime($oDB->DBLookup("IFNULL(LastModified, '1/1/2000')", "team_images", "TeamID=$teamID", "1/1/2000"));
CheckLastModified($lastModified);

ob_start();   // buffer the output so it's sent as a single chunk
$rs = $oDB->query("SELECT HomePageImage FROM team_images WHERE TeamID=$teamID AND HomePageImage IS NOT NULL");
if(($record = $rs->fetch_array())!=false)
{
    // display homepage image from database
    header("Content-type: image/jpeg");
    echo $record['HomePageImage'];
}
else
{
    // This team does not have a homepage image. Display default image.
    // Default homepage image depends on the team type (hard-code for now)
    if($teamTypeID==2)
    {
        // image for 2BY2012 pages
        $imageFile = "2by2012-homepage.jpg";
    }
    else
    {
        // image for racing and recreational teams
        $imageFile = "ridenet-homepage.jpg";
    }
    $picData=file_get_contents(dirname(__FILE__) . "/../images/$imageFile");
    header("Content-type: image/jpeg");
    echo $picData;
}
header("Content-Length: " . ob_get_length());   // tell the browser the size of the image
ob_end_flush();                                 // flush and close buffer
?>