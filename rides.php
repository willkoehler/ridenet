<?
require("script/app-master.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");
require("dynamic-sections/rides.php");

$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
$CalendarWeeks = 8;  // number of weeks to show in calendar

// --- Get calendar filter zip code and range from cookies.
$defaultZipCode = $oDB->DBLookup("ZipCodeID", "teams", "TeamID=$pt", 43214);
$CalendarFilterRange = isset($_COOKIE['CalendarFilterRange']) ? $_COOKIE['CalendarFilterRange'] : 100;
$CalendarFilterZip = isset($_COOKIE['CalendarFilterZip']) ? $_COOKIE['CalendarFilterZip'] : $defaultZipCode;
$rs = $oDB->query("SELECT *, CONCAT(City, ', ', State, ' ', ZipCode) AS ZipCodeText
                   FROM ref_zipcodes WHERE ZipCodeID=" . IntVal($CalendarFilterZip));
$record = $rs->fetch_array();
$CalendarLongitude = ($record==false) ? 0 : $record['Longitude'];
$CalendarLatitude = ($record==false) ? 0 : $record['Latitude'];
$ZipCodeText = ($record==false) ? "(unknown)" : $record['ZipCodeText'];
// filter rides by team based on presence of 'tf' query parameter
$teamFilter = isset($_REQUEST['tf']) ? $pt : 0;
$teamName = $oDB->DBLookup("TeamName", "teams", "TeamID=$pt");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta name="description" content="The Community Ride Calendar lists club rides, training rides, group rides. Find where other cyclists are riding in your area">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, 0, "Community Ride Calendar")?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="/styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("/rides.js")?>
  <?MinifyAndInclude("/dialogs/calendar-event-dialog.js")?>
  <?MinifyAndInclude("/dialogs/location-dialog.js")?>
  <?MinifyAndInclude("/script/ridenet-helpers.js")?>
<!-- Build javascript arrays for local/static combobox lookups -->
  <script type="text/javascript">
    g_domainRoot="<?=GetDomainRoot()?>";
    g_calendarWeeks = <?=$CalendarWeeks?>;
    g_teamFilter = "<?=$teamFilter?>";
    g_pt = <?=$pt?>
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
<!-- facebook meta tags to provide information for the like button -->
  <meta property="og:title" content="Community Ride Calendar on RideNet" />
  <meta property="og:image" content="http://ridenet.net/images/ridenet-fb-logo3.png" />
  <meta property="og:site_name" content="RideNet" />
  <meta property="og:description" content="Visit RideNet to find bike rides in your area." />
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
      <h1>Community Ride Calendar</h1>
    </div>
    <div style="float:left;margin-left:10px;position:relative;left:0px;top:12px">
      <? SocialMediaButtons("Find bike rides in your area using the Community Ride Calendar on #RideNet") ?>
    </div>
    <div style="float:right;text-align:right;position:relative;left:0px;top:15px">
      <span id='help-btn' onclick="g_helpDialog.show({ ypos:100, animateTarget: 'help-btn' });">
        What is this?
      </span>
    </div>
    <div class='clearfloat'></div>

    <table cellpadding=0 cellspacing=0><tr>
      <td><h2 style="margin:0px">Showing rides within <?=$CalendarFilterRange?> miles of <?=$ZipCodeText?></h2></td>
      <td>
        <div class="grid-button" style="margin:0px 0px 0px 10px;color:#5074AF" id='event-filters-btn' onclick="g_locationDialog.show({ ypos:100, animateTarget: 'event-filters-btn', zipCodeText: '<?=$ZipCodeText?>', zipCode: '<?=$CalendarFilterZip?>', range: '<?=$CalendarFilterRange?>' })">Set&nbsp;Location...</div>
      </td>
    </tr></table>

    <?if($pt!=0) { ?>
      <div style="height:3px"></div>
      <table border=0 cellpadding=0 cellspacing=0><tr>
        <td valign=center>
          <h2 style="margin:0px">Showing rides posted by</h2>
        </td>
        <td valign=center style="padding: 2px 0 0 5px">
          <SELECT onChange="window.location.href='/rides' + options[selectedIndex].value">
            <OPTION value='' <?if($teamFilter==0) {?>selected<? } ?>>All RideNet Teams
            <OPTION value='?tf' <?if($teamFilter > 0) {?>selected<? } ?>><?=$teamName?>
          </SELECT>
        </td>
      </tr></table>
    <? } ?>
    
  </div>
    
  <div id="extraWideContent">  
    <div id='ride-calendar-holder' align=center>
      <?RenderRideCalendar($oDB, $CalendarFilterRange, $CalendarLongitude, $CalendarLatitude, $CalendarWeeks, $teamFilter)?>
    </div>
  </div>
  
  <div id="mainContent">
    <?InsertRideClassKey()?>
  </div><!-- end #mainContent -->
  <br class="clearfloat" />  <!-- clear all floating elements -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>
