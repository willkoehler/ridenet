<?
require("../script/app-master.php");

// store query/post values in local variables
$calendarID = SmartGetInt('CalendarID');

$oDB = oOpenDBConnection();
$rs = $oDB->query("SELECT CalendarID, CalendarDate, EventName, Location, Comments, ZipCodeID,
                   CONCAT(City, ', ', State, ' ', ZipCode) AS ZipCodeText, MapURL,
                   ClassX, ClassA, ClassB, ClassC, ClassD
                   FROM calendar LEFT JOIN ref_zipcodes USING (ZipCodeID)
                   WHERE CalendarID=$calendarID", __FILE__, __LINE__);
$result['results'] = $rs->fetch_object();
// figure out if logged in rider is attending this ride
$result['results']->Attending = $oDB->DBLookup("Attending", "calendar_attendance", "CalendarID={$result['results']->CalendarID} AND RiderID=" . GetUserID());
$result['success'] = true;

// --- Dump output.
Echo json_encode($result);
?>
