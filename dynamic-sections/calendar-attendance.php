<?
if(isset($_REQUEST['pb']))
{
    require_once("../script/app-master.php");
    $calendarID = SmartGetInt("CalendarID");
    $oDB = oOpenDBConnection();

    RenderAttendingRiders($oDB, $calendarID);
}


//----------------------------------------------------------------------------------
//  RenderAttendingRiders()
//
//  This function renders the list of riders attending a ride calendar event.
//
//  PARAMETERS:
//    oDB         - database connection (mysqli object)
//    calendarID  - ID of calendar event
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RenderAttendingRiders($oDB, $calendarID)
{
    $sql = "SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderID, RacingTeamID, TeamName, Domain, Attending
            FROM calendar_attendance
            LEFT JOIN rider USING (RiderID)
            LEFT JOIN teams ON (RacingTeamID=TeamID)
            WHERE CalendarID=$calendarID AND (Notify=1 OR Attending=1)
            ORDER BY LastName, FirstName";
    $rs = $oDB->query($sql, __FILE__, __LINE__);
    while(($record = $rs->fetch_array())!=false) { ?>
      <div id="R<?=$record['RiderID']?>" class="photobox">
        <a href="<?=BuildTeamBaseURL($record['Domain'])?>/profile.php?RiderID=<?=$record['RiderID']?>">
          <img class="tight <?if($record['Attending']==0) { ?>dimmed<? } ?>" src="<?=GetFullDomainRoot()?>/dynamic-images/rider-portrait.php?RiderID=<?=$record['RiderID']?>&T=<?=$record['RacingTeamID']?>" height=40 width=32>
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
