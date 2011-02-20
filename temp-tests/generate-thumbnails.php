<?
require("../script/app-master.php");
require(SHAREDBASE_DIR . "SimpleImage.php");
$oDB = oOpenDBConnection();
$RaceID = SmartGetInt("ID");

$rs = $oDB->query("SELECT * FROM event_photos WHERE RaceID=$RaceID");
while(($record = $rs->fetch_array())!=false)
{
    $picData=file_get_contents("../imgstore/full/{$record['Filename']}");
    $picture = new SimpleImage;
    $picture->set($picData);
    $picture->resizeAndCropToFit(73,73);
    $picData=file_put_contents("../imgstore/thumb/{$record['Filename']}", $picture->getJPEGImageData());
}
?>