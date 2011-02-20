<?
//----------------------------------------------------------------------------------
//  RenderWall()
//
//  This function renders the RideNet Wall. The specific contents of the wall depend
//  on the result object passed in. Some pages display ride logs, race results, and
//  posts. Other pages just display ride logs.
//
//  PARAMETERS:
//    rs            - mysqli result object containing data for the ride board
//    pt            - ID of the presented team
//    showTime      - true to show time of messages
//    showHeaders   - true to show day headers
//    emptyMessage  - text to display if wall is blank
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RenderWall($rs, $pt, $showTime=false, $showHeaders=true, $emptyMessage="Nothing happening yet...")
{
  if($rs->num_rows==0) { 
    if($emptyMessage) { ?>
    <div style="height:15px"></div>
    <div class=no-data-rp style="width:450px;text-align:left">
      <?=$emptyMessage?>
    </div>
    <div style="height:15px"></div>
<?  }
  }
  else
  {
    $previousDate = '';
    while(($record = $rs->fetch_array())!=false)
    {
      // generate age text
      if($record['Age']<=0)
      {
        $dateText = 'Today';
      }
      elseif($record['Age']==1)
      {
        $dateText = 'Yesterday';
      }
      elseif($record['Age']<7)
      {
        $dateText = date_create($record['Date'])->format("l");
      }
      elseif($record['Age']<200)
      {
        $dateText = date_create($record['Date'])->format("l, M j");
      }
      else
      {
        $dateText = date_create($record['Date'])->format("F j, Y");
      }
      // generate distance text
      if(!is_null($record['Distance']))
      {
        $distanceText = $record['Distance'] . "&nbsp;mile&nbsp;" . strtolower($record['RideLogType']);
      }
      elseif(!is_null($record['Duration']))
      {
          $d = $record['Duration'];
          $distanceText = (($d <= 90) ? $d . "&nbsp;minute&nbsp;" : number_format($d/60, 1) . "&nbsp;hour&nbsp;") . strtolower($record['RideLogType']);
      }
      else
      {
        $distanceText = $record['RideLogType'];
      }
      if(date_create($record['Date'])->format('z')!=$previousDate && $showHeaders)
      {
        $previousDate=date_create($record['Date'])->format('z') ?>
        <!--====== Day header ======-->
        <div class="day-header">
          <?=$dateText?>
          <?if($record['Age']>1 && $record['Age']<365) { ?>
            <span class="day-header-age"> &bull; <?=$record['Age']?> days ago</span>
          <? } ?>
        </div>
      <? } ?>
      <div class="wrapper">
      <!--====== Delete X Button ======-->
<?      if($record['DeleteID'] && $record['RiderID']==GetUserID())
        { ?>
          <div class="delete-x" id="delete-btn<?=$record['DeleteID']?>" onclick="clickDeleteMessage(<?=$record['DeleteID']?>)">X</div>
        <? } ?>
      <!--====== Photo and Title ======-->
        <div class="picture">
          <a href="<?=BuildTeamBaseURL($record['Domain'])?>/profile.php?RiderID=<?=$record['RiderID']?>">
            <img class="tight" width=35 height=44 src="/dynamic-images/rider-portrait.php?RiderID=<?=$record['RiderID']?>&T=<?=$record['RacingTeamID']?>">
          </a>
        </div>
        <div class="title">
          <a href="<?=BuildTeamBaseURL($record['Domain'])?>/profile.php?RiderID=<?=$record['RiderID']?>">
            <b><?=$record['RiderName']?></b>
          </a>
          <? if($record['RacingTeamID']!=$pt && $record['CommutingTeamID']!=$pt) { ?>
            <span class="bullet">&bull;</span>
            <a style="color:#888" href="<?=BuildTeamBaseURL($record['Domain'])?>/home.php">
              <?=$record['TeamName']?>
            </a>
          <? } ?>
        </div>
        <div class="icon">
          <img class="tight" width=30 src="/images/teamboard/<?=$record['Image']?>" title="<?=$record['Type']?>">
        </div>
        <? switch($record['Type']) { case 'Ride Log':?>
        <!--====== Ride Log Detail ======-->
          <div class="body">
            <img style="position:relative;top:3px" src="/images/ridelog/<?=$record['RideLogTypeImage']?>" height='14' title="<?=$record['RideLogType']?>">
            <?if($record['Weather']!="N/A") { ?>
              <img style="position:relative;top:3px" src="/images/weather/<?=$record['WeatherImage']?>" height='14' title="<?=$record['Weather']?>">
            <? } ?>
            <?=htmlentities($record['PostText'])?><span class="tag">&nbsp;<span class="bullet">&bull;</span> <?=$distanceText?></span>
          </div>
          <? break; ?>
        <? case 'Race Result': ?>
        <!--====== Race results detail ======-->
          <div class="body">
            <a href="results-detail.php?RaceID=<?=$record['RaceID']?>&RiderID=<?=$record['RiderID']?>"><?=$record['EventName']?></a>
            <?if(!is_null($record['PostText'])) { ?>
              <span class="bullet">&bull;</span> <?=htmlentities($record['PostText'])?>
            <? } ?>
          </div>
          <? break; ?>
        <? case 'Message': ?>
        <!--====== Message detail ======-->
          <div class="body">
              <?=htmlentities($record['PostText'])?><?if($showTime) { ?><span class="tag">&nbsp;<span class="bullet">&bull;</span> <?=date_create($record['Date'])->format("g:ia")?></span><? } ?>
          </div>
          <? break; ?>
        <? } ?>
        <div class="divider"></div>
      </div>
    <? } ?>
  <? } ?>
<? } ?>