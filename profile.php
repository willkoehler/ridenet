<?
require("script/app-master.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");
require("dynamic-sections/ride-log.php");

$oDB = oOpenDBConnection();
RecordPageView($oDB);
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
$organizationID = $oDB->DBLookup("OrganizationID", "teams", "TeamID=$pt", 0);
$RideLogLength = 8;

// if race year is passed in use it. Otherwise default to current year
$ShowYear = (isset($_REQUEST['Year'])) ? SmartGetInt("Year") : CURRENT_YEAR;

if(!isset($_REQUEST['RiderID']))
{
  // No rider ID was provided, display profile of currently logged in rider, check login and turn on edit mode
  CheckLoginAndRedirect();
  $RiderID = GetUserID();   // ignore RiderID and show profile of logged in rider
  $editable = true;
  // Redirect rider to their team page if they're not already on it.  //!!!! Ask rider which team they want?
  $teamInfo = GetRiderTeamInfo($oDB, $RiderID);
  if($teamInfo['CommutingTeamID']!=$pt && $teamInfo['RacingTeamID']!=$pt)
  {
    $Domain = $oDB->DBLookup("Domain", "teams", "TeamID=" . $teamInfo['RacingTeamID']);
    header("Location: " . BuildTeamBaseURL($Domain) . "/profile.php");
    exit();
  }
}
else
{
  $RiderID = SmartGetInt("RiderID");
  $editable=false;
}
// --- get rider information
$rs = $oDB->query("SELECT RiderType, CONCAT(FirstName, ' ', LastName) AS RiderName, YearsCycling, Height, Weight,
                          BornIn, ResideIn, Occupation, FavoriteFood, FavoriteRide, FavoriteQuote, WhyIRide,
                          MyCommute, URL, CommuteMapURL, RacingTeamID, CommutingTeamID, YTDMiles, CEDaysMonth, CMilesDay,
                          tr.TeamID AS RacingTeamID, tr.TeamName AS RacingTeamName, tr.Domain AS RacingDomain,
                          tc.TeamID AS CommutingTeamID, tc.TeamName AS CommutingTeamName, tc.Domain AS CommutingDomain,   
                          FLOOR(DATEDIFF(NOW(), DateOfBirth) / 365.25) AS Age
                   FROM rider
                   LEFT JOIN teams tr ON (RacingTeamID = tr.TeamID)
                   LEFT JOIN teams tc ON (CommutingTeamID = tc.TeamID)
                   LEFT JOIN ref_rider_type USING (RiderTypeID)
                   WHERE RiderID=$RiderID", __FILE__, __LINE__);
if(($riderInfo = $rs->fetch_array())==false)
{
    exit("Invalid RiderID<br><br><br>");
}
$rs->free();
$ridersRacingTeamID=$riderInfo['RacingTeamID'];   // photos are always based on rider's RacingTeamID
$riderName = $riderInfo['RiderName'];

// --- increment rider view count
// exclude visits from web bots, multiple visits per session, and don't count self views
if(!DetectBot() && !isset($_SESSION['RiderView' . $RiderID]) && $RiderID!=GetUserID())
{
  $oDB->query("INSERT INTO rider_view_log (RiderID, DateViewed) VALUES ($RiderID, NOW())", __FILE__, __LINE__);
  $_SESSION['RiderView' . $RiderID] = true;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, $pt, $editable ? "Your Profile" : $riderName)?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("profile.js")?>
  <?MinifyAndInclude("dialogs/profile-dialog.js")?>
  <?MinifyAndInclude("dialogs/ride-log-dialog.js")?>
  <?MinifyAndInclude("dialogs/change-teams-dialog.js")?>
  <?MinifyAndInclude("script/ridenet-helpers.js")?>
<!-- file upload field -->
  <?echo "<script type='text/javascript' src='" . EXTBASE_URL . "examples/ux/fileuploadfield/FileUploadField.js'></script>\n";?>
  <?echo "<link rel='stylesheet' type='text/css' href='" . EXTBASE_URL . "examples/ux/fileuploadfield/css/fileuploadfield.css'>\n";?>
<!-- Build javascript arrays for local/static combobox lookups -->
  <script type="text/javascript">
    <?SessionToJS()?>
    g_pt=<?=$pt?>;
    riderTypeLookup = <?$oDB->DumpToJSArray("SELECT RiderTypeID, RiderType FROM ref_rider_type ORDER BY Sort")?>
    rideLogTypeLookup = <?$oDB->DumpToJSArray("SELECT RideLogTypeID, RideLogType, RideLogTypeImage, RideLogDescription
                                               FROM ref_ride_log_type
                                               ORDER BY Sort")?>
    weatherLookup = <?$oDB->DumpToJSArray("SELECT WeatherID, Weather, WeatherImage FROM ref_weather ORDER BY Sort")?>
    g_rideLogLength = <?=$RideLogLength?>;
    g_riderID = <?=$RiderID?>;
    g_editable = <?=($editable) ? 'true' : 'false'?>;
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
<!-- facebook meta tags to provide information for the like button -->
  <meta property="og:title" content="<?=htmlentities($riderName)?>'s Rider Bio | <?=htmlentities($riderInfo['RacingTeamName'])?>" />
  <meta property="og:image" content="<?=GetFullDomainRoot()?>/dynamic-images/rider-portrait.php?RiderID=<?=$RiderID?>&T=<?=$ridersRacingTeamID?>" />
  <meta property="og:site_name" content="RideNet" />
  <meta property="og:description" content="Visit RideNet to see <?=htmlentities($riderName)?>'s rider bio including race results and ride log" />
  <meta property="fb:app_id" content="147642135282357" />
</head>

<body class="oneColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
<?  if($editable)
    {
      InsertMainMenu($oDB, $pt, "YourProfile");
      InsertMemberMenu($oDB, $pt, "YourProfile");
    }
    else
    {
      InsertMainMenu($oDB, $pt, "Roster");
    }?>
  </div>
  
  <div id="mainContent">
    <?if($editable) {?>
      <div style="float:left">
        <h1>Your Profile</h1>
      </div>
      <div style="float:left;margin-left:10px;position:relative;top:13px">
        <?SocialMediaButtons("I'm on RideNet. Check out my profile.", GetBaseHref() . "profile.php?RiderID=$RiderID")?>
      </div>
    <? } else { ?>
      <div style="float:left">
        <h1>Rider Profile</h1>
      </div>
      <div style="float:left;margin-left:10px;position:relative;top:13px">
        <?SocialMediaButtons("See $riderName on RideNet")?>
      </div>
    <? } ?>
    <div class='clearfloat'></div>

    <div align=center>
      <div class=block-table2 style="position:relative;width:680px;">
        <?if($editable) { ?>
          <span class='action-btn' id='edit-profile' style="position:absolute;width:60px;top:-21px;left:620px" onclick="clickEditProfile();">Edit Profile</span>
        <? } ?>
        <table cellpadding="0" cellspacing="0" width=100%>
          <tr>
            <td style="padding:0px 5px" valign="top" width=165>
              <img src="<?=GetFullDomainRoot()?>/dynamic-images/rider-portrait.php?RiderID=<?=$RiderID?>&T=<?=$ridersRacingTeamID?>" height=200 width=160/>
            </td>
            <td valign=top>
              <div id='more-content1'>
                <div class=header>
                  <?=$riderName?><?=(is_null($riderInfo['RiderType'])) ? "" : ": " . $riderInfo['RiderType']?>
                </div>
                <table id="profile" cellspacing=0 cellpadding=0 width=100%>
                  <?if($riderInfo['RacingTeamID']==$riderInfo['CommutingTeamID']) { ?>
                    <tr>
                      <td class=label valign=top width=110>Team:</td>
                      <td class=text><a href="<?=BuildTeamBaseURL($riderInfo['RacingDomain'])?>/home.php"><?=$riderInfo['RacingTeamName']?></a></td> <!--!!!!Need to list both teams-->
                    </tr>
                  <? } else { ?>
                    <tr>
                      <td class=label valign=top width=110>Racing Team:</td>
                      <td class=text><a href="<?=BuildTeamBaseURL($riderInfo['RacingDomain'])?>/home.php"><?=$riderInfo['RacingTeamName']?></a></td> <!--!!!!Need to list both teams-->
                    </tr>
                    <tr>
                      <td class=label valign=top width=110>Commuting Team:</td>
                      <td class=text><a href="<?=BuildTeamBaseURL($riderInfo['CommutingDomain'])?>/home.php"><?=$riderInfo['CommutingTeamName']?></a></td> <!--!!!!Need to list both teams-->
                    </tr>
                  <? } ?>
                  <tr><td class="table-spacer" colspan=2 style="height:5px">&nbsp;</td></tr>
                  <?if(!is_null($riderInfo['Age']) && !is_null($riderInfo['YearsCycling'])) { ?>
                    <tr><td colspan=2 align=left>
                      <table cellspacing=0 cellpadding=0>
                        <tr>
                          <td class=label width=110>Years Cycling:</td>
                          <td class=text width=30><?=$riderInfo['YearsCycling']?></td>
                          <td class=label width=65>Age:</td>
                          <td class=text><?=$riderInfo['Age']?></td>
                        </tr>
                      </table>
                    </td></tr>
                  <? } ?>
                  <?if(!is_null($riderInfo['Height']) && !is_null($riderInfo['Weight'])) { ?>
                    <tr><td colspan=2 align=left>
                      <table cellspacing=0 cellpadding=0>
                        <tr>
                          <td class=label width=110>Height:</td>
                          <td class=text width=30><?=$riderInfo['Height']?></td>
                          <td class=label width=65>Weight:</td>
                          <td class=text><?=$riderInfo['Weight']?></td>
                        </tr>
                      </table>
                    </td></tr>
                  <? } ?>
                  <?if($riderInfo['YTDMiles'] || $riderInfo['CEDaysMonth']) { ?>
                    <tr><td colspan=2 align=left>
                      <table cellspacing=0 cellpadding=0>
                        <tr>
                          <td class=label width=110>
                            <img class="tight" src="images/ridelog/commute.png" style="position:relative;top:-1px" height=12>
                            <img class="tight" src="images/ridelog/errand.png" style="position:relative;top:-1px" height=12>
                            Days/Month:
                          </td>
                          <td class=text id='cedays-month' width=30><?=$riderInfo['CEDaysMonth']?></td>
                          <td class=label width=65>Miles YTD:</td>
                          <td class=text id='ytd-miles'><?=($riderInfo['YTDMiles'])?></td>
                        </tr>
                      </table>
                    </td></tr>
                  <? } ?>
                  <tr><td class="table-spacer" colspan=2 style="height:5px">&nbsp;</td></tr>
                  <?if(!is_null($riderInfo['BornIn'])) { ?>
                    <tr>
                      <td class=label valign=top>Born:</td>
                      <td class=text><?=$riderInfo['BornIn']?></td>
                    </tr>
                  <? } ?>
                  <?if(!is_null($riderInfo['ResideIn'])) { ?>
                    <tr>
                      <td class=label valign=top>Resides:</td>
                      <td class=text><?=$riderInfo['ResideIn']?></td>
                    </tr>
                  <? } ?>
                  <?if(!is_null($riderInfo['Occupation'])) { ?>
                    <tr>
                      <td class=label valign=top>Occupation:</td>
                      <td class=text><?=$riderInfo['Occupation']?></td>
                    </tr>
                  <? } ?>
                  <?if(!is_null($riderInfo['FavoriteFood'])) { ?>
                    <tr>
                      <td class=label valign=top>Favorite Food:</td>
                      <td class=text><?=$riderInfo['FavoriteFood']?></td>
                    </tr>
                  <? } ?>
                  <?if(!is_null($riderInfo['FavoriteRide'])) { ?>
                    <tr>
                      <td class=label valign=top valign=top>Favorite Ride:</td>
                      <td class=text><?=$riderInfo['FavoriteRide']?></td>
                    </tr>
                  <? } ?>
                  <?if(!is_null($riderInfo['FavoriteQuote'])) { ?>
                    <tr>
                      <td class=label valign=top>Favorite Quote:</td>
                      <td class=text><?=$riderInfo['FavoriteQuote']?></td>
                    </tr>
                  <? } ?>
                  <?if(!is_null($riderInfo['WhyIRide'])) { ?>
                    <tr>
                      <td class=label valign=top valign=top>Why I Ride:</td>
                      <td class=text><?=$riderInfo['WhyIRide']?></td>
                    </tr>
                  <? } ?>
                  <?if(!is_null($riderInfo['MyCommute']) || !is_null($riderInfo['CommuteMapURL'])) { ?>
                    <tr>
                      <td class=label valign=top>My Commute:</td>
                      <td class=text>
                        <?=$riderInfo['MyCommute']?>
                        <?if(!is_null($riderInfo['CommuteMapURL'])) { ?>
                          <a href="http://<?=$riderInfo['CommuteMapURL']?>" target="_blank">[Show Route Map]</a>
                        <? } ?>
                      </td>
                    </tr>
                  <? } ?>
                  <?if(!is_null($riderInfo['URL'])) { ?>
                    <tr>
                      <td class=label valign=top>Website/Blog:</td>
                      <td class=text><a href="http://<?=$riderInfo['URL']?>" target="_blank"><div class="ellipses" style="width:370px">http://<?=$riderInfo['URL']?></div></a></td>
                    </tr>
                  <? } ?>
                </table>
              </div>
            </td>
          </tr>
        </table><script type="text/javascript">createMoreWrapper(1, 210, 'MORE');</script>
      </div>
    </div>

    <!-- RACE RESULTS (only show for riders with at least 1 race result posted) -->
    <?if($oDB->DBCount("results", "RiderID=$RiderID") > 0) { ?>
      <div style="height:30px"><!--vertical spacer--></div>
      <div class="text50" style="float:right;padding-top:2px;font:12px arial">
        Other Years:
  <?   // Show list of other years
        $rs = $oDB->query("SELECT DISTINCT(YEAR(RaceDate)) AS Year
                           FROM results LEFT JOIN event USING (RaceID)
                           WHERE RaceDate IS NOT NULL AND Archived=0 AND RiderID=$RiderID
                           ORDER BY RaceDate", __FILE__, __LINE__);
        while(($record=$rs->fetch_array())!=false) { ?>
          <?if($record['Year']!=$ShowYear) { ?>
            <a href="profile.php?<?if(!$editable) {?>RiderID=<?=$RiderID?>&<?}?>Year=<?=$record['Year']?> ">[<?=$record['Year']?>]</a>&nbsp;
          <? } else { ?>
            [<?=$record['Year']?>]&nbsp;
          <? } ?>
        <? } ?>
      </div>
      <h3><?=$ShowYear?> Race Results</h3>
      <div class=centered style="width:630px">
<?      $sql = "SELECT PlaceName, CategoryID, CategoryName, RaceID,
                       RiderID, EventName, RaceDate, PlaceOrdinal, PlaceID, TeamID
                FROM results
                LEFT JOIN event USING (RaceID)
                LEFT JOIN ref_placing USING (PlaceID)
                LEFT JOIN ref_race_category USING (CategoryID)
                WHERE Year(RaceDate) = $ShowYear AND RiderID=$RiderID
                ORDER by RaceDate DESC";
        $rs = $oDB->query($sql, __FILE__, __LINE__);
        if($rs->num_rows==0) { ?>
          <div style="height:5px"><!--vertical spacer--></div>
          <div class=no-data-rp>
            No results have been entered for <?=$ShowYear?>
          </div>
        <? } else { ?>
          <div style="height:5px"><!--vertical spacer--></div>
          <div id='more-content2'>
            <table id="profile-table" width=100% border=0 cellpadding=0 cellspacing=0>
              <tr>
                <td class="header" width="85">Date</td>
                <td class="header" width="355">Event</td>
                <td class="header" width="80">Place</td>
                <td class="header" width="110">Category</td>
              </tr>
              <? while(($result=$rs->fetch_array())!=false) { ?>
                <tr>
                  <td class="data"><?=date_create($result['RaceDate'])->format("D n/j")?></td>
                  <td class="data"><div class=ellipses style="width:345px">
                    <a href="results-detail.php?RaceID=<?=$result['RaceID']?>&RiderID=<?=$result['RiderID']?>">
                      <?=$result['EventName']?>
                    </a>
                  </div></td>
                  <td class="data"><?=$result['PlaceName']?></td>
                  <td class="data"><?=$result['CategoryName']?></td>
                </tr>
              <? } ?>
            </table>
          </div><script type="text/javascript">createMoreWrapper(2, 160, 'MORE RESULTS');</script>
          <? $rs->free(); ?>
        <? } ?>
      </div>
    <? } ?>

    <div style="height:30px"><!--vertical spacer--></div>

    <!-- RIDE LOG -->
    <?if($editable) {?>
      <h3>Your Ride Log</h3>
    <? } else { ?>
      <h3>Ride Log</h3>
    <? } ?>
    <div id='ride-log-masker'>  <!-- IE7 has problems with masks on divs with position:relative -->
      <div id='ride-log-holder' style="position:relative;" align=center>
        <?RenderRideLog($oDB, $RiderID, $RideLogLength, $editable)?>
      </div>
    </div>

    <!-- RIDES ATTENDANCE -->
<?  $sql = "SELECT CalendarID, CalendarDate, EventName, ClassX, ClassA, ClassB, ClassC, ClassD,
                   CONCAT(City, ', ', State, ' ', ZipCode) AS GeneralArea,
                   IF(DATEDIFF(NOW(), CalendarDate)>=0, '', '*') AS Future
            FROM calendar c JOIN calendar_attendance ca USING (CalendarID) LEFT JOIN ref_zipcodes USING (ZipCodeID)
            WHERE c.Archived=0 AND ca.RiderID=$RiderID AND Attending=1
            ORDER BY CalendarDate DESC
            LIMIT 50";
    $rs = $oDB->query($sql, __FILE__, __LINE__);
    if($rs->num_rows > 0) {?>
      <div style="height:30px"><!--vertical spacer--></div>
      <h3>Ride Attendance</h3>
      <div style="height:2px"><!--vertical spacer--></div>
      <div class=centered style="width:630px">
        <div id='more-content3'>
          <table id="profile-table" cellpadding=0 cellspacing=0>
            <tr>
              <td class=header style="padding-left:4px;">Date</td>
              <td class=header style="text-align:center">&nbsp;</td>
              <td class=header>Ride</td>
              <td class=header>Location</td>
            </tr>
            <? while(($record = $rs->fetch_array())!=false) { ?>
              <!-- Ride Row -->
              <tr>
                <td class=data width="70" style="padding-left:4px;"><?=date_create($record['CalendarDate'])->format("n/j/Y")?></td>
                <td class=data width="15"><b><?=$record['Future']?></b></td>
                <td class=data width="335"><div class=ellipses style="width:325px">
                  <a href=calendar-detail.php?CID=<?=$record['CalendarID']?>>
                    <?=$record['EventName']?>
                  </a>
                </div></td>
                <td class=data width="175"><div class=ellipses style="width:165px">
                  <?=$record['GeneralArea']?>
                </div></td>
              </tr>
            <?}?>
          </table>
        </div><script type="text/javascript">createMoreWrapper(3, 150, 'MORE');</script>
      </div>
    <? } ?>

    <!-- RIDE WIDGET (only show when you are viewing your own profile) -->
    <?if($editable) { ?>
      <div style="height:30px"><!--vertical spacer--></div>
      <h3>Who Else is on RideNet:</h3>
<?    $sql = "SELECT RiderID, RacingTeamID, CEDaysMonth, Domain
               FROM rider LEFT JOIN teams ON (CommutingTeamID = TeamID)
               WHERE rider.Archived=0
               ORDER BY CEDaysMonth DESC
               LIMIT 0,35";
      $rs = $oDB->query($sql, __FILE__, __LINE__); ?>
      <div class="commute-ride-group" style="margin-left:35px;width:160px">
        <? while(($rider=$rs->fetch_array())!=false) { ?>
          <div id="R<?=$rider['RiderID']?>" class="photobox">
            <a href="<?=BuildTeamBaseURL($rider['Domain'])?>/profile.php?RiderID=<?=$rider['RiderID']?>">
              <img class="tight" src="<?=GetFullDomainRoot()?>/dynamic-images/rider-portrait.php?RiderID=<?=$rider['RiderID']?>&T=<?=$rider['RacingTeamID']?>" height=35 width=28 border="0">
            </a>
          </div><script type="text/javascript">riderInfoCallout(<?=$rider['RiderID']?>, '')</script>
        <? } ?>
        <? $rs->free() ?>
      </div>
      <div style="float:left;margin-left:15px">
        <script type='text/javascript' src='../ride-widget.js'></script> 
        <script type="text/javascript">
          var rideWidget = new C_RideWidget({
              height: 270,
              width: 450,
              interval: 7000,
              preload: 5,
              headfoot: false,
              scrollbar: false,
              domainRoot: '<?=GetDomainRoot()?>',
              size: {
                font: 13,
                pic: 45
              },
              color: {
                background: '#FFF',
                text: '#444',
                links: '#383',
                widget: '#CCC'
              }
            });
          rideWidget.create();
        </script>
      </div>
      <br class="clearfloat" /> 
    <? } ?>

    <!-- ACTION SHOT -->
    <? if($oDB->DBCount("rider_photos", "RiderID=$RiderID AND TeamID=$ridersRacingTeamID AND ActionPicture<>''") > 0) { ?>
      <div style="height:30px"><!--vertical spacer--></div>
      <h3 style="margin:0px">Action Shot</h3>
      <div style="height:10px"><!--vertical spacer--></div>
      <div style="text-align:center">
        <img class="action-photo" src="dynamic-images/rider-action-shot.php?RiderID=<?=$RiderID?>&T=<?=$ridersRacingTeamID?>" />
      </div>
    <? } ?>

  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>