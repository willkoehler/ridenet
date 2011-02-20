<?
if(isset($_REQUEST['pb']))
{
    require("../script/app-master.php");
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
    <h3 align=center>Local Rides</h3>
    <p class="text75">
      Come ride with us. Click on a date for ride details.
    </p>
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
        $sql = "SELECT CalendarDate, EventName, CalendarID
                FROM calendar
                WHERE Archived=0 AND
                      CalendarDate Between '" . $FirstDayOfMonth->format("Y-m-d") . "' and '" . $LastDayOfMonth->format("Y-m-d") . " 23:59' AND
                      TeamID = $pt
                GROUP BY CalendarDate
                ORDER BY CalendarDate";
        $rs = $oDB->query($sql, __FILE__, __LINE__);
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
          $DayURL1 = "";
          $DayURL2 = "";
      
          while($record!=false)
          {
            if(strtotime($record['CalendarDate']) >= strtotime($ThisDay->format("n/j/Y")))
            {
              break;
            }
            $record=$rs->fetch_array();
          }
          if($record!=false)
          {
            if(date_create($record['CalendarDate'])->format("n/j/Y")==$ThisDay->format("n/j/Y"))
            {
              $DayClass = "cell has-events";
              $DayURL = "<a href=calendar-detail.php?CID=" . $record['CalendarID'] . " title='" . $record['EventName'] . "'>" .
                        "<div>$DayURL</div></a>";
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
      $sql = "SELECT CalendarID, CalendarDate, EventName, AddedBy, DATEDIFF(NOW(), CalendarDate) AS EventAge
              FROM calendar
              WHERE Archived=0 AND TeamID=$pt AND DATE(CalendarDate) >= DATE(NOW())
              ORDER BY CalendarDate
              LIMIT 15";
      $rs = $oDB->query($sql, __FILE__, __LINE__);
      if(($record = $rs->fetch_array())!=false)
      {?>
      <table border=0 cellpadding=0 cellspacing=0>
        <tr><td class=table-spacer style="height:4px">&nbsp;</td></tr>
        <tr class="calendar-event-list"><td colspan=2>
          <b>Upcoming Rides:</b>
        </td></tr>
<?    while($record!=false)
      {?>
        <tr class="calendar-event-list">
          <td align=right style="padding:0px 2px 0px 2px">
            <?=date("n/j", strtotime($record['CalendarDate'])) . ":"?>
          </td>
          <td style="padding:0px"><div class=ellipses style="width:125px">
            <a href="calendar-detail.php?CID=<?=$record['CalendarID']?>" title="<?=$record['EventName']?>">
              <?=$record['EventName']?>
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
    $CalendarDate = (isset($_REQUEST['cd'])) ? date_create($_REQUEST['cd']) : FirstOfMonth(new DateTime); ?>
    <?// if($oDB->DBCount("calendar", "TeamID=$pt") > 0) { ?>
      <div class="sidebarBlock">
        <div id='calendar-sidebar'>
          <?CalendarSidebarContent($oDB, $pt, $CalendarDate);?>
        </div>
        <?if(CheckLogin() && isMyTeam($oDB, $pt)) { ?>
          <div style="height:10px"><!--vertical spacer--></div>
          <div id='add-calendar-btn' align=center></div>
          <div style="height:5px"><!--vertical spacer--></div>
        <? } ?>
          <div style="height:5px"><!--vertical spacer--></div>
        <p class="text75">
          For a complete list of rides in the area, see the <a href="calendar.php">Community Ride Calendar</a>
        </p>
      </div>
    <?// } ?>

<!-- Calendar sidebar javascript -->
    <script type="text/javascript">
      g_calendarDate = new Date('<?=$CalendarDate->format("n/j/Y")?>');
    
      // This will be called when DOM is loaded and ready
      Ext.onReady(function()
      {
      // --- Turn on validation errors beside the field globally and enable quick tips that will
      // --- popup tooltip when mouse is hovered over field
          Ext.form.Field.prototype.msgTarget = 'qtip';
          Ext.QuickTips.init();
      // --- create ride dialog
          g_rideDialog = new C_RideDialog();
          if(Ext.get('add-calendar-btn'))   // make sure button holder exists before creating
          {
          // --- create "Add Ride" button
              var btn = new Ext.Button({
                  text: '<span style="color:#94302E">&nbsp;Add A Ride</span>',
                  icon: 'images/plus-icon.png',
                  width: 90,
                  id: 'btn-add-calendar',
                  renderTo: 'add-calendar-btn',
                  handler: clickAddRide
              });
          }
      });

      function timeShift(interval)
      {
          g_calendarDate = g_calendarDate.add(Date.MONTH, interval);
          updateCalendarSidebar();
      }

      function clickAddRide()
      {
          g_rideDialog.show({
              animateTarget: 'btn-add-calendar',
              callback: updateCalendarSidebar
          });
      }

      function clickEditRide(calendarID)
      {
          g_rideDialog.show({
              calendarID: calendarID,
              makeCopy: false,
              animateTarget: 'edit-btn' + calendarID,
              callback: updateCalendarSidebar
          });
      }

      function updateCalendarSidebar()
      {
          Ext.get('calendar-sidebar').up("div").mask("Updating");   // step up one div to get the entire sidebar
          Ext.Ajax.request( {url: 'dynamic-sections/calendar-sidebar.php?pb&T=<?=$pt?>&cd=' + g_calendarDate.format("n/j/Y"), success: function(response, options) {
              Ext.get('calendar-sidebar').up("div").unmask();
              Ext.get('calendar-sidebar').update(response.responseText);
          }});
      }
    </script>
<?
}
?>
