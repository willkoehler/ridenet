<?
// This page appears to the browser as an image file containing the specified rider's picture.
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");
require(SHAREDBASE_DIR . "SimpleImage.php");

$oDB = oOpenDBConnection();
$riderID = intval($_REQUEST['RiderID']);
$teamID = intval($_REQUEST['T']);

// Check if image has been modified since browser cached it.
$lastModified = strtotime($oDB->DBLookup("IFNULL(LastModified, '1/1/2000')", "rider_photos", "RiderID=$riderID AND TeamID=$teamID", "1/1/2000"));
CheckLastModified($lastModified);

ob_start();   // buffer the output so it's sent as a single chunk
$rs = $oDB->query("SELECT ActionPicture FROM rider_photos WHERE RiderID=$riderID AND TeamID=$teamID");
if(($record = $rs->fetch_array())!=false && ($record['ActionPicture']!=""))
{
    // resize and display rider action shot from database
    header("Content-type: image/jpeg");
    $picture = new SimpleImage;
    $picture->set($record['ActionPicture']);
    $picture->resizeToFit(160,120);
    $picture->output(IMAGETYPE_JPEG);
}
else
{
    // This rider has not uploaded a picture for this team. Display "unavailable" picture
    $picData=file_get_contents(dirname(__FILE__) . "/../images/action-shot_sm.jpg");
    header("Content-type: image/jpg");
    echo $picData;
}
header("Content-Length: " . ob_get_length());   // tell the browser the size of the image
ob_end_flush();                                 // flush and close buffer
?>