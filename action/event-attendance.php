<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();
if(!CheckSession())
{
    echo "Session Expired. Please login again";
    exit();
}

$vRaceID = SmartGetInt("RaceID");
$strWhere = "RiderID = " . GetUserID() . " AND RaceID=$vRaceID";

if($_REQUEST['Attend'] == "N")
{
    // Delete record if rider is not going to attend
    $oDB->query("DELETE FROM race_attendance WHERE $strWhere", __FILE__, __LINE__);
}
else
{
    $values['RiderID'] = GetUserID();
    $values['RaceCategory'] = SmartGetString("Category");
    $values['RaceID'] = $vRaceID;
    if($oDB->DBCount("race_attendance", $strWhere)==0)
    {
      // rider is not currently attending, clear WHERE clause so new record will be created
      $strWhere="";
    }
    InsertOrUpdateRecord($oDB, "race_attendance", $strWhere, $values);
}

header("Location: ../event-attendance.php?RaceID=$vRaceID");
exit();
?>