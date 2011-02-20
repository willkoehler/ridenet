<?
if(isset($_REQUEST['pb']))
{
    require("../script/app-master.php");
    $pt = $_REQUEST['T'];
    $showYear = $_REQUEST['Y'];
    $oDB = oOpenDBConnection();

    $ScheduleFilterStates = isset($_COOKIE['ScheduleFilterStates']) ? $_COOKIE['ScheduleFilterStates'] : 'All';
    $ScheduleFilterTypes = isset($_COOKIE['ScheduleFilterTypes']) ? $_COOKIE['ScheduleFilterTypes'] : 'All';

    RenderEventSchedule($oDB, $pt, $ScheduleFilterStates, $ScheduleFilterTypes, $showYear);
}

//----------------------------------------------------------------------------------
//  RenderEventSchedule()
//
//  This function renders the content of the Regional Event Schedule.
//
//  PARAMETERS:
//    oDB   - database connection (mysqli object)
//    pt    - ID of team currently being presented to the user
//    ScheduleFilterStates  - comma-separated list of state IDs to include in list
//    ScheduleFilterTypes   - comma-separated list of event types to include in list
//    ShowYear              - Calendar year to show schedule for
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RenderEventSchedule($oDB, $pt, $ScheduleFilterStates, $ScheduleFilterTypes, $ShowYear)
{
    // Build where clause to filter schedule based on user preferences
    $strWhere = "WHERE YEAR(RaceDate)=$ShowYear AND Archived=0";
    $strWhere .= ($ScheduleFilterStates=="All") ? "" : " AND (StateID IN $ScheduleFilterStates)";
    $strWhere .= ($ScheduleFilterTypes=="All") ? "" : " AND (RideTypeID IN $ScheduleFilterTypes)";
    // Show list of months at the top of the schedule with links to jump to each month
    $rs = $oDB->query("SELECT DISTINCT MONTH(RaceDate) AS Month, MONTHNAME(RaceDate) AS MonthName FROM event $strWhere ORDER BY RaceDate", __FILE__, __LINE__);
    if($rs->num_rows > 0) { ?>
      <p align=center class="text50" style="font:bold arial 11px">Go To:&nbsp;
        <? while(($record=$rs->fetch_array())!=false) { ?>
          <span class='action-btn linkcolor' onclick="scrollToMonth(<?=$record['Month']?>)"><?=$record['MonthName']?></span>&nbsp;
        <? } ?>
      </p>
      <div style="height:10px"><!--vertical spacer--></div>
    <? } ?>

    <!-- If user is logged in shift table to the right so main body of table is still centered -->
    <div <?if(CheckLogin()) {?>style="margin-left:35px"<?}?>>
      <!-- The Schedule -->
      <table id='event-list' border=0 cellpadding=0 cellspacing=0>
<?      // List the Events
        $sql = "SELECT e.RaceID, RaceDate, EventName, WebPage, DateAdded,
                       AddedBy, RideType, Picture,
                       CONCAT(City, ', ', StateAbbr) AS Location,
                       DATEDIFF(NOW(), RaceDate) AS EventAge,
                       DATEDIFF(NOW(), DateAdded) AS AddedAge,
                       IF((SELECT COUNT(*) FROM results WHERE RaceID=e.RaceID) > 0, 1, 0) AS HasResults
                FROM event e
                LEFT JOIN ref_event_type t USING (RideTypeID)
                LEFT JOIN ref_states USING (StateID)
                $strWhere
                GROUP BY e.RaceID
                ORDER BY RaceDate, DateAdded";
        $rs = $oDB->query($sql, __FILE__, __LINE__);
        $PrevMonth = 0;
        $PrevWeek = 0;
        $FirstMonth = true;
        if($rs->num_rows==0)
        { ?>
          <!-- No Events Found -->
          <tr><td class="table-divider" colspan=5>&nbsp;</td></tr>
          <tr><td class="table-spacer" style="height:5px" colspan=5>&nbsp;</td></tr>
          <tr><td class=data colspan=4 width=525 style="font:13px arial">
            No events found matching your selections in <?=$ShowYear?>
          </td>
          <td align=right>
            <?if(CheckLogin()) { ?>
              <span class='action-btn' id='add-btn0' onclick="clickAddEvent(this.id);">&nbsp;<b>+</b> Add Event&nbsp;</span>
            <? } else { ?>
              <span class='action-btn' onclick="window.location.href='login.php?Goto=<?=urlencode("../event-schedule.php?Year=$ShowYear")?>'">&nbsp;Login To Add an Event&nbsp;</span>
            <? } ?>
          </td></tr>
          <tr><td class="table-spacer" style="height:5px" colspan=5>&nbsp;</td></tr>
          <tr><td class="table-divider" colspan=5>&nbsp;</td></tr>
<?      }
        else
        {
          while(($record = $rs->fetch_array())!=false)
          {
            $eventDate = new DateTime($record['RaceDate']);
            if($eventDate->format("n")!=$PrevMonth && $FirstMonth == false)
            { ?>
            <!-- End of month. Table divider and spacing below -->
              <tr><td class="table-spacer" style="height:1px" colspan=5>&nbsp;</td></tr>
              <tr><td class="table-divider" colspan=5>&nbsp;</td></tr>
              <tr><td class="table-spacer" style="height:25px" colspan=5>&nbsp;</td></tr>
<?          }
            if($eventDate->format("n")!=$PrevMonth)
            { ?>
            <!-- Beginning of Month. Month Header -->
              <tr><td colspan=5 class="section-header">
                <table cellpadding=0 cellspacing=0 border=0 width=100%><tr>
                  <td width=150>&nbsp;</td>
                  <td align=center>
                    <a id="M<?=$eventDate->format("n")?>" name="<?=$eventDate->format("F")?>"></a><?=$eventDate->format("F Y")?>
                  </td>
                  <td width=150 align=right>
                    <?if(CheckLogin()) { ?>
                      <span class='action-btn' id='add-btn<?=$eventDate->format("n")?>' onclick="clickAddEvent(this.id);"><b>+</b> Add Event</span>
                    <? } else { ?>
                      <span class='action-btn' onclick="window.location.href='login.php?Goto=<?=urlencode("../event-schedule.php?Year=$ShowYear")?>'">&nbsp;Login To Edit&nbsp;</span>
                    <? } ?>
                  </td>
                </tr></table>
              </td></tr>
              <tr>
                <td class=header style="padding:1px 2px;" align=left>Date</td>
                <td class=header style="padding:1px 0px;" align=left>Event (click for details)</td>
                <td class=header style="padding:1px 0px;" align=left>Type</td>
                <td class=header style="padding:1px 0px;" align=left>Location</td>
                <td class=header style="padding:1px 0px;" align=left>&nbsp;</td>
                <?if(CheckLogin()) { ?>
                  <td class=data style="padding:1px 0px;" align=left>&nbsp;</td>
                <? } ?>
              </tr>
              <tr><td class="table-spacer" style="height:1px" colspan=5>&nbsp;</td></tr>
<?            $PrevMonth = $eventDate->format("n");
              $PrevWeek = $eventDate->format("W");
              $FirstMonth = false;
            }
            if($eventDate->format("W")!=$PrevWeek) { ?>
            <!-- End of Week. Table divider and spacing between weeks -->
              <tr><td class="table-spacer" style="height:1px" colspan=5>&nbsp;</td></tr>
              <tr><td class="table-divider" colspan=5>&nbsp;</td></tr>
              <tr><td class="table-spacer" style="height:1px" colspan=5>&nbsp;</td></tr>
              <? $PrevWeek = $eventDate->format("W") ?>
            <? } ?>
            <!-- Event Row -->
            <tr class=data>
              <td width="65" style="padding:0px 2px;" align=left><b><?=$eventDate->format("D n/j")?></b></td>
              <td width="320" align=left><div class=ellipses style="width:310px">
                <?if($record['WebPage']!="") { ?>
                  <a href="http://<?=$record['WebPage']?>" title="<?=$record['EventName']?>" target='_blank'><?=$record['EventName']?></a>
                <? } else { ?>
                  <?=$record['EventName']?>
                <? } ?>
                <?if($record['AddedAge'] < 14) { ?>
                  <img border=0 src="images/redstar2.png" title="Added <?=$record['DateAdded'] ?>">
                <? } ?>
              </div></td>
              <td width="45" align="left"><img border=0 style="padding:0px 0px" src='images/event-types/<?=$record['Picture']?>' title='<?=$record['RideType']?>'></td>
              <td width="155" align=left><div class=ellipses style="width:145px">
                <?=$record['Location']?>
              </div></td>
              <td width="70" align=left>
                <?// --- Show link for race attendance or link for results ?>
                <?if($record['EventAge'] < 0) {?>
                  <a href="event-attendance.php?RaceID=<?=$record['RaceID']?>" title="Click here if you are planning on going" class="results-btn">Who's&nbsp;Going?</a>
                <? } elseif($record['HasResults']) { ?>
                  <a href="results-detail.php?RaceID=<?=$record['RaceID']?>" title="Click Here for Race Results" class="results-btn">RESULTS</a>
                <? } ?>
              </td>
              <?if(CheckLogin()) { ?>
                <td width="35" style="padding-left:5px">
                  <?if(($record['EventAge'] < 0 || $record['AddedAge'] < 7) && ($record['AddedBy']==GetUserID() || isSystemAdmin()) && !$record['HasResults']) {?>
                  <!-- it's possible to edit an event if no results are posted and a) it was created less than 7 days ago or
                       b) the event is still in the future -->
                    <span class='action-btn-sm' id='edit-btn<?=$record['RaceID']?>' onclick="clickEditEvent(<?=$record['RaceID']?>);" title="Edit this event">Edit</span>
                  <? } ?>
                </td>
              <? } ?>
            </tr>
          <? } ?>
        <tr><td class="table-divider" colspan=5>&nbsp;</td></tr>
      <? } ?>
      </table>
    </div>
<?
}
?>
