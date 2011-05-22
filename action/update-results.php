<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();
if(!CheckSession())
{
    echo "Session Expired. Please login again";
    exit();
}

// --- Save Result
$values['RaceID'] = SmartGetInt("RaceID");
$values['CategoryID'] = SmartGetInt("CategoryID");
$values['PlaceID'] = SmartGetInt("PlaceID");
$values['RiderID'] = GetUserID();
$values['TeamID'] = $oDB->DBLookup("RacingTeamID", "rider", "RiderID=" . GetUserID());

if($values['RaceID']==0 || $values['CategoryID']==0 || $values['PlaceID']==0)
{
    // data missing, do not post the result
    header("Location: ../update-results");
    exit();
}

$strWhere = "RaceID={$values['RaceID']} AND RiderID={$values['RiderID']} AND CategoryID={$values['CategoryID']}";
// --- has result already been entered for this rider, race, & category?
$rs = $oDB->query("SELECT COUNT(*) AS Count FROM results WHERE $strWhere", __FILE__, __LINE__);
$record=$rs->fetch_array();
if($record['Count'] == 0)
{
    // There are no matching results, clear Where clause so a new result will be created
    $strWhere="";
    $values['Created'] =  "'" . date("Y-m-d H:i:s") . "'";
}
InsertOrUpdateRecord($oDB, "results", $strWhere, $values);
// record activity
$oDB->RecordActivityIfOK("Modify [results] C={$values['CategoryID']}", $values['RaceID']);

// -- Save Race Report
$values2['RaceID'] = SmartGetInt("RaceID");
$values2['RiderID'] = GetUserID();
$values2['TeamID'] = $values['TeamID'];
$values2['Report'] = SmartGetString("Report");
$values2['DateFiled'] = "NOW()";

if($values2['Report']!="NULL" && $oDB->DBCount("race_report", "RaceID={$values2['RaceID']} AND RiderID={$values2['RiderID']}")==0)
{
    // Rider entered a report and there is no existing report, create new report
    // (We do not update race report if one already exists)
    InsertOrUpdateRecord($oDB, "race_report", "", $values2);
    // record activity
    $oDB->RecordActivityIfOK("Add [race_report]", $values['RaceID']);
}

header("Location: ../update-results");
exit();
?>