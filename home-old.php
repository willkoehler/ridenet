<?
require("script/app-master.php");
require("dynamic-sections/calendar-sidebar.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
// Get team information for this page
$sql = "SELECT HomePageHTML, TeamName, OrganizationID
        FROM Teams
        WHERE TeamID=$pt";
$rs = $oDB->query($sql, __FILE__, __LINE__);
$team = $rs->fetch_array();
$rs->free();
RecordPageView($oDB);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="description" content="Create a rider bio, track your race results, keep a ride log, build a team page, find cycling events and rides in your area, connect with other riders.">
  <title><?BuildPageTitle($oDB, $pt)?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("dialogs/calendar-event-dialog.js")?>
<!-- Build javascript arrays for local/static combobox lookups -->
  <script type="text/javascript">
    <?SessionToJS()?>
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
</head>

<body class="twoColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "Home")?>
  </div>

  <div id="sidebarHolderRight">
    <?if($team['OrganizationID']==1) { ?>
      <?SignupSidebar($oDB)?>
    <? } else { ?>
      <?ColumbusFoundationSidebar($oDB)?>
    <? } ?>
    <?if($pt==2) { ?> <!--Team Echelon sponsors are hard-coded for now-->
      <?SponsorSidebar($oDB)?>
    <? } ?>
    <?AdSidebar($oDB)?>
    <?CalendarSidebar($oDB, $pt)?>
    <?MostViewedRiderSidebar($oDB, $pt)?>
  </div><!-- end right sidebar -->

  <div id="mainContent">
<?  if(is_null($team['HomePageHTML']))
    {
      $team['HomePageHTML'] = SampleHomePageHTML($team['TeamName']);?>
      <!-- If home page HTML is null, show message with instructions to customize -->
      <p style="padding-top:8px">
        <b>This site can be customized using the <a href="team-manager.php#1">Team Manager</a> Here's a sample
        of what your home page might look like:</b>
      </p>
    <? } ?>
    <!-- Advanced and Premium sites have a full HTML home page-->
    <?=$team['HomePageHTML']?>
  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>

</html>