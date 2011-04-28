<?
// Include only the essentials. Don't start a session here - it causes a big hit on Windows/IIS servers
define("SHAREDBASE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/Shared/");
require(SHAREDBASE_DIR . "DBConnection.php");
require(SHAREDBASE_DIR . "RequestHelpers.php");
require(dirname(__FILE__) . "/../script/data-helpers.php");

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
