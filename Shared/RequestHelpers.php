<?
//----------------------------------------------------------------------------------
//  SmartGet()
//
//  This function first checks for the existence of a POST variable with the name
//  provided in varName. If that is not found, it checks for a GET variable. If that
//  is not found, $default is returned. This function allows pages that normally
//  receive POST variables from Ext objects to be tested with GET variables
//
//  NOTE: Disabled controls do not submit their value. Because of this sometimes
//  not all values are posted that we expect so we can't display an error when
//  an submitted value is missing.
//
//  PARAMETERS:
//    varName   - variable name to read
//    default   - value to return if varName is not found
//
//  RETURN: value of requested POST/GET variable
//-----------------------------------------------------------------------------------
function SmartGet($varName, $default="NULL")
{
    if(isset($_POST[$varName]) && $_POST[$varName]!="")
    {
        return($_POST[$varName]);
    }
    elseif(isset($_GET[$varName]) && $_GET[$varName]!="")
    {
        return($_GET[$varName]);
    }
    else
    {
        return($default);
    }
}


//----------------------------------------------------------------------------------
//   SmartGetString()
//
//   This function reads a string value from a POST/GET variable and converts it
//   to a format acceptable to MySQL. If the string is empty, this function returns
//   NULL, otherwise it returns the string stipped of leading and trailing spaces
//   and delimited by quotes.
//
//   If a database connection is provided in oDB, the string is escaped using a
//   database library function that's more thorough than addslashes(). This is
//   needed to fully protect against SQL injection attacks.
//
//  PARAMETERS:
//     name  - name of string variable to read
//     oDB   - optional database connection.
//
//  RETURN: "NULL" if string is empty, otherwise string stipped of leading/trailing spaces
//-----------------------------------------------------------------------------------
function SmartGetString($name, $oDB=null)
{
    return PrepareStringForSQL(SmartGet($name), $oDB);
}


//----------------------------------------------------------------------------------
//   SmartGetDate()
//
//   This function reads a date from a POST/GET variable and converts it to a
//   format acceptable to MySQL. If the date value is empty, this function returns
//   NULL, otherwise it returns the date value in a format that MySQL will accept
//   delimited by quotes
//
//  PARAMETERS:
//     name  - name of POST/GET variable to read as date
//
//  RETURN: "NULL" if date is empty, otherwise date converted to YYYY-MM-DD
//-----------------------------------------------------------------------------------
function SmartGetDate($name)
{
    return PrepareDateForSQL(SmartGet($name));
}


//----------------------------------------------------------------------------------
//   SmartGetInt()
//
//   This function reads an integer value from a POST/GET variable and converts it
//   to a format acceptable to MySQL. If the integer is empty, this function returns
//   NULL, otherwise it returns the integer
//
//  PARAMETERS:
//     name  - name of integer variable to read
//
//  RETURN: "NULL" if integer is empty, otherwise integer
//-----------------------------------------------------------------------------------
function SmartGetInt($name)
{
    $val = SmartGet($name);
    if(strtoupper($val)=="NULL")
    {
        return("NULL");
    }
    else
    {
        $val = str_replace(",", "", $val);        // remove commas
        $val = str_replace("$", "", $val);        // remove dollar signs
        $newval = intval($val);
        return($newval);
    }
}


//----------------------------------------------------------------------------------
//   SmartGetFloat()
//
//   This function reads a floating point value from a POST/GET variable and converts
//   it to a format acceptable to MySQL. If the value is empty, this function returns
//   NULL, otherwise it returns the value
//
//  PARAMETERS:
//     name  - name of floating point variable to read
//
//  RETURN: "NULL" if value is empty, otherwise value
//-----------------------------------------------------------------------------------
function SmartGetFloat($name)
{
    $val = SmartGet($name);
    if(strtoupper($val)=="NULL")
    {
        return("NULL");
    }
    else
    {
        $val = str_replace(",", "", $val);        // remove commas
        $val = str_replace("$", "", $val);        // remove dollar signs
        return(floatval($val));
    }
}


//----------------------------------------------------------------------------------
//   SmartGetCheckbox()
//
//   This function reads the value of a checkbox from a POST/GET variable and
//   converts it to a format acceptable to MySQL. When a checkbox is checked, its
//   value is posted as "on", when it's not checked the checkbox value is absent
//   from the post.
//
//  PARAMETERS:
//     name  - name of checkbox variable to read
//
//  RETURN: 1 if checkbox is checked, otherwise 0
//-----------------------------------------------------------------------------------
function SmartGetCheckbox($name)
{
    if(isset($_GET[$name]))
    {
    // first check GET variables
        return(($_GET[$name]=="on") ? 1 : 0);
    }
    elseif(isset($_POST[$name]))
    {
    // then check POST variables
        return(($_POST[$name]=="on") ? 1 : 0);
    }
    else
    {
    // if checkbox wasn't found, it was unchecked
        return(0);
    }
}


//----------------------------------------------------------------------------------
//   SmartGetDuration()
//
//   This function reads a duration value in the form of H:MM or MM from a POST/GET
//   variable and converts it number of minutes. If the duration is empty, this
//   function returns NULL, otherwise it returns the duration in minutes
//
//  PARAMETERS:
//     name  - name of duration variable to read
//
//  RETURN: "NULL" if duration is empty, otherwise duration in minutes
//-----------------------------------------------------------------------------------
function SmartGetDuration($name)
{
    $val = SmartGet($name);
    if(strtoupper($val)=="NULL")
    {
        return("NULL");
    }
    else
    {
        $parts = explode(':', $val);
        switch(count($parts)) {
          case 1:
            // value entered as straight minutes
            $newval = intval($parts[0]);
            break;
          case 2:
            // value entered as H:MM
            $newval = intval($parts[0] * 60 + $parts[1]);
            break;
          default:
            $newval = "NULL";
        }
        return($newval);
    }
}



//----------------------------------------------------------------------------------
//  CheckRequiredParameters()
//
//  Checks to see if required parameters were posted with the request. If required
//  parameters are not present, the page terminates with a 400 Bad request code.
//  This is primarily needed to handle bots scanning pages.
//
//  PARAMETERS:
//    required    - array of required parameters
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function CheckRequiredParameters($required)
{
    foreach($required AS $param)
    {
        if(!isset($_REQUEST[$param]))
        {
            header("HTTP/1.1 400 Bad Request");
            exit();
        }
    }
}


//----------------------------------------------------------------------------------
//  CheckLastModified()
//
//  Checks to see if the current resource has been modified since the browser cached
//  it. If the resouce has not been modified, the function end the current script
//  and returns a "304 Not Modified" header. Otherwise the function sets the
//  Last-Modified header.
//
//  This function modifies the header and must be called before any output has been
//  written
//
//  PARAMETERS:
//    lastModified    - Unix timestamp of date & time this resource was last modified
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function CheckLastModified($lastModified)
{
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])==$lastModified)
    {
        header("HTTP/1.1 304 Not Modified");
        exit;
    }
    header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");
}


define('SALT_LENGTH', 23);
define('HASH_LENGTH', 40);
//----------------------------------------------------------------------------------
//  GetPasswordSalt()
//
//  Passwords are stored as hash + salt. The hash is HASH_LENGTH chars and the salt
//  is SALT_LENGTH chars. This function returns the salt portion of the password.
//
//  PARAMETERS:
//    hashsalt    - password hash + salt
//
//  RETURN: salt portion of the password
//-----------------------------------------------------------------------------------
function GetPasswordSalt($hashsalt)
{
    if(strlen($hashsalt)==HASH_LENGTH)
    {
        // for backwards compatibility, support passwords without salt
        return("");
    }
    else
    {
        return(substr($hashsalt, -SALT_LENGTH, SALT_LENGTH));
    }
}


//----------------------------------------------------------------------------------
//  CheckPassword()
//
//  This function checks to see if the given plain text password matches the given
//  hashsalt.
//
//  PARAMETERS:
//    pw        - plain text password
//    hashsalt  - hashsalt of password to check against
//
//  RETURN: true if pw matches hashsalt
//-----------------------------------------------------------------------------------
function CheckPassword($pw, $hashsalt)
{
    $salt = GetPasswordSalt($hashsalt);
    return(MakePasswordHash($pw, $salt) == $hashsalt);

}


//----------------------------------------------------------------------------------
//  MakePasswordHash()
//
//  Build a password hash + salt. If salt is not provided, a random salt will be
//  generated.
//
//  PARAMETERS:
//    pw    - plain text password
//    salt  - salt (will be generated if not provided)
//
//  RETURN: password hash + salt hased with sha1
//-----------------------------------------------------------------------------------
function MakePasswordHash($pw, $salt = null)
{
    if ($salt === null)
    {
        $salt = substr(uniqid("", true), 0, SALT_LENGTH);
    }
    else
    {
        $salt = substr($salt, 0, SALT_LENGTH);
    }

    return sha1($salt . $pw) . $salt;
}


//----------------------------------------------------------------------------------
//  GeneratePassword()
//
//  Generates a random password string.
//
//  PARAMETERS:
//    length  - length of the password
//    level   - level of password complexity
//
//  RETURN: generated password
//-----------------------------------------------------------------------------------
function GeneratePassword($length=6, $level=2)
{
    list($usec, $sec) = explode(' ', microtime());
    srand((float) $sec + ((float) $usec * 100000));

    $validchars[1] = "0123456789abcdfghjkmnpqrstvwxyz";
    $validchars[2] = "0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $validchars[3] = "0123456789_!@#$%&*()-=+/abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_!@#$%&*()-=+/";

    $password  = "";
    $counter   = 0;

    while ($counter < $length)
    {
        $actChar = substr($validchars[$level], rand(0, strlen($validchars[$level])-1), 1);

        // All character must be different
        if (!strstr($password, $actChar))
        {
            $password .= $actChar;
            $counter++;
        }
    }

    return $password;
}


//----------------------------------------------------------------------------------
//   GetBrowserString()
//
//   This function builds a string describing the client's browser.
//
//  PARAMETERS:
//    agent   - HTTP_USER_AGENT string from browser
//
//  RETURN: string describing client browser
//-----------------------------------------------------------------------------------
function GetBrowserString($agent)
{
// This code based on code in ExtJS (file: Ext.js)
    $isOpera = preg_match('/opera/i', $agent);
    $isChrome = preg_match('/chrome/i', $agent);
    $isWebKit = preg_match('/webkit/i', $agent);
    $isSafari = !$isChrome && preg_match('/safari/i', $agent);
    $isSafari2 = $isSafari && preg_match('/applewebkit\/4/i', $agent); // unique to Safari 2
    $isSafari3 = $isSafari && preg_match('/version\/3/i', $agent);
    $isSafari4 = $isSafari && preg_match('/version\/4/i', $agent);
    $isSafari5 = $isSafari && preg_match('/version\/5/i', $agent);
    $isIE = !$isOpera && preg_match('/msie/i', $agent);
    $isIE7 = $isIE && preg_match('/msie 7/i', $agent);
    $isIE8 = $isIE && preg_match('/msie 8/i', $agent);
    $isIE6 = $isIE && !$isIE7 && !$isIE8;
    $isGecko = !$isWebKit && preg_match('/gecko/i', $agent);
    $isGecko2 = $isGecko && preg_match('/rv:1\.8/i', $agent);
    $isGecko3 = $isGecko && preg_match('/rv:1\.9/i', $agent);
    $isWindows = preg_match('/windows|win32/i', $agent);
    $isMac = preg_match('/macintosh|mac os x/i', $agent);
    $isAir = preg_match('/adobeair/i', $agent);
    $isLinux = preg_match('/linux/i', $agent);

    $type = ($isOpera) ? "Opera" : (($isChrome) ? "Chrome" : (($isSafari) ? "Safari" : (($isIE) ? "IE" : (($isGecko) ? "Firefox" : "Other"))));
    $os = ($isWindows) ? "Windows" : (($isMac) ? "Mac" : (($isAir) ? "Air" : (($isLinux) ? "Linux" : "Other")));
    $version = ($isSafari2 || $isGecko2) ? "2" : (($isSafari3 || $isGecko3) ? "3" : (($isSafari4) ? "4" : (($isSafari5) ? "5" : (($isIE6) ? "6" : (($isIE7) ? "7" : (($isIE8) ? "8" : "0"))))));
    $browserString = $type . " " . $version . " - " . $os;

    return($browserString);
}


//----------------------------------------------------------------------------------
//   ConvertToHTML()
//
//   This function converts a manually formatted list strText into an HTML formatted
//   list. It does the conversion by replacing each "**" in the string with an HTML
//   list item tag <li>.
//
//   This function also adds an HTML line break <br> wherever it finds a conventional
//   line breaks (carraige-return / line-feed). Normally HTML will ignore line breaks
//   in text. By changing the line breaks to <br>, we format the the text so that it
//   appears with the correct line breaks on the web page.
//
//  PARAMETERS:
//     Variant strText   - string to convert
//
//  RETURN: Variant. strText with converted to HTML format list
//-----------------------------------------------------------------------------------
function ConvertToHTML($strText)
{
    $strConverted="";
    $bHasList=false;
    $iCharPos=0;

    // --- replace line breaks with <br> tags
    $strText = preg_replace('/(\r\n|[\r\n])/', '<br />', $strText);
    // --- replace "**" with <li> tags
    while($iCharPos < strlen($strText))
    {
        if(substr($strText, $iCharPos, 2)=="**" && $bHasList==false)
        {
        // first item in the list, add <ul> tag then <li> tag
            $strConverted .= "<ul class=CompactList><li>";
            $bHasList=true;
            $iCharPos += 2;
        }
        elseif(substr($strText, $iCharPos, 2)=="**")
        {
        // subsequent items in the list, just add <li> tag
            $strConverted .= "<li>";
            $iCharPos += 2;
        }
        elseif($bHasList && substr($strText, $iCharPos, 6)=="<br />" && substr($strText, $iCharPos+6, 2)!="**")
        {
        // end of the list, add </ul> tag
            $strConverted .= "</ul>";
            $iCharPos += 6;
            $bHasList=false;
        }
        else
        {
            $strConverted .= $strText[$iCharPos];
            $iCharPos++;
        }
    }
    if ($bHasList)
    {
        $strConverted .= "</ul>";
    }
    return $strConverted;
}


//----------------------------------------------------------------------------------
//   ParseURLs()
//
//   This function searches the give text for URLS and adds HTML tags to make the
//   URLs clickable.
//
//  PARAMETERS:
//     text   - text to search for URLs
//
//  RETURN: text with HTML tags added to make them clickable
//-----------------------------------------------------------------------------------
function ParseURLs($text, $maxurl_len = 35, $target = '_blank')
{
    if (preg_match_all('/((ht|f)tps?:\/\/([\w\.]+\.)?[\w-]+(\.[a-zA-Z]{2,4})?[^\s\r\n\(\)"\'<>\,\!]+)/si', $text, $urls))
    {
        $offset1 = ceil(0.65 * $maxurl_len) - 2;
        $offset2 = ceil(0.30 * $maxurl_len) - 1;

        foreach (array_unique($urls[1]) AS $url)
        {
            if ($maxurl_len AND strlen($url) > $maxurl_len)
            {
                $urltext = substr($url, 0, $offset1) . '…' . substr($url, -$offset2);
            }
            else
            {
                $urltext = $url;
            }
            $text = str_replace($url, '<a href="'. $url .'" target="'. $target .'" title="'. $url .'">'. $urltext .'</a>', $text);
        }
    }
    return $text;
}


//----------------------------------------------------------------------------------
//   LF2BR()
//
//   This function replaces conventional line breaks (LF, CR, CRLF) in a string
//   with HTML "<br>" tags.
//
//  PARAMETERS:
//     Variant strText   - string to convert
//
//  RETURN: strText with <br> in place of conventional line breaks
//-----------------------------------------------------------------------------------
function LF2BR($strText)
{
    return (preg_replace("/(\r\n|[\r\n])/", "<br />", $strText));
}


//----------------------------------------------------------------------------------
//   ReplaceUnprintables()
//
//   This function replaces unprintable characters, smart quotes, elipses, dashes,
//   etc. pasted from MS Word with characters compatible with browsers
//
//  PARAMETERS:
//     Variant strText   - string to convert
//
//  RETURN: strText with unprintable characters replaced
//-----------------------------------------------------------------------------------
function ReplaceUnprintables($strText)
{
    $strText = str_replace("“", "\"", $strText);
    $strText = str_replace("”", "\"", $strText);
    $strText = str_replace("’", "'", $strText);
    $strText = str_replace("‘", "'", $strText);
    $strText = str_replace("…", "...", $strText);
    $strText = str_replace("–", "-", $strText);
    return($strText);
}



//----------------------------------------------------------------------------------
//  LimitString()
//
//  Limits length of string to $maxLen characters. If string is truncated then
//  ellipses ... are appended to the end of the string to indicate it has been
//  truncated.
//
//  PARAMETERS:
//    str     - string to limit
//    maxLen  - maximum length of string
//
//  RETURN: str truncated to length specified by maxLne
//-----------------------------------------------------------------------------------
function LimitString($str, $maxLen)
{
    if(strlen($str) > $maxLen)
    {
        return(substr($str, 0, $maxLen) . "...");
    }
    else
    {
        return($str);
    }
}


//----------------------------------------------------------------------------------
//  Plural()
//
//  This function choose the singlular or plural form of a word based on the value
//  passed in and then concatenate the value and word. For example Plural(1, "duck")
//  returns "1 duck", Plural(4, "duck") returns "4 ducks"
//
//  PARAMETERS:
//    value     - value to determine whether word is signular or plural
//    singular  - singular version of the word
//    plural    - (optional) plural version of the word (defaults to singular + s)
//
//  RETURN: singlular/plural version of a word concatenated with the value
//-----------------------------------------------------------------------------------
function Plural($value, $singular, $plural="")
{
    $plural = ($plural=="") ? $singular . 's' : $plural;
    return ($value==1) ? "$value $singular" : "$value $plural";
}


//----------------------------------------------------------------------------------
//  EscapeJavascript()
//
//  This function escapes escapes carriage returns and single and double quotes in
//  a string so it can be embedded in javascript code.
//
//  PARAMETERS:
//    string    - string to be escaped
//
//  RETURN: escaped version of the string
//-----------------------------------------------------------------------------------
function EscapeJavascript($string)
{
    return(preg_replace("/\r?\n/", "\\n", addslashes($string)));
}


//----------------------------------------------------------------------------------
//  h()
//
//  Short version of htmlspecialchars() for use in views. This is used to mitigate
//  XSS exploits by replacing special characters &, <, >, etc with HTML entities
//
//  PARAMETERS:
//    content  - content for the view - which may contain special characters
//
//  RETURN: content with special characters converted to HTML entities
//-----------------------------------------------------------------------------------
function h($content)
{
    return(htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401));
}
?>