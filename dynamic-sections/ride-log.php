<?
if(isset($_REQUEST['pb']))
{
    require("../script/app-master.php");
    CheckRequiredParameters(Array('RiderID', 'l'));
    $length = $_REQUEST['l'];
    if(isset($_REQUEST['edit']) && CheckLogin())
    {
      $riderID = GetUserID();   // always show profile of logged in rider
      $editable = true;
    }
    else
    {
      $riderID = SmartGetInt("RiderID");
      $editable=false;
    }

    $oDB = oOpenDBConnection();
    RenderRideLOG($oDB, $riderID, $length, $editable);
}


//----------------------------------------------------------------------------------
//  RenderRideLog()
//
//  This function renders the content of the Ride Log.
//
//  PARAMETERS:
//    oDB       - database connection (mysqli object)
//    riderID   - ID of rider
//    length    - number of days to render
//    editable  - true if ride log should be editable
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RenderRideLog($oDB, $riderID, $length, $editable)
{?>
<?    $sql = "SELECT RideLogID, Date, RideLogType, RideLogTypeImage, Distance, Duration, Comment, Link, Source,
                   IF(HasMap, RideLogID, 0) AS MapID, DATE_SUB(Date, INTERVAL WEEKDAY(Date) DAY) AS FirstDayOfWeek,
                   IFNULL(Weather, 'N/A') AS Weather, IFNULL(WeatherImage, 'none.png') AS WeatherImage,
                   DATEDIFF(NOW(), Created) AS Age
              FROM ride_log rl
              LEFT JOIN ref_weather USING (WeatherID)
              LEFT JOIN ref_ride_log_type USING (RideLogTypeID)
              WHERE RiderID=$riderID
              ORDER BY Date DESC, Created DESC
              LIMIT $length";
      $rs = $oDB->query($sql, __FILE__, __LINE__);
      if($rs->num_rows==0) { ?>
          <div class=no-data-rp style="width:625px;text-align:left">
            No rides have been logged
            <?if($editable) { ?>
              <span style="position:relative;margin-left:365px;top:-1px" class='action-btn' id='log-ride-btn' onclick="clickAddRide(this.id);">+ Log A Ride</span>
            <? } ?>
          </div>
      <? } else { ?>
        <div style="height:5px"></div>
        <?if($editable) { ?>
          <span class='action-btn' id='log-ride-btn' style="position:absolute;top:-15px;left:622px" onclick="clickAddRide(this.id);">+ Log A Ride</span>
        <? } ?>
        <table id="ride-log" cellpadding=0 cellspacing=0 border=0>
<?        $rideCount = 0;
          $previousWeek = 0;
          $firstrow=true;
          $mapPrivacy = $oDB->DBLookup("MapPrivacy", "rider", "RiderID=$riderID");
          while(($record = $rs->fetch_array())!=false)
          {
            $currentWeek = date_create($record['Date'])->format("W");
            $currentYear = date_create($record['Date'])->format("Y");
            if($currentWeek!=$previousWeek)
            {
              $previousWeek = $currentWeek;
              // lookup totals for the week
              $weekStart = date_create($record['FirstDayOfWeek'])->format('Y-m-d');
              $weekEnd = AddDays(date_create($record['FirstDayOfWeek']),6)->format('Y-m-d');
              $sql = "SELECT Count(*) AS Rides, SUM(Distance) AS Distance, SUM(Duration) AS Duration
                      FROM ride_log
                      WHERE RiderID=$riderID AND Date BETWEEN '$weekStart' AND '$weekEnd'";
              $rs2 = $oDB->query($sql, __FILE__, __LINE__);
              $totals = $rs2->fetch_array();
              $rs2->free(); ?>
              <!--Output week header-->
              <tr>
                <td colspan=5 class="week-header">
                  Week of <?=date_create($record['FirstDayOfWeek'])->format("F j, Y")?>
                </td>
                <td colspan=2 class="week-header" style="text-align:right">
                  <span class="summary">
                     <?=($totals['Rides']==1) ? "1 Ride" : "{$totals['Rides']} Rides"?>
                     <?=($totals['Distance']) ? "&nbsp;&bull;&nbsp;&nbsp;" . Plural($totals['Distance'], "Mile") : ""?>
                     <?=($totals['Duration']) ? "&nbsp;&bull;&nbsp;&nbsp;" . number_format($totals['Duration']/60, 1) . " hours" : ""?>
                  </span>
                </td>
                <?if($editable) { ?>
                  <td class="week-header">
                    <?if($firstrow) { $firstrow=false; ?>
                      <span class="instructions">&nbsp;Copy/Edit</span>
                    <? } else { // &nbsp; needed for IE7 (POS)?>
                      &nbsp;
                    <? } ?>
                  </td>
                <? } ?>
              </tr>
            <? } ?>
            <!--Ride-->
            <tr>
              <td class="data" width="60"><?=date_create($record['Date'])->format("D n/j")?></td>
              <td class="data" width="60" style="text-align:center"><img src="<?=GetFullDomainRoot()?>/images/ridelog/<?=$record['RideLogTypeImage']?>" title="<?=$record['RideLogType']?>"></td>
              <td class="data" width="60">
                <?=$record['Distance'] ? Plural($record['Distance'], "mile") : "&nbsp;"?>
              </td>
              <td class="data" width="30" style="text-align:center">
                <?=$record['Duration'] ? ($record['Duration'] <= 90) ? $record['Duration'] . "&nbsp;min" : number_format($record['Duration']/60, 1) . "&nbsp;hr" : "&nbsp;"?>
              </td>
              <td class="data" width="55" style="text-align:center">
                <img src="<?=GetFullDomainRoot()?>/images/weather/<?=$record['WeatherImage']?>" title="<?=$record['Weather']?>">
              </td>
                <?if($record['Source']==2) { ?> <td class="comment" width="340"> <? } else { ?> <td class="comment" width="355" colspan=2> <? } ?>
                <?=BuildRideLogComment(htmlentities($record['Comment']), $record['Link'], $record['MapID'], IsMapVisible($mapPrivacy))?>&nbsp;
              </td>
              <?if($record['Source']==2) { ?>
                <td class="comment" width="13" style="text-align:right">
                  <a href="/mobile" target="_blank">
                    <img src="/images/mobile.gif" title="RideNet Mobile">
                  </a>
                </td>
              <? } ?>
              <?if($editable) { ?>
                <td class="data" width="50" align=left style="padding-left:10px">
                  <span class='action-btn-sm' style="color:#009A00" id='copy-btn<?=$record['RideLogID']?>' onclick="clickCopyRide(<?=$record['RideLogID']?>);" title="Log a new ride based on this one">&nbsp;C&nbsp;</span>
                  <?if($record['Age'] < 5) { ?>
                    <span class='action-btn-sm' id='edit-btn<?=$record['RideLogID']?>' onclick="clickEditRide(<?=$record['RideLogID']?>);" title="Edit this ride">&nbsp;E&nbsp;</span>
                  <? } ?>
                </td>
              <? } ?>
            </tr>
<?        $rideCount++;
          }?>
        </table>
        <?if($rideCount==$length) { ?>
          <div class='more-btn' onclick="getMore(30)">MORE RIDES</div>
        <? } ?>
      <? } ?>
<?
}
?>