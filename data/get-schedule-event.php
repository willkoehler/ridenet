<?
require("../script/app-master.php");

// store query/post values in local variables
$raceID = SmartGetInt('RaceID');

$oDB = oOpenDBConnection();
$rs = $oDB->query("SELECT * FROM event WHERE RaceID=$raceID", __FILE__, __LINE__);
$result['results'] = $rs->fetch_object();
$result['success'] = true;

// --- Dump output.
Echo json_encode($result);
?>
