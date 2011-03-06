<?
require("script/app-master.php");
require("dynamic-sections/commuting.php");
require("dynamic-sections/calendar-sidebar.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
RecordPageView($oDB);
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
  <link href="styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("dialogs/calendar-event-dialog.js")?>
  <?MinifyAndInclude("script/ridenet-helpers.js")?>
  <?MinifyAndInclude("commuting.js")?>
<!-- Build javascript arrays for local/static combobox lookups -->
  <script type="text/javascript">
    g_rideBoardLength = <?=$RideBoardLength?>;
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
<!-- facebook meta tags to provide information for the like button -->
  <meta property="og:title" content="Central Ohio Bike Bus" />
  <meta property="og:image" content="http://ridenet.net/images/2by2012-fb-logo3.png" />
  <meta property="og:site_name" content="RideNet" />
  <meta property="og:description" content="Starting Friday, April 1, Consider Biking will be commencing “bike bus service” from the 4 quadrants of the city into downtown. Buses will “run/ride” two days each week, on Tuesdays and Fridays from April through October." />
  <meta property="fb:app_id" content="147642135282357" />
</head>

<body class="twoColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "Ranking")?>
  </div>

  <div id="sidebarHolderRight">
    <?ColumbusFoundationSidebar($oDB)?>
    <?AdSidebar($oDB)?>
    <?CalendarSidebar($oDB, $pt)?>
    <?MostViewedRiderSidebar($oDB, $pt)?>
  </div>

  <div id="mainContent">
    <div style="float:left">
      <h1>Take a Ride on the Bike Bus</h1>
    </div>
    <div style="float:left;margin-left:10px;position:relative;left:0px;top:12px">
      <?SocialMediaButtons("Take a ride on the Central Ohio Bike Bus")?>
    </div>
    <div class='clearfloat'></div>
    <div style="height:20px"></div>
    <div style="width:480px;margin: 0 auto">
      <img src="images/bikebus1.jpg" style="border:1px solid #DDD" width=480>
    </div>
    <div style="height:30px"></div>
    <p class="newp">
      Starting Friday, April 1, Consider Biking will be commencing “bike bus service” from the 4
      quadrants of the city into downtown. Buses will “run/ride” two days each week, on Tuesdays
      and Fridays from April through October.
    </p>
    <p class="newp">
      The concept is this: we expect to have lots of new riders biking to work as part of 2 by 2012. These newbies
      will be a lot more comfortable riding on the bike paths and streets if (1) they are riding in a group (visibility
      and safety in numbers) and (2) there is an experienced cyclist(s) leading the group, demonstrating where to ride
      in the lane, etc.
    </p>
    <div style="float:right;padding: 0 20px">
      <img src="dynamic-images/rider-portrait.php?RiderID=149&T=13" style="border:1px solid #DDD">
      <p class=photo-caption>Doug Morgan</p>
    </div>
    <p class="newp">
      To get things started <a href="http://hahnlaw.ridenet.net/profile.php?RiderID=149">Doug Morgan</a> will be
      leading the "bike bus" down High Street from Morse and High to downtown, making stops at
      designated locations (e.g. coffee shops) along the way to pick up additional riders.
    </p>
    <p class="newp">
      If you’re interested in leading a bike bus, email Doug Morgan <a href="mailto:DMorgan@hahnlaw.com">dmorgan@hahnlaw.com</a>.
      You don’t have to commit to do it every day—we’ll get other cyclists from your part of town to share the load.
      If you don’t commute downtown, you may wish to create your own bus route from your neighborhood to your workplace. We
      can help you plan your route and publicize it on RideNet.
    </p>
    <p class="newp">
      We'll host a short training session for bike bus drivers later this month at Consider Biking’s office in
      Clintonville. Please spread the word and let Doug know if you have any friends that might be interested in pitching in.
    </p>
  

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
