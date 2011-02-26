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
    $rs=$oDB->query("SELECT FirstName, LastName, RiderEmail, OrganizationID, RacingTeamID, TeamName, Domain
                     FROM rider JOIN teams ON (RacingTeamID = TeamID)
                     WHERE RiderID=$newRiderID", __FILE__, __LINE__);
    $newRiderInfo = $rs->fetch_array();
    $rs->free();
    $rs=$oDB->query("SELECT FirstName, LastName, RiderEmail FROM rider WHERE RiderID=$createdByID");
    $createdByInfo = $rs->fetch_array();
    $rs->free();
    $teamURL =  "http://" . $newRiderInfo['Domain'] . "." . GetDomainRoot();
    
    if($newRiderInfo && $createdByInfo)
    {
        if($newRiderInfo['RacingTeamID']==3)
        {
        // sandbox team has special welcome email
            $subject = "Welcome To RideNet";
            $msg = "Hi {$newRiderInfo['FirstName']},\n\n" .
                   "Welcome to RideNet. You are currently a member of the RideNet Sandbox. The Sandbox is a " .
                   "holding place for riders that are not yet a member of a RideNet team. See the RideNet Sandbox " .
                   "home page for more information on joining a team. You can login to RideNet using your email " .
                   "address and temporary password.\n\n" .
                   "Email Address: {$newRiderInfo['RiderEmail']}\n" .
                   "Password: live2ride\n\n" .
                   "Homepage: $teamURL\n\n" .
                   "Some things you can do on RideNet:\n" .
                   "- Update your rider bio. Click \"Edit Profile\" at the top of Your Profile page\n" .
                   "- Post race results. Choose \"Your Results\" from the menu in Your Profile page\n" .
                   "- Log some rides. Click \"+ Log A Ride\" on Your Profile page\n" .
                   "- Find other riders/teams. Choose \"Find\" from the main menu.\n\n" .
                   "If you have any questions about your account, email us at info@ridenet.net\n\n" .
                   "Welcome to RideNet: http://www.ridenet.net We're excited to have you on board.\n\n" .
                   "Sincerely,\n" .
                   "The RideNet Development Team";
        }
        else
        {
            switch($newRiderInfo['OrganizationID']) {
                case 1:
                // racing and recreational teams
                    $subject = "Welcome to RideNet - {$newRiderInfo['TeamName']}";
                    $msg = "Hi {$newRiderInfo['FirstName']},\n\n" .
                           "Welcome to RideNet. {$createdByInfo['FirstName']} {$createdByInfo['LastName']} has " .
                           "created a RideNet account for you as part of \"{$newRiderInfo['TeamName']}\". You can login to RideNet " .
                           "using your email address and temporary password.\n\n" .
                           "Email Address: {$newRiderInfo['RiderEmail']}\n" .
                           "Password: live2ride\n\n" .
                           "Homepage: $teamURL\n\n" .
                           "Some things you can do on RideNet:\n" .
                           "- Update your rider bio. Click \"Edit Profile\" at the top of Your Profile page\n" .
                           "- Post race results. Choose \"Your Results\" from the menu in Your Profile page\n" .
                           "- Log some rides. Click \"+ Log A Ride\" on Your Profile page\n" .
                           "- Find other riders/teams. Choose \"Find\" from the main menu.\n\n" .
                           "If you have any questions about your account, email your team admin: {$createdByInfo['FirstName']} " .
                           "{$createdByInfo['LastName']} {$createdByInfo['RiderEmail']}\n\n" .
                           "Welcome to RideNet: http://www.ridenet.net We're excited to have you on board.\n\n" .
                           "Sincerely,\n" .
                           "The RideNet Development Team";
                    break;
          
                case 2:
                // commuting teams (consider biking 2 by 2012 sites for now)
                    $subject = "Welcome to RideNet - {$newRiderInfo['TeamName']}";
                    $msg = "Hi {$newRiderInfo['FirstName']},\n\n" .
                           "Welcome to the active transportation revolution!\n\n" .
                           "{$createdByInfo['FirstName']} {$createdByInfo['LastName']} has created a RideNet account " .
                           "for you as part of \"{$newRiderInfo['TeamName']}\" RideNet team. RideNet was setup in conjunction with " .
                           "Consider Biking's 2 BY 2012 program to promote bicycling as a viable form of transportation " .
                           "in Central Ohio and to track how many miles people are riding. Each 2 BY 2012 " .
                           "participating organization has their own RideNet team where riders log their bicycling miles. " .
                           "You can login to your account using your email address and temporary password.\n\n" .
                           "Email Address: {$newRiderInfo['RiderEmail']}\n" .
                           "Password: live2ride\n\n" .
                           "Homepage: $teamURL\n\n" .
                           "Some things you can do on RideNet:\n" .
                           "- Update your rider bio. Click \"Edit Profile\" at the top of Your Profile page\n" .
                           "- Log some rides. Click \"+ Log A Ride\" on Your Profile page\n" .
                           "- Find other riders/teams. Choose \"Find\" from the main menu.\n\n" .
                           "If you have any questions about your membership, email your team admin: {$createdByInfo['FirstName']} " .
                           "{$createdByInfo['LastName']} {$createdByInfo['RiderEmail']}\n\n" .
                           "The goal of 2 BY 2012 is for each citizen of central Ohio to bicycle to work or school at " .
                           "least two days per month by the Columbus Bicentennial in 2012. Consider Biking, the local " .
                           "bicycle advocacy non-profit organization, offers the 2 BY 2012 program to help companies design " .
                           "bike to work programs. The 2 BY 2012 program provides a host of proven tools, customized consulting, " .
                           "and ongoing support that get more people on bicycles - and help the business bottom-line. Your team " .
                           "website is one of these proven tools.\n\n" .
                           "For more information please go to http://www.2by2012.com or email Bryan Saums, Consider Biking's " .
                           "2 BY 2012 Program Manager at bryan@considerbiking.com\n\n" .
                           "Remember: Ride and record those miles!\n\n" .
                           "Sincerely,\n\n" .
                           "The Consider Biking 2 BY 2012 team\n\n" .
                           "2 BY 2012 is made possible by the Robert Bartels, William C. and Naoma W. Denison, Charlotte R. Hallel, " .
                           "Robert B. Hurst and Martha G. Staub funds of the The Columbus Foundation.";
                    break;
            }
        }
        $to = $newRiderInfo['FirstName'] . " " . $newRiderInfo['LastName'] . " <" . $newRiderInfo['RiderEmail'] . ">";
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
                               WHERE CalendarID={$post['CalendarID']} AND Notify=1 AND RiderID<>{$post['RiderID']}", __FILE__, __LINE__);
    $subject = "Ride Update - {$post['EventName']}";
    while(($recipient = $recipients->fetch_array())!=false)
    {
        $to = $recipient['RiderName'] . " <" . $recipient['RiderEmail'] . ">";
        $msg = "{$post['RiderName']} posted an update to {$post['EventName']}:\n\n" .
               "\"{$post['Text']}\"\n\n" .
               "You received this email because you checked \"Email me ride updates\" on the ride information page. " .
               "For details go to the ride information page: " .
               BuildTeamBaseURL($recipient['Domain']) . "/calendar-detail.php?CID={$post['CalendarID']}";
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
                               WHERE RaceID={$post['RaceID']} AND Notify=1 AND RiderID<>{$post['RiderID']}", __FILE__, __LINE__);
    $subject = "Event Update - {$post['EventName']}";
    while(($recipient = $recipients->fetch_array())!=false)
    {
        $to = $recipient['RiderName'] . " <" . $recipient['RiderEmail'] . ">";
        $msg = "{$post['RiderName']} posted an update to {$post['EventName']}:\n\n" .
               "\"{$post['Text']}\"\n\n" .
               "You received this email because you checked \"Email me event updates\" on the event information page. " .
               "For details go to the event information page: " .
               BuildTeamBaseURL($recipient['Domain']) . "/event-detail.php?RaceID={$post['RaceID']}";
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