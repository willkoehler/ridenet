<?
// --- Minimal version of app master that does not start a session. Saves time for things like dynamic
// --- images where a session is not needed.

// --- Location of shared library in local file system (for use in PHP require() statements)
define("SHAREDBASE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/Shared/");

require(SHAREDBASE_DIR . "RequestHelpers.php");
require(SHAREDBASE_DIR . "DBConnection.php");
require(SHAREDBASE_DIR . "DateHelpers.php");
require(dirname(__FILE__) . "/defaults.php");
require(dirname(__FILE__) . "/data-helpers.php");
?>