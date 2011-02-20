<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();

$itemID = SmartGetInt('ID');
$riderID = $oDB->DBLookup("RiderID", "posts", "PostID=$itemID");

if(!CheckSession())
{
    $result['success'] = false;
    $result['message'] = "You are not logged in";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
elseif($riderID!=GetUserID())
{
    $result['success'] = false;
    $result['message'] = "Unauthorized Access";
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );     // needed so Ext returns failureType 'server'
}
else
{
    $oDB->query("DELETE FROM posts WHERE PostID=$itemID", __FILE__, __LINE__);
    $oDB->RecordActivityIfOK("Delete [post] ID=$itemID", $riderID);
    $result['success'] = true;
}

// --- Encode response and send back to form
Echo json_encode($result);
?>
