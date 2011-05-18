<?
// This page appears to the browser as an image file containing the specified team's page background.
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");

$oDB = oOpenDBConnection();
$teamID = intval($_REQUEST['T']);
$teamTypeID = $oDB->DBLookup("TeamTypeID", "teams", "TeamID=$teamID", 1);

// Check if image has been modified since browser cached it.
$lastModified = strtotime($oDB->DBLookup("IFNULL(LastModified, '1/1/2000')", "team_images", "TeamID=$teamID", "1/1/2000"));
CheckLastModified($lastModified);

ob_start();   // buffer the output so it's sent as a single chunk
$rs = $oDB->query("SELECT BodyBGColor, BodyBG FROM teams JOIN team_images USING (TeamID) WHERE TeamID=$teamID", __FILE__, __LINE__);
if(($record = $rs->fetch_array())==false || (is_null($record['BodyBG']) && (is_null($record['BodyBGColor']) || $record['BodyBGColor']==BODY_BG_COLOR)))
{
    // This team has not customized their background color & background image yet
    // Use default background image.
    if($teamTypeID==2)
    {
        header("Content-type: image/gif");
        $picData=file_get_contents(dirname(__FILE__) . "/../images/tdot.gif");
    }
    else
    {
        header("Content-type: image/jpeg");
        $picData=file_get_contents(dirname(__FILE__) . "/../images/ridenetbg.jpg");
    }
    echo $picData;
}
elseif(!is_null($record['BodyBG']))
{
    // display page background from database
    header("Content-type: image/jpeg");
    echo $record['BodyBG'];
}
else
{
    // This team has a custom background color and no background image.
    // Return a blank image (background color will show through)
    $picData=file_get_contents(dirname(__FILE__) . "/../images/tdot.gif");
    header("Content-type: image/gif");
    echo $picData;
}
header("Content-Length: " . ob_get_length());   // tell the browser the size of the image
ob_end_flush();                                 // flush and close buffer
?>