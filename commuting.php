<?
require("script/app-master.php");
require("dynamic-sections/commuting.php");
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
  <title><?BuildPageTitle($oDB, 0, "Commuting Home")?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="/styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("/dialogs/calendar-event-dialog.js")?>
  <?MinifyAndInclude("/script/ridenet-helpers.js")?>
  <?MinifyAndInclude("/commuting.js")?>
<!-- Build javascript arrays for local/static combobox lookups -->
  <script type="text/javascript">
    g_rideBoardLength = <?=$RideBoardLength?>;
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
<!-- facebook meta tags to provide information for the like button -->
  <meta property="og:title" content="Commuting on RideNet" />
  <meta property="og:image" content="http://ridenet.net/images/2by2012-fb-logo3.png" />
  <meta property="og:site_name" content="RideNet" />
  <meta property="og:description" content="Stay up to date with the Central Ohio commuting scene" />
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
  <?InsertCommutingMenu("Commuting")?>

  <div id="sidebarHolderRight">
    <?ColumbusFoundationSidebar($oDB)?>
    <?AdSidebar($oDB)?>
    <?CalendarSidebar($oDB, $pt)?>
    <?MostViewedRiderSidebar($oDB, $pt)?>
  </div>

  <div id="mainContent">
    <div style="float:left">
      <h1><a href="/bike-bus.php">Take a Ride on the Bike Bus</a></h1>
    </div>
    <div style="float:left;margin-left:10px;position:relative;left:0px;top:12px">
      <?SocialMediaButtons("Stay up to date with the Central Ohio commuting scene on #RideNet")?>
    </div>
    <div class='clearfloat'></div>
    <p>
      To help new commuters coming on the scene, Consider Biking is coordinating "bike bus service"
      from the 4 quadrants of the city into downtown. We're looking for veteran commuters to "drive" the buses
      and show the newbies the ropes. <a href="/bike-bus.php">Read more...</a>
    </p>
    <a href="/clothing.php">
      <img style="float:right;margin:15px 20px 0 20px;" src="/images/clothing/ridenet-jersey1.png" Height=60 border=0>
    </a>
    <h1><a href="/clothing.php">RideNet Clothing</a></h1>
    <p>
      We're selling cycling clothing to promote RideNet. A portion of each order will be
      donated to Consider Biking to support 2BY2012. <a href="/clothing.php" style="color:red">Details...</a>
    </p>
<?  // Get top 22 commuters sorted by Commute/Errand days in the last 30 days
    $sql = "SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderID, RacingTeamID, CEDaysMonth, Domain,
                   COUNT(DISTINCT IF(RideLogTypeID=1 OR RideLogTypeID=3, Date, NULL)) AS CEDays30
            FROM ride_log
            LEFT JOIN rider USING (RiderID)
            LEFT JOIN teams ON (CommutingTeamID = TeamID)
            WHERE rider.Archived=0 AND DATEDIFF(NOW(), Date) < 30
            GROUP BY RiderID
            ORDER BY CEDays30 DESC
            LIMIT 22";
    $rs = $oDB->query($sql, __FILE__, __LINE__); ?>
    
    <div class="clearfloat" style="height:5px"></div>
    <div style="padding:5px;border-bottom:1px dotted #CCC;border-top:1px dotted #CCC">
      <h2 style="margin:0px">Who's Commuting This Week</h2>
    </div>
    <div class="clearfloat" style="height:1px"></div>
    <div style="height:15px"></div>
    <div class="commute-ride-group" style="width:550px">
<?    while(($record=$rs->fetch_array())!=false) { ?>
        <div id="R<?=$record['RiderID']?>" class="photobox">
          <a href="<?=BuildTeamBaseURL($record['Domain'])?>/profile.php?RiderID=<?=$record['RiderID']?>">
            <img class="tight" src="<?=GetFullDomainRoot()?>/dynamic-images/rider-portrait.php?RiderID=<?=$record['RiderID']?>&T=<?=$record['RacingTeamID']?>" height=58 width=46 border="0">
          </a>
          <div class="countbox">
            <?=$record['CEDaysMonth']?>
          </div>
        </div><script type="text/javascript">riderInfoCallout(<?=$record['RiderID']?>, '')</script>
      <? } ?>
      <br class="clearfloat" /> 
    </div>
    <div class="clearfloat" style="height:10px"></div>

    <div id='commuting-wall' class='ridenet-wall' style="padding:0 50px 0 25px ">
      <? RenderCommutingWall($oDB, $RideBoardLength) ?>
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
