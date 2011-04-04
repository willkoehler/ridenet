<?
require("../script/app-master.php");
require("../script/email-notifications.php");

$oDB = oOpenDBConnection();
AccountCreatedEmail($oDB, $_REQUEST['to'], $_REQUEST['from']);
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
</head>

<body>
  Email sent<br>
  FROM: <?=$oDB->DBLookup("CONCAT(FirstName, ' ', LastName)", "rider", "RiderID={$_REQUEST['from']}")?><br>
  TO: <?=$oDB->DBLookup("CONCAT(FirstName, ' ', LastName)", "rider", "RiderID={$_REQUEST['to']}")?>
</body>
</html>
