<?
require("../script/app-master.php");
require("../script/email-notifications.php");
require(SHAREDBASE_DIR . "BufferHelpers.php");

// Reject requests that are missing required parameters (to handle bots scanning this page)
CheckRequiredParameters(Array('FirstName', 'LastName', 'Email'));

$firstName = SmartGetString('FirstName');
$lastName = SmartGetString('LastName');
$email = SmartGetString('Email');
$teamID = SmartGetInt('TeamID');
$noTeam = SmartGetCheckbox('NoTeam');
$createTeam = SmartGetCheckbox('CreateTeam');
$source = SmartGet('Source');

// --- open connection to database
$oDB = oOpenDBConnection();

// Default to Sandbox team if no team is provided
$teamID = ($teamID=="NULL" || $noTeam==1) ? SANDBOX_TEAM_ID : $teamID;

$teamName = $oDB->DBLookup("TeamName", "teams", "TeamID=$teamID");

// Does this rider already have a profile?
if($oDB->DBCount("rider", "RiderEmail=$email"))
{
    $result['success'] = false;
    $result['message'] = "There is already a RideNet member with this email address.<br>" .
                         "Do you want to <a href=\"/login\">Login</a> or " .
                         "<a id='resetpw-btn' href=\"javascript:g_resetPWDialog.show({animateTarget:'resetpw-btn'})\">Reset Your Password</a>?";
    $result['errors'][] = array('id' => 'Email', 'msg' => 'There is already a RideNet rider with this email address' );
}
else
{
    // create rider
    $values['RiderEmail'] = SmartGetString('Email');
    $values['LastName'] = SmartGetString('LastName');
    $values['FirstName'] = SmartGetString('FirstName');
    $values['Archived'] = 0;
    $values['RacingTeamID'] = $teamID;
    $values['CommutingTeamID'] = $teamID;
    $values['sCommutingTeamAdmin'] = 0;
    $values['sRacingTeamAdmin'] = 0;
    $values['MustChangePW'] = 1;
    $values['MapPrivacy'] = 1;
    $values['CreatedByID'] = 0;
    $values['DateCreated'] =  "'" . date("Y-m-d") . "'";
    $values['Password'] = chr(34) . MakePasswordHash('live2ride') . chr(34);
    $result = InsertOrUpdateRecord2($oDB, "rider", "RiderID", -1, $values);
}

// Encode response, send to the browser, and close the connection.
FlushAndClose(json_encode($result));

// Send email notifications silently after page connection is closed
if($result['success'])
{
    // send welcome email
    AccountCreatedEmail($oDB, $result['RiderID'], 0);
    // send notification email
    $msg = "NAME: " . trim($firstName,"\"") . " " . trim($lastName,"\"") ."\n" .
           "EMAIL: " . trim($email,"\"") ."\n" .
           "TEAM: $teamName\n" .
           "SOURCE: $source\n" .
           "NO TEAM: $noTeam\n" .
           "CREATE TEAM: $createTeam\n";
    SendMail("signup@ridenet.net", "RideNet Signup!", $msg, "info@ridenet.net");
}
?>
