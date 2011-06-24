<?
require("../script/app-master.php");
require(SHAREDBASE_DIR . "SimpleImage.php");
$oDB = oOpenDBConnection();
$existingRiders = $oDB->DBCount("rider", "RiderEmail=" . SmartGetString('RiderEmail'));

$riderID = SmartGetInt('RiderID');

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
// Make sure user is authorized to modify this rider
else if($riderID!=GetUserID())
{
    $result['success'] = false;
    $result['message'] = "You do not have rights to modify this rider";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
// Do not allow rider to use email of an existing rider
else if($existingRiders > 0 && strtolower(SmartGet('RiderEmail'))!=strtolower($oDB->DBLookup("RiderEmail", "rider", "RiderID=$riderID")))
{
    $result['success'] = false;
    $result['message'] = "There is another RideNet member with this email address.";
    $result['errors'][] = array('id' => 'RiderEmail', 'msg' => 'There is another RideNet rider with this email address' );
}
// TEMPORARY CHECK to make sure users don't have a cached version of profile dialog with name fields
else if(!isset($_REQUEST['FirstName']) || !isset($_REQUEST['LastName']))
{
    $result['success'] = false;
    $result['message'] = "Refresh this page. You are using an old version of the profile dialog.";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
else
{
    // ==== Store Rider Record ====
    $values['RiderEmail'] = SmartGetString('RiderEmail');
    $values['FirstName'] = SmartGetString('FirstName');
    $values['LastName'] = SmartGetString('LastName');
    $values['FavoriteQuote'] = SmartGetString('FavoriteQuote');
    $values['FavoriteRide'] = SmartGetString('FavoriteRide');
    $values['FavoriteFood'] = SmartGetString('FavoriteFood');
    $values['WhyIRide'] = SmartGetString('WhyIRide');
    $values['MyCommute'] = SmartGetString('MyCommute');
    $values['BornIn'] = SmartGetString('BornIn');
    $values['ResideIn'] = SmartGetString('ResideIn');
    $values['Occupation'] = SmartGetString('Occupation');
    $values['RiderTypeID'] = SmartGetInt('RiderTypeID');
    $values['Height'] = SmartGetString('Height');
    $values['Weight'] = SmartGetInt('Weight');
    $values['YearsCycling'] = SmartGetInt('YearsCycling');
    $values['DateOfBirth'] = SmartGetDate('DateOfBirth');
    $values['URL'] = SmartGetString('URL');
    $values['MapPrivacy'] = SmartGetString('MapPrivacy');
    $result = InsertOrUpdateRecord2($oDB, "rider", "RiderID", $riderID, $values);

    // ==== Store Uploaded Pictures ====
    $teamID = $oDB->DBLookup("RacingTeamID", "rider", "RiderID=$riderID");    // always use racing team ID for picture
    $pictureUploaded = ($result['success'] && isset($_FILES['PictureFile']['tmp_name']) && $_FILES['PictureFile']['tmp_name']!="") ? true : false;
    $actionPictureUploaded = ($result['success'] && isset($_FILES['ActionPictureFile']['tmp_name']) && $_FILES['ActionPictureFile']['tmp_name']!="") ? true : false;
    if($result['success'] && $pictureUploaded || $actionPictureUploaded)
    {
    // --- User uploaded new picture file. Resize and store in database
        $picture = new SimpleImage;
        $action = new SimpleImage;
        if($pictureUploaded && ($picture->load($_FILES['PictureFile']['tmp_name'])==false))
        {
            $result['success'] = false;
            $result['message'] = "Portrait file is not a valid image file";
            $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
        }
        elseif($actionPictureUploaded && $action->load($_FILES['ActionPictureFile']['tmp_name'])==false)
        {
            $result['success'] = false;
            $result['message'] = "Action Shot file is not a valid image file";
            $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
        }
        else
        {
            $picvalues['RiderID'] = $result['RiderID'];
            $picvalues['TeamID'] = $teamID;
            $picvalues['LastModified'] = "'" . date("Y-m-d H:i:s") . "'";
            if($actionPictureUploaded)
            {
                $action->resizeToFit(600,600);  // make sure largest size is <= 600 pixels
                $picvalues['ActionPicture'] = "'" . addslashes($action->getJPEGImageData()) . "'";
            }
            if($pictureUploaded)
            {
                $picture->resizeAndCropToFit(160,200);
                $picvalues['Picture'] = "'" . addslashes($picture->getJPEGImageData()) . "'";
            }
            // search for existing pictures
            $photoID = $oDB->DBLookup("PhotoID", "rider_photos", "RiderID={$picvalues['RiderID']} AND TeamID={$picvalues['TeamID']}", -1);
            // insert/update photos
            $result = InsertOrUpdateRecord2($oDB, "rider_photos", "PhotoID", $photoID, $picvalues);
        }
    }
}
// --- Encode response and send back to form
Echo json_encode($result);
?>
