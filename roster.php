<?
require("script/app-master.php");
require("dynamic-sections/calendar-sidebar.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
RecordPageView($oDB);
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
$teamTypeID = $oDB->DBLookup("TeamTypeID", "teams", "TeamID=$pt");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, $pt, "Roster")?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="/styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Data View -->
  <?echo "<script type='text/javascript' src='" . EXTBASE_URL . "examples/ux/DataViewTransition.js'></script>\n"?>
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("/roster.js")?>
  <?MinifyAndInclude("/dialogs/calendar-event-dialog.js")?>
  <?MinifyAndInclude("/script/ridenet-helpers.js")?>
<!-- Build javascript arrays for local/static combobox lookups -->
  <script type="text/javascript">
    g_fullDomainRoot="<?=GetFullDomainRoot()?>";
    g_teamTypeID = <?=$oDB->DBLookup("IFNULL(TeamTypeID, 3)", "teams", "TeamID=$pt")?>;
    rosterData = <?$oDB->DumpToJSArray("SELECT RiderID, RacingTeamID, CONCAT(FirstName, ' ', LastName) AS FullName, LastName, YearsCycling,
                                        IFNULL(Height, '-') AS Height, IFNULL(Weight, 0) AS Weight,
                                        IFNULL(RiderTypeID,100) AS RiderTypeID, IFNULL(RiderType, '(unkown)') AS RiderType,
                                        FLOOR(DATEDIFF(NOW(), DateOfBirth) / 365.25) AS Age,
                                        (SELECT COUNT(*) FROM ride_log WHERE RiderID=rider.RiderID AND YEAR(Date)=YEAR(NOW())),
                                        YTDMiles,
                                        CEDaysMonth
                                        FROM rider LEFT JOIN ref_rider_type USING (RiderTypeID)
                                        WHERE (RacingTeamID=$pt OR CommutingTeamID=$pt) AND Archived=false")?>
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
</head>

<body class="twoColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "Roster")?>
  </div>
  
  <div id="sidebarHolderRight">
    <? if($teamTypeID==2) { ?>
      <?ColumbusFoundationSidebar($oDB)?>
    <? } else { ?>
      <?SignupSidebar($oDB)?>
    <? } ?>
    <?if($pt==2) { ?> <!--Team Echelon sponsors are hard-coded for now-->
      <?SponsorSidebar($oDB)?>
    <? } ?>
    <?AdSidebar($oDB)?>
    <?CalendarSidebar($oDB, $pt)?>
    <?MostViewedRiderSidebar($oDB, $pt)?>
  </div>

  <div id="mainContent">
    <div id="roster-holder"></div>
  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>