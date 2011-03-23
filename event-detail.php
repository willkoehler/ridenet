<?
require("script/app-master.php");
require("dynamic-sections/event-updates.php");
require("dynamic-sections/event-attendance.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
RecordPageView($oDB);
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
$EventUpdatesLength = 30;

$raceID = SmartGetInt("RaceID");

// Get name of the event
$eventName = $oDB->DBLookup("EventName", "event", "RaceID=$raceID");
$eventDate = $oDB->DBLookup("RaceDate", "event", "RaceID=$raceID");

// Get attendance information for the current user
$attendanceID = $oDB->DBLookup("AttendanceID", "event_attendance", "RaceID=$raceID AND RiderID=" . GetUserID(), -1);
$attending = $oDB->DBLookup("Attending", "event_attendance", "AttendanceID=$attendanceID", 0);
$notify = $oDB->DBLookup("Notify", "event_attendance", "AttendanceID=$attendanceID", 0);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, 0, $eventName)?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("event-detail.js")?>
  <?MinifyAndInclude("dialogs/schedule-event-dialog.js")?>
  <?MinifyAndInclude("dialogs/post-update-dialog.js")?>
  <?MinifyAndInclude("script/ridenet-helpers.js")?>
  <script type="text/javascript">
    g_eventUpdatesLength = <?=$EventUpdatesLength?>;
    g_raceID = <?=$raceID?>;
    g_domainRoot="<?=GetDomainRoot()?>";
    g_fullDomainRoot="<?=GetFullDomainRoot()?>";
    stateLookup = <?$oDB->DumpToJSArray("SELECT StateID, StateName, StateAbbr FROM ref_states ORDER BY StateName")?>
    eventTypeLookup = <?$oDB->DumpToJSArray("SELECT RideTypeID, RideType, Picture FROM ref_event_type ORDER BY RideType")?>
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
</head>

<body class="oneColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "Event")?>
  </div>
  
  <div id="mainContent">
    <h1><?=date_create($eventDate)->format("l F j, Y")?></h1>

<?  $vDate = SmartGetDate("Date");
    $sql = "SELECT RaceID, RaceDate, EventName, WebPage, RideType, City, StateAbbr, AddedBy,
                   (TIMESTAMPDIFF(DAY, RaceDate, NOW())) AS EventAgeDays, Archived
            FROM event
            LEFT JOIN ref_states USING (StateID)
            LEFT JOIN ref_event_type USING (RideTypeID)
            WHERE RaceID=$raceID";
    $rs = $oDB->query($sql, __FILE__, __LINE__);
    if(($record=$rs->fetch_array())==false)
    {
      exit ("Invalid Event ID");
    }
    elseif($record['Archived'])
    {
      exit ("This event has been deleted");
    }
    else
    { 
      $attendanceOpen = ($record['EventAgeDays'] < 1);   // don't allow attendance to be changed 1 day after the event ?>
      <div style="height:10px"><!--vertical spacer--></div>
      <div class="block-table centered" id="ride-details" style="width:500px;position:relative">
        <!--- Edit button. Allow event to be edit for 1 day after the event -->
        <?if($record['EventAgeDays'] < 1 && ($record['AddedBy']==GetUserID() || isSystemAdmin())) {?>
          <span class='action-btn' id='edit-btn<?=$record['RaceID']?>' style="position:absolute;width:60px;top:19px;left:470px" onclick="clickEditEvent(<?=$record['RaceID']?>);" title="Edit this event">Edit Event</span>
        <? } ?>
        <div class="header">
          <?=$record['EventName']?>
        </div>
        <table id="event-detail" cellpadding=0 cellspacing=0 width=100%>
          <tr>
            <td class="label" width=108>Date:</td>
            <td class="text" width=384><?=date_create($record['RaceDate'])->format("l F j, Y")?></td>
          </tr>
          <tr>
            <td class="label">Location:</td>
            <td class="text"><?=$record['City']?>, <?=$record['StateAbbr']?></td>
          </tr>
          <tr>
            <td class="label">Even&nbsp;Type:</td>
            <td class="text"><?=$record['RideType']?></td>
          </tr>
          <tr>
            <td class="label">Event Website:</td>
            <td class="text"><a href="http://<?=$record['WebPage']?>" target="_blank"><?=LimitString("http://" . $record['WebPage'],55)?></a></td>
          </tr>
          <!-- List of who's going -->
          <tr <?if($oDB->DBCount("event_attendance", "RaceID=$raceID AND (Attending=1 OR Notify=1)")==0) { ?> style="display:none" <? } ?>>
            <td class="label" valign="top" style="padding-top:15px">
              (<span style="font-size:9px;padding:1px 2px;background-color:#E5D40E">I</span>)&nbsp;I'll&nbsp;be&nbsp;there:<br>
              (<span style="font-size:9px;padding:1px 2px;background-color:#33A53A">W</span>)&nbsp;watching:
            </td>
            <td class="text" style="padding-top:8px">
              <div id="attending-holder" class="commute-ride-group">
                <?=RenderAttendingRiders($oDB, $raceID);?>
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
                      raceID: <?=$raceID?>,
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

    <div class="centered" style="width:550px">
<?    if(CheckLogin())
      { 
        $rs = $oDB->query("SELECT RiderID, RacingTeamID, CONCAT(FirstName, ' ', LastName) AS RiderName, TeamName
                           FROM rider LEFT JOIN teams ON (RacingTeamID = TeamID)
                           WHERE RiderID=" . GetUserID(), __FILE__, __LINE__);
        $loggedInRider = $rs->fetch_array();
        $rs->free();?>
        <div style="float:right;position:relative;top:6px;" class='action-btn' id='post-message-btn' onclick="clickPostUpdate(this.id, { riderID:<?=$loggedInRider['RiderID']?>, racingTeamID: <?=$loggedInRider['RacingTeamID']?>, postedToID: <?=$raceID?>, riderName: '<?=htmlentities(addslashes($loggedInRider['RiderName']))?>', teamName: '<?=htmlentities(addslashes($loggedInRider['TeamName']))?>', postingTo: '<?=htmlentities(addslashes($record['EventName']))?>' });">
          + Post Update
        </div>
      <? } else { ?>
        <div style="float:right;position:relative;top:6px;" class='action-btn' onclick="window.location.href='login.php?Goto=<?=urlencode($_SERVER['REQUEST_URI'])?>'">&nbsp;Login To Post Update&nbsp;</div>
      <? } ?>
      <div style="padding:5px;border-bottom:1px dotted #CCC;border-top:1px dotted #CCC">
        <h2 style="margin:0px">Event Updates</h2>
        <div class="team-board-instructions">
          Event updates will be emailed to everyone attending or watching this event
        </div>
      </div>
      <div class="clearfloat" style="height:1px"></div>
      <div id='event-updates' class='ridenet-wall' style="padding:0 50px 0 25px ">
        <? RenderEventUpdates($oDB, $raceID, $EventUpdatesLength) ?>
      </div>
    </div>

  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>