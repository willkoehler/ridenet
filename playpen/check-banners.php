<?
require("../script/app-master.php");
$oDB = oOpenDBConnection();

$rs = $oDB->query("SELECT TeamID FROM team_images WHERE Banner IS NOT NULL ORDER BY TeamID");
while(($record = $rs->fetch_array())!=false) { ?>
  <img src="/imgstore/banner/<?=$record['TeamID']?>.jpg">
<? } ?>