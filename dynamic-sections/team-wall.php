<?
require_once(dirname(__FILE__) . "/render-wall.php");

if(isset($_REQUEST['pb']))
{
    require("../script/app-master.php");
    CheckRequiredParameters(Array('TeamID', 'l'));
    $length = intval(SmartGet('l',0));
    $teamID = SmartGetInt("TeamID");
    $oDB = oOpenDBConnection();
    RenderTeamWall($oDB, $teamID, $length);
}


//----------------------------------------------------------------------------------
//  RenderTeamWall()
//
//  This function renders the content of the Team Wall.
//
//  PARAMETERS:
//    oDB       - database connection (mysqli object)
//    teamID    - ID of team to render wall for
//    length    - number of days to render
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RenderTeamWall($oDB, $teamID, $length)
{
    // Wrapping the components of the union into subqueries allow MySQL to use the best indexes
    // Obviously this could use some work. Joining these three distinctive tables together is a mess.
    $sql = "SELECT * FROM (
                SELECT Date, tbt.Type, tbt.Image, Created, 0 AS DeleteID,
                       RiderID, CONCAT(FirstName, ' ', LastName) AS RiderName, RacingTeamID, CommutingTeamID, TeamName, Domain,
                       DATEDIFF(NOW(), Date) AS Age, Comment AS PostText, Link,
                       Distance, Duration, RideLogType, RideLogTypeImage, IFNULL(Weather, 'N/A') AS Weather, IFNULL(WeatherImage, 'none.png') AS WeatherImage,
                       RideLogID, Source, HasMap, 0 AS RaceID, '' AS EventName
                FROM ride_log
                LEFT JOIN rider USING (RiderID)
                LEFT JOIN teams ON (TeamID=$teamID)
                LEFT JOIN ref_ride_log_type USING (RideLogTypeID)
                LEFT JOIN ref_weather USING (WeatherID)
                LEFT JOIN ref_team_board_type tbt ON (TeamBoardTypeID=1)
                WHERE (RacingTeamID=$teamID OR CommutingTeamID=$teamID)
                ORDER BY Date DESC, Created DESC
                LIMIT $length) dt1

            UNION
        
            SELECT * FROM (
                SELECT DATE(Created) AS Date, tbt.Type, tbt.Image, Created, 0 AS DeleteID,
                       RiderID, CONCAT(FirstName, ' ', LastName) AS RiderName, RacingTeamID, CommutingTeamID, TeamName, Domain,
                       DATEDIFF(NOW(), Created) AS Age, IF(LENGTH(Report)>140, CONCAT(SUBSTRING(Report, 1, 140),'...'), Report) AS PostText, NULL AS Link,
                       0 AS Distance, 0 AS Duration, '' AS RideLogType, '' AS RideLogTypeImage, '' AS Weather, '' AS WeatherImage,
                       0 AS RideLogID, 0 AS Source, 0 AS HasMap, RaceID,
                       CONCAT(DATE_FORMAT(RaceDate, '%b %e, %Y'), ' | ', PlaceName, ' - ', CategoryName, ' | ', EventName) AS EventName
                FROM results
                LEFT JOIN event USING (RaceID)
                LEFT JOIN rider USING (RiderID)
                LEFT JOIN teams t ON (t.TeamID=$teamID)
                LEFT JOIN race_report USING (RaceID, RiderID)
                LEFT JOIN ref_placing USING (PlaceID)
                LEFT JOIN ref_race_category USING (CategoryID)
                LEFT JOIN ref_team_board_type tbt ON (TeamBoardTypeID=2)
                WHERE (results.TeamID=$teamID)
                ORDER BY Created DESC
                LIMIT $length) dt2

            UNION
        
            SELECT * FROM (
                SELECT DATE(Date) AS Date, tbt.Type, tbt.Image, Date AS Created, PostID as DeleteID,
                       RiderID, CONCAT(FirstName, ' ', LastName) AS RiderName, posts.TeamID AS RacingTeamID, posts.TeamID AS CommutingTeamID, TeamName, Domain,
                       DATEDIFF(NOW(), Date) AS Age, Text AS PostText, NULL AS Link,
                       0 AS Distance, 0 AS Duration, '' AS RideLogType, '' AS RideLogTypeImage, '' AS Weather, '' AS WeatherImage,
                       0 AS RideLogID, 0 AS Source, 0 AS HasMap, 0 AS RaceID, '' AS EventName
                FROM posts
                LEFT JOIN rider USING (RiderID)
                LEFT JOIN teams USING (TeamID)
                LEFT JOIN ref_team_board_type tbt ON (TeamBoardTypeID=3)
                WHERE (PostType=0 AND PostedToID=$teamID)
                ORDER BY posts.Date DESC
                LIMIT $length) dt3

            ORDER BY Date DESC, Created DESC
            LIMIT 0,$length";

    $rs = $oDB->query($sql, __FILE__, __LINE__);
    RenderWall($rs, $teamID);?>
    <?if($rs->num_rows==$length) { ?>
      <div class='more-btn' onclick="getMore(30)">GET MORE</div>
    <? } ?>
<? } ?>