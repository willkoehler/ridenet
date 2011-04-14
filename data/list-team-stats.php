<?
require("../script/app-master.php");

// store query/post values in local variables
$limit = SmartGetInt('limit');     // Number of records to retrieve (used for grid object paging)
$start = SmartGetInt('start');     // Starting record (used for grid object paging)
$dir = SmartGet('dir');            // Sort direction desc, asc (used for remoteSort)
$sort = SmartGet('sort');          // name of sort row (used for remoteSort)
$searchFor = SmartGet('SearchFor', "");
$startDate = SmartGetDate('StartDate');
$endDate = SmartGetDate('EndDate');

// --- open connection to database
$oDB = oOpenDBConnection();

// -- build WHERE clause based on search terms
$teamFilter = ($searchFor != "") ?  "TeamName LIKE \"%$searchFor%\"" : "1";

// --- Get team stats
$sql = "SELECT TeamID, TeamName, TeamType, Domain, CONCAT(City, ', ', State, ' ', Zipcode) AS Location,
               SUM(Distance) AS Distance,
               SUM(IF(RideLogTypeID=1 OR RideLogTypeID=3, Distance, 0)) AS CEDistance,
               COUNT(DISTINCT IF(CEDaysMonth >= 2, RiderID, NULL)) AS StarRiders,
               COUNT(DISTINCT IF(RideLogTypeID=1 OR RideLogTypeID=3, CONCAT(Date,RiderID), NULL)) AS CEDays
        FROM (SELECT RacingTeamID AS TeamID, RiderID, CEDaysMonth, Date, RideLogTypeID, Distance
              FROM ride_log JOIN rider USING (RiderID)
              WHERE IFNULL(rider.Archived,0)=0 AND Date BETWEEN $startDate AND $endDate
              UNION
              SELECT CommutingTeamID AS TeamID, RiderID, CEDaysMonth, Date, RideLogTypeID, Distance
              FROM ride_log JOIN rider USING (RiderID)
              WHERE IFNULL(rider.Archived,0)=0 AND Date BETWEEN $startDate AND $endDate) d1
        LEFT JOIN teams USING (TeamID)
        LEFT JOIN ref_team_type USING (TeamTypeID)
        LEFT JOIN ref_zipcodes USING (ZipCodeID)
        WHERE $teamFilter
        GROUP BY TeamID
        ORDER BY $sort $dir LIMIT $start, $limit";


/*$sql = "SELECT TeamID, TeamName, TeamType, Domain, CONCAT(City, ', ', State, ' ', Zipcode) AS Location,
               Distance1 + Distance2 AS Distance, CEDistance1 + CEDistance2 AS CEDistance,
               StarRiders1 + StarRiders2 AS StarRiders, CEDays1 + CEDays2 AS CEDays
        FROM (SELECT CommutingTeamID AS TeamID, SUM(Distance) AS Distance1,
                     SUM(IF(RideLogTypeID=1 OR RideLogTypeID=3, Distance, 0)) AS CEDistance1,
                     COUNT(DISTINCT IF(CEDaysMonth >= 2, RiderID, NULL)) AS StarRiders1,
                     COUNT(DISTINCT IF(RideLogTypeID=1 OR RideLogTypeID=3, CONCAT(Date,RiderID), NULL)) AS CEDays1
              FROM ride_log
              LEFT JOIN rider USING (RiderID)
              WHERE IFNULL(rider.Archived,0)=0 AND Date BETWEEN $startDate AND $endDate
              GROUP BY CommutingTeamID) d1
        JOIN (SELECT RacingTeamID AS TeamID, SUM(Distance) AS Distance2,
                     SUM(IF(RideLogTypeID=1 OR RideLogTypeID=3, Distance, 0)) AS CEDistance2,
                     COUNT(DISTINCT IF(CEDaysMonth >= 2, RiderID, NULL)) AS StarRiders2,
                     COUNT(DISTINCT IF(RideLogTypeID=1 OR RideLogTypeID=3, CONCAT(Date,RiderID), NULL)) AS CEDays2
              FROM ride_log
              LEFT JOIN rider USING (RiderID)
              WHERE IFNULL(rider.Archived,0)=0 AND Date BETWEEN $startDate AND $endDate
              GROUP BY RacingTeamID) d2
        USING (TeamID)
        LEFT JOIN teams USING (TeamID)
        LEFT JOIN ref_team_type USING (TeamTypeID)
        LEFT JOIN ref_zipcodes USING (ZipCodeID)
        ORDER BY $sort $dir LIMIT $start, $limit";*/

/*$sql = "SELECT TeamID, TeamName, TeamType, Domain, CONCAT(City, ', ', State, ' ', Zipcode) AS Location,
               SUM(Distance) AS Distance,
               SUM(IF(RideLogTypeID=1 OR RideLogTypeID=3, Distance, 0)) AS CEDistance,
               COUNT(DISTINCT IF(CEDaysMonth >= 2, RiderID, NULL)) AS StarRiders,
               COUNT(DISTINCT IF(RideLogTypeID=1 OR RideLogTypeID=3, CONCAT(Date,RiderID), NULL)) AS CEDays
        FROM ride_log
        LEFT JOIN rider USING (RiderID)
        LEFT JOIN teams ON (TeamID = CommutingTeamID)
        LEFT JOIN ref_team_type USING (TeamTypeID)
        LEFT JOIN ref_zipcodes USING (ZipCodeID)
        WHERE IFNULL(rider.Archived,0)=0 AND Date BETWEEN $startDate AND $endDate
        GROUP BY TeamID
        ORDER BY $sort $dir LIMIT $start, $limit";*/
$rs = $oDB->query($sql, __FILE__, __LINE__);

// --- Loop through all the records and add the contents of each record to the output array
$result['results'] = array();
while($row = $rs->fetch_object())
{
	  $result['results'][] = $row;
}

// --- Dump output.
Echo json_encode($result);
?>
