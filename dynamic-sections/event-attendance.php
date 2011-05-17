<?
if(isset($_REQUEST['pb']))
{
    require_once("../script/app-master.php");
    $raceID = SmartGetInt("RaceID");
    $oDB = oOpenDBConnection();

    RenderAttendingRiders($oDB, $raceID);
}


//----------------------------------------------------------------------------------
//  RenderAttendingRiders()
//
//  This function renders the list of riders attending an event.
//
//  PARAMETERS:
//    oDB         - database connection (mysqli object)
//    raceID      - ID of event
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RenderAttendingRiders($oDB, $raceID)
{
    $sql = "SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderID, RacingTeamID, TeamName, Domain, Attending
            FROM event_attendance
            LEFT JOIN rider USING (RiderID)
            LEFT JOIN teams ON (RacingTeamID=TeamID)
            WHERE RaceID=$raceID AND (Notify=1 OR Attending=1)
            ORDER BY LastName, FirstName";
    $rs = $oDB->query($sql, __FILE__, __LINE__);
    while(($record = $rs->fetch_array())!=false) { ?>
      <div id="R<?=$record['RiderID']?>" class="photobox">
        <a href="<?=BuildTeamBaseURL($record['Domain'])?>/rider/<?=$record['RiderID']?>">
          <img class="tight <?if($record['Attending']==0) { ?>dimmed<? } ?>" src="<?=GetFullDomainRoot()?>/imgstore/rider-portrait/<?=$record['RacingTeamID']?>/<?=$record['RiderID']?>.jpg" height=40 width=32>
        </a>
        <?if($record['Attending']) { ?>
          <div class="countbox-sm" style="background-color:#E5D40E">I</div>
        <? } else { ?>
          <div class="countbox-sm" style="background-color:#33A53A">W</div>
        <? } ?>
      </div>
      <script type="text/javascript">riderInfoCalloutSimple(<?=$record['RiderID']?>, "<?=htmlentities($record['RiderName'])?>", "<?=htmlentities($record['TeamName'])?>")</script>
<?  }
}
?>
