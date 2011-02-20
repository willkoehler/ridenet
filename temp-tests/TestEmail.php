<?
require("../script/app-master.php");
require("../script/email-notifications.php");

$oDB = oOpenDBConnection();

AccountCreatedEmail($oDB, $_REQUEST['to'], $_REQUEST['from']);

?>