<?
require_once(dirname(__FILE__) . "/render-wall.php");

if(isset($_REQUEST['pb']))
{
    require("../script/app-master.php");
    CheckRequiredParameters(Array('CalendarID', 'l'));
    $length = $_REQUEST['l'];
    $calendarID = SmartGetInt("CalendarID");
    $oDB = oOpenDBConnection();
    RenderRideUpdates($oDB, $calendarID, $length);
}


//----------------------------------------------------------------------------------
//  RenderCalendarUpdates()
//
//  This function renders the content of the calendar updates.
//
//  PARAMETERS:
//    oDB         - database connection (mysqli object)
//    calendarID  - ID of ride to render updates for
//    length      - number of entries to render
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RenderRideUpdates($oDB, $calendarID, $length)
{
  $sql = "SELECT * FROM (
              SELECT Date, tbt.Type, tbt.Image, Created, 0 AS DeleteID,
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
              LIMIT $length) dt1
              
          UNION
          
          SELECT * FROM (
              SELECT Date, tbt.Type, tbt.Image, DATE AS Created, PostID as DeleteID,
                     RiderID, CONCAT(FirstName, ' ', LastName) AS RiderName, posts.TeamID AS RacingTeamID, posts.TeamID AS CommutingTeamID, TeamName, Domain,
                     DATEDIFF(NOW(), Date) AS Age, Text AS PostText, NULL AS Link,
                     0 AS Distance, 0 AS Duration, '' AS RideLogType, '' AS RideLogTypeImage, '' AS Weather, '' AS WeatherImage,
                     0 AS RideLogID, 0 AS Source, 0 AS HasMap, 0 AS RaceID, '' AS EventName
              FROM posts
              LEFT JOIN rider USING (RiderID)
              LEFT JOIN teams USING (TeamID)
              LEFT JOIN ref_team_board_type tbt ON (TeamBoardTypeID=3)
              WHERE (PostType=1 AND PostedToID=$calendarID)
              ORDER BY Date DESC
              LIMIT $length) dt2

          ORDER BY Date DESC, Created DESC
          LIMIT 0,$length";

  $rs = $oDB->query($sql, __FILE__, __LINE__);
  RenderWall($rs, 0, true, true, "");?>
  <?if($rs->num_rows==$length) { ?>
    <div class='more-btn' onclick="getMore(30)">GET MORE</div>
  <? } ?>
<? } ?>