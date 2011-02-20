<?
// --- Session ID and sub-directory name for storing sessions
define("SESSION_ID", "RIDENETSESSID");
define("SESSION_SUBDIR", "RideNetSessions");
define("SESSION_LIFETIME", 60 * 60 * 24 * 30);   // 30 days

// --- Location of shared library in local file system (for use in PHP require() statements)
define("SHAREDBASE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/Shared/");

require(SHAREDBASE_DIR . "RequestHelpers.php");
require(SHAREDBASE_DIR . "Session.php");

echo "<pre>";
print_r($_SERVER);
echo "\n\nGetBaseHref() = " . GetBaseHref() . "\n";
echo "GetDomainRoot() = " . GetDomainRoot() . "\n";
echo "GetFullDomainRoot() = " . GetFullDomainRoot() . "\n";
echo "GetFullURL() = " . GetFullURL() . "\n";
echo "</pre>";
?>
<a href='http://considerbiking.ridenet.local/temp-tests/serverinfo2.php'>Refer ME</a><br>
<span onclick="window.location.href='http://considerbiking.ridenet.local/temp-tests/serverinfo2.php'">Refer ME 2</span>
