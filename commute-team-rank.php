<?
require("script/app-master.php");
require("dynamic-sections/calendar-sidebar.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented

// if time span is passed in, use it. Otherwise default to year to date
$range = (isset($_REQUEST['R'])) ? $_REQUEST['R'] : "L30";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, 0, "Commute Team Rankings")?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="/styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("/dialogs/calendar-event-dialog.js")?>
  <?MinifyAndInclude("/script/ridenet-helpers.js")?>
<!-- Build javascript arrays for local/static combobox lookups -->
  <script type="text/javascript">
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
<!-- facebook meta tags to provide information for the like button -->
  <meta property="og:title" content="Latest 2 BY 2012 Team Rankings on RideNet" />
  <meta property="og:image" content="http://ridenet.net/images/2by2012-fb-logo3.png" />
  <meta property="og:site_name" content="RideNet" />
  <meta property="og:description" content="Visit RideNet for complete 2 BY 2012 stats and team rankings" />
  <meta property="fb:app_id" content="147642135282357" />
</head>

<body class="twoColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "Ranking")?>
  </div>
  <!-- This submenu is outside the header div so it floats side by side with the right column -->
  <?InsertCommutingMenu("Teams")?>

  <div id="sidebarHolderRight">
    <?ColumbusFoundationSidebar($oDB)?>
    <?AdSidebar()?>
    <?CalendarSidebar($oDB, $pt)?>
    <?MostViewedRiderSidebar($oDB, $pt)?>
  </div>

  <div id="mainContent">
    <div style="float:left">
      <h1>Current Team Rankings</h1>
    </div>
    <div style="float:left;margin-left:10px;position:relative;left:0px;top:12px">
      <?SocialMediaButtons("Latest 2 BY 2012 stats and team rankings on #RideNet")?>
    </div>
    <div class='clearfloat'></div>

<?  // ======= RANKINGS BY AVERAGE DAYS/MONTH =========
    $sql = "SELECT TeamName, TeamID, Domain, SUM(CEDaysMonth)/COUNT(RiderID) AS CEAvgDaysMonth, COUNT(RiderID) AS RiderCount
             FROM rider LEFT JOIN teams ON (CommutingTeamID = TeamID)
             WHERE rider.Archived=FALSE
             GROUP BY TeamID
             HAVING CEAvgDaysMonth > 0 AND RiderCount >=5
             ORDER BY CEAvgDaysMonth DESC
             LIMIT 0,25";
    $rs = $oDB->query($sql, __FILE__, __LINE__); ?>
    <h2>Ranked By AVERAGE Days/Month</h2>
    <div style="height:5px"><!--vertical spacer--></div>
      <?TeamTable($oDB, $rs, "CEAvgDaysMonth", 1, "A");?>
    <div style="height:35px"><!--vertical spacer--></div>

<?  // ======= RANKINGS BY TOTAL DAYS/MONTH =========
    $sql = "SELECT TeamName, TeamID, Domain, SUM(CEDaysMonth) AS CEDaysMonth, COUNT(RiderID) AS RiderCount
             FROM rider LEFT JOIN teams ON (CommutingTeamID = TeamID)
             WHERE rider.Archived=FALSE
             GROUP BY TeamID
             HAVING CEDaysMonth > 0
             ORDER BY CEDaysMonth DESC
             LIMIT 0,25";
    $rs = $oDB->query($sql, __FILE__, __LINE__); ?>
    <h2>Ranked By TOTAL Days/Month</h2>
    <div style="height:5px"><!--vertical spacer--></div>
      <?TeamTable($oDB, $rs, "CEDaysMonth", 0, "T");?>
    <div style="height:25px"><!--vertical spacer--></div>

  </div><!-- end #mainContent -->
  <br class="clearfloat" /> <!-- clear all floating elements -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>


<?
function TeamTable($oDB, $rs, $rankField, $decimals, $tag)
{
    $rank = 1; ?>
    <table class="commute-team-rank" cellpadding=0 cellspacing=0 border=0>
      <tr>
        <td></td>
        <td class="table-divider" colspan=4></td>
      </tr>
<?    if($rs->num_rows==0)
      { ?>
        <p class="no-data">No rides have been logged in the last year</p>
        <div style="height:15px"><!--vertical spacer--></div>
<?    }
      else
      {
        while(($record=$rs->fetch_array())!=false)
        { ?>
          <tr>
            <td class="rank" width=20><?=$rank++?>.</td>
            <td class="data">
              <a href="<?=BuildTeamBaseURL($record['Domain'])?>/">
                <table cellspacing=0 cellpadding=0 style="height:30px;width:90px;margin:2px 9px"><tr><td align=center>
                  <img id="T<?=$record['TeamID'] . $tag?>" class="tight" src="<?=GetFullDomainRoot()?>/imgstore/team-logo/fit/<?=$record['TeamID']?>.png">
                </td></tr></table>
              </a>
              <!-- Team name callout -->
              <script type="text/javascript">
                  new Ext.ToolTip({
                      target: 'T<?=$record['TeamID'] . $tag?>',
                      anchor: 'bottom',
                      anchorOffset: 15,
                      dismissDelay: 0,
                      showDelay: 200,
                      html: "<div class='team-name-callout'><?=htmlentities($record['TeamName'])?></div>",
                      padding: 5
                    });
              </script>
            </td>
            <td class="data" width=275>
            <? TopTenRiders($oDB, $record['TeamID'], $tag); ?>
            </td>
            <td align=left class="data" width=50>
              <?=$record['RiderCount']?> <span class="text50" style="font-size:0.9em"><?=($record['RiderCount']==1) ? "Rider " : "Riders"?></span><br>
            </td>
            <td align=center width=80 class="data">
              <table cellpadding=0 cellspacing=0><tr>
                <td class="days-month"><?=number_format($record[$rankField],$decimals)?></td>
                <td><img class="tight" src="/images/ridelog/tiny/commute.png"></td>
                <td><img class="tight" src="/images/ridelog/tiny/errand.png"></td>
              </tr></table>
              <span class="days-month-label">
                Days/Month
              </span>
            </td>
          </tr>
        <? } ?>
      <? } ?>
    </table>
<?
}
?>



<?
function TopTenRiders($oDB, $teamID, $tag)
{
    $sql = "SELECT RiderID, RacingTeamID, Domain
            FROM rider LEFT JOIN teams ON (CommutingTeamID = TeamID)
            WHERE CommutingTeamID=$teamID AND rider.Archived=0
            GROUP BY RiderID
            ORDER BY CEDaysMonth DESC
            LIMIT 0,10";
    $rs = $oDB->query($sql, __FILE__, __LINE__); ?>
    <div class="commute-ride-group">
      <? while(($record=$rs->fetch_array())!=false) { ?>
        <div id="R<?=$record['RiderID'] . $tag?>" class="photobox">
          <a href="<?=BuildTeamBaseURL($record['Domain'])?>/rider/<?=$record['RiderID']?>">
            <img class="tight" src="<?=GetFullDomainRoot()?>/imgstore/rider-portrait/<?=$record['RacingTeamID']?>/<?=$record['RiderID']?>.jpg" height=27 width=22 border="0">
          </a>
        </div><script type="text/javascript">riderInfoCallout(<?=$record['RiderID']?>, '<?=$tag?>')</script>
      <? } ?>
      <? $rs->free() ?>
    </div>
<?
}
?>