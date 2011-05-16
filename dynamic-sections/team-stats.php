<?
if(isset($_REQUEST['pb']))
{
    define("SHAREDBASE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/Shared/");
    require(SHAREDBASE_DIR . "DBConnection.php");
    require(SHAREDBASE_DIR . "RequestHelpers.php");
    require(dirname(__FILE__) . "/../script/data-helpers.php");
    $startDate = SmartGetDate('StartDate');
    $endDate = SmartGetDate('EndDate');
    $oDB = oOpenDBConnection();

    RenderTeamStats($oDB, $startDate, $endDate);
}


//----------------------------------------------------------------------------------
//  RenderTeamStats()
//
//  This function renders the global team stats at the top of the team stats page.
//
//  PARAMETERS:
//    oDB         - database connection (mysqli object)
//    startDate   - start of date range to calculate stats over
//    endDate     - end of date range to calculate stats over
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RenderTeamStats($oDB, $startDate, $endDate)
{
    $riderCount = $oDB->DBCount("rider", "true");
    $teamCount = $oDB->DBCount("teams", "true");
    $starCount = $oDB->DBCount("rider", "CEDaysMonth>=2");
    $sql = "SELECT SUM(Distance) AS Miles,
                   SUM(IF(RideLogTypeID=1 OR RideLogTypeID=3, Distance, 0)) AS CEMiles,
                   COUNT(IF(RideLogTypeID=1 OR RideLogTypeID=3, RideLogID, NULL)) AS CETrips,
                   COUNT(*) AS Rides
            FROM ride_log
            WHERE Date BETWEEN $startDate AND $endDate";
    $rs = $oDB->query($sql, __FILE__, __LINE__);
    $record = $rs->fetch_array();
?>
    <div style="font:30px arial;line-height:21px;padding:30px 0 20px 0;text-align:center">
      <table cellpadding=0 cellspacing=0 class="centered"><tr>
        <td width=100><div><?=$teamCount?><div class="text50" style="font-size:13px">&nbsp;Teams</div></div></td>
        <td width=110><?=$riderCount?><div class="text50" style="font-size:13px">&nbsp;Riders</div></td>
        <td width=140><?=number_format($record['Miles'])?><div class="text50" style="font-size:13px">&nbsp;Total Miles</div></td>
        <td width=110><?=$starCount?><div class="text50" style="font-size:13px">&nbsp;STARs*</div></td>
      </tr></table>
    </div>
<!--    <div style="font:20px arial;line-height:20px;padding:0 0 20px 0;text-align:center">
      <table cellpadding=0 cellspacing=0 style="width:250px" class="centered"><tr>
        <td>
          <?=number_format($record['CETrips'])?>
          <div class="text50" style="font-size:13px">
            <span style="position:relative;top:-3px">
              <img class="tight" src="/images/ridelog/commute.png" height=14><img class="tight" src="/images/ridelog/errand.png" height=14>
            </span>
            Rides
          </div>
        </td>
        <td>
          <?=number_format($record['CEMiles'])?>
          <div class="text50" style="font-size:13px">
            <span style="position:relative;top:-3px">
              <img class="tight" src="/images/ridelog/commute.png" height=14><img class="tight" src="/images/ridelog/errand.png" height=14>
            </span>
            Miles
          </div>
        </td>
      </tr></table>
    </div>-->
<?  
}
?>
