<?
require("../script/app-master.php");
require(SHAREDBASE_DIR . "SimpleImage.php");
$oDB = oOpenDBConnection();

$teamID = SmartGetInt('TeamID');
$domainCheck=$oDB->DBLookup("TeamName", "teams", "TeamID<>$teamID AND Domain=" . SmartGetString('Domain'));

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
else if($domainCheck!="")
{
    $result['success'] = false;
    $result['message'] = "The chosen domain name is taken by \"$domainCheck\"";
    $result['errors'][] = array('id' => 'Domain', 'msg' => 'This domain name is already taken' );     // server-side error message
}
else
{
    // ==== Store Uploaded Pictures ====
    $bannerUploaded = (isset($_FILES['BannerFile']['tmp_name']) && $_FILES['BannerFile']['tmp_name']!="") ? true : false;
    $logoUploaded = (isset($_FILES['LogoFile']['tmp_name']) && $_FILES['LogoFile']['tmp_name']!="") ? true : false;
    $backgroundUploaded = (isset($_FILES['PageBGFile']['tmp_name']) && $_FILES['PageBGFile']['tmp_name']!="") ? true : false;

    $banner = new SimpleImage;
    $logo = new SimpleImage;
    $background = new SimpleImage;
    if($bannerUploaded && $banner->load($_FILES['BannerFile']['tmp_name'])==false)
    {
        $result['success'] = false;
        $result['message'] = "Banner file is not a valid image file";
        $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
    }
    elseif($bannerUploaded && ($banner->getWidth() != 780))
    {
        $result['success'] = false;
        $result['message'] = "Banner image must be 780px wide";
        $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
    }
    elseif($logoUploaded && $logo->load($_FILES['LogoFile']['tmp_name'])==false)
    {
        $result['success'] = false;
        $result['message'] = "Logo file is not a valid image file";
        $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
    }
    // Since we don't resize background image before storing in the database, we need to limit the size of files
    // we accept. This also prevents "Got a packet bigger than 'max_allowed_packet' bytes" errors from MySQL
    elseif($backgroundUploaded && $_FILES['PageBGFile']['size'] > 500000)
    {
        $result['success'] = false;
        $result['message'] = "Background image file is too large. Choose a smaller image";
        $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
    }
    elseif($backgroundUploaded && $background->load($_FILES['PageBGFile']['tmp_name'])==false)
    {
        $result['success'] = false;
        $result['message'] = "Background file is not a valid image file";
        $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
    }
    else
    {
        $values['TeamName'] = SmartGetString("TeamName");
        $values['bRacing'] = SmartGetCheckbox("bRacing");
        $values['bCommuting'] = SmartGetCheckbox("bCommuting");
        $values['Domain'] = SmartGetString("Domain");
        $values['TeamTypeID'] = SmartGetInt('TeamTypeID');
        $values['ZipCodeID'] = SmartGetInt('ZipCodeID');
        $values['ShowLogo'] = SmartGetCheckbox("ShowLogo");
        $values['PrimaryColor'] = str_replace("#", "", SmartGetString("PrimaryColor"));
        $values['SecondaryColor'] = str_replace("#", "", SmartGetString("SecondaryColor"));
        $values['BodyBGColor'] = str_replace("#", "", SmartGetString("BodyBGColor"));
        $values['PageBGColor'] = str_replace("#", "", SmartGetString("PageBGColor"));
        $values['LinkColor'] = str_replace("#", "", SmartGetString("LinkColor"));
        $result = InsertOrUpdateRecord2($oDB, "teams", "TeamID", $teamID, $values);
        if($result['success'] && ($logoUploaded || $bannerUploaded || $backgroundUploaded))
        {
            $picvalues['TeamID'] = $result['TeamID'];
            $picvalues['LastModified'] = "'" . date("Y-m-d H:i:s") . "'";
            if($logoUploaded)
            {
                $logo->resizeToFit(300,70);  // make sure logo fits in correct dimensions
                $picvalues['Logo'] = "'" . addslashes($logo->getPNGImageData()) . "'";
            }
            if($bannerUploaded)
            {
                $picvalues['Banner'] = "'" . addslashes($banner->getJPEGImageData()) . "'";
            }
            if($backgroundUploaded)
            {
                $picvalues['BodyBG'] = "'" . addslashes($background->getJPEGImageData()) . "'";
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
