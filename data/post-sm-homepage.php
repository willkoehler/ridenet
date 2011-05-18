<?
require("../script/app-master.php");
require(SHAREDBASE_DIR . "SimpleImage.php");
$oDB = oOpenDBConnection();

$teamID = SmartGetInt('TeamID');

if(!CheckSession())
{
    $result['success'] = false;
    $result['message'] = "You are not logged in";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
// If uploaded image exceeds post_max_size, PHP discards the entire post
else if(count($_REQUEST)==0)
{
    $result['success'] = false;
    $result['message'] = "Selected image file is too large. Max image size is " . ini_get('post_max_size');
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
// Make sure user is authorized to modify this team
else if(!isSystemAdmin() && !isDesigner() && !isTeamAdmin($oDB, $teamID))
{
    $result['success'] = false;
    $result['message'] = "You do not have rights to modify this team site";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
else
{
    // ==== Store Uploaded Pictures ====
    $imageUploaded = ($_FILES['ImageFile']['tmp_name']!="") ? true : false;

    $image = new SimpleImage;
    if($imageUploaded && $image->load($_FILES['ImageFile']['tmp_name'])==false)
    {
        $result['success'] = false;
        $result['message'] = "Homepage image file is not a valid image file";
        $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
    }
    else
    {
        $values['HomePageHTML'] = SmartGetString("HomePageHTML");
        $values['HomePageTitle'] = (SmartGet('HomePageTitle')=="Type a title for your page") ? "NULL" : SmartGetString('HomePageTitle');
        $values['HomePageText'] = (SmartGet('HomePageText')=="Type Something About Your Team") ? "NULL" : SmartGetString('HomePageText');
        $values['HomePageType'] = SmartGetInt("HomePageType");
        $result = InsertOrUpdateRecord2($oDB, "teams", "TeamID", $teamID, $values);
        if($result['success'] && $imageUploaded)
        {
            $picvalues['TeamID'] = $result['TeamID'];
            $picvalues['LastModified'] = "'" . date("Y-m-d H:i:s") . "'";
            if($imageUploaded)
            {
                $image->resizeAndCropToFit(450,270);  // resize image to desired dimensions
                $picvalues['HomePageImage'] = "'" . addslashes($image->getJPEGImageData()) . "'";
            }
            // search for existing pictures
            $photoID = $oDB->DBLookup("PhotoID", "team_images", "TeamID={$result['TeamID']}", -1);
            // insert/update photos
            $result = InsertOrUpdateRecord2($oDB, "team_images", "PhotoID", $photoID, $picvalues);
        }
    }
}
// --- Encode response and send back to form
Echo json_encode($result);
?>
