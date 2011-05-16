<?
require("script/app-master.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented

$RaceID = SmartGetInt("RaceID");
// --- Get race year so we can link to user's results for the appropriate year
$RaceYear=$oDB->DBLookup("YEAR(RaceDate)", "event", "RaceID=$RaceID");
// filter results by team if there based on presence of 'tf' query parameter
$teamFilter = isset($_REQUEST['tf']) ? " AND results.TeamID=$pt" : "";
$tf = isset($_REQUEST['tf']) ? "&tf" : "";
// get event information
$rs = $oDB->query("SELECT EventName, RaceDate FROM event WHERE RaceID = $RaceID", __FILE__, __LINE__);
$eventInfo = $rs->fetch_array();
$rs->free()
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <!--use latin charset to support smart apostrophes and other special characters people
      are inserting into race reports -->
  <meta http-equiv="Content-Type" content="text/html; charset=latin1" />
  <title><?BuildPageTitle($oDB, 0, $eventInfo['EventName'])?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="/styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("/results-detail.js")?>
  <?MinifyAndInclude("/script/ridenet-helpers.js")?>
<!-- lightbox -->
  <?echo "<script type='text/javascript' src='" . SHAREDBASE_URL . "lightbox/lightbox.js'></script>\n";?>
  <?echo "<link rel='stylesheet' type='text/css' href='" . SHAREDBASE_URL . "lightbox/lightbox.css'>\n";?>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
<!-- facebook meta tags to provide information for the like button -->
  <meta property="og:title" content="Results for <?=htmlentities($eventInfo['EventName'])?> on RideNet" />
  <meta property="og:image" content="http://ridenet.net/images/ridenet-fb-logo3.png" />
  <meta property="og:site_name" content="RideNet" />
  <meta property="og:description" content="Visit RideNet for results, race reports, and pictures from the event" />
  <meta property="fb:app_id" content="147642135282357" />
</head>

<body class="oneColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "Results")?>
  </div>

  <div id="mainContent">
    <div style="float:left;width:610px">
      <h1><?=ReplaceUnprintables($eventInfo['EventName'])?></h1>
      <div style="float:left;">
        <h2><?=date_create($eventInfo['RaceDate'])->format("F j, Y")?></h2>
      </div>
      <div style="float:left;margin-left:10px;position:relative;left:0px;top:-1px">
        <?SocialMediaButtons($eventInfo['EventName'] . " Results on #RideNet")?>
      </div>
    </div>
    <div class="ridenet-mini-ad" style="float:right;padding-top:12px">
      <a href="http://ridenet.net" target="_blank"><img border=0 src="/images/ads/ridenetad-mini.png" alt="RideNet"/><br></a>
      <a href="http://ridenet.net" target="_blank">Your team...online</a>
    </div>
    <div class="clearfloat" style="height:20px"><!--vertical spacer--></div>
    <div align="center">
    <table cellpadding="0" cellspacing="0" border="0">
      <tr><td>
        <table id="results" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td class="header" colspan=2 width="200">&nbsp;Rider</td>
            <td class="header" width="240">Team</td>
            <td class="header" width="60">Place</td>
            <td class="header" width="120">Field</td>
          </tr>
<?      $sql = "SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderID, PlaceName,
                       CategoryID, CategoryName, IF(Report IS NOT NULL, 1, 0) AS HasReport,
                       resultsteam.TeamName AS ResultsTeamName, currentteam.Domain AS Domain
                FROM results
                LEFT JOIN ref_placing USING (PlaceID)
                LEFT JOIN ref_race_category USING (CategoryID)
                LEFT JOIN rider USING (RiderID)
                LEFT JOIN race_report USING (RaceID, RiderID)
                LEFT JOIN teams resultsteam ON (results.TeamID = resultsteam.TeamID)
                LEFT JOIN teams currentteam ON (rider.RacingTeamID = currentteam.TeamID)
                WHERE RaceID=$RaceID $teamFilter
                ORDER by PlaceOrdinal";
        $rs = $oDB->query($sql, __FILE__, __LINE__);
        while(($record=$rs->fetch_array())!=false) { ?>
          <tr>
            <td class="data" width=20 style="text-align:center">
              <? if($record['HasReport']) { ?>
                <a onclick="scrollToRaceReport(<?=$record['RiderID']?>)" style="cursor:pointer">
                  <img src="/images/race-report.png" title="Click to jump to race report" style="position:relative;top:1px">
                </a>
              <? } else { ?>
                -
              <? } ?>
            </td>
            <td class="data"><div class=ellipses style="width:170px">
              <a href="<?=BuildTeamBaseURL($record['Domain'])?>/profile.php?RiderID=<?=$record['RiderID']?>&Year=<?=$RaceYear?>"><?=$record['RiderName']?></a>
            </div></td>
            <td class="data"><div class=ellipses style="width:230px">
              <?=$record['ResultsTeamName']?>&nbsp;
            </div></td>
            <td class="data"><div class=ellipses style="width:50px">
              <?=$record['PlaceName']?>&nbsp;
            </div></td>
            <td class="data"><?=$record['CategoryName']?>&nbsp;</td>
          </tr>
        <? } ?>
          </td></tr>
        </table>
      </td></tr>
    </table>
    </div>
    <div style="height:20px"><!--vertical spacer--></div>
    <? $rs->free() ?>

<?  $sql = "SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderID, phototeam.TeamID as PhotoTeamID,
                          phototeam.TeamName as PhotoTeamName, raceteam.Domain AS Domain
            FROM event_photos
            LEFT JOIN rider USING (RiderID)
            LEFT JOIN teams raceteam ON (rider.RacingTeamID = raceteam.TeamID)
            LEFT JOIN teams phototeam ON (event_photos.TeamID = phototeam.TeamID)
            LEFT JOIN race_report USING (RaceID, RiderID)
            WHERE RaceID=$RaceID
            GROUP BY RiderID";
    $rs = $oDB->query($sql, __FILE__, __LINE__);
    while(($record=$rs->fetch_array())!=false) { ?>
      <div align="center">
      <table id="race-report" width="600" cellpadding="0">
        <tr>
          <td colspan="2">&nbsp;</td>
          <td class="header">
              Photos by <?=$record['RiderName']?>
          </td>
        </tr>
        <tr valign=top>
          <td width=86>
          <!-- show rider photo with caption in first column -->
            <table>
              <tr><td align=center>
                <a href="<?=BuildTeamBaseURL($record['Domain'])?>/profile.php?RiderID=<?=$record['RiderID']?>&Year=<?=$RaceYear?>">
                <img class="tight" src="<?=GetFullDomainRoot()?>/imgstore/rider-portrait/<?=$record['PhotoTeamID']?>/<?=$record['RiderID']?>.jpg" height=100 width=80 border="0"></a>
              </td></tr>
              <tr><td class="profile-photo-caption">
                <?=$record['RiderName']?>
              </td></tr>
            </table>
          </td>
          <td width="15">
          <!-- second column is spacer -->
            &nbsp
          </td>
          <td>
            <div id='more-content-PHOTOS<?=$record['RiderID']?>'>
<?          $sql = "SELECT Filename
                    FROM event_photos
                    WHERE RaceID=$RaceID AND RiderID={$record['RiderID']}
                    ORDER BY PhotoID";
            $rs2 = $oDB->query($sql, __FILE__, __LINE__);
            while(($photo=$rs2->fetch_array())!=false) { ?>
                <a href="imgstore/full/<?=$photo['Filename']?>" rel="photos" title="Posted by <?=htmlentities($record['RiderName']) . " - " . $record['PhotoTeamName']?>">
                  <img class="event-thumbnail" src="imgstore/thumb/<?=$photo['Filename']?>">
                </a>
            <? } ?>
            <? $rs2->free() ?>
            <div class="clearfloat"></div>
            </div>
          </td>
        </tr>
        <tr><td height="30" colspan="3"></td></tr>
      </table><script type="text/javascript">createMoreWrapper('-PHOTOS' + <?=$record['RiderID']?>, 170, 'SHOW ALL');</script>
      <script>
        // register photos with the lightbox plugin
        Ext.ux.Lightbox.register('[rel^=photos]', true); // true to show them as a set
      </script>
      </div>
    <? } ?>
    <? $rs->free() ?>
                        
<?  $sql = "SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderID, PlaceName, CategoryName,
                   results.TeamID as ResultsTeamID, Report, Domain
            FROM results
            LEFT JOIN ref_placing USING (PlaceID)
            LEFT JOIN ref_race_category USING (CategoryID)
            LEFT JOIN rider USING (RiderID)
            LEFT JOIN teams ON (rider.RacingTeamID = teams.TeamID)
            LEFT JOIN race_report USING (RaceID, RiderID)
            WHERE RaceID=$RaceID AND IFNULL(HideRaceReports,0)=0 $teamFilter
            GROUP BY RiderID
            ORDER BY PlaceOrdinal";
    $rs = $oDB->query($sql, __FILE__, __LINE__);
    while(($record=$rs->fetch_array())!=false)
    { 
      if($record['Report']!="")
      {?>
        <div align="center">
        <table id="race-report" width="600" cellpadding="0">
          <tr>
            <td colspan="2">&nbsp;</td>
            <td class="header">
            <!-- create report title including placing and field name -->
                <a id="R<?=$record['RiderID']?>" name="R<?=$record['RiderID']?>"></a>
                <?=$record['RiderName']?>: <?=$record['PlaceName']?>, <?=$record['CategoryName']?>
            </td>
          </tr>
          <tr valign=top>
            <td width=86>
            <!-- show rider photo with caption in first column -->
              <table>
                <tr><td align=center>
                  <a href="<?=BuildTeamBaseURL($record['Domain'])?>/profile.php?RiderID=<?=$record['RiderID']?>&Year=<?=$RaceYear?>">
                  <img class="tight" src="<?=GetFullDomainRoot()?>/imgstore/rider-portrait/<?=$record['ResultsTeamID']?>/<?=$record['RiderID']?>.jpg" height=100 width=80 border="0"></a>
                </td></tr>
                <tr><td class="profile-photo-caption">
                  <?=$record['RiderName']?>
                </td></tr>
              </table>
            </td>
            <td width="15">
            <!-- second column is spacer -->
              &nbsp
            </td>
            <td class="data">
            <!-- third column contains race report -->
              <div id='more-content<?=$record['RiderID']?>'>
              <?=ReplaceUnprintables(LF2BR($record['Report']))?>
              <div>
            </td>
          </tr>
          <? if(strlen($record['Report']) > 400) { ?>
            <!-- Spacer row between reports if report text is long. Otherwise photo+caption creates enough space -->
            <tr><td height="30" colspan="3"></td></tr>
          <? } ?>
        </table><script type="text/javascript">createMoreWrapper(<?=$record['RiderID']?>, 150, 'READ MORE');</script>
        </div>
      <? } ?>
    <? } ?>
    <? $rs->free() ?>
    
  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>