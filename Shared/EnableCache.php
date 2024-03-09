<?
// It turns out that caching content output by PHP pages is disabled by default. Apache and IIS insert
// their own cache headers to disable the cache when generating output from a PHP page
// The following headers are required to enable caching.
$timeout = 60 * 60 * 48;  // 48 hours
header( 'Expires: ' . gmdate('D, d M Y H:i:s', time() + $timeout) . 'GMT');   // set expiration date
header( "Cache-Control: public max-age=$timeout s-maxage=$timeout" );         // newer HTTP 1.1 cache control headers
header( "Pragma: cache");                                       // also needed to override "pragma: no-cache" added by Apache/IIS
?>