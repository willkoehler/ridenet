<?
// prevent browser and proxies from caching this page
header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );                   // set expiration date in the past
header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );    // set last modified date to now
header( "Cache-Control: no-cache, must-revalidate" );                 // newer HTTP 1.1 cache control headers

// use strict error reporting
error_reporting(E_ALL);
?>
