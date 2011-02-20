<?
// --- Session ID and sub-directory name for storing sessions
define("SESSION_ID", "RIDENETSESSID");
define("SESSION_SUBDIR", "RideNetSessions");
define("SESSION_LIFETIME", 60 * 60 * 24 * 30);   // 30 days

// --- Location of shared library in local file system (for use in PHP require() statements)
define("SHAREDBASE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/Shared/");
// --- Location to cache minified js files.
define("JSCACHE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/minjs/");

require(dirname(__FILE__) . "/defaults.php");
require(SHAREDBASE_DIR . "RequestHelpers.php");
require(SHAREDBASE_DIR . "Session.php");
require(SHAREDBASE_DIR . "DBConnection.php");
require(SHAREDBASE_DIR . "DateHelpers.php");
require(dirname(__FILE__) . "/data-helpers.php");
require(dirname(__FILE__) . "/security-helpers.php");
require(dirname(__FILE__) . "/header-and-menus.php");

// --- Root URL of ExtJS library and Shared library. By using the root domain name
// --- (i.e. ridenet.org), resorces loaded from this URL will be cached once and used for
// --- all the subdomains on RideNet.
define("SHAREDBASE_URL", GetFullDomainRoot() . "/Shared/");
define("EXTBASE_URL", GetFullDomainRoot() . "/Ext3.2.2/");


// Redirect users to the new ridenet.net domain
$strFullURL = GetFullURL();
if(strpos($strFullURL, "velobug.com") || strpos($strFullURL, "ridenet.org"))
{ 
  $oDB = oOpenDBConnection();
  RecordPageView($oDB);
  $newloc = str_ireplace("ridenet.org", "ridenet.net", $strFullURL);
  $newloc = str_ireplace("velobug.com", "ridenet.net", $newloc);
  ?>
  <html>
  <body style="font:15px helvetica;text-align:left">
    <br>RideNet has moved to a new domain (<span style="color:orange">www.ridenet.net</span>).<br>
    Please update any bookmarks you have made to this page.<br>
    The new page location is:<br><br>
    <span style="font-size:18px;color:black"><?=$newloc?></span>
  </body>
  </html>
<?exit();
}
?>