<?
require("../script/app-master.php");

$oDB = oOpenDBConnection();
// store query/post values in local variables
$riderID = SmartGetInt('RiderID');      // Rider ID

if(!CheckSession())
{
    $result['results'][] = array();   // (dummy results array is required)
    $result['success'] = false;  
}
else if($riderID!=GetUserID())
{
    $result['results'][] = array();   // (dummy results array is required)
    $result['success'] = false;  
}
else
{   // (photo is always based on racing team ID)
    $rs = $oDB->query("SELECT RiderID, FirstName, LastName, RacingTeamID, CommutingTeamID, rt.TeamName AS RacingTeamName,
                              ct.TeamName AS CommutingTeamName, RiderEmail, DateOfBirth, FavoriteQuote, FavoriteRide,
                              FavoriteFood, WhyIRide, MyCommute, BornIn, ResideIn, Occupation, RiderTypeID, YearsCycling,
                              Height, Weight, URL, CONCAT(RiderID, \",\", RacingTeamID) AS RiderPictureID
                       FROM rider
                       LEFT JOIN teams rt ON (RacingTeamID = rt.TeamID)
                       LEFT JOIN teams ct ON (CommutingTeamID = ct.TeamID)
                       WHERE RiderID=$riderID", __FILE__, __LINE__);
    $result['results'] = $rs->fetch_object();
    $result['success'] = true;
}

// --- Dump output.
Echo json_encode($result);
?>
