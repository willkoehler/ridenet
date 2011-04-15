<?
// This page appears to the browser as an image file containing the specified team's page banner.
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
define("SHAREDBASE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/Shared/");
require(SHAREDBASE_DIR . "DBConnection.php");
require(SHAREDBASE_DIR . "EnableCache.php");    // allow browser to cache image generated by this page
require(SHAREDBASE_DIR . "SimpleImage.php");
require(dirname(__FILE__) . "/../script/data-helpers.php");

$oDB = oOpenDBConnection();
$teamID = intval($_REQUEST['T']);

ob_start();   // buffer the output so it's sent as a single chunk
$rs = $oDB->query("SELECT Logo FROM teams WHERE TeamID=$teamID AND Logo IS NOT NULL", __FILE__, __LINE__);
if(($record = $rs->fetch_array())!=false)
{
    // resize and display team logo from database
    header("Content-type: image/jpeg");
    $picture = new SimpleImage;
    $picture->set($record['Logo']);
    $picture->resizeToFit(165,70);
    echo $picture->output(IMAGETYPE_PNG);
}
else
{
    // This team has not uploaded a logo. Display blank picture
    $picData=file_get_contents(dirname(__FILE__) . "/../images/logo-unavailable.png");
    header("Content-type: image/png");
    echo $picData;
}
header("Content-Length: " . ob_get_length());   // tell the browser the size of the image
ob_end_flush();                                 // flush and close buffer
?>