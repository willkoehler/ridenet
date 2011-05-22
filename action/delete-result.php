<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();
if(!CheckSession())
{
    echo "Session Expired. Please login again";
    exit();
}

$RaceID = SmartGetInt("RaceID");
$CategoryID = SmartGetInt("CategoryID");
$RiderID = GetUserID();

// verify that the result was posted less than 14 days ago
if($oDB->DBCount("results", "RiderID=$RiderID AND RaceID=$RaceID AND DATEDIFF(NOW(), Created) <= 14") > 0)
{
    $oDB->query("DELETE FROM results WHERE RaceID=$RaceID AND RiderID=$RiderID AND CategoryID=$CategoryID", __FILE__, __LINE__);
    $oDB->RecordActivityIfOK("Delete [results] C=$CategoryID", $RaceID);
}
else
{  
    exit("You are not allowed to delete this result");
}

header("Location: ../update-results");
exit();
?>