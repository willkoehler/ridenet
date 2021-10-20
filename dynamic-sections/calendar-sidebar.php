<?
if(isset($_REQUEST['pb']))
{
    require("../script/app-master.php");
    CheckRequiredParameters(Array('T', 'cd'));
    $pt = $_REQUEST['T'];
    $Date = date_create($_REQUEST['cd']);
    $oDB = oOpenDBConnection();
    CalendarSidebarContent($oDB, $pt, $Date);
}

//----------------------------------------------------------------------------------
//  CalendarSidebarContent()
//
//  This function renders the content of the Calendar Sidebar. The content and
//  script are separated because the content needs to be updated dynamically
//
//  PARAMETERS:
//    oDB           - database connection (mysqli object)
//    pt            - ID of team currently being presented to the user.
//    CalendarDate  - date to show calendar for (only month and year are used)
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function CalendarSidebarContent($oDB, $pt, $CalendarDate)
{
?>  
    <h3 align=center>Meet Us On The Road</h3>
    <p class="text75">
      Want to join us for a ride? Here's where we're riding this month.
    </p>
    <div style="height:5px"></div>
    <!-- Calendar -->
      <table id=calendar cellspacing="0" cellpadding="0">
        <tr>
          <td class="month"><a href="javascript:timeShift(-1)">&lt;</a></td>
          <td class="month" colspan=5><?=$CalendarDate->format("M Y")?></td>
          <td class="month"><a href="javascript:timeShift(1)">&gt;</a></td>
        </tr>
        <tr>
          <td class="cell weekday">S</td>
          <td class="cell weekday">M</td>
          <td class="cell weekday">T</td>
          <td class="cell weekday">W</td>
          <td class="cell weekday">T</td>
          <td class="cell weekday">F</td>
          <td class="cell weekday">S</td></tr>
        <tr>
<?      // Get First day of month and last day of month
        $FirstDayOfMonth = FirstOfMonth($CalendarDate);
        $LastDayOfMonth = LastOfMonth($CalendarDate);
          // If month doesn't begin on Sunday, write out blank cells
        $firstWeekday = intval($FirstDayOfMonth->format("w"));
        if($firstWeekday != 0)
        {
          $DayOfWeek = 0;
          while($DayOfWeek < $firstWeekday)
          { ?>
            <td class="cell notaday">&nbsp;</td>     
<?          $DayOfWeek++;
          }
        }
      
        // Get rides from the calendar table (if there are multiple rides on a day, link to first ride only)
        $fromDate = $FirstDayOfMonth->format("Y-m-d");
        $toDate = $LastDayOfMonth->format("Y-m-d") . "  23:59";
        $sql = "SELECT CalendarDate AS Date, EventName AS Name, CalendarID AS ID, 'ride' AS URLBase,
                       COUNT(IF((CommutingTeamID=$pt OR RacingTeamID=$pt) AND (Attending=1 OR Notify=1), 1, NULL)) AS NumAttending
                FROM calendar
                JOIN calendar_attendance USING (CalendarID)
                JOIN rider USING (RiderID)
                WHERE calendar.Archived=0 AND CalendarDate Between '$fromDate' AND '$toDate'
                GROUP BY ID
                HAVING NumAttending > 0
                
                UNION
                
                SELECT RaceDate AS Date, EventName AS Name, RaceID AS ID, 'event' AS URLBase,
                       COUNT(IF((CommutingTeamID=$pt OR RacingTeamID=$pt) AND (Attending=1 OR Notify=1), 1, NULL)) AS NumAttending
                FROM event
                JOIN event_attendance USING (RaceID)
                JOIN rider USING (RiderID)
                WHERE event.Archived=0 AND RaceDate Between '$fromDate' AND '$toDate'
                GROUP BY ID
                HAVING NumAttending > 0
                                
                ORDER BY Date, NumAttending";
        $rs = $oDB->query($sql);
        $record=$rs->fetch_array();
        //  Loop through the days in the month
        $ThisDay = $FirstDayOfMonth;
        while($ThisDay->format("n") == $LastDayOfMonth->format("n"))  // while the months are the same
        {
          if(intval($ThisDay->format("w")) == 0)  // start new row each week
          {
            echo("<tr>");
          }
          $DayClass="cell day";
          $DayURL = $ThisDay->format("j");
      
          while($record!=false)
          {
            if(strtotime($record['Date']) >= strtotime($ThisDay->format("n/j/Y")))
            {
              break;
            }
            $record=$rs->fetch_array();
          }
          if($record!=false)
          {
            if(date_create($record['Date'])->format("n/j/Y")==$ThisDay->format("n/j/Y"))
            {
              $DayClass = "cell has-events";
              $DayURL = "<a href=\"/{$record['URLBase']}/{$record['ID']}\" title=\"" . htmlentities($record['Name']) . "\"><div>$DayURL</div></a>";
            }
          }?>
          <td class="<?=$DayClass?>"><?=$DayURL?></td>
<?        if(intval($ThisDay->format("w"))==6)    // end the row on saturday
          {
            echo("</tr>");
          }
          $ThisDay = AddDays($ThisDay, 1);
        }
        // If month doesn't end on Saturday, write out blank cells   
        $lastWeekday = intval($LastDayOfMonth->format("w"));
        if($lastWeekday != 6)
        {
          for($DayOfWeek = $lastWeekday; $DayOfWeek < 6; $DayOfWeek++)
          { ?>
            <td class="cell notaday">&nbsp;</td>     
          <?}?>
          </tr>  
        <?}?>
      </table>
<?  // Show upcoming rides
      $sql = "SELECT CalendarDate AS Date, EventName AS Name, CalendarID AS ID, 'ride' AS URLBase,
                     COUNT(IF((CommutingTeamID=$pt OR RacingTeamID=$pt) AND (Attending=1 OR Notify=1), 1, NULL)) AS NumAttending
              FROM calendar
              JOIN calendar_attendance USING (CalendarID)
              JOIN rider USING (RiderID)
              WHERE calendar.Archived=0 AND CalendarDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
              GROUP BY ID
              HAVING NumAttending > 0
              
              UNION
              
              SELECT RaceDate AS Date, EventName AS Name, RaceID AS ID, 'event' AS URLBase,
                     COUNT(IF((CommutingTeamID=$pt OR RacingTeamID=$pt) AND (Attending=1 OR Notify=1), 1, NULL)) AS NumAttending
              FROM event
              JOIN event_attendance USING (RaceID)
              JOIN rider USING (RiderID)
              WHERE event.Archived=0 AND RaceDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
              GROUP BY ID
              HAVING NumAttending > 0
                              
              ORDER BY Date
              LIMIT 15";
      $rs = $oDB->query($sql);
      if(($record = $rs->fetch_array())!=false)
      {?>
      <table border=0 cellpadding=0 cellspacing=0>
        <tr><td class=table-spacer style="height:10px">&nbsp;</td></tr>
        <tr class="calendar-event-list"><td colspan=2>
          <b>Upcoming rides we're attending:</b>
        </td></tr>
<?    while($record!=false)
      {?>
        <tr class="calendar-event-list">
          <td align=right style="padding:0px 2px 0px 2px">
            <?=date("n/j", strtotime($record['Date'])) . ":"?>
          </td>
          <td style="padding:0px"><div class=ellipses style="width:125px">
            <a href="/<?=$record['URLBase']?>/<?=$record['ID']?>" title="<?=htmlentities($record['Name'])?>">
              <?=htmlentities($record['Name'])?>
            </a>
          </div></td>
        </tr>
<?      $record = $rs->fetch_array();
      }?>
      </table>
      <? } ?>
<?
}


//----------------------------------------------------------------------------------
// CalendarSidebar()
//
//  This function inserts the calendar sidebar block into the current page
//
//  PARAMETERS:
//    oDB   - database connection (mysqli object)
//    pt    - ID of team currently being presented to the user
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function CalendarSidebar($oDB, $pt)
{
    // --- Event calendar date. If not provided, default to current month
    $CalendarDate = (isset($_REQUEST['cd'])) ? date_create($_REQUEST['cd']) : FirstOfMonth(new DateTime);
    // --- Is this team attending any rides in the next 30 days?
    $sql = "(SELECT CalendarID AS ID
            FROM calendar
            JOIN calendar_attendance USING (CalendarID)
            JOIN rider USING (RiderID)
            WHERE calendar.Archived=0 AND CalendarDate Between CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND
                  (CommutingTeamID=$pt OR RacingTeamID=$pt) AND (Attending=1 OR Notify=1) LIMIT 1)
            UNION
            (SELECT RaceID AS ID
            FROM event
            JOIN event_attendance USING (RaceID)
            JOIN rider USING (RiderID)
            WHERE event.Archived=0 AND RaceDate Between CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND
                  (CommutingTeamID=$pt OR RacingTeamID=$pt) AND (Attending=1 OR Notify=1) LIMIT 1)";
    $ride = $oDB->query($sql);
    if($ride->num_rows > 0) { ?>
      <div class="sidebarBlock">
        <div id='calendar-sidebar'>
          <?CalendarSidebarContent($oDB, $pt, $CalendarDate);?>
        </div>
        <div style="height:10px"><!--vertical spacer--></div>
        <p class="text75">
          For a complete listing of rides and events see the <a href="/rides">Ride Calendar</a> and <a href="/events">Event Schedule</a>.
        </p>
      </div>
    <? } ?>

<!-- Calendar sidebar javascript -->
    <script type="text/javascript">
      g_calendarDate = new Date('<?=$CalendarDate->format("n/j/Y")?>');
    
      function timeShift(interval)
      {
          g_calendarDate = g_calendarDate.add(Date.MONTH, interval);
          updateCalendarSidebar();
      }

      function updateCalendarSidebar()
      {
          Ext.get('calendar-sidebar').up("div").mask("Updating");   // step up one div to get the entire sidebar
          Ext.Ajax.request( {url: '/dynamic-sections/calendar-sidebar.php?pb&T=<?=$pt?>&cd=' + g_calendarDate.format("n/j/Y"), success: function(response, options) {
              Ext.get('calendar-sidebar').up("div").unmask();
              Ext.get('calendar-sidebar').update(response.responseText);
          }});
      }
    </script>
<?
}
?>
