<?
require(SHAREDBASE_DIR . "SendMail.php");

//----------------------------------------------------------------------------------
//  AccountCreatedEmail()
//
//  Sends an email message to a user when an account has been created for them
//
//  PARAMETERS:
//    oDB         - a mysqli connection object (used to read mail configuration)
//    newRiderID  - ID of the rider who's account was created
//    createdByID - ID of the rider who created the account
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function AccountCreatedEmail($oDB, $newRiderID, $createdByID)
{
    $rs=$oDB->query("SELECT CONCAT(FirstName, ' ', LastName) AS Name, RiderEmail, TeamTypeID, RacingTeamID, TeamName, Domain
                     FROM rider JOIN teams ON (RacingTeamID = TeamID)
                     WHERE RiderID=$newRiderID", __FILE__, __LINE__);
    $newRiderInfo = $rs->fetch_array();
    $rs->free();
    $teamURL =  "http://" . $newRiderInfo['Domain'] . "." . GetDomainRoot();
    if($createdByID==15 || $createdByID==0)     // created by the system?
    {
        $createdBy = "We have created a profile for you on the \"{$newRiderInfo['TeamName']}\" RideNet team";
        $questions = "If you have any questions about your account, email us at info@ridenet.net";
    }
    else
    {
        $rs=$oDB->query("SELECT CONCAT(FirstName, ' ', LastName) AS Name, RiderEmail FROM rider WHERE RiderID=$createdByID");
        $createdByInfo = $rs->fetch_array();
        $rs->free();
        $createdBy = "{$createdByInfo['Name']} has created a profile for you on the \"{$newRiderInfo['TeamName']}\" RideNet team";
        $questions = "If you have any questions about your account, email your team admin: {$createdByInfo['Name']} {$createdByInfo['RiderEmail']}";
    }
    
    if($newRiderInfo)
    {
        if($newRiderInfo['RacingTeamID']==SANDBOX_TEAM_ID)
        {
        // sandbox team has special welcome email
            $subject = "Welcome To RideNet";
            $msg = "Hi {$newRiderInfo['Name']},\n\n" .
                   "Welcome to RideNet. You are currently a member of the RideNet Sandbox. The Sandbox is a " .
                   "holding place for riders that are not yet a member of a RideNet team. You can login to " .
                   "RideNet using your email address and temporary password.\n\n" .
                   "Team Home: $teamURL\n" .
                   "Your Profile: $teamURL/profile\n" .
                   "Email Address: {$newRiderInfo['RiderEmail']}\n" .
                   "Password: live2ride\n\n" .
                   "Some things you can do on RideNet:\n" .
                   "- Update your rider bio. Click \"Edit Profile\" at the top of Your Profile page\n" .
                   "- Post race results. Choose \"Your Results\" from the menu in Your Profile page\n" .
                   "- Log your rides. Click \"+ Log A Ride\" on Your Profile page\n" .
                   "- Join a team! See the Sandbox home page for more information on joining or creating a team\n" .
                   "- Find other riders/teams. Use the \"Search\" box in the main menu.\n\n" .
                   "If you have any questions about your account, email us at info@ridenet.net\n\n" .
                   "Welcome to RideNet: http://ridenet.net We're excited to have you on board.\n\n" .
                   "Sincerely,\n" .
                   "The RideNet Development Team";
        }
        else
        {
            switch($newRiderInfo['TeamTypeID']) {
                case 2:
                // commuting teams (consider biking 2 by 2012 sites for now)
                    $subject = "Welcome to RideNet - {$newRiderInfo['TeamName']}";
                    $msg = "Hi {$newRiderInfo['Name']},\n\n" .
                           "Welcome to the active transportation revolution!\n\n" .
                           "RideNet has partnered with Consider Biking's 2 BY 2012 program to promote bicycling as " .
                           "a viable form of transportation in Central Ohio and to track how many miles people are " .
                           "riding. Each 2 BY 2012 participating organization has a RideNet team where riders log " .
                           "their bicycling miles.\n\n $createdBy. You can login to your account using your email address " .
                           "and temporary password.\n\n" .
                           "Team Home: $teamURL\n" .
                           "Your Profile: $teamURL/profile\n" .
                           "Email Address: {$newRiderInfo['RiderEmail']}\n" .
                           "Password: live2ride\n\n" .
                           "Some things you can do on RideNet:\n" .
                           "- Update your rider bio. Click \"Edit Profile\" at the top of Your Profile page\n" .
                           "- Log your rides. Click \"+ Log A Ride\" on Your Profile page\n" .
                           "- Find other riders/teams. Use the \"Search\" box in the main menu.\n\n" .
                           "$questions. Remember: Ride and record those miles!\n\n" .
                           "Sincerely,\n" .
                           "The RideNet Development Team\n\n" .
                           "2 BY 2012 is made possible by the Robert Bartels, William C. and Naoma W. Denison, Charlotte R. Hallel, " .
                           "Robert B. Hurst and Martha G. Staub funds of the The Columbus Foundation.";
                    break;

                default:
                // racing and recreational teams
                    $subject = "Welcome to RideNet - {$newRiderInfo['TeamName']}";
                    $msg = "Hi {$newRiderInfo['Name']},\n\n" .
                           "Welcome to RideNet. $createdBy. You can login to RideNet using your email address and " .
                           "temporary password.\n\n" .
                           "Team Home: $teamURL\n" .
                           "Your Profile: $teamURL/profile\n" .
                           "Email Address: {$newRiderInfo['RiderEmail']}\n" .
                           "Password: live2ride\n\n" .
                           "Some things you can do on RideNet:\n" .
                           "- Update your rider bio. Click \"Edit Profile\" at the top of Your Profile page\n" .
                           "- Post race results. Choose \"Your Results\" from the menu in Your Profile page\n" .
                           "- Log your rides. Click \"+ Log A Ride\" on Your Profile page\n" .
                           "- Find other riders/teams. Use the \"Search\" box in the main menu.\n\n" .
                           "$questions\n\n" .
                           "Welcome to RideNet: http://ridenet.net We're excited to have you on board.\n\n" .
                           "Sincerely,\n" .
                           "The RideNet Development Team";
                    break;
            }
        }
        $to = $newRiderInfo['Name'] . " <" . $newRiderInfo['RiderEmail'] . ">";
        if(SendMail($to, $subject, $msg, "info@ridenet.net"))
        {
            $oDB->RecordActivity("Email OK: " . addslashes($to), $newRiderID);
        }
        else
        {
            $oDB->RecordActivity("Email Error: " . addslashes($to), $newRiderID);
        }
    }
}


//----------------------------------------------------------------------------------
//  CalendarUpdateEmail()
//
//  Sends an email message to a list of users when a calendar update is posted
//
//  PARAMETERS:
//    oDB         - a mysqli connection object (used to read mail configuration)
//    postID      - ID of the post
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function CalendarUpdateEmail($oDB, $postID)
{
    $posts = $oDB->query("SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderID, PostedToID AS CalendarID, Text, EventName
                          FROM posts
                          JOIN calendar ON (PostedToID = CalendarID)
                          JOIN rider USING (RiderID)
                          WHERE PostID=$postID", __FILE__, __LINE__);
    $post = $posts->fetch_array();
    $posts->free();
    $recipients = $oDB->query("SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderEmail, Domain
                               FROM calendar_attendance
                               JOIN rider USING (RiderID)
                               JOIN teams ON (rider.RacingTeamID = teams.TeamID)
                               WHERE CalendarID={$post['CalendarID']} AND Notify=1", __FILE__, __LINE__);
    $subject = "Ride Update - {$post['EventName']}";
    while(($recipient = $recipients->fetch_array())!=false)
    {
        $to = $recipient['RiderName'] . " <" . $recipient['RiderEmail'] . ">";
        $msg = "{$post['RiderName']} posted an update to {$post['EventName']}:\n\n" .
               "\"{$post['Text']}\"\n\n" .
               "You received this email because you checked \"Email me ride updates\" on the ride information page. " .
               "For details go to the ride information page: " .
               BuildTeamBaseURL($recipient['Domain']) . "/ride/{$post['CalendarID']}";
        if(SendMail($to, $subject, $msg, "noreply@ridenet.net"))
        {
            $oDB->RecordActivity("Email OK: " . addslashes($to), $post['CalendarID']);
        }
        else
        {
            $oDB->RecordActivity("Email Error: " . addslashes($to), $post['CalendarID']);
        }
    }
}


//----------------------------------------------------------------------------------
//  EventUpdateEmail()
//
//  Sends an email message to a list of users when an event update is posted
//
//  PARAMETERS:
//    oDB         - a mysqli connection object (used to read mail configuration)
//    postID      - ID of the post
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function EventUpdateEmail($oDB, $postID)
{
    $posts = $oDB->query("SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderID, PostedToID AS RaceID, Text, EventName
                          FROM posts
                          JOIN event ON (PostedToID = RaceID)
                          JOIN rider USING (RiderID)
                          WHERE PostID=$postID", __FILE__, __LINE__);
    $post = $posts->fetch_array();
    $posts->free();
    $recipients = $oDB->query("SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderEmail, Domain
                               FROM event_attendance
                               JOIN rider USING (RiderID)
                               JOIN teams ON (rider.RacingTeamID = teams.TeamID)
                               WHERE RaceID={$post['RaceID']} AND Notify=1", __FILE__, __LINE__);
    $subject = "Event Update - {$post['EventName']}";
    while(($recipient = $recipients->fetch_array())!=false)
    {
        $to = $recipient['RiderName'] . " <" . $recipient['RiderEmail'] . ">";
        $msg = "{$post['RiderName']} posted an update to {$post['EventName']}:\n\n" .
               "\"{$post['Text']}\"\n\n" .
               "You received this email because you checked \"Email me event updates\" on the event information page. " .
               "For details go to the event information page: " .
               BuildTeamBaseURL($recipient['Domain']) . "/event/{$post['RaceID']}";
        if(SendMail($to, $subject, $msg, "noreply@ridenet.net"))
        {
            $oDB->RecordActivity("Email OK: " . addslashes($to), $post['RaceID']);
        }
        else
        {
            $oDB->RecordActivity("Email Error: " . addslashes($to), $post['RaceID']);
        }
    }
}


?>