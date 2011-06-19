<?
if(isset($_REQUEST['pb']))
{
    require("../script/app-master.php");
    CheckRequiredParameters(Array('Y'));
    $showYear = $_REQUEST['Y'];
    $Editable = isset($_REQUEST['edit']) && CheckLogin();
    $oDB = oOpenDBConnection();
    $ScheduleFilterStates = isset($_COOKIE['ScheduleFilterStates']) ? $_COOKIE['ScheduleFilterStates'] : 'All';
    $ScheduleFilterTypes = isset($_COOKIE['ScheduleFilterTypes']) ? $_COOKIE['ScheduleFilterTypes'] : 'All';
    RenderEventSchedule($oDB, $ScheduleFilterStates, $ScheduleFilterTypes, $showYear, $Editable);
}

//----------------------------------------------------------------------------------
//  RenderEventSchedule()
//
//  This function renders the content of the Regional Event Schedule.
//
//  PARAMETERS:
//    oDB   - database connection (mysqli object)
//    ScheduleFilterStates  - comma-separated list of state IDs to include in list
//    ScheduleFilterTypes   - comma-separated list of event types to include in list
//    ShowYear              - Calendar year to show schedule for
//    Editable              - true if event Schedule should be editable
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RenderEventSchedule($oDB, $ScheduleFilterStates, $ScheduleFilterTypes, $ShowYear, $Editable)
{
    $colspan = $Editable ? 7 : 6;
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

    <!-- The Schedule -->
    <table id='event-list' border=0 cellpadding=0 cellspacing=0>
<?    // List the Events
      $sql = "SELECT e.RaceID, RaceDate, EventName, WebPage, e.DateAdded,
                     AddedBy, RideType, Picture, City, StateAbbr,
                     CONCAT(City, ', ', StateAbbr) AS Location,
                     DATEDIFF(NOW(), RaceDate) AS EventAge,
                     DATEDIFF(NOW(), e.DateAdded) AS AddedAge,
                     Count(r.RiderID) AS HasResults
              FROM event e
              LEFT JOIN ref_event_type USING (RideTypeID)
              LEFT JOIN ref_states USING (StateID)
              LEFT JOIN results r USING (RaceID)
              $strWhere
              GROUP BY e.RaceID
              ORDER BY RaceDate, RaceID";
      $rs = $oDB->query($sql, __FILE__, __LINE__);
      $PrevMonth = 0;
      $PrevWeek = 0;
      $FirstMonth = true;
      if($rs->num_rows==0)
      { ?>
        <!-- No Events Found -->
        <tr><td class="table-divider" colspan=<?=$colspan?>>&nbsp;</td></tr>
        <tr><td class="table-spacer" style="height:5px" colspan=<?=$colspan?>>&nbsp;</td></tr>
        <tr><td class=data colspan=<?=$colspan-1?> width=525 style="font:13px arial">
          No events found matching your selections in <?=$ShowYear?>
        </td>
        <td align=right>
          <?if($Editable) { ?>
            <span class='action-btn' id='add-btn0' onclick="clickAddEvent(this.id);">&nbsp;<b>+</b> Add Event&nbsp;</span>
          <? } ?>
        </td></tr>
        <tr><td class="table-spacer" style="height:5px" colspan=<?=$colspan?>>&nbsp;</td></tr>
        <tr><td class="table-divider" colspan=<?=$colspan?>>&nbsp;</td></tr>
<?    }
      else
      {
        while(($record = $rs->fetch_array())!=false)
        {
          $eventDate = new DateTime($record['RaceDate']);
          $thisWeek = ($eventDate->format("WY")==date_format(new DateTime(), "WY")) ? true : false;
          if($eventDate->format("n")!=$PrevMonth && $FirstMonth == false)
          { ?>
          <!-- End of month. Table divider and spacing below -->
            <tr><td class="table-spacer" style="height:1px" colspan=<?=$colspan?>>&nbsp;</td></tr>
            <tr><td class="table-divider" colspan=<?=$colspan?>>&nbsp;</td></tr>
            <tr><td class="table-spacer" style="height:25px" colspan=<?=$colspan?>>&nbsp;</td></tr>
<?        }
          if($eventDate->format("n")!=$PrevMonth)
          { ?>
          <!-- Beginning of Month. Month Header -->
            <tr><td colspan=<?=$colspan?> class="section-header">
              <table cellpadding=0 cellspacing=0 border=0 width=100%><tr>
                <td width=150>&nbsp;</td>
                <td align=center>
                  <a id="M<?=$eventDate->format("n")?>" name="<?=$eventDate->format("F")?>"></a><?=$eventDate->format("F Y")?>
                </td>
                <td width=150 align=right>
                  <?if($Editable) { ?>
                    <span class='action-btn' id='add-btn<?=$eventDate->format("n")?>' onclick="clickAddEvent(this.id);"><b>+</b> Add Event</span>
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
              <td class=header style="padding:1px 0px;" align=left>&nbsp;</td>
              <?if($Editable) { ?>
                <td class=header><span class="instructions">&nbsp;Copy/Edit</span></td>
              <? } ?>
            </tr>
            <tr><td class="table-spacer" style="height:1px" colspan=<?=$colspan?>>&nbsp;</td></tr>
<?          $PrevMonth = $eventDate->format("n");
            $PrevWeek = $eventDate->format("W");
            $FirstMonth = false;
          }
          if($eventDate->format("W")!=$PrevWeek) { ?>
          <!-- End of Week. Table divider and spacing between weeks -->
            <tr><td class="table-spacer" style="height:1px" colspan=<?=$colspan?>>&nbsp;</td></tr>
            <tr><td class="table-divider" colspan=<?=$colspan?>>&nbsp;</td></tr>
            <tr><td class="table-spacer" style="height:1px" colspan=<?=$colspan?>>&nbsp;</td></tr>
            <? $PrevWeek = $eventDate->format("W") ?>
          <? } ?>
          <!-- Event Row -->
          <tr class=data>
            <td width="65" <?if($thisWeek) {?>id="highlight"<? } ?> style="padding:0px 2px;" align=left><b><?=$eventDate->format("D n/j")?></b></td>
            <td width="320" <?if($thisWeek) {?>id="highlight"<? } ?> align=left><div class=ellipses style="width:310px">
              <a href="/event/<?=$record['RaceID']?>" title="<?=$record['EventName']?>"><?=$record['EventName']?></a>
              <?if($record['AddedAge'] < 14) { ?>
                <img border=0 src="/images/redstar2.png" title="Added <?=$record['DateAdded'] ?>">
              <? } ?>
            </div></td>
            <td width="45" <?if($thisWeek) {?>id="highlight"<? } ?> align="left"><img border=0 style="padding:0px 0px" src='/images/event-types/<?=$record['Picture']?>' title='<?=$record['RideType']?>'></td>
            <td width="120" <?if($thisWeek) {?>id="highlight"<? } ?> align=left><div class=ellipses style="width:110px">
              <?=$record['City']?>
            </div></td>
            <td width="30" <?if($thisWeek) {?>id="highlight"<? } ?> align=left><div class=ellipses style="width:25px">
              <?=$record['StateAbbr']?>
            </div></td>
            <td width="70" <?if($thisWeek) {?>id="highlight"<? } ?> align=left>
              <?// --- Show link for race attendance or link for results ?>
              <?if($record['EventAge'] < 0) {?>
                <a href="/event/<?=$record['RaceID']?>" title="Click here if you are planning on going" class="results-btn">Who's&nbsp;Going?</a>
              <? } elseif($record['HasResults']) { ?>
                <a href="/results/<?=$record['RaceID']?>" title="Click Here for Race Results" class="results-btn">RESULTS</a>
              <? } ?>
            </td>
            <?if($Editable) { ?>
              <td width="50" <?if($thisWeek) {?>id="highlight"<? } ?> style="padding-left:5px">
                <span class='action-btn-sm' style="color:#009A00" id='copy-btn<?=$record['CalendarID']?>' onclick="clickCopyEvent(<?=$record['RaceID']?>);" title="Create a new event based on this one">&nbsp;C&nbsp;</span>
                <?if(($record['EventAge'] < 0 || $record['AddedAge'] < 7) && ($record['AddedBy']==GetUserID() || isSystemAdmin()) && !$record['HasResults']) {?>
                <!-- it's possible to edit an event if no results are posted and a) it was created less than 7 days ago or
                     b) the event is still in the future -->
                  <span class='action-btn-sm' id='edit-btn<?=$record['RaceID']?>' onclick="clickEditEvent(<?=$record['RaceID']?>);" title="Edit this event">&nbsp;E&nbsp;</span>
                <? } ?>
              </td>
            <? } ?>
          </tr>
        <? } ?>
      <tr><td class="table-divider" colspan=<?=$colspan?>>&nbsp;</td></tr>
    <? } ?>
    </table>
<?
}
?>
