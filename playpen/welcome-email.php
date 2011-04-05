<?
require("../script/app-master.php");
require("../script/email-notifications.php");

$oDB = oOpenDBConnection();
ob_start();
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

<?
// send headers to tell the browser to close the connection
header("Content-Length: " . ob_get_length());
header('Connection: close');
ob_end_flush();
ob_flush();
flush();
session_write_close();

AccountCreatedEmail($oDB, $_REQUEST['to'], $_REQUEST['from']);
//  sleep(10);
?>
