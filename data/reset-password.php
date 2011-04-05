<?
require("../script/app-master.php");
require(SHAREDBASE_DIR . "SendMail.php");
$oDB = oOpenDBConnection();

$email = SmartGetString('Email');
$emailraw = SmartGet('Email');

$riderID = $oDB->DBLookup("RiderID", "rider", "RiderEmail=$email", -1);
if($riderID==-1)
{
    $result['success'] = false;
    $result['message'] = "Could not find an account matching this email address";
    $result['errors'][] = array('id' => 'Email', 'msg' => 'Could not find an account matching this email address' );
}
else
{
    $newPW = GeneratePassword(8,1);
    $newPWHash = MakePasswordHash($newPW);
    $oDB->query("UPDATE rider SET Password='$newPWHash', MustChangePW=1 WHERE RiderID=$riderID", __FILE__, __LINE__);
    $msg = "We received a request to reset your RideNet password. Below is your login information and temporary " .
           "password. Once you are logged in, you will be asked to choose a new password\n\n" .
           "Login Page: " . GetFullDomainRoot() . "/login.php\n" .
           "Your Email: $emailraw\n" .
           "Temporary Password: $newPW\n";
    SendMail($emailraw, "RideNet Password Reset", $msg, "noreply@ridenet.net");
    $oDB->RecordActivityIfOK("Reset Password", $riderID);
    $result['success'] = true;
}

// --- Encode response and send back to browser. The small delay caused by sending the email
// --- is desireable in this case so we don't flush the response before sending the email
Echo json_encode($result);
?>
