<?
echo "Disabled...";
exit;
// Converting banners to png increases the images size significantly (~3x) with no appreciable
// change in quality. (The Pelotonia banner still has the color darkening problem so this is
// most like a problem with the PHP image functions rather than the image format.) The only
// advantage to png format is that we can use transparency in the banners. While this could
// be cool, I decided to wait until there is a specific need.

require("../script/app-master.php");
require(SHAREDBASE_DIR . "SimpleImage.php");
$oDB = oOpenDBConnection();

$rs = $oDB->query("SELECT PhotoID, Banner FROM team_images WHERE Banner IS NOT NULL");
while(($record = $rs->fetch_array())!=false)
{
    $banner = new SimpleImage;
    $banner->set($record['Banner']);

    $picvalues['LastModified'] = "'" . date("Y-m-d H:i:s") . "'";
    $picvalues['Banner'] = "'" . addslashes($banner->getPNGImageData()) . "'";
    $result = InsertOrUpdateRecord2($oDB, "team_images", "PhotoID", $record['PhotoID'], $picvalues);
    print_r($result);
    echo "<br>";
}
?>