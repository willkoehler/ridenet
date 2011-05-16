<?
require("script/app-master.php");
require("dynamic-sections/calendar-sidebar.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
CheckLoginAndRedirect();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, $pt, "Following")?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="/styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("/following.js")?>
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
    <?InsertMainMenu($oDB, $pt, "Following")?>
  </div>

  <div id="sidebarHolderRight">
    <?SignupSidebar($oDB)?>
    <?AdSidebar($oDB)?>
    <?CalendarSidebar($oDB, $pt)?>
    <?MostViewedRiderSidebar($oDB, $pt)?>
  </div>

  <div id="mainContent">
    <div id="form-holder"></div>
    <div style="height:10px"></div>

<?    $pageSize = 50;
      $offset = 0;
      $riderID = 15;
      $sql = "SELECT RiderID, RacingTeamID, Domain, CONCAT(FirstName, ' ', LastName) AS RiderName, RideLogID, Date, RideLogType,
                     RideLogTypeImage, Distance, Comment, DATE_SUB(Date, INTERVAL WEEKDAY(Date) DAY) AS FirstDayOfWeek,
                     IFNULL(Weather, 'N/A') AS Weather, IFNULL(WeatherImage, 'none.png') AS WeatherImage
              FROM ride_log rl
              LEFT JOIN ref_weather USING (WeatherID)
              LEFT JOIN ref_ride_log_type USING (RideLogTypeID)
              LEFT JOIN rider USING (RiderID)
              LEFT JOIN teams ON (RacingTeamID = TeamID)
              LEFT JOIN following ON (FollowingID=RiderID)
              WHERE FollowerID=$riderID
              ORDER BY Date DESC
              LIMIT $offset, $pageSize";
      $rs = $oDB->query($sql, __FILE__, __LINE__);
      if($rs->num_rows==0) { ?>
          <div class=no-data-rp style="width:625px;text-align:left">
            No rides have been logged
          </div>
<?    }
      else
      { ?>
        <div style="height:5px"></div>
        <table id="ride-log" cellpadding=0 cellspacing=0 border=0>
<?        $rideCount = 0;
          $previousDay = '';
          $firstrow=true;
          while(($record = $rs->fetch_array())!=false)
          {
            $currentDay = $record['Date'];
            if($currentDay!=$previousDay) { ?>
              <?if($previousDay!='') { ?>
              <!--Spacing between days-->
                <tr><td class="table-spacer" style="height:20px" colspan=5>&nbsp;</td></tr>
              <? } ?>
<?            $previousDay = $currentDay;?>
              <!--Output day header-->
              <tr>
                <td colspan=5 class="week-header">
                  <?=date_create($record['Date'])->format("l - F j, Y")?>
                </td>
              </tr>
            <? } ?>
            <!--Ride-->
            <tr>
              <td class="data" width="35" style="text-align:center">
                <a href="<?=BuildTeamBaseURL($record['Domain'])?>/profile.php?RiderID=<?=$record['RiderID']?>">
                  <img src="<?=GetFullDomainRoot()?>/dynamic-images/rider-portrait.php?RiderID=<?=$record['RiderID']?>&T=<?=$record['RacingTeamID']?>" class="tight" style="height:35px" title="<?=$record['RiderName']?>">
                </a>
              </td>
              <td class="data" width="35" style="text-align:center"><img src="/images/ridelog/<?=$record['RideLogTypeImage']?>" title="<?=$record['RideLogType']?>"></td>
              <td class="data" width="55"><?=Plural($record['Distance'], "mile")?></td>
              <td class="data" width="35" style="text-align:center"><img src="/images/weather/<?=$record['WeatherImage']?>" title="<?=$record['Weather']?>"></td>
              <td class="data" width="355"><?=$record['Comment']?>&nbsp;</td>
            </tr>
<?        $rideCount++;
          }?>
        </table>
      <? } ?>

  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>