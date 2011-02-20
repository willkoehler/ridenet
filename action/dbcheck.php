<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented

$email = SmartGetString("id");
$password = SmartGet("pw");
$stayLoggedIn = SmartGetCheckbox("StayLoggedIn");
$gotoParam = isset($_REQUEST['Goto']) ? "Goto=" . urlencode($_REQUEST['Goto']) : "";

// --- Save user's email in cookie for next time user visits login page. Set cookie's domain to our
// --- domain root (i.e. ridenet.org) so it will be valid for all subdomains (i.e. echeloncycling.ridenet.org)
setcookie("RiderEmail", $_REQUEST['id'], time() + (60*60*24)*60, "/", GetDomainRoot(), NULL, TRUE);  // TRUE --> set HTTPOnly flag

if($email == "NULL")
{
    $_SESSION['logonmsg'] = "INVALID USER ID OR PASSWORD, PLEASE RE-ENTER";
    $_SESSION['lcheck'] = 0;
    header("Location: ../login.php?$gotoParam");
    exit();
}
else
{
    // Get row matching email address (this should be unique)
    $sql= "SELECT RiderID, Password, FirstName, LastName, CommutingTeamID, RacingTeamID, IFNULL(MustChangePW,0) AS MustChangePW,
                  sSystemAdmin, sDesigner
           FROM rider WHERE RiderEmail=$email AND Archived=0";
    $rs = $oDB->query($sql, __FILE__, __LINE__);
    if(($record = $rs->fetch_array())==false)
    {
        // No matches, login failed
        $_SESSION['logonmsg'] = "INVALID USER ID OR PASSWORD, PLEASE RE-ENTER";
        $_SESSION['lcheck'] = 0;
        header("Location: ../login.php?$gotoParam");
        exit();
    }
    elseif(CheckPassword($password, $record['Password'])==false)
    {
        // Password doesn't match, login failed
        $_SESSION['logonmsg'] = "INVALID USER ID OR PASSWORD, PLEASE RE-ENTER";
        $_SESSION['lcheck'] = 0;
        header("Location: ../login.php?$gotoParam");
        exit();
    }
    else
    {
        // -- record login
        $oDB->query("INSERT INTO logins (IPAddress, HTTP_USER_AGENT, Browser, RiderID, LoginDate) VALUES (" . 
                    "'" . $_SERVER['REMOTE_ADDR'] . "', " .
                    "'" . substr($_SERVER['HTTP_USER_AGENT'], 0, 200) . "', " .
                    "'" . GetBrowserString($_SERVER['HTTP_USER_AGENT']) . "', " .
                    $record['RiderID'] . ", " .
                    "NOW())", __FILE__, __LINE__);
        $_SESSION['loginTableID'] = $oDB->insert_id;

        // --- store user info in session
        $_SESSION['lcheck'] = $record['RiderID'];
        $_SESSION['RiderName'] = $record['FirstName'] . " " . $record['LastName'];
        $_SESSION['MustChangePW'] = $record['MustChangePW'];

        // --- check security level
        $_SESSION['sSystemAdmin'] = (is_null($record['sSystemAdmin'])) ? 0 : $record['sSystemAdmin'];
        $_SESSION['sDesigner'] = (is_null($record['sDesigner'])) ? 0 : $record['sDesigner'];

        // --- if StayLoggedIn is checked, preserve session after browser closes
        $_SESSION['KeepOpen'] = $stayLoggedIn;

        if(isset($_REQUEST['Goto']))
        {
          // goto page passed in the "Goto" query parameter
          header("Location: " . $_REQUEST['Goto']);
          exit();
        }
        else
        {
          // goto riders' profile page
          header("Location: ../profile.php");
          exit();
        }
    }
}
?>