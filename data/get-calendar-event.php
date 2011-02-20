<?
require("../script/app-master.php");

// store query/post values in local variables
$calendarID = SmartGetInt('CalendarID');

$oDB = oOpenDBConnection();
$rs = $oDB->query("SELECT *, CONCAT(City, ', ', State, ' ', ZipCode) AS ZipCodeText
                   FROM calendar LEFT JOIN ref_zipcodes USING (ZipCodeID)
                   WHERE CalendarID=$calendarID", __FILE__, __LINE__);
$result['results'] = $rs->fetch_object();
$result['success'] = true;

// --- Dump output.
Echo json_encode($result);
?>
