<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
require("../script/app-master-min.php");

$oDB = oOpenDBConnection();
$uid = SmartGetInt('id');
$pw = SmartGetString('pw');

if(!isset($_REQUEST['id']) || !isset($_REQUEST['pw']))
{
    header("HTTP/1.1 400 Bad Request");
    $result['error'] = "Missing parameters";
}
elseif($oDB->DBCount("rider", "RiderID=$uid AND Password=$pw") == 0)
{
    header("HTTP/1.1 403 Forbidden");
    $result['error'] = "Login credentials are not valid";
}
else
{
    $result=null;
}

if($result)
{
    echo json_encode($result);
}
?>
