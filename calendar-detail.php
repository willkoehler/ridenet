<?
require("script/app-master.php");
require("dynamic-sections/calendar-updates.php");
require("dynamic-sections/calendar-wall.php");
require("dynamic-sections/calendar-attendance.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
RecordPageView($oDB);
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
$CalendarUpdatesLength = 30;
$CalendarWallLength = 30;

// --- Get calendar filter zip code from cookies.
$CalendarFilterRange = isset($_COOKIE['CalendarFilterRange']) ? $_COOKIE['CalendarFilterRange'] : 2500;
$CalendarFilterZip = isset($_COOKIE['CalendarFilterZip']) ? $_COOKIE['CalendarFilterZip'] : 43210;
$rs = $oDB->query("SELECT *, CONCAT(City, ', ', State, ' ', ZipCode) AS ZipCodeText
                   FROM ref_zipcodes WHERE ZipCodeID=" . IntVal($CalendarFilterZip));
$record = $rs->fetch_array();
$CalendarLongitude = ($record==false) ? 0 : $record['Longitude'];
$CalendarLatitude = ($record==false) ? 0 : $record['Latitude'];
$ZipCodeText = ($record==false) ? "(unknown)" : $record['ZipCodeText'];

$calendarID = SmartGetInt("CID");

// Get name of the ride
$rideName = $oDB->DBLookup("EventName", "calendar", "CalendarID=$calendarID");
$rideComments = $oDB->DBLookup("Comments", "calendar", "CalendarID=$calendarID");
$rideDate = $oDB->DBLookup("CalendarDate", "calendar", "CalendarID=$calendarID");

// Get attendance information for the current user
$attendanceID = $oDB->DBLookup("AttendanceID", "calendar_attendance", "CalendarID=$calendarID AND RiderID=" . GetUserID(), -1);
$attending = $oDB->DBLookup("Attending", "calendar_attendance", "AttendanceID=$attendanceID", 0);
$notify = $oDB->DBLookup("Notify", "calendar_attendance", "AttendanceID=$attendanceID", 0);

// Has anyone posted a ride log for today?
//$ridesLogged = $oDB->DBCount("ride_log LEFT JOIN calendar_attendance USING (RiderID) LEFT JOIN calendar USING (CalendarID)",
//                             "CalendarID=$calendarID AND ride_log.Date = DATE(calendar.CalendarDate) AND Attending=1");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, 0, $rideName)?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("calendar-detail.js")?>
  <?MinifyAndInclude("dialogs/calendar-event-dialog.js")?>
  <?MinifyAndInclude("dialogs/post-update-dialog.js")?>
  <?MinifyAndInclude("dialogs/location-dialog.js")?>
  <?MinifyAndInclude("script/ridenet-helpers.js")?>
  <script type="text/javascript">
    g_calendarWallLength = <?=$CalendarWallLength?>;
    g_calendarUpdatesLength = <?=$CalendarUpdatesLength?>;
    g_calendarID = <?=$calendarID?>;
    g_domainRoot="<?=GetDomainRoot()?>";
    g_fullDomainRoot="<?=GetFullDomainRoot()?>";
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
<!-- facebook meta tags to provide information for the like button -->
  <meta property="og:title" content="<?=htmlentities($rideName)?>" />
  <meta property="og:image" content="http://ridenet.net/images/ridenet-fb-logo3.png" />
  <meta property="og:site_name" content="RideNet" />
  <meta property="og:description" content="<?=htmlentities($rideComments)?>" />
  <meta property="fb:app_id" content="147642135282357" />
</head>

<body class="oneColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "Calendar")?>
  </div>
  
  <div id="mainContent">
    <div style="float:left">
      <h1><?=date_create($rideDate)->format("l F j, Y")?></h1>
    </div>
    <div style="float:left;margin-left:10px;position:relative;left:0px;top:12px">
      <?SocialMediaButtons("Join us: " . $rideName)?>
    </div>
    <div class='clearfloat'></div>

<?  $vDate = SmartGetDate("Date");
    $sql = "SELECT CalendarID, EventName, Location, CalendarDate, ClassX, ClassA, ClassB, ClassC, ClassD, Comments, MapURL,
                   FirstName, LastName, RiderID, AddedBy, c.Archived,
                   postedTeam.TeamName AS PostedTeamName, postedTeam.Domain AS PostedDomain,
                   CONCAT(City, ', ', State, ' ', ZipCode) AS GeneralArea,
                   CalculateDistance(Longitude, Latitude, $CalendarLongitude, $CalendarLatitude) AS Distance,
                   (TIMESTAMPDIFF(HOUR, CalendarDate, NOW())) AS EventAgeHours
            FROM calendar c
            LEFT JOIN rider r ON (c.AddedBy = r.RiderID)
            LEFT JOIN teams postedTeam ON (c.TeamID = postedTeam.TeamID)
            LEFT JOIN ref_intensity USING (IntensityID)
            LEFT JOIN ref_zipcodes USING (ZipCodeID) 
            WHERE CalendarID=$calendarID";
    $rs = $oDB->query($sql, __FILE__, __LINE__);
    if(($record=$rs->fetch_array())==false)
    {
      exit ("Invalid Ride ID");
    }
    elseif($record['Archived'])
    {
      exit ("This ride has been deleted");
    }
    else
    { 
      $attendanceOpen = ($record['EventAgeHours'] < 168);   // don't allow attendance to be changed 7 days after the ride ?>
      <div style="height:10px"><!--vertical spacer--></div>
      <div class="block-table centered" id="ride-details" style="width:500px;position:relative">
        <!--- Edit button. Allow ride to be edit for 12 hours after the ride -->
        <?if($record['EventAgeHours'] < 12 && ($record['AddedBy']==GetUserID() || isSystemAdmin())) {?>
          <span class='action-btn' id='edit-btn<?=$record['CalendarID']?>' style="position:absolute;width:50px;top:19px;left:480px" onclick="clickEditRide(<?=$record['CalendarID']?>);" title="Edit this ride">Edit Ride</span>
        <? } ?>
        <div class="header">
          <?=$record['EventName']?>
        </div>
        <table id="event-detail" cellpadding=0 cellspacing=0 width=100%>
          <?if($record['Distance']!="") { ?>
          <tr>
            <td class="label" width=108>General&nbsp;Area:</td>
            <td class="text" width=384><?=$record['GeneralArea']?> (<?=number_format($record['Distance'],0)?> miles from <a id="your-location" href="javascript:g_locationDialog.show({ ypos:100, animateTarget: 'your-location', zipCodeText: '<?=$ZipCodeText?>', zipCode: '<?=$CalendarFilterZip?>', range: '<?=$CalendarFilterRange?>' })">your location</a>)</td>
          </tr>
          <? } ?>
          <tr>
            <td class="label">Meet&nbsp;At:</td>
            <td class="text"><?=$record['Location']?></td>
          </tr>
          <tr>
            <td class="label">Time & Date:</td>
            <td class="text"><?=date_create($record['CalendarDate'])->format("g:i a - l, F j")?></td>
          </tr>
          <tr>
            <td class="label" valign=top><b><a href="#class-key">*</a>&nbsp;Ride&nbsp;Classes:</td>
            <td class="text" style="font-weight:bold;font-family:courier new;"><?=BuildRideClass($record)?></td>
          </tr>
          <?if($record['MapURL']!="") { ?>
          <tr>
            <td class="label" valign="top">Route&nbsp;Map:</td>
            <td class="text"><a href="<?=$record['MapURL']?>" target="_blank"><?=LimitString($record['MapURL'],55)?></a></td>
          </tr>
          <? } ?>
          <?if($record['Comments']!="") { ?>
          <tr>
            <td class="label" valign="top">Comments:</td>
            <td class="text"><?=$record['Comments']?></td>
          </tr>
          <? } ?>
          <tr>
            <td class="label">Posted&nbsp;By:</td>
            <td class="text">
              <a href="<?=BuildTeamBaseURL($record['PostedDomain'])?>/profile.php?RiderID=<?=$record['RiderID']?>">
                <?=$record['FirstName'] . " " . $record['LastName']?>
              </a>&nbsp;-&nbsp;
              <a href="<?=BuildTeamBaseURL($record['PostedDomain'])?>/home.php">
                <b><?=$record['PostedTeamName']?></b>
              </a>
            </td>
          </tr>
          <!-- List of who's going -->
          <tr <?if($oDB->DBCount("calendar_attendance", "CalendarID=$calendarID AND (Attending=1 OR Notify=1)")==0) { ?> style="display:none" <? } ?>>
            <td class="label" valign="top" style="padding-top:15px">
              (<span style="font-size:9px;padding:1px 2px;background-color:#E5D40E">I</span>)&nbsp;I'll&nbsp;be&nbsp;there:<br>
              (<span style="font-size:9px;padding:1px 2px;background-color:#33A53A">W</span>)&nbsp;watching:
            </td>
            <td class="text" style="padding-top:8px">
              <div id="attending-holder" class="commute-ride-group">
                <?=RenderAttendingRiders($oDB, $calendarID);?>
              </div>
            </td>
          </tr>
          <? if($attendanceOpen) { ?>
            <tr><td class="table-spacer" style="height:12px" colspan=2>&nbsp;</td></tr>
            <tr><td colspan=2>
              <div class="centered" style="width:440px;border-top:1px solid #AAA">
                <? if(!CheckLogin()) { ?>
                  <div style="text-align:center;padding: 10px 0 5px 0">
                    <span class="label">Are you going?</span>
                    <span class="text"><a href="login.php?T=<?=$pt?>&Goto=<?=urlencode($_SERVER['REQUEST_URI'])?>">[ Login Required ]</a></span>
                  </div>
                <? } else { ?>
                  <div id='form-holder'></div>
                  <!-- "Are You Going?" Attendance form -->
                  <script type="text/javascript">
                    new C_Attendance().create({
                      parent:'form-holder',
                      attendanceID: <?=$attendanceID?>,
                      calendarID: <?=$calendarID?>,
                      attending: <?=$attending?>,
                      notify: <?=$notify?>,
                      loggedIn: <?=CheckLogin() ? 1 : 0?>
                    });
                  </script>
                <? } ?>
              </div>
            </td></tr>
          <? } ?>
        </table>
      </div>
    <? } ?>
    <div style="height:25px"><!--vertical spacer--></div>

    <?if($record['EventAgeHours'] < 2) { ?>
    <!-- Ride Updates. Display ride updates until 2 hours after the ride -->
      <div class="centered" style="width:550px">
<?      if(CheckLogin())
        { 
          $rs = $oDB->query("SELECT RiderID, RacingTeamID, CONCAT(FirstName, ' ', LastName) AS RiderName, TeamName
                             FROM rider LEFT JOIN teams ON (RacingTeamID = TeamID)
                             WHERE RiderID=" . GetUserID(), __FILE__, __LINE__);
          $loggedInRider = $rs->fetch_array();
          $rs->free();?>
          <div style="float:right;position:relative;top:6px;" class='action-btn' id='post-message-btn' onclick="clickPostUpdate(this.id, { riderID:<?=$loggedInRider['RiderID']?>, racingTeamID: <?=$loggedInRider['RacingTeamID']?>, postedToID: <?=$calendarID?>, riderName: '<?=htmlentities(addslashes($loggedInRider['RiderName']))?>', teamName: '<?=htmlentities(addslashes($loggedInRider['TeamName']))?>', postingTo: '<?=htmlentities(addslashes($record['EventName']))?>' });">
            + Post Update
          </div>
        <? } else { ?>
          <div style="float:right;position:relative;top:6px;" class='action-btn' onclick="window.location.href='login.php?Goto=<?=urlencode($_SERVER['REQUEST_URI'])?>'">&nbsp;Login To Post Update&nbsp;</div>
        <? } ?>
        <div style="padding:5px;border-bottom:1px dotted #CCC;border-top:1px dotted #CCC">
          <h2 style="margin:0px">Ride Updates</h2>
          <div class="team-board-instructions">
            Ride updates will be emailed to everyone attending or watching this ride
          </div>
        </div>
        <div class="clearfloat" style="height:1px"></div>
        <div id='calendar-updates' class='ridenet-wall' style="padding:0 50px 0 25px ">
          <? RenderCalendarUpdates($oDB, $calendarID, $CalendarUpdatesLength) ?>
        </div>
      </div>
    <? } else { ?>
    <!-- What people said about the ride. Make this visible 2 hours after the ride -->
      <div class="centered" style="width:550px">
        <div style="padding:5px;border-bottom:1px dotted #CCC;border-top:1px dotted #CCC">
          <h2 style="margin:0px">What People Said About This Ride</h2>
          <? if($attendanceOpen) { ?>
            <div class="team-board-instructions">
              To share a ride comment here&nbsp;&nbsp;1. Check "I'll be there" (above)
              &nbsp;&nbsp;2. Go to <a href="profile.php">Your Profile</a> and log a ride on <?=date_create($rideDate)->format("n/j/Y")?>
            </div>
          <? } ?>
        </div>
        <div class="clearfloat" style="height:1px"></div>
        <div id='calendar-wall' class='ridenet-wall' style="padding:0 50px 0 25px ">
          <? RenderCalendarWall($oDB, $calendarID, $CalendarWallLength) ?>
        </div>
      </div>
      <div style="height:25px"><!--vertical spacer--></div>
    <? } ?>

    <div style="height:20px"><!--vertical spacer--></div>
    <?InsertRideClassKey()?>

  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>