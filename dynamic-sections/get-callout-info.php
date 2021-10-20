<?
require("../script/app-master-min.php");
CheckRequiredParameters(Array('RiderID', 'rt'));

$oDB = oOpenDBConnection();
$riderID = SmartGetInt("RiderID");
$riderType = SmartGetInt("rt");    // 0 = commuter  1 = racer

// --- Get rider stats
$sql = "SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderID, TeamName, CEDaysMonth, CMilesDay, Y0_Miles AS YTDMiles
        FROM rider
        LEFT JOIN rider_stats USING (RiderID)
        LEFT JOIN teams ON (CommutingTeamID = TeamID)
        WHERE RiderID=$riderID";
$rs = $oDB->query($sql);
$stats = $rs->fetch_array();
$rs->free();
// --- Get most recent ride log entry within the last 30 days
$sql = "SELECT RideLogID, Date, RideLogType, RideLogTypeImage, Distance, Duration, Comment,
               IFNULL(Weather, 'N/A') AS Weather, IFNULL(WeatherImage, 'none.png') AS WeatherImage,
               DATEDIFF(NOW(), Date) AS Age
        FROM ride_log
        LEFT JOIN ref_ride_log_type USING (RideLogTypeID)
        LEFT JOIN ref_weather USING (WeatherID)
        WHERE RiderID=$riderID AND DATEDIFF(NOW(), Date) < 365
        ORDER BY Date DESC
        LIMIT 1";
$rs = $oDB->query($sql);
$logEntry = $rs->fetch_array();
$rs->free();
// generate age text
$ageText = ($logEntry['Age']==0) ? "today" : (($logEntry['Age']==1) ? "yesterday" : $logEntry['Age'] . "&nbsp;days&nbsp;ago");
// generate distance text
if(!is_null($logEntry['Distance']))
{
  $distanceText = $logEntry['Distance'] . "&nbsp;mile&nbsp;" . strtolower($logEntry['RideLogType']);
}
elseif(!is_null($logEntry['Duration']))
{
    $d = $logEntry['Duration'];
    $distanceText = (($d <= 90) ? $d . "&nbsp;minute&nbsp;" : number_format($d/60, 1) . "&nbsp;hour&nbsp;") . strtolower($logEntry['RideLogType']);
}
else
{
  $distanceText = $logEntry['RideLogType'];
}
?>

<div class="rider-callout" id="RC<?=$stats['RiderID']?>">
  <table cellpadding=0 cellspacing=0 border=0 width=100%><tr>
    <td>
      <div class="primary"><?=$stats['RiderName']?></div>
      <div class="secondary"><?=LimitString($stats['TeamName'],40)?></div>
    </td>
    <td style="padding-left:18px" align=center>
      <? if($riderType==1) { ?>
        <!--============ for racers show YTD miles ============-->
        <span class="primary"><?=$stats['YTDMiles']?></span><br>
        <span class="secondary">
          Miles&nbsp;YTD
        </span>
      <? } else { ?>
        <!--============ for commuters show days/month ============-->
        <table cellpadding=0 cellspacing=0><tr>
          <td class="primary" style="padding-right:5px"><?=$stats['CEDaysMonth']?></td>
          <td><img class="tight" src="/images/ridelog/tiny/commute.png"></td>
          <td><img class="tight" src="/images/ridelog/tiny/errand.png"></td>
        </tr></table>
        <span class="secondary">
          Days/Month
        </span>
      <? } ?>
    </td>
  </tr></table>
  <?if($logEntry!=false) { ?>
    <div style="height:5px"><!--vertical spacer--></div>
    <div style="border-bottom: 1px dotted"></div>
    <div style="height:5px"><!--vertical spacer--></div>
    <div class="secondary" style="margin-left:5px;margin-bottom:2px">Recent Ride:</div>
    <div class="ridetext" style="position:relative;margin-left:5px">
      <img style="position:relative;top:3px" src="/images/ridelog/<?=$logEntry['RideLogTypeImage']?>" height='14' title="<?=$logEntry['RideLogType']?>">
      <?if($logEntry['Weather']!="N/A") { ?>
        <img style="position:relative;top:3px" src="/images/weather/<?=$logEntry['WeatherImage']?>" height='14' title="<?=$logEntry['Weather']?>">
      <? } ?>
      <?=htmlentities($logEntry['Comment'])?><span class="ridetag">&nbsp;&bull; <?=$distanceText?>&nbsp;&bull; <?=$ageText?></span>
    </div>
    <div style="height:5px"><!--vertical spacer--></div>
  <? } ?>
  <div style="height:5px"><!--vertical spacer--></div>
  <div style="border-bottom: 1px dotted"></div>
  <div style="height:5px"><!--vertical spacer--></div>
  <? if($riderType==1) { ?>
  <!--============ For racers show results breakdown ============-->
<?  $sql = "SELECT RiderID, COUNT(*) AS TotalEvents,
                   SUM(PlaceID=1) AS 1ST,
                   SUM(PlaceID=2) AS 2ND,
                   SUM(PlaceID=3) AS 3RD,
                   SUM(PlaceID=4) AS 4TH,
                   SUM(PlaceID=5) AS 5TH,
                   SUM(PlaceID=6) AS 6TH,
                   SUM(PlaceID=7) AS 7TH,
                   SUM(PlaceID=8) AS 8TH,
                   SUM(PlaceID=9) AS 9TH,
                   SUM(PlaceID=10) AS 10TH
            FROM results LEFT JOIN event USING (RaceID)
            WHERE Year(RaceDate)=YEAR(NOW()) AND event.Archived=0 AND RiderID=$riderID
            GROUP BY RiderID";
    $rs = $oDB->query($sql);
    if(($results = $rs->fetch_array())==false) { ?>
      <span class="ridetext text50">No results have been entered this year</span>
    <? } else { ?>
      <table id="results-breakdown" border="1" cellpadding="0" cellspacing="0" width=280>
        <tr>
          <td class=header-xsm style="background-color:#000000;color:white">Events</td>
          <td class=header-xsm style="background-color:#FF0000">Wins</td>
          <td class=header-xsm style="background-color:#FF6600">2nd</td>
          <td class=header-xsm style="background-color:#FF9900">3rd</td>
          <td class=header-xsm style="background-color:#FFCC00">4th</td>
          <td class=header-xsm style="background-color:#FFCC33">5th</td>
          <td class=header-xsm style="background-color:#FFFF00">6th</td>
          <td class=header-xsm style="background-color:#FFFF33">7th</td>
          <td class=header-xsm style="background-color:#FFFF66">8th</td>
          <td class=header-xsm style="background-color:#FFFF99">9th</td>
          <td class=header-xsm style="background-color:#FFFFCC">10th</td>
        </tr>
        <tr>
          <td class=data-xsm><?=$results['TotalEvents']?></td>
          <td class=data-xsm><?=($results['1ST']) ? $results['1ST'] : "-"?></td>
          <td class=data-xsm><?=($results['2ND']) ? $results['2ND'] : "-"?></td>
          <td class=data-xsm><?=($results['3RD']) ? $results['3RD'] : "-"?></td>
          <td class=data-xsm><?=($results['4TH']) ? $results['4TH'] : "-"?></td>
          <td class=data-xsm><?=($results['5TH']) ? $results['5TH'] : "-"?></td>
          <td class=data-xsm><?=($results['6TH']) ? $results['6TH'] : "-"?></td>
          <td class=data-xsm><?=($results['7TH']) ? $results['7TH'] : "-"?></td>
          <td class=data-xsm><?=($results['8TH']) ? $results['8TH'] : "-"?></td>
          <td class=data-xsm><?=($results['9TH']) ? $results['9TH'] : "-"?></td>
          <td class=data-xsm><?=($results['10TH']) ? $results['10TH'] : "-"?></td>
        </tr>
      </table>
    <? } ?>
  <? } else { ?>
  <!--============ For commuters show YTD miles and length of commute ============-->
    <table cellpadding="0" cellspacing="0" width=100%><tr>
      <td style="padding:1px 5px" class="stats" align=left>
        <?=($stats['YTDMiles']) ? Plural($stats['YTDMiles'], "mile") . " YTD" : "0 miles YTD"?>
      </td>
      <td class="stats" style="padding:1px 8px" align=right>
        <?if($stats['CMilesDay']>0) { ?>
          <table cellspacing=0 cellpadding=0 class=centered><tr>
            <td style="padding-right:5px"><img class="tight" src="/images/ridelog/tiny/commute.png"></td>
            <td><?="I commute " . Plural($stats['CMilesDay'], "mile") . "/day"?></td>
          </tr></table>
        <? } else { ?>
          No commutes this period
        <? } ?>
      </td>
    </tr></table>
  <? } ?>
</div>
