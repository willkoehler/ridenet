<?
require_once(dirname(__FILE__) . "/render-wall.php");

if(isset($_REQUEST['pb']))
{
    require("../script/app-master.php");
    $length = $_REQUEST['l'];
    $calendarID = SmartGetInt("CalendarID");

    $oDB = oOpenDBConnection();
    RenderCalendarWall($oDB, $calendarID, $length);
}


//----------------------------------------------------------------------------------
//  RenderCalendarWall()
//
//  This function renders the content of the calendar wall. The calendar wall
//  contains ride log entries posted by riders on the day of the ride
//
//  PARAMETERS:
//    oDB         - database connection (mysqli object)
//    calendarID  - ID of ride to render wall for
//    length      - number of days to render
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RenderCalendarWall($oDB, $calendarID, $length)
{
  $sql = "SELECT Date, tbt.Type, tbt.Image, 0 AS DeleteID,
                 ride_log.RiderID, CONCAT(FirstName, ' ', LastName) AS RiderName, RacingTeamID, CommutingTeamID, TeamName, Domain,
                 DATEDIFF(NOW(), Date) AS Age, Comment AS PostText, Link,
                 Distance, Duration, RideLogType, RideLogTypeImage, IFNULL(Weather, 'N/A') AS Weather, IFNULL(WeatherImage, 'none.png') AS WeatherImage,
                 RideLogID, Source, HasMap, 0 AS RaceID, '' AS EventName
          FROM ride_log
          LEFT JOIN rider USING (RiderID)
          LEFT JOIN teams ON (TeamID=RacingTeamID)
          LEFT JOIN calendar_attendance USING (RiderID)
          LEFT JOIN calendar USING (CalendarID)
          LEFT JOIN ref_ride_log_type USING (RideLogTypeID)
          LEFT JOIN ref_weather USING (WeatherID)
          LEFT JOIN ref_team_board_type tbt ON (TeamBoardTypeID=1)
          WHERE CalendarID=$calendarID AND ride_log.Date = DATE(calendar.CalendarDate) AND Attending=1

          ORDER BY DATE(Date) Desc, RiderID
          LIMIT 0,$length";

  $rs = $oDB->query($sql, __FILE__, __LINE__);
  RenderWall($rs, 0, true, false, "");?>
  <?if($rs->num_rows==$length) { ?>
    <div class='more-btn' onclick="getMoreWall(30)">GET MORE</div>
  <? } ?>
<? } ?>