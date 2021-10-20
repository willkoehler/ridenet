<?
require("../script/app-master.php");

$oDB = oOpenDBConnection();

// --- Verify old password is correct
$rs = $oDB->query("SELECT * FROM rider WHERE RiderID=" . GetUserID());

if(($record=$rs->fetch_array())==false)
{
// --- no rows matched, password check failed
    $result['success'] = false;
    $result['message'] = "Invalid UserID";
// --- needed so Ext returns failureType of 'server' (FYI: could also be used to do server-side field validation)
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );
}
elseif(CheckPassword(SmartGet('OldPW'), $record['Password'])==false)
{
    $result['success'] = false;
    $result['message'] = "Old Password was not correct";
// --- needed so Ext returns failureType of 'server' (FYI: could also be used to do server-side field validation)
    $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );
}                   
else
{
// --- password check succeeded, update the password
    $hashsalt = MakePasswordHash(SmartGet('NewPW1'));
    $rs = $oDB->query("UPDATE rider SET Password=\"$hashsalt\", MustChangePW=0 WHERE RiderID=" . GetUserID());
    if($oDB->errno!=0)
    {
        $result['success'] = false;
        $result['message'] = "[" . $oDB->errno . "] SQL Error";// . $oDB->error;      
        $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );
    }
    else    
    {
    // --- password update succeeded, clear session variable requiring password change and record
    // --- password change in the log
        $result['success'] = true;
        $_SESSION['MustChangePW'] = 0;
        $oDB->RecordActivity("Password Changed", GetUserID());
    }
}                  

Echo json_encode($result);

?>