<?
// This page appears to the browser as an image file containing the specified team's page banner.
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");
require(SHAREDBASE_DIR . "SimpleImage.php");

$oDB = oOpenDBConnection();
$teamID = intval($_REQUEST['T']);

// Check if image has been modified since browser cached it.
$lastModified = strtotime($oDB->DBLookup("IFNULL(LastModified, '1/1/2000')", "team_images", "TeamID=$teamID", "1/1/2000"));
CheckLastModified($lastModified);

ob_start();   // buffer the output so it's sent as a single chunk
$rs = $oDB->query("SELECT Logo FROM team_images WHERE TeamID=$teamID AND Logo IS NOT NULL", __FILE__, __LINE__);
if(($record = $rs->fetch_array())!=false)
{
    // resize and display team logo from database
    header("Content-type: image/png");
    $picture = new SimpleImage;
    $picture->set($record['Logo']);
    $picture->resizeToFit(90,30);
    $picture->output(IMAGETYPE_PNG);
}
else
{
    // This team has not uploaded a logo. Display default logo
    $picData=file_get_contents(dirname(__FILE__) . "/../images/logo-unavailable-sm.png");
    header("Content-type: image/png");
    echo $picData;
}
header("Content-Length: " . ob_get_length());   // tell the browser the size of the image
ob_end_flush();                                 // flush and close buffer
?>