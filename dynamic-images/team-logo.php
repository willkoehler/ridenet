<?
// This page appears to the browser as an image file containing the specified team's page banner.
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");

$oDB = oOpenDBConnection();
$teamID = intval($_REQUEST['T']);

// Check if image has been modified since browser cached it.
$lastModified = strtotime($oDB->DBLookup("IFNULL(LastModified, '1/1/2000')", "team_images", "TeamID=$teamID", "1/1/2000"));
CheckLastModified($lastModified);

ob_start();   // buffer the output so it's sent as a single chunk
$rs = $oDB->query("SELECT Logo FROM team_images WHERE TeamID=$teamID AND Logo IS NOT NULL");
if(($record = $rs->fetch_array())!=false)
{
    // display team logo from database
    header("Content-type: image/png");
    echo $record['Logo'];
}
else
{
    // This team has not uploaded a logo. Display default logo
    $picData=file_get_contents(dirname(__FILE__) . "/../images/logo-unavailable.png");
    header("Content-type: image/png");
    echo $picData;
}
header("Content-Length: " . ob_get_length());   // tell the browser the size of the image
ob_end_flush();                                 // flush and close buffer
?>