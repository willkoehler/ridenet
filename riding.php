<?
require("script/app-master.php");
require("dynamic-sections/riding.php");
require("dynamic-sections/calendar-sidebar.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
$RideBoardLength = 30;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, 0, "Riding and Commuting Home")?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="/styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("/dialogs/calendar-event-dialog.js")?>
  <?MinifyAndInclude("/script/ridenet-helpers.js")?>
  <?MinifyAndInclude("/riding.js")?>
<!-- Build javascript arrays for local/static combobox lookups -->
  <script type="text/javascript">
    g_rideWallLength = <?=$RideBoardLength?>;
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
<!-- facebook meta tags to provide information for the like button -->
  <meta property="og:title" content="Riding and Commuting Scene on RideNet" />
  <meta property="og:image" content="http://ridenet.net/images/ridenet-fb-logo3.png" />
  <meta property="og:site_name" content="RideNet" />
  <meta property="og:description" content="Stay up to date with the Central Ohio riding scene. Find cycling events and casual rides in your area. Signup to create a rider profile." />
  <meta property="fb:app_id" content="147642135282357" />
</head>

<body class="twoColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "Riding")?>
  </div>
  <!-- This submenu is outside the header div so it floats side by side with the right column -->
  <?InsertRidingMenu("Riding")?>

  <div id="sidebarHolderRight">
    <?ColumbusFoundationSidebar($oDB)?>
    <?AdSidebar()?>
    <?CalendarSidebar($oDB, $pt)?>
    <?MostViewedRiderSidebar($oDB, $pt)?>
  </div>

  <div id="mainContent">

<?  $riderCount = $oDB->DBCount("rider", "true");
    $teamCount = $oDB->DBCount("teams", "true");
    $starCount = $oDB->DBCount("rider_stats", "CEDaysMonth>=2");
    $totalMiles = $oDB->DBLookup("SUM(Distance)", "ride_log", "true");?>

    <div style="float:left">
      <h1>Riding and Commuting Scene</h1>
    </div>
    <div style="float:left;margin-left:10px;position:relative;left:0px;top:12px">
      <?SocialMediaButtons("Stay up to date with the Central Ohio riding scene on #RideNet")?>
    </div>
    <div class='clearfloat'></div>
    <p class='text50' style="line-height:1.2em;font-size:.7em;margin-bottom:0px">
      * STAR riders use their bikes for commuting or errands two days a month.
    </p>
    <div style="font:30px arial, 'helvetica neue', sans-serif;line-height:21px;padding:25px 0 5px 0;text-align:center">
      <table cellpadding=0 cellspacing=0 class="centered"><tr>
        <td width=100><div><?=$teamCount?><div class="text50" style="font-size:13px">&nbsp;Teams</div></div></td>
        <td width=110><?=$riderCount?><div class="text50" style="font-size:13px">&nbsp;Riders</div></td>
        <td width=140><?=number_format($totalMiles)?><div class="text50" style="font-size:13px">&nbsp;Total Miles</div></td>
        <td width=110><?=$starCount?><div class="text50" style="font-size:13px">&nbsp;STARs *</div></td>
      </tr></table>
    </div>

<?  // Get top 22 commuters sorted by Commute/Errand days in the last 30 days (skip riders that don't have pictures)
    $sql = "SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, rider.RiderID, RacingTeamID, CEDaysMonth, Domain,
                   COUNT(DISTINCT IF(RideLogTypeID=1 OR RideLogTypeID=3, Date, NULL)) AS CEDays30
            FROM ride_log
            LEFT JOIN rider USING (RiderID)
            LEFT JOIN rider_stats USING (RiderID)
            LEFT JOIN teams ON (CommutingTeamID = teams.TeamID)
            JOIN rider_photos ON (rider_photos.RiderID=rider.RiderID and rider_photos.TeamID=RacingTeamID)
            WHERE rider.Archived=0 AND DATEDIFF(NOW(), Date) < 30
            GROUP BY RiderID
            ORDER BY CEDaysMonth DESC
            LIMIT 22";
    $rs = $oDB->query($sql); ?>
    
    <div class="clearfloat" style="height:5px"></div>
    <div style="padding:5px;border-bottom:1px dotted #CCC;border-top:1px dotted #CCC">
      <h2 style="margin:0px">Top Commuters, days/month</h2>
    </div>
    <div class="clearfloat" style="height:1px"></div>
    <div style="height:5px"></div>
    <div class="commute-ride-group" style="width:550px">
      <? while(($record=$rs->fetch_array())!=false) { ?>
        <div id="R<?=$record['RiderID']?>C" class="photobox">
          <a href="<?=BuildTeamBaseURL($record['Domain'])?>/rider/<?=$record['RiderID']?>">
            <img class="tight" src="<?=GetFullDomainRoot()?>/imgstore/rider-portrait/<?=$record['RacingTeamID']?>/<?=$record['RiderID']?>.jpg" height=58 width=46 border="0">
          </a>
          <div class="countbox">
            <?=$record['CEDaysMonth']?>
          </div>
        </div><script type="text/javascript">riderInfoCallout(<?=$record['RiderID']?>, 'C')</script>
      <? } ?>
      <br class="clearfloat" /> 
    </div>
    <div class="clearfloat" style="height:10px"></div>

<?  // Get top 22 riders sorted by miles in the last 30 days (skip riders that don't have pictures)
    $sql = "SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, rider.RiderID, RacingTeamID, Y0_Miles, Domain,
                   SUM(Distance) AS Miles30
            FROM ride_log
            LEFT JOIN rider USING (RiderID)
            LEFT JOIN rider_stats USING (RiderID)
            LEFT JOIN teams ON (RacingTeamID = teams.TeamID)
            JOIN rider_photos ON (rider_photos.RiderID=rider.RiderID and rider_photos.TeamID=RacingTeamID)
            WHERE rider.Archived=0 AND DATEDIFF(NOW(), Date) < 30
            GROUP BY RiderID
            ORDER BY Miles30 DESC
            LIMIT 22";
    $rs = $oDB->query($sql); ?>
    
    <div class="clearfloat" style="height:5px"></div>
    <div style="padding:5px;border-bottom:1px dotted #CCC;border-top:1px dotted #CCC">
      <h2 style="margin:0px">Top Mileage, last 30 days</h2>
    </div>
    <div class="clearfloat" style="height:1px"></div>
    <div style="height:5px"></div>
    <div class="commute-ride-group" style="width:550px">
      <? while(($record=$rs->fetch_array())!=false) { ?>
        <div id="R<?=$record['RiderID']?>R" class="photobox">
          <a href="<?=BuildTeamBaseURL($record['Domain'])?>/rider/<?=$record['RiderID']?>">
            <img class="tight" src="<?=GetFullDomainRoot()?>/imgstore/rider-portrait/<?=$record['RacingTeamID']?>/<?=$record['RiderID']?>.jpg" height=58 width=46 border="0">
          </a>
          <div class="countbox">
            <?=$record['Miles30']?>
          </div>
        </div><script type="text/javascript">riderInfoCallout(<?=$record['RiderID']?>, 'R')</script>
      <? } ?>
      <br class="clearfloat" /> 
    </div>
    <div class="clearfloat" style="height:10px"></div>

    <div id='riding-wall' class='ridenet-wall' style="padding:0 50px 0 25px ">
      <? RenderRidingWall($oDB, $RideBoardLength) ?>
    </div>
  

  </div><!-- end #mainContent -->
  <br class="clearfloat" />  <!-- clear all floating elements -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>

<script type="text/javascript">

Ext.onReady(function()
{
    // Highlight current user's photo
    <?if(CheckLogin()) { ?>
        var img = Ext.get('R<?=GetUserID()?>');
        if(img)
        {
            img.pause(.25);
            img.frame("ff0000", 2, { duration: .5 });
        }
    <? } ?>
});
</script>
