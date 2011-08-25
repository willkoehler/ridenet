<?
require("script/app-master.php");
require("dynamic-sections/calendar-sidebar.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, 0, "Rider Stats")?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="/styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("/dialogs/calendar-event-dialog.js")?>
  <?MinifyAndInclude("/rider-stats.js")?>
  <?MinifyAndInclude("/script/ridenet-helpers.js")?>
<!-- Build javascript arrays for local/static combobox lookups -->
  <script type="text/javascript">
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
<!-- facebook meta tags to provide information for the like button -->
  <meta property="og:title" content="Latest Rider Stats on RideNet" />
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
  <?InsertRidingMenu("Riders")?>

  <div id="sidebarHolderRight">
    <?ColumbusFoundationSidebar($oDB)?>
    <?AdSidebar()?>
    <?CalendarSidebar($oDB, $pt)?>
    <?MostViewedRiderSidebar($oDB, $pt)?>
  </div>

  <div id="mainContent">
    <div style="float:left">
      <h1>Current Rider Stats</h1>
    </div>
    <div style="float:left;margin-left:10px;position:relative;left:0px;top:12px">
      <?SocialMediaButtons("Latest rider stats on #RideNet")?>
    </div>
    <div style="float:left;margin-left:10px;position:relative;left:0px;top:11px">
      <select id="date-range">
        <option value='A'>All Time</option>
        <option value='Y0'><?=date("Y")?> YTD</option>
        <option value='Y1'>Last Year</option>
        <option value='M0'>This Month</option>
        <option value='M1'><?=AddMonths(new DateTime,-1)->format("F Y")?></option>
        <option value='M2'><?=AddMonths(new DateTime,-2)->format("F Y")?></option>
      </select>
    </div>
    <div class='clearfloat'></div>
    <div style="height:10px"></div>
    <div id='report-form'></div>
    <div style="height:20px"></div>
  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>
