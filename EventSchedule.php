<?
require("script/app-master.php");
?>

<html>
<body style="font:15px helvetica;text-align:center">
  <?$newloc = GetBaseHref() . "event-schedule.php" . $_SERVER["QUERY_STRING"]?>
  <br>This page has moved to <?=$newloc?><br><br>
  Please update any bookmarks you have made to this page.
</body>
</html>
