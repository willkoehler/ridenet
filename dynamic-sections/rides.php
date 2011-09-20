<?
if(isset($_REQUEST['pb']))
{
    require("../script/app-master.php");
    CheckRequiredParameters(Array('w', 'T'));
    $CalendarWeeks = intval($_REQUEST['w']);
    $Editable = isset($_REQUEST['edit']) && CheckLogin();
    $oDB = oOpenDBConnection();
    $pt = $_REQUEST['T'];

    // --- Get calendar filter zip code and range from cookies.
    $defaultZipCode = $oDB->DBLookup("ZipCodeID", "teams", "TeamID=$pt", 43214);
    $CalendarFilterRange = isset($_COOKIE['CalendarFilterRange']) ? $_COOKIE['CalendarFilterRange'] : 100;
    $CalendarFilterZip = isset($_COOKIE['CalendarFilterZip']) ? $_COOKIE['CalendarFilterZip'] : $defaultZipCode;
    $rs = $oDB->query("SELECT *, CONCAT(City, ', ', State, ' ', ZipCode) AS ZipCodeText
                       FROM ref_zipcodes WHERE ZipCodeID=" . IntVal($CalendarFilterZip));
    $record = $rs->fetch_array();
    $CalendarLongitude = ($record==false) ? 0 : $record['Longitude'];
    $CalendarLatitude = ($record==false) ? 0 : $record['Latitude'];
    // filter rides by team based on presence of 'tf' query parameter
    RenderRideCalendar($oDB, $CalendarFilterRange, $CalendarLongitude, $CalendarLatitude, $CalendarWeeks, $Editable);
}


//----------------------------------------------------------------------------------
//  RenderRideCalendar()
//
//  This function renders the content of the Community Ride Calendar.
//
//  PARAMETERS:
//    oDB   - database connection (mysqli object)
//    CalendarFilterRange - Range (miles) from center point to filter events in
//    CalendarLongitude   - Longitude of center point for calendar filter
//    CalendarLatitude    - Latitude of center point for calendar filter
//    CalendarWeeks       - number of weeks to show in calendar
//    Editable            - true if ride calendar should be editable
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RenderRideCalendar($oDB, $CalendarFilterRange, $CalendarLongitude, $CalendarLatitude, $CalendarWeeks, $Editable)
{?>
    <!-- If user is logged in shift table to the right so main body of table is still centered -->
    <table id='event-list' cellpadding=0 cellspacing=0>
<?    $colspan = $Editable ? 6 : 5;
      $StartDate = AddDays(MondayOfWeek(new DateTime), -7);
      $EndDate = AddDays($StartDate, $CalendarWeeks*7 - 1);
      $whereClause = "c.Archived=0";
      $whereClause .= " AND CalendarDate Between '" . $StartDate->format("Y-m-d") . "' and '" . $EndDate->format("Y-m-d") . " 23:59'";
      $whereClause .= " AND CalculateDistance(Longitude, Latitude, $CalendarLongitude, $CalendarLatitude) <= $CalendarFilterRange";
      $sql = "SELECT CalendarID, CalendarDate, EventName, Location, ClassX, ClassA, ClassB, ClassC, ClassD, AddedBy,
                     CONCAT(City, ', ', State, ' ', ZipCode) AS GeneralArea,
                     CalculateDistance(Longitude, Latitude, $CalendarLongitude, $CalendarLatitude) AS Distance,
                     DATEDIFF(NOW(), CalendarDate) AS EventAge
              FROM calendar c
              LEFT JOIN ref_zipcodes USING (ZipCodeID) 
              WHERE $whereClause
              ORDER BY CalendarDate";
      $rs = $oDB->query($sql, __FILE__, __LINE__);
      $PrevWeek = 0;
      $PrevDay = 0;
      $FirstWeek = true;
      $highlightClass = "";
      if($rs->num_rows==0)
      { ?>
        <!-- No Rides Found -->
        <tr><td class="table-spacer" style="height:10px" colspan=2>&nbsp;</td></tr>
        <tr><td class="table-divider" colspan=2>&nbsp;</td></tr>
        <tr><td class="table-spacer" style="height:5px" colspan=2>&nbsp;</td></tr>
        <tr><td class=data width=475 style="font:13px arial, 'helvetica neue', sans-serif">
          No rides found in your area between <?=$StartDate->format("n/j/Y")?> and <?=$EndDate->format("n/j/Y")?>
        </td>
        <td align=right>
          <?if($Editable) { ?>
            <span class='action-btn' id='add-btn0' onclick="clickAddRide(this.id);">&nbsp;<b>+</b> Add Ride&nbsp;</span>
          <? } ?>
        </td></tr>
        <tr><td class="table-spacer" style="height:5px" colspan=2>&nbsp;</td></tr>
        <tr><td class="table-divider" colspan=2>&nbsp;</td></tr>
<?    }
      else
      {
        while(($record = $rs->fetch_array())!=false)
        {
          $eventDate = new DateTime($record['CalendarDate']);
          $mondayOfWeek = MondayOfWeek(new DateTime($record['CalendarDate']));
          if($eventDate->format("W")!=$PrevWeek)
          { 
            if($FirstWeek == false) { ?>
            <!-- End of week boundary - table divider and spacing below -->
              <tr><td class="table-spacer <?=$highlightClass?>" style="height:5px" colspan=<?=$colspan?>>&nbsp;</td></tr>
              <tr><td class="table-divider" colspan=<?=$colspan?>>&nbsp;</td></tr>
              <tr><td class="table-spacer" style="height:20px" colspan=<?=$colspan?>>&nbsp;</td></tr>
            <? } ?>
            <?$highlightClass = ($eventDate->format("W")==date("W")) ? "thisweek" : ""?>
          <!-- Beginning of week header and table header -->
            <tr><td colspan=<?=$colspan?> class="section-header">
              <table cellpadding=0 cellspacing=0 border=0 width=100%><tr>
                <td width=150>&nbsp;</td>
                <td align=center>
                  <?if($eventDate->format("W")==date("W")) { ?>
                    This Week
                  <? } elseif(AddDays($mondayOfWeek, 7) == MondayOfWeek(new DateTime)) {?>
                    Last Week
                  <? } elseif(AddDays($mondayOfWeek, -7) == MondayOfWeek(new DateTime)) {?>
                    Next Week
                  <? } else {?>
                    Week of <?=$mondayOfWeek->format("F j, Y")?>
                  <? } ?>
                </td>
                <td width=150 align=right>
                  <?if($Editable) { ?>
                    <span class='action-btn' id='add-btn<?=$eventDate->format("n-j")?>' onclick="clickAddRide(this.id);"><b>+</b> Add Ride</span>
                  <? } ?>
                </td>
              </tr></table>
            </td></tr>
            <tr>
              <td class=header style="padding:1px 2px;" align=left colspan=2>Date/Time</td>
              <td class=header style="padding:1px 0px;" align=left>Class<a href="#class-key"><b>*</b></a></td>
              <td class=header style="padding:1px 0px;" align=left>Ride (click for more info)</td>
              <td class=header style="padding:1px 0px;" align=left>Location [miles from you]</td>
              <?if($Editable) { ?>
                <td class=header><span class="instructions">&nbsp;Copy/Edit</span></td>
              <? } ?>
            </tr>
            <tr><td class="table-spacer <?=$highlightClass?>" style="height:5px" colspan=<?=$colspan?>>&nbsp;</td></tr>
<?          $PrevWeek = $eventDate->format("W");
            $PrevDay = $eventDate->format("j");
            $FirstWeek = false;
          }
          if($eventDate->format("j")!=$PrevDay) { ?>
          <!-- End of Day. Table divider and spacing between days -->
            <tr><td class="table-spacer <?=$highlightClass?>" style="height:5px" colspan=<?=$colspan?>>&nbsp;</td></tr>
            <tr><td class="table-divider" colspan=<?=$colspan?>>&nbsp;</td></tr>
            <tr><td class="table-spacer <?=$highlightClass?>" style="height:5px" colspan=<?=$colspan?>>&nbsp;</td></tr>
            <? $PrevDay = $eventDate->format("j") ?>
          <? } ?>
          <!-- Ride Row -->
          <tr class=data riderow=1>
            <td width="65" class="<?=$highlightClass?>" style="padding:0px 2px;"><b><?=$eventDate->format("D n/j")?></b></td>
            <td width="60" class="<?=$highlightClass?>"><?=$eventDate->format("g:i a")?></td>
            <td width="50" class="<?=$highlightClass?>" style="font-weight:bold;font-family:courier new;"><?=BuildRideClass($record)?></td>
            <td width="255" class="<?=$highlightClass?>"><div class=ellipses style="width:245px">
              <a href="/ride/<?=$record['CalendarID']?>">
                <?=$record['EventName']?>
              </a>
            </div></td>
            <td width="190" class="<?=$highlightClass?>"><div class="ellipses text75" style="width:180px">
              <?=$record['GeneralArea']?> <span class="text50">[<?=number_format($record['Distance'],0)?>]</span>
            </div></td>
            <?if($Editable) { ?>
              <td width="50" class="<?=$highlightClass?>" align=left style="padding-left:5px">
                <span class='action-btn-sm' style="color:#009A00" id='copy-btn<?=$record['CalendarID']?>' onclick="clickCopyRide(<?=$record['CalendarID']?>);" title="Create a new ride based on this one">&nbsp;C&nbsp;</span>
                <?if($record['EventAge'] < 0 && ($record['AddedBy']==GetUserID() || isSystemAdmin())) {?>
                  <span class='action-btn-sm' id='edit-btn<?=$record['CalendarID']?>' onclick="clickEditRide(<?=$record['CalendarID']?>);" title="Edit this ride">&nbsp;E&nbsp;</span>
                <? } ?>
              </td>
            <? } ?>
          </tr>
        <?}?>
        <!-- Table footer -->
          <tr><td class="table-spacer" style="height:5px" colspan=<?=$colspan?>>&nbsp;</td></tr>
          <tr><td class="table-divider" colspan=<?=$colspan?>>&nbsp;</td></tr>
          <tr><td class="table-spacer" style="height:10px" colspan=<?=$colspan?>>&nbsp;</td></tr>
      <?}?>
      <tr>
        <td colspan=<?=$colspan?> align=center>
          <div class='more-btn' onclick="getMore(8)">NEXT 8 WEEKS</div>
        </td>
      </tr>
    </table>
<?
}
?>
