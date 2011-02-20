<?
require_once(dirname(__FILE__) . "/render-wall.php");

if(isset($_REQUEST['pb']))
{
    require("../script/app-master.php");
    $length = $_REQUEST['l'];
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
  $sql = "SELECT Date, tbt.Sort, tbt.Type, tbt.Image, -RideLogID AS Sort2, 0 AS DeleteID,
                 RiderID, CONCAT(FirstName, ' ', LastName) AS RiderName, RacingTeamID, CommutingTeamID, TeamName, Domain,
                 DATEDIFF(NOW(), Date) AS Age, Comment AS PostText,
                 Distance, Duration, RideLogType, RideLogTypeImage, IFNULL(Weather, 'N/A') AS Weather, IFNULL(WeatherImage, 'none.png') AS WeatherImage,
                 0 AS RaceID, '' AS EventName
          FROM ride_log
          LEFT JOIN rider USING (RiderID)
          LEFT JOIN teams ON (TeamID=$teamID)
          LEFT JOIN ref_ride_log_type USING (RideLogTypeID)
          LEFT JOIN ref_weather USING (WeatherID)
          LEFT JOIN ref_team_board_type tbt ON (TeamBoardTypeID=1)
          WHERE (RacingTeamID=$teamID OR CommutingTeamID=$teamID)

          UNION
          
          SELECT results.DateAdded AS Date, tbt.Sort, tbt.Type, tbt.Image, RiderID AS Sort2, 0 AS DeleteID,
                 RiderID, CONCAT(FirstName, ' ', LastName) AS RiderName, RacingTeamID, CommutingTeamID, TeamName, Domain,
                 DATEDIFF(NOW(), results.DateAdded) AS Age, IF(LENGTH(Report)>140, CONCAT(SUBSTRING(Report, 1, 140),'...'), Report) AS PostText,
                 0 AS Distance, 0 AS Duration, '' AS RideLogType, '' AS RideLogTypeImage, '' AS Weather, '' AS WeatherImage,
                 RaceID, EventName
          FROM results
          LEFT JOIN event USING (RaceID)
          LEFT JOIN rider USING (RiderID)
          LEFT JOIN teams t ON (t.TeamID=$teamID)
          LEFT JOIN race_report USING (RaceID, RiderID)
          LEFT JOIN ref_placing USING (PlaceID)
          LEFT JOIN ref_race_category USING (CategoryID)
          LEFT JOIN ref_team_board_type tbt ON (TeamBoardTypeID=2)
          WHERE (results.TeamID=$teamID)

          UNION
          
          SELECT Date, tbt.Sort, tbt.Type, tbt.Image, -PostID AS Sort2, PostID as DeleteID,
                 RiderID, CONCAT(FirstName, ' ', LastName) AS RiderName, posts.TeamID AS RacingTeamID, posts.TeamID AS CommutingTeamID, TeamName, Domain,
                 DATEDIFF(NOW(), Date) AS Age, Text AS PostText,
                 0 AS Distance, 0 AS Duration, '' AS RideLogType, '' AS RideLogTypeImage, '' AS Weather, '' AS WeatherImage,
                 0 AS RaceID, '' AS EventName
          FROM posts
          LEFT JOIN rider USING (RiderID)
          LEFT JOIN teams USING (TeamID)
          LEFT JOIN ref_team_board_type tbt ON (TeamBoardTypeID=3)
          WHERE (PostType=0 AND PostedToID=$teamID)

          ORDER BY Date Desc, Sort, Sort2
          LIMIT 0,$length";

  $rs = $oDB->query($sql, __FILE__, __LINE__);
  RenderWall($rs, $teamID);?>
  <?if($rs->num_rows==$length) { ?>
    <div class='more-btn' onclick="getMore(30)">GET MORE</div>
  <? } ?>
<? } ?>