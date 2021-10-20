<?
require("script/app-master.php");
require("dynamic-sections/calendar-sidebar.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented

// Determine which group to show
if(isset($_REQUEST['g']))
{
  // use the group passed in with the page request
  $group = $_REQUEST['g'];
}
elseif(CheckLogin())
{
  // use the group of the logged in rider
  $commutes = $oDB->DBLookup("CEDaysMonth", "rider", "RiderID=" . GetUserID());
  if($commutes >= 0 && $commutes <= 4)  $group = 1;
  elseif($commutes >= 5 && $commutes <= 9)  $group = 2;
  elseif($commutes >= 10 && $commutes <= 14)  $group = 3;
  elseif($commutes >= 15 && $commutes <= 19)  $group = 4;
  else $group = 5;
}
else
{
  // fallback to the 1 star group
  $group = 1;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, 0, "STAR Riders")?></title>
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
</head>

<body class="twoColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "Ranking")?>
  </div>
  <!-- This submenu is outside the header div so it floats side by side with the right column -->
  <?InsertRidingMenu($group)?>

  <div id="sidebarHolderRight">
    <?ColumbusFoundationSidebar($oDB)?>
    <?AdSidebar()?>
    <?CalendarSidebar($oDB, $pt)?>
    <?MostViewedRiderSidebar($oDB, $pt)?>
  </div>

  <div id="mainContent">
<?  
    switch($group) {
      case 5:
        $minDays = 20;
        $maxDays = 200;
        $description = "<img src='/images/stars/star5.png'> 20 or more";
        break;
      case 4:
        $minDays = 15;
        $maxDays = 19;
        $description = "<img src='/images/stars/star4.png'> 15 to 19";
        break;
      case 3:
        $minDays = 10;
        $maxDays = 14;
        $description = "<img src='/images/stars/star3.png'> 10 to 14";
        break;
      case 2:
        $minDays = 5;
        $maxDays = 9;
        $description = "<img src='/images/stars/star2.png'> 5 to 9";
        break;
      case 1:
        $minDays = 2;
        $maxDays = 4;
        $description = "<img src='/images/stars/star1.png'> 2 to 4";
        break;
      default:
        $minDays = 0;
        $maxDays = 0;
        $description = "Riders that have no commutes or errands";
        break;
    }

    $sql = "SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderID, RacingTeamID, CEDaysMonth, Domain
            FROM rider
            LEFT JOIN rider_stats USING (RiderID)
            LEFT JOIN teams ON (CommutingTeamID = TeamID)
            LEFT JOIN rider_photos USING (RiderID)
            WHERE rider.Archived=0 AND CEDaysMonth BETWEEN $minDays AND $maxDays
            GROUP BY RiderID
            ORDER BY CEDaysMonth DESC";
    $rs = $oDB->query($sql);
    $riderCount = $rs->num_rows;
?>
    <h1>Every Commuter is a STAR</h1>
    <h2>
      <?=$description?>
      <img class='tight' src='/images/ridelog/commute.png' style='position:relative;top:-2px' height=13><img class='tight' src='/images/ridelog/errand.png' style='position:relative;top:-2px' height=13>
      days/month. Commutes and Errands
    </h2>
    <div style="height:5px"></div>
    <div class="commute-ride-group" style="width:550px">
<?    if($riderCount==0)
      { ?>
        <p class="no-data">(No riders in this group)</p>
<?    }
      else
      {
        $first=true;
        while(($record=$rs->fetch_array())!=false)
        {
          $picHeight = ($first) ? 120 : 58;
          $picWidth = ($first) ? 96 : 46;
          $first = false; ?>
          <div id="R<?=$record['RiderID']?>" class="photobox">
            <a href="<?=BuildTeamBaseURL($record['Domain'])?>/rider/<?=$record['RiderID']?>">
              <img class="tight" src="<?=GetFullDomainRoot()?>/imgstore/rider-portrait/<?=$record['RacingTeamID']?>/<?=$record['RiderID']?>.jpg" height=<?=$picHeight?> width=<?=$picWidth?> border="0">
            </a>
          </div><script type="text/javascript">riderInfoCallout(<?=$record['RiderID']?>, '')</script>
        <? } ?>
      <? } ?>
      <br class="clearfloat" /> 
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
//            img.scrollIntoView(document.body);
    <? } ?>
});
</script>
