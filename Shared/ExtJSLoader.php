<?
require(SHAREDBASE_DIR . "jsmin.php");
// --- Set jsdebug true to send debug version of JavaScript.
if(isset($_REQUEST['debug']))  { define("JSDEBUG", true); }


//----------------------------------------------------------------------------------
//  IncludeExtJSFiles()
//
//  Include files required for Ext
//
//  PARAMETERS:
//    includeCSS    - true = include ExtJS UI stylesheets. Defaults to true
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function IncludeExtJSFiles($includeCSS=true)
{
// --- ExtJS doesn't currently support IE9. Force IE9 into IE8 compatibility mode until we
// --- can add native support for IE9
    echo "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=EmulateIE8\">\n";
    if($includeCSS)
    {
    // ---- Ext stylesheet + Theme
        echo "<link rel='stylesheet' type='text/css' href='" . EXTBASE_URL . "resources/css/ext-all-notheme.css'>\n";
        echo "<link rel='stylesheet' type='text/css' href='" . SHAREDBASE_URL . "themes/css/xtheme-gray-wck.css'>\n";
    // --- Ext Fixes Stylesheet (must come after Ext stylesheet)
        echo "<link rel='stylesheet' type='text/css' href='" . SHAREDBASE_URL . "ExtFixes.css'>";
    }
// --- Ext javascript code and our helper code
    if(defined("JSDEBUG"))
    {
        echo "<script type='text/javascript' src='" . EXTBASE_URL . "adapter/ext/ext-base-debug.js'></script>\n";
        echo "<script type='text/javascript' src='" . EXTBASE_URL . "ext-all-debug.js'></script>\n";
    }
    else
    {
        echo "<script type='text/javascript' src='" . EXTBASE_URL . "adapter/ext/ext-base.js'></script>\n";
        echo "<script type='text/javascript' src='" . EXTBASE_URL . "ext-all.js'></script>\n";
    }
        echo "<script type='text/javascript' src='" . SHAREDBASE_URL . "ExtHelpers.js'></script>\n";
// --- redefine BLANK_IMAGE_URL to point to a local image file
    echo "<script type='text/javascript'>Ext.BLANK_IMAGE_URL = '" . EXTBASE_URL . "resources/images/default/s.gif';</script>\n";
}


//----------------------------------------------------------------------------------
//  IncludeRowEditor()
//
//  Include files for row editor component in ExtJS
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function IncludeRowEditor()
{
  echo "<script type='text/javascript' src='" . EXTBASE_URL . "examples/ux/RowEditor.js'></script>\n";
  echo "<link rel='stylesheet' type='text/css' href='" . EXTBASE_URL . "examples/ux/css/RowEditor.css'>\n";
  echo "<script type='text/javascript' src='" . SHAREDBASE_URL . "RowEditorFixes.js'></script>\n";
}


//----------------------------------------------------------------------------------
//  MinifyAndInclude()
//
//  Minify javascript code and then link to minified version. Cache minified version
//  for future use. Cache is updated when javascript file changes. Minifying the
//  javascript reduces the size by about 50%. It also makes the javascript harder
//  to read and should discourage others from trying to reuse the code.
//
//  The location pointed to by JSCACHE_DIR must be writable by PHP
//
//  if JSDEBUG is defined, code will not be minified
//
//  PARAMETERS:
//    String filename   - javascript file to include, expressed as absolute
//                        path and filename from the website root.
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function MinifyAndInclude($filename)
{
    if(defined("JSDEBUG") || !defined("JSCACHE_DIR"))
    {
        // in debug mode (or if cache dir is not defined) link to original javascript file
        echo "<script type='text/javascript' src='$filename'></script>\n";
    }
    else
    {
        // By using the root domain name (i.e. ridenet.net), resorces loaded from this URL will be
        // cached once and used for all the subdomains on ridenet.
        $jsCacheURL = GetFullDomainRoot() . str_replace($_SERVER["DOCUMENT_ROOT"], "", JSCACHE_DIR);
        $minfile = JSCACHE_DIR . basename($filename);
        $minurl = $jsCacheURL . basename($filename);
        $fullPathAndFile = $_SERVER["DOCUMENT_ROOT"] . $filename;
        // Check js cache for minified version of file. If file does not exist or is
        // out of date, create a new minified version of the js file
        if(!file_exists($minfile) || filemtime($fullPathAndFile) > filemtime($minfile))
        {
            $minifiedJS = JSMin::minify(file_get_contents($fullPathAndFile));
            if(file_put_contents($minfile, $minifiedJS))
            {
                // add a link to the minified file to the page
                echo "<script type='text/javascript' src='$minurl'></script>\n";
            }
            else
            {
                // minification failed, add a link to the original file to the page and log warning
                trigger_error("Failed to minify and cache $filename to $minfile", E_USER_WARNING);
                echo "<script type='text/javascript' src='$filename'></script>\n";
            }
        }
        else
        {
            // minified version found in cache, add a link to the minified file to the page
            echo "<script type='text/javascript' src='$minurl'></script>\n";
        }
    }
}

?>