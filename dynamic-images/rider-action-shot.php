<?
// This page appears to the browser as an image file containing the specified rider's picture.
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
define("SHAREDBASE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/Shared/");
require(SHAREDBASE_DIR . "DBConnection.php");
require(SHAREDBASE_DIR . "EnableCache.php");    // allow browser to cache image generated by this page
require(dirname(__FILE__) . "/../script/data-helpers.php");

$oDB = oOpenDBConnection();
$riderID = intval($_REQUEST['RiderID']);
$teamID = intval($_REQUEST['T']);

ob_start();   // buffer the output so it's sent as a single chunk
$rs = $oDB->query("SELECT ActionPicture FROM rider_photos WHERE RiderID=$riderID AND TeamID=$teamID", __FILE__, __LINE__);
if(($record = $rs->fetch_array())!=false && ($record['ActionPicture']!=""))
{
    // display rider picture from database
    header("Content-type: image/jpeg");
    echo $record['ActionPicture'];
}
else
{
    // This rider has not uploaded a picture for this team. Display "unavailable" picture
    $picData=file_get_contents(dirname(__FILE__) . "/../images/tdot.gif");
    header("Content-type: image/gif");
    echo $picData;
}
header("Content-Length: " . ob_get_length());   // tell the browser the size of the image
ob_end_flush();                                 // flush and close buffer
?>