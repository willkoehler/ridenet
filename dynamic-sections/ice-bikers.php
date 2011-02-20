<?
require_once(dirname(__FILE__) . "/render-wall.php");

if(isset($_REQUEST['pb']))
{
    require("../script/app-master.php");
    $length = $_REQUEST['l'];

    $oDB = oOpenDBConnection();
    RenderIceBikers($oDB, $length);
}


//----------------------------------------------------------------------------------
//  RenderIceBikers()
//
//  This function renders the content of the Ice Bikers ride board.
//
//  PARAMETERS:
//    oDB       - database connection (mysqli object)
//    length    - number of days to render
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RenderIceBikers($oDB, $length)
{
  $sql = "SELECT Date, tbt.Sort, tbt.Type, tbt.Image, -RideLogID AS Sort2, 0 AS DeleteID,
                 RiderID, CONCAT(FirstName, ' ', LastName) AS RiderName, RacingTeamID, CommutingTeamID, TeamName, Domain,
                 DATEDIFF(NOW(), Date) AS Age, Comment AS PostText,
                 Distance, Duration, RideLogType, RideLogTypeImage, IFNULL(Weather, 'N/A') AS Weather, IFNULL(WeatherImage, 'none.png') AS WeatherImage,
                 0 AS RaceID, '' AS EventName
          FROM ride_log
          LEFT JOIN rider USING (RiderID)
          LEFT JOIN teams ON (RacingTeamID = TeamID)
          LEFT JOIN ref_ride_log_type USING (RideLogTypeID)
          LEFT JOIN ref_weather USING (WeatherID)
          LEFT JOIN ref_team_board_type tbt ON (TeamBoardTypeID=1)
          WHERE RideLogTypeID<>6
          ORDER BY Date Desc, Sort, Sort2
          LIMIT $length";

  $rs = $oDB->query($sql, __FILE__, __LINE__);
  RenderWall($rs, 0); ?>
  <?if($rs->num_rows==$length) { ?>
    <div class='more-btn' onclick="getMore(30)">GET MORE</div>
  <? } ?>
<?
}
?>

