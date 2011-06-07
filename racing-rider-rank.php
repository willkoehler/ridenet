<?
require("script/app-master.php");
require("dynamic-sections/calendar-sidebar.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
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
  <title><?BuildPageTitle($oDB, $pt, "$ShowYear Rider Rankings")?></title>
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
  <meta property="og:title" content="<?=($tf) ? "$ShowYear $teamName Rider Rankings on RideNet" : "$ShowYear Rider Rankings on RideNet"?>" />
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
  <?InsertResultsMenu($ShowYear, "Ranking")?>

  <div id="sidebarHolderRight">
    <?RideNetAdSidebar()?>
    <?if($pt==2) { ?> <!--Team Echelon sponsors are hard-coded for now-->
      <?SponsorSidebar()?>
    <? } ?>
    <?AdSidebar()?>
    <?CalendarSidebar($oDB, $pt)?>
    <?MostViewedRiderSidebar($oDB, $pt)?>
  </div>

  <div id="mainContent">
    <div style="float:left">
      <h1><?=$ShowYear?> Rider Rankings</h1>
    </div>
    <div style="float:left;margin-left:10px;position:relative;left:0px;top:12px">
      <? SocialMediaButtons(($tf) ? "$ShowYear $teamName Rider Rankings on #RideNet" : "$ShowYear Rider Rankings on #RideNet") ?>
    </div>
    <div class='clearfloat'></div>
    <?if($pt!=0) { ?>
      <table border=0 cellpadding=0 cellspacing=0><tr>
        <td valign=center>
          <h2 style="margin:0px">Showing Rankings For</h2>
        </td>
        <td valign=center style="padding: 0px 5px">
          <SELECT name="Year" onChange="window.location.href='/racing-rider-rank?Year=<?=$ShowYear?>' + options[selectedIndex].value">
            <OPTION value='' <?if(!$tf) {?>selected<? } ?>>All RideNet Teams
            <OPTION value='&tf' <?if($tf) {?>selected<? } ?>><?=$teamName?>
          </SELECT>
        </td>
      </tr></table>
    <? } ?>

    <div style="height:10px"><!--vertical spacer--></div>

<?  $sql = "SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderID, SUM(Points) AS Points,
                   COUNT(*) AS TotalEvents, TeamName, Domain, results.TeamID AS ResultsTeamID,
                   SUM(PlaceID=1) AS 1ST,
                   SUM(PlaceID=2) AS 2ND,
                   SUM(PlaceID=3) AS 3RD,
                   SUM(PlaceID=4) AS 4TH,
                   SUM(PlaceID=5) AS 5TH,
                   SUM(PlaceID=6) AS 6TH,
                   SUM(PlaceID=7) AS 7TH,
                   SUM(PlaceID=8) AS 8TH,
                   SUM(PlaceID=9) AS 9TH,
                   SUM(PlaceID=10) AS 10TH
            FROM results
            LEFT JOIN event USING (RaceID)
            LEFT JOIN ref_placing USING (PlaceID)
            LEFT JOIN rider USING (RiderID)
            LEFT JOIN teams ON (rider.RacingTeamID = teams.TeamID)
            WHERE Year(RaceDate)=$ShowYear AND event.Archived=0 $teamFilter
            GROUP BY RiderID
            ORDER BY Points DESC";
    $rs = $oDB->query($sql, __FILE__, __LINE__);
    if($rs->num_rows==0)
    { ?>
      <div style="height:15px"><!--vertical spacer--></div>
      <p style="font:14px verdana">No results have been entered for <?=$ShowYear?></p>
<?  }
    else
    {
      while(($record=$rs->fetch_array())!=false) { ?>
        <div class="link-box" style="width:535px" onclick="window.location.href='<?=BuildTeamBaseURL($record['Domain'])?>/rider/<?=$record['RiderID']?>?Year=<?=$ShowYear?>'">
          <table id="results-breakdown" cellpadding=0; cellspacing=0 width=100%><tr>
            <td width=50 style="padding-right:5px" valign=bottom>
              <img class="tight" src="<?=GetFullDomainRoot()?>/imgstore/rider-portrait/<?=$record['ResultsTeamID']?>/<?=$record['RiderID']?>.jpg" height=56 width=45 border="0">
            </td>
            <td valign=middle>
              <table cellpadding=0 cellspacing=0 width=100%>
                <tr>
                  <td class="title">
                    <div class='ellipses' style="width:400px">
                      <?=$record['RiderName']?> - <span class='text50'><?=$record['TeamName']?></span>
                    </div>
                  </td>
                  <td class="title" style="text-align:right">
                    <b><?=$record['Points']?> Points</b>
                  </td>
                </tr>
                <tr>
                  <td colspan=2 align=center valign=top>
                    <table id="results-breakdown" border="1" cellpadding="0" cellspacing="0" width=100%>
                      <tr>
                        <td class=header-sm style="background-color:#000000;color:white">Events</td>
                        <td class=header-sm style="background-color:#FF0000">Wins</td>
                        <td class=header-sm style="background-color:#FF6600">2nd</td>
                        <td class=header-sm style="background-color:#FF9900">3rd</td>
                        <td class=header-sm style="background-color:#FFCC00">4th</td>
                        <td class=header-sm style="background-color:#FFCC33">5th</td>
                        <td class=header-sm style="background-color:#FFFF00">6th</td>
                        <td class=header-sm style="background-color:#FFFF33">7th</td>
                        <td class=header-sm style="background-color:#FFFF66">8th</td>
                        <td class=header-sm style="background-color:#FFFF99">9th</td>
                        <td class=header-sm style="background-color:#FFFFCC">10th</td>
                      </tr>
                      <tr>
                        <td class=data-sm><?=$record['TotalEvents']?></td>
                        <td class=data-sm><?=($record['1ST']) ? $record['1ST'] : "-"?></td>
                        <td class=data-sm><?=($record['2ND']) ? $record['2ND'] : "-"?></td>
                        <td class=data-sm><?=($record['3RD']) ? $record['3RD'] : "-"?></td>
                        <td class=data-sm><?=($record['4TH']) ? $record['4TH'] : "-"?></td>
                        <td class=data-sm><?=($record['5TH']) ? $record['5TH'] : "-"?></td>
                        <td class=data-sm><?=($record['6TH']) ? $record['6TH'] : "-"?></td>
                        <td class=data-sm><?=($record['7TH']) ? $record['7TH'] : "-"?></td>
                        <td class=data-sm><?=($record['8TH']) ? $record['8TH'] : "-"?></td>
                        <td class=data-sm><?=($record['9TH']) ? $record['9TH'] : "-"?></td>
                        <td class=data-sm><?=($record['10TH']) ? $record['10TH'] : "-"?></td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr></table>
        </div>
      <? } ?>
    <? } ?>
    <div style="height:15px"><!--vertical spacer--></div>
    <div id="ride-class-key" style="margin:0 auto;width:100%">
      <div class=header>* Scoring</div>
      <div class=details>
        <b>Each Event:</b> 5 points&nbsp;&nbsp;+&nbsp;
        <b>1st Place:</b> 10 points,&nbsp;
        <b>2nd Place:</b> 9 points,&nbsp;<b>...</b>
        <b>10th Place:</b> 1 point&nbsp;&nbsp;
        <b>DNF:</b> 0 points
      </div>
    </div>

    <div style="height:20px"><!--vertical spacer--></div>
    <p align=center>
      <b>Other Years:</b>
<?   // Show list of other years
      $rs = $oDB->query("SELECT DISTINCT(YEAR(RaceDate)) AS Year FROM event WHERE RaceDate IS NOT NULL AND Archived=0 ORDER BY RaceDate", __FILE__, __LINE__);
      while(($record=$rs->fetch_array())!=false) { ?>
        <?if($record['Year']!=$ShowYear) { ?>
          <a href="/racing-rider-rank?Year=<?=$record['Year']?> ">[<?=$record['Year']?>]</a>&nbsp;
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