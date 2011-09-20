<?
require("script/app-master.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");
require("dynamic-sections/events.php");

$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
$Editable = isset($_REQUEST['edit']) && CheckLogin();

// --- Get list of states and event types to filter schedule
$ScheduleFilterStates = isset($_COOKIE['ScheduleFilterStates']) ? $_COOKIE['ScheduleFilterStates'] : 'All';
$ScheduleFilterTypes = isset($_COOKIE['ScheduleFilterTypes']) ? $_COOKIE['ScheduleFilterTypes'] : 'All';
// --- if year is passed in use it. Otherwise default to current year
$ShowYear = (isset($_REQUEST['Year'])) ? SmartGetInt("Year") : date("Y");
// --- if start/end months are passed in, use them. Otherwise default to next three months
$StartMonth = (isset($_REQUEST['s'])) ? SmartGetInt("s") : intval(date("n"));
$EndMonth = (isset($_REQUEST['e'])) ? SmartGetInt("e") : min($StartMonth+2, 12);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta name="description" content="The Regional Event Schedule lists promoted cycling events, such as road races, criteriums, cyclocross, and organized tours. Find cycling events in your area">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, 0, "Regional Event Schedule")?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="/styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("/events.js")?>
  <?MinifyAndInclude("/dialogs/schedule-event-dialog.js")?>
  <?MinifyAndInclude("/script/ridenet-helpers.js")?>
<!-- Build javascript arrays for local/static combobox lookups -->
  <script type="text/javascript">
    g_pt=<?=$pt?>;
    g_domainRoot="<?=GetDomainRoot()?>";
    g_showYear=<?=$ShowYear?>;
    g_startMonth=<?=$StartMonth?>;
    g_endMonth=<?=$EndMonth?>;
    g_stateFilter = "<?=$ScheduleFilterStates?>";
    g_typeFilter = "<?=$ScheduleFilterTypes?>";
    stateLookup = <?$oDB->DumpToJSArray("SELECT StateID, StateName, StateAbbr FROM ref_states ORDER BY StateName")?>
    eventTypeLookup = <?$oDB->DumpToJSArray("SELECT RideTypeID, RideType, Picture FROM ref_event_type ORDER BY RideType")?>
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
<!-- facebook meta tags to provide information for the like button -->
  <meta property="og:title" content="Regional Event Schedule on RideNet" />
  <meta property="og:image" content="http://ridenet.net/images/ridenet-fb-logo3.png" />
  <meta property="og:site_name" content="RideNet" />
  <meta property="og:description" content="Visit RideNet to find bike tours, road races, criteriums, and other cycling events in your area." />
  <meta property="fb:app_id" content="147642135282357" />
</head>

<body class="oneColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "Schedule");?>
  </div>
  
  <div id="mainContent">
    <div style="float:left">
      <h1>Regional Event Schedule</h1>
    </div>
    <div style="float:left;margin-left:10px;position:relative;left:0px;top:12px">
      <? SocialMediaButtons("Find bike tours, road races, criteriums and other cycling events in your area on #RideNet") ?>
    </div>
    <? if(!$Editable) { ?>
      <div style="float:right;text-align:right;position:relative;left:0px;top:15px">
        <?if(CheckLogin()) { ?>
          <a id='edit-btn' href="/events?Year=<?=$ShowYear?>&s=<?=$StartMonth?>&e=<?=$EndMonth?>&edit">
        <? } else { ?>
          <a id='edit-btn' href="/login?Goto=<?=urlencode("../events?Year=$ShowYear&s=$StartMonth&e=$EndMonth&edit")?>">
        <? } ?>
          Add/Edit Events
        </a>
        <script type="text/javascript">
            new Ext.ToolTip({
                target: 'edit-btn',
                anchor: 'top',
                xanchorOffset: 20,
                dismissDelay: 0,
                showDelay: 200,
                width: 180,
                html: "<b>Enable Editing:</b> Add events to the schedule \
                       or edit events you've previously added.",
                padding: 5
              });
        </script>
      </div>
    <? } ?>
    <div class='clearfloat'></div>

<?  // Build string describing states
    $states = "";
    if($ScheduleFilterStates=="All")
    {
        $states .= "Entire U.S.";
    }
    else
    {
        $rs = $oDB->query("SELECT StateAbbr FROM ref_states WHERE StateID IN $ScheduleFilterStates", __FILE__, __LINE__);
        while(($record=$rs->fetch_array())!=false)
        {
            $states .= $record['StateAbbr'] . ", ";
        }
        $states = substr($states, 0, -2) . " ";  // remove trailing ","
    }
?>
    <table cellpadding=0 cellspacing=0 id="filter-holder"><tr>
      <td style="font:13px arial,helvetica;color:#888">Show:</td>
<?    $rs = $oDB->query("SELECT * FROM ref_event_type", __FILE__, __LINE__);
      while(($record=$rs->fetch_array())!=false)
      { 
        $checked = ($ScheduleFilterTypes=="All" OR strstr($ScheduleFilterTypes, $record['RideTypeID'])) ? "checked" : ""?>
        <td style="font-size:1px;line-height:1px;padding-top:2px">
          <input type="checkbox" id="filter<?=$record['RideTypeID']?>" <?=$checked?> style="margin-left:7px;width:13px" onClick="clickEventFilter(this)">
        </td>
        <td style="font-size:14px;line-height:14px;padding-top:2px">
        <span onclick="document.getElementById('filter<?=$record['RideTypeID']?>').click()">
          <img src="/images/event-types/<?=$record['Picture']?>" title="<?=$record['RideType']?>" class="tight" height=15>
        </span>
        </td>
      <? } ?>
      <td>
        <div class="grid-button" style="margin:0px 0px 0px 8px;color:#5074AF" id='event-filters-btn' onclick="g_filterDialog.show({ ypos:100, animateTarget: 'event-filters-btn' })">Location...</div>
      </td>
      <td>
        <div style="font:12px helvetica, arial;margin-left:6px;width:130px" class="secondary-color ellipses"><?=$states?></div>
      </td>
    </tr></table>
    <div style="height:20px"><!--vertical spacer--></div>
    <div style="font:13px arial,helvetica;color:#888;text-align:center">
      Months:
      <a class='action-btn linkcolor<?if($StartMonth==1 && $EndMonth==12) { ?> highlight<? } ?>' href="/events?Year=<?=$ShowYear?>&s=1&e=12<?=($Editable ? "&edit" : "")?>">
      Entire Year</a>
      <? for($month=1;$month<=12;$month++) { ?>
        <a class='action-btn linkcolor<?if($month >= $StartMonth && $month <=$EndMonth) { ?> highlight<? } ?>' href="/events?Year=<?=$ShowYear?>&s=<?=$month?>&e=<?=min($month+2,12)?><?=($Editable ? "&edit" : "")?>">
        <?=strtoupper(date("M",mktime(0,0,0,$month,1,2000)))?></a>
      <? } ?>
    </div>
    <div style="height:3px"><!--vertical spacer--></div>
  </div>

  <div id="extraWideContent" style="margin:-2px 0;padding:2px 0">  <!-- margin+padding is a fix for mobile Safari div seam lines artifact -->
    <div id='event-schedule-holder' align=center>
      <?RenderEventSchedule($oDB, $ScheduleFilterStates, $ScheduleFilterTypes, $ShowYear, $StartMonth, $EndMonth, $Editable)?>
    </div>
    <div style="height:20px"><!--vertical spacer--></div>
    <p align=center>
      <b>Other Years:</b>
<?   // Show list of other years
      $rs = $oDB->query("SELECT DISTINCT(YEAR(RaceDate)) AS Year FROM event WHERE RaceDate IS NOT NULL AND Archived=0 ORDER BY RaceDate", __FILE__, __LINE__);
      while(($record=$rs->fetch_array())!=false) { ?>
        <?if($record['Year']!=$ShowYear) { ?>
          <a href="/events?Year=<?=$record['Year']?>&s=1&e=12">[<?=$record['Year']?>]</a>&nbsp;
        <? } else { ?>
          [<?=$record['Year']?>]&nbsp;
        <? } ?>
      <? } ?>
    </p>
    <div style="height:20px"><!--vertical spacer--></div>
  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>