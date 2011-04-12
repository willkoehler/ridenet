<?
require("script/app-master.php");
require("dynamic-sections/calendar-sidebar.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
RecordPageView($oDB);
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
// if race year is passed in use it. Otherwise default to current year
$ShowYear = (isset($_REQUEST['Year'])) ? SmartGetInt("Year") : CURRENT_YEAR;
// filter results by team based on presence of 'tf' query parameter
$teamFilter = isset($_REQUEST['tf']) ? " AND results.TeamID=$pt" : "";
$tf = isset($_REQUEST['tf']) ? "&tf" : "";
$teamName = $oDB->DBLookup("TeamName", "teams", "TeamID=$pt");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, $pt, $ShowYear . " Results Summary")?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("dialogs/calendar-event-dialog.js")?>
  <?MinifyAndInclude("script/ridenet-helpers.js")?>
<!-- Build javascript arrays for local/static combobox lookups -->
  <script type="text/javascript">
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
<!-- facebook meta tags to provide information for the like button -->
  <meta property="og:title" content="<?=($tf) ? "Summary of $ShowYear $teamName Results on RideNet" : "Summary of $ShowYear racing results on RideNet"?>" />
  <meta property="og:image" content="http://ridenet.net/images/ridenet-fb-logo3.png" />
  <meta property="og:site_name" content="RideNet" />
  <meta property="og:description" content="Visit RideNet for results, race reports, and pictures from <?=$ShowYear?>" />
  <meta property="fb:app_id" content="147642135282357" />
</head>

<body class="twoColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "Results")?>
  </div>
  <!-- This submenu is outside the header div so it floats side by side with the right column -->
  <?InsertResultsMenu($ShowYear, "Results")?>

  <div id="sidebarHolderRight">
    <?SignupSidebar($oDB)?>
    <?if($pt==2) { ?> <!--Team Echelon sponsors are hard-coded for now-->
      <?SponsorSidebar($oDB)?>
    <? } ?>
    <?AdSidebar($oDB)?>
    <?CalendarSidebar($oDB, $pt)?>
    <?MostViewedRiderSidebar($oDB, $pt)?>
  </div>

  <div id="mainContent">
    <div style="float:left">
      <h1><?=$ShowYear?> Results Summary</h1>
    </div>
    <div style="float:left;margin-left:10px;position:relative;left:0px;top:12px">
      <? SocialMediaButtons(($tf) ? "Summary of $ShowYear $teamName Results on #RideNet" : "Summary of $ShowYear racing results on #RideNet") ?>
    </div>
    <div class='clearfloat'></div>

    <?if($pt!=0) { ?>
      <table border=0 cellpadding=0 cellspacing=0><tr>
        <td valign=center>
          <h2 style="margin:0px">Showing Results For</h2>
        </td>
        <td valign=center style="padding: 0px 5px">
          <SELECT name="Year" onChange="window.location.href='racing-results.php?Year=<?=$ShowYear?>' + options[selectedIndex].value">
            <OPTION value='' <?if(!$tf) {?>selected<? } ?>>All RideNet Teams
            <OPTION value='&tf' <?if($tf) {?>selected<? } ?>><?=$teamName?>
          </SELECT>
        </td>
      </tr></table>
    <? } ?>

    <div style="height:15px"><!--vertical spacer--></div>

    <? if(isset($_REQUEST['tf'])) { ?>
    <!-- ======= Results Breakdown ======= -->
    <table id="results-breakdown" border="1" cellpadding="2" cellspacing="0" width="550">
      <tr>
        <td class=header style="background-color:#000000;color:white">Total Events</td>
        <td class=header style="background-color:#FF0000">Wins</td>
        <td class=header style="background-color:#FF6600">2nd</td>
        <td class=header style="background-color:#FF9900">3rd</td>
        <td class=header style="background-color:#FFCC00">4th</td>
        <td class=header style="background-color:#FFCC33">5th</td>
        <td class=header style="background-color:#FFFF00">6th</td>
        <td class=header style="background-color:#FFFF33">7th</td>
        <td class=header style="background-color:#FFFF66">8th</td>
        <td class=header style="background-color:#FFFF99">9th</td>
        <td class=header style="background-color:#FFFFCC">10th</td>
      </tr>
      <tr>
<?      $rs = $oDB->query("SELECT COUNT(DISTINCT RaceID) AS NumEvents
                           FROM results LEFT JOIN event USING (RaceID)
                           WHERE Year(RaceDate)=$ShowYear $teamFilter", __FILE__, __LINE__);
        $record = $rs->fetch_array() ?>
        <td class=data><?=$record['NumEvents']?></td>
<?      $rs->free();
        $rs = $oDB->query("SELECT PlaceID, COUNT(*) AS NumResults
                           FROM results LEFT JOIN event USING (RaceID)
                           WHERE PlaceID BETWEEN 1 and 10 AND Year(RaceDate)=$ShowYear AND RideTypeID NOT IN (4,5) $teamFilter
                           GROUP BY PlaceID
                           ORDER BY PlaceID", __FILE__, __LINE__);
        $results = array();
        while(($record=$rs->fetch_array())!=false)
        {
          $results[$record['PlaceID']] = $record['NumResults'];
        }
        for($i=1; $i<=10; $i++) { ?>
          <td class=data><?=(isset($results[$i])) ? $results[$i] : '-'?></td>
        <? } ?>
      </tr>
    </table>
    <div style="height:10px"><!--vertical spacer--></div>
    
    <? } else { ?>

  <!-- ======= Featured Racers ========= -->
<?  $sql = "SELECT RiderID, RacingTeamID, COUNT(*) AS ResultCount, Domain
            FROM rider
            LEFT JOIN teams ON (RacingTeamID = TeamID)
            LEFT JOIN results USING (RiderID)
            LEFT JOIN event USING (RaceID)
            WHERE rider.Archived=0 AND YEAR(RaceDate)=$ShowYear $teamFilter 
            GROUP BY RiderID
            ORDER BY ResultCount DESC
            LIMIT 30";
    $rs = $oDB->query($sql, __FILE__, __LINE__); ?>
    <div class="commute-ride-group" style="margin-left:5px;width:550px">
      <? while(($rider=$rs->fetch_array())!=false) { ?>
        <div id="R<?=$rider['RiderID']?>B" class="photobox">
          <a href="<?=BuildTeamBaseURL($rider['Domain'])?>/profile.php?RiderID=<?=$rider['RiderID']?>&Year=<?=$ShowYear?>">
            <img class="tight" src="<?=GetFullDomainRoot()?>/dynamic-images/rider-portrait.php?RiderID=<?=$rider['RiderID']?>&T=<?=$rider['RacingTeamID']?>" height=40 width=32 border="0">
          </a>
        </div><script type="text/javascript">riderInfoCallout(<?=$rider['RiderID']?>, 'B', 1)</script>
      <? } ?>
      <? $rs->free() ?>
    </div>
    <div style="clear:both;height:10px"></div> <!-- clear all floating elements -->

    <? } ?>

    <!-- ======= Results List ======= -->
<?      $lastEventID = 0;
        $sql = "SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, PlaceName, CategoryName, RaceID,
                       RiderID, EventName, RaceDate, TeamName as ResultsTeam
                FROM results
                LEFT JOIN event USING (RaceID)
                LEFT JOIN ref_placing USING (PlaceID)
                LEFT JOIN ref_race_category USING (CategoryID)
                LEFT JOIN rider USING (RiderID)
                LEFT JOIN teams ON (results.TeamID = teams.TeamID) 
                WHERE Year(RaceDate)=$ShowYear AND event.Archived=0 $teamFilter
                ORDER by RaceDate DESC, RaceID, PlaceOrdinal";
        $rs = $oDB->query($sql, __FILE__, __LINE__);
        if($rs->num_rows==0)
        { ?>
          <p style="font:14px verdana">No results have been entered for <?=$ShowYear?></p>
          <div style="height:15px"><!--vertical spacer--></div>
<?      }
        else
        {
          $record=$rs->fetch_array();
          while($record!=false)
          {
            if($record['RaceID']!=$lastEventID) {?>
            <!-- Race Results Header -->
              <div style="height:5px"><!--vertical spacer--></div>
              <div class="link-box" style="width:535px" onclick="javascript:document.location='results-detail.php?RaceID=<?=$record['RaceID']?><?=$tf?>'">
              <table id="results" cellpadding=0 cellspacing=0 width=100%>
              <tr align=left>
                <td colspan=3 class="header-sm"><div class=ellipses style="width:440px">
                  <?=date_create($record['RaceDate'])->format("D n/j")?>: <?=$record['EventName']?>
                </div></td>
                <td class="header-sm text40" style="font:11px arial;text-align:right">(Click for details)</td>
              </tr>
              <tr><td colspan=4 class="table-spacer" style="height:2px">&nbsp;</td></tr>
              <? $lastEventID = $record['RaceID'];?>
            <? } ?>
            <!-- Race Results Row -->
            <tr align=left>
              <td class="data-sm link-color" style="padding:0px 4px"><div class=ellipses style="width:150px">
                <?=$record['RiderName']?>
              </div></td>
              <td class="data-sm" style="padding:0px 4px"><div class=ellipses style="width:200px">
                <?=$record['ResultsTeam']?>
              </div></td>
              <td class="data-sm"><div class=ellipses style="width:50px"><?=$record['PlaceName']?></div></td>
              <td class="data-sm"><div class=ellipses style="padding-left:5px;width:85px"><?=$record['CategoryName']?></div></td>
            </tr>
<?          $record=$rs->fetch_array();
            if($record==false || $record['RaceID']!=$lastEventID) {?>
            <!-- Race Results Footer -->
              </table>
              </div>
            <? } ?>
          <? } ?>
        <? } ?>

    <div style="height:10px"><!--vertical spacer--></div>
    <p align=center>
      <b>Other Years:</b>
<?   // Show list of other years
      $rs = $oDB->query("SELECT DISTINCT(YEAR(RaceDate)) AS Year FROM event WHERE RaceDate IS NOT NULL AND Archived=0 ORDER BY RaceDate", __FILE__, __LINE__);
      while(($record=$rs->fetch_array())!=false) { ?>
        <?if($record['Year']!=$ShowYear) { ?>
          <a href="racing-results.php?Year=<?=$record['Year']?><?=$tf?>">[<?=$record['Year']?>]</a>&nbsp;
        <? } else { ?>
          [<?=$record['Year']?>]&nbsp;
        <? } ?>
      <? } ?>
    </p>

  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>