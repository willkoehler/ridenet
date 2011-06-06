<?
require_once(dirname(__FILE__) . "/render-wall.php");

if(isset($_REQUEST['pb']))
{
    require("../script/app-master.php");
    CheckRequiredParameters(Array('RaceID', 'l'));
    $length = $_REQUEST['l'];
    $raceID = SmartGetInt("RaceID");
    $oDB = oOpenDBConnection();
    RenderEventUpdates($oDB, $raceID, $length);
}


//----------------------------------------------------------------------------------
//  RenderEventUpdates()
//
//  This function renders the content of the event updates.
//
//  PARAMETERS:
//    oDB         - database connection (mysqli object)
//    raceID      - ID of event to render updates for
//    length      - number of entries to render
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RenderEventUpdates($oDB, $raceID, $length)
{
  $sql = "SELECT * FROM (
              SELECT RaceDate AS Date, tbt.Type, tbt.Image, Created, 0 AS DeleteID,
                     RiderID, CONCAT(FirstName, ' ', LastName) AS RiderName, RacingTeamID, CommutingTeamID, TeamName, Domain,
                     DATEDIFF(NOW(), Created) AS Age, IF(LENGTH(Report)>140, CONCAT(SUBSTRING(Report, 1, 140),'...'), Report) AS PostText, NULL AS Link,
                     0 AS Distance, 0 AS Duration, '' AS RideLogType, '' AS RideLogTypeImage, '' AS Weather, '' AS WeatherImage,
                     0 AS RideLogID, 0 AS Source, 0 AS HasMap, RaceID,
                     CONCAT(PlaceName, ' - ', CategoryName) AS EventName
              FROM results
              LEFT JOIN event USING (RaceID)
              LEFT JOIN rider USING (RiderID)
              LEFT JOIN teams t ON (t.TeamID=RacingTeamID)
              LEFT JOIN race_report USING (RaceID, RiderID)
              LEFT JOIN ref_placing USING (PlaceID)
              LEFT JOIN ref_race_category USING (CategoryID)
              LEFT JOIN ref_team_board_type tbt ON (TeamBoardTypeID=2)
              WHERE (results.RaceID=$raceID)
              ORDER BY Created DESC
              LIMIT $length) dt2
              
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
              WHERE (PostType=2 AND PostedToID=$raceID)
              ORDER BY Date DESC
              LIMIT $length) dt1

          ORDER BY Date DESC, Created DESC
          LIMIT 0,$length";

  $rs = $oDB->query($sql, __FILE__, __LINE__);
  RenderWall($rs, 0, true, true, "");?>
  <?if($rs->num_rows==$length) { ?>
    <div class='more-btn' onclick="getMoreUpdates(30)">GET MORE</div>
  <? } ?>
<? } ?>