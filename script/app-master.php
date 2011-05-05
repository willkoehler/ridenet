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

// Redirect visitors from www.ridenet.net/ to ridenet.net/. Search engines honor this
// redirect and will treat www.ridenet.net and ridenet.net as the same site
/*$fullURL = GetFullURL();
if(strpos($fullURL, "www.ridenet"))
{
    $newloc = str_ireplace("www.ridenet", "ridenet", $fullURL);
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $newloc");
    exit();
}*/
?>