<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();
if(!CheckSession())
{
    echo "Session Expired. Please login again";
    exit();
}

$values['RaceID'] = SmartGetInt("RaceID");
$values['RiderID'] = GetUserID();
$values['Report'] = SmartGetString("Report");
$values['DateFiled'] = "NOW()";

// verify that the result was posted less than 14 days ago
if($oDB->DBCount("results", "RiderID={$values['RiderID']} AND RaceID={$values['RaceID']} AND DATEDIFF(NOW(), DateAdded) <= 14") > 0)
{
    if($values['Report']=="NULL")
    {
        // Delete report record if user enters a blank report
        $oDB->query("DELETE FROM race_report WHERE RaceID={$values['RaceID']} AND RiderID={$values['RiderID']}", __FILE__, __LINE__);
        // record activity
        $oDB->RecordActivityIfOK("Delete [race_report]", $values['RaceID']);
    }
    else
    {
        // Update existing report
        InsertOrUpdateRecord($oDB, "race_report", "RaceID={$values['RaceID']} AND RiderID={$values['RiderID']}", $values);
        // record activity
        $oDB->RecordActivityIfOK("Modify [race_report]", $values['RaceID']);
    }
}
else
{  
    exit("You are not allowed to modify this report");
}

header("Location: ../update-results.php");
exit();
?>