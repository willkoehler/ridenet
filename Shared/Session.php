<?
// Restore current session or start new session if session isn't started
StartSession();


//----------------------------------------------------------------------------------
//  CheckSession()
//
//  This function checks to make sure the current session has not expired. The
//  application should set $_SESSION['loginTableID'] after validating the login.
//
//  PARAMETERS: none
//
//  RETURN: true if current session is still valid
//-----------------------------------------------------------------------------------
function CheckSession()
{
    return (isset($_SESSION['loginTableID'])) ? true : false;
}


//----------------------------------------------------------------------------------
//  StartSession()
//
//  Resumes the current session or starts a new one if there is no current session.
//  This function extends the default session timeout to 20 hours.
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function StartSession()
{
    // Don't start session if this is a web bot. Web bots will leave a blank session file for
    // every page they visit which can create thousands of blank session files over time, esepcially
    // if SESSION_LIFETIME is large.
    if(!DetectBot())
    {
        // We need a custom session name to prevent different application's sessions from stomping on each other
        ini_set('session.name', SESSION_ID);

        // --- Set session cookie's domain to the root domain (i.e. ridenet.org) so it will be valid for all
        // --- subdomains (i.e. echeloncycling.ridenet.org, cscc.ridenet.org, etc)
        session_set_cookie_params(0, "/", GetCookieDomainRoot(), is_ssl(), TRUE);

        // Set the garbage collector to SESSION_LIFETIME. Session files older than SESSION_LIFETIME will be
        // deleted when garbage collection runs. The session file timestamp is updated each time session_start()
        // is called. So sessions are only deleted after they have been idle for SESSION_LIFETIME
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
        // Every 100 session request we will do garbage collection
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);

        // we need a distinct directory for the session files,
        // otherwise another garbage collector with a lower gc_maxlifetime
        // will clean our files as well. But in our own directory, we only
        // clean sessions with our "own" garbage collector (which has a
        // custom timeout/maxlifetime set each time one of our scripts is
        // executed)
        $sep = strstr(strtoupper(substr(PHP_OS, 0, 3)), "WIN") ? "\\" : "/";
        $sessdir = ini_get('session.save_path') . $sep . SESSION_SUBDIR;
        if (is_dir($sessdir)==FALSE)
        {
            mkdir($sessdir, 0777);
        }
        ini_set('session.save_path', $sessdir);

        // now we're ready to start the session
        // $start = microtime(true);                     // TEMP CODE to work on IIS slow session_start issue
        session_start();
        // $elapsed = (microtime(true)-$start) * 1000;   // TEMP CODE to work on IIS slow session_start issue
        // if($elapsed > 100)                            // TEMP CODE to work on IIS slow session_start issue
        // {                                             // TEMP CODE to work on IIS slow session_start issue
        //     trigger_error("Slow Session Start: (" . number_format($elapsed) . " msec)", E_USER_NOTICE);   // TEMP CODE to work on IIS slow session_start issue
        // }                                             // TEMP CODE to work on IIS slow session_start issue

        // If session parameter KeepOpen is set, keep session open after browser closes. Since
        // the session cookie has already been created at this point, we have to overwrite the
        // session cookie manually. PHP will not create a new session cookie once a session has
        // started. So calling session_set_cookie_params will not have any effect.
        if(isset($_SESSION['KeepOpen']) && $_SESSION['KeepOpen'])
        {
            // preserve PHP session cookie after browser closes by setting an expiration date
            setcookie(session_name(), session_id(), time()+SESSION_LIFETIME, "/", GetCookieDomainRoot(), is_ssl(), TRUE);
        }
        else
        {
            // set PHP session cookie to expire when browser closes
            setcookie(session_name(), session_id(), 0, "/", GetCookieDomainRoot(), is_ssl(), TRUE);
        }

        // When a session is writable, PHP locks the session file and blocks subsequent calls to session_start()
        // This can cause bottlenecks if we need to load several pages at the same time. By default we unlock
        // the session file. NOTE: This doesn't appear to help CPU load problems on IIS servers. Theoretically it
        // could be an issue, but for now I'm going to hold off until I have a concrete reason to do this. Even
        // if I close the session here, the system still can pause on the call to session_start().
    //    if(!defined('WRITEABLE_SESSION'))
    //    {
    //        session_write_close();
    //    }
    }
}



//----------------------------------------------------------------------------------
//  EndSession()
//
//  End the current session and release all variables associated with the session
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function EndSession()
{
    // Unset all of the session variables.
    $_SESSION = array();
    // Finally, destroy the session.
    session_destroy();
}


//----------------------------------------------------------------------------------
//  IE6Check()
//
//  This function inserts code into the page to display a warning message if the
//  user is running IE6 or older. This function should be placed just below the
//  <body> tag in the page
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function IE6Check()
{?>
<!--[if lte IE 6]><script src="<?=SHAREDBASE_URL . "ie6warning/warning.js"?>"></script><script>window.onload=function(){e("<?=SHAREDBASE_URL . "ie6warning/"?>")}</script><![endif]-->
<?
}


//----------------------------------------------------------------------------------
//  IE5Check()
//
//  This function inserts code into the page to display a warning message if the
//  user is running IE6 or older. This function should be placed just below the
//  <body> tag in the page
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function IE5Check()
{?>
<!--[if lt IE 6]><script src="<?=SHAREDBASE_URL . "ie6warning/warning.js"?>"></script><script>window.onload=function(){e("<?=SHAREDBASE_URL . "ie6warning/"?>")}</script><![endif]-->
<?
}


//----------------------------------------------------------------------------------
//   GetDomainRoot()
//
//   This function gets the root domain of the current page minus the protocol prefix
//   That is the first two parts of the domain name. ex:
//
//    http://pattycacke.ridenet.org ==> ridenet.org
//    https://www.videojobsys.com ==> videojobsys.com
//
//   PARAMETERS: none
//
//   RETURN: the domain root
//-----------------------------------------------------------------------------------
function GetDomainRoot()
{
    $host = $_SERVER['HTTP_HOST'];
    if(filter_var($host, FILTER_VALIDATE_IP))
    {
        return $host;   // This is an IP address, leave it alone
    }
    else
    {
        $parts = array_reverse(explode(".", $host));
        return($parts[1] . "." . $parts[0]);
    }
}


//----------------------------------------------------------------------------------
//   GetFullDomainRoot()
//
//   This function gets the root domain of the current page with the protocol
//   prefix.
//
//    http://pattycacke.ridenet.org ==> http://ridenet.org
//    https://www.videojobsys.com ==> https://videojobsys.com
//
//   PARAMETERS: none
//
//   RETURN: the domain root
//-----------------------------------------------------------------------------------
function GetFullDomainRoot()
{
    $strProtocol = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on") ? "https://" : "http://";
    return $strProtocol . GetDomainRoot();
}


//----------------------------------------------------------------------------------
//   GetCookieDomainRoot()
//
//   This function gets the root domain to be used for the domain parameter of
//   setcookie. That is the first two parts of the domain name without the port.
//
//    http://pattycacke.ridenet.org ==> ridenet.org
//    https://www.videojobsys.com ==> videojobsys.com
//    http://psiapps.test:8080 ==> psiapps.test
//
//   PARAMETERS: none
//
//   RETURN: the domain root
//-----------------------------------------------------------------------------------
function GetCookieDomainRoot()
{
    return(strtok(GetDomainRoot(), ':')); // remove port ":8080" - if present
}


//----------------------------------------------------------------------------------
//   GetBaseHref()
//
//   This function gets the base HREF of the current page. That is the URL minus the
//   name of the page and query string.
//
//   PARAMETERS: none
//
//   RETURN: the base HREF
//-----------------------------------------------------------------------------------
function GetBaseHref()
{
    $baseHref="";
    $strFullURL = GetFullURL();
    $arrParts=explode("/", $strFullURL);
    for ($i=0; $i < (count($arrParts)-1); $i++)
    {
        $baseHref .= $arrParts[$i] . "/";
    }
    return $baseHref;
}


//----------------------------------------------------------------------------------
//   GetFullURL()
//
//   This function gets the full URL of the current page. ex:
//   "http://mydomain.com/mypage.php?param1=55
//
//   PARAMETERS: none
//
//   RETURN: Full URL of the current page
//-----------------------------------------------------------------------------------
function GetFullURL()
{
    $strProtocol = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on") ? "https://" : "http://";
    return($strProtocol . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
}


//----------------------------------------------------------------------------------
//  DetectBot()
//
//  This function determines whether the current page request has come from a web bot
//  To get a list of potential bot agents, parse the access_log file and group by
//  agent string:
//
//  awk -F\" '{print $6}' access_log | sort | uniq -c | sort -fr
//
//  PARAMETERS: none
//
//  RETURN: false if the page request did not come form a bot, otherwise will contain
//          the name of of the web bot
//-----------------------------------------------------------------------------------
function DetectBot()
{
    $botfound = false;
    $bot_list = array("Teoma", "alexa", "froogle", "Gigabot", "inktomi", "looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory",
                      "Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot", "crawler", "www.galaxy.com", "Googlebot", "Scooter",
                      "Slurp", "msnbot", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz", "Baiduspider", "Feedfetcher-Google",
                      "TechnoratiSnoop", "Rankivabot", "Mediapartners-Google", "Sogou web spider", "WebAlta Crawler", "YandexBot",
                      "Exabot", "NetcraftSurveyAgent", "TwengaBot", "PycURL", "Voyager", "Butterfly", "facebookexternalhit", "JS-Kit",
                      "Twitterbot", "bitlybot", "PostRank", "Jakarta", "LinkedInBot", "bingbot", "Huaweisymantecspider",
                      "KKman", "TweetmemeBot", "PeoplePal", "WebCapture", "SurveyBot", "ScoutJet", "BrowseX", "MJ12bot",
                      "SEOkicks", "DotBot", "SemrushBot", "SemrushBot", "AhrefsBot", "spbot", "MegaIndex");

    if(!isset($_SERVER['HTTP_USER_AGENT']))
    {
        // some really bad bots even mask HTTP_USER_AGENT
        $botfound = "Rogue";
    }
    else
    {
        foreach($bot_list as $bot)
        {
            if(preg_match('/' . $bot . '/', $_SERVER['HTTP_USER_AGENT']))
            {
                $botfound = $bot;
            }
        }

        // some rogue bots give themselves away by the query parameters
        if($botfound==false)
        {
            if(substr($_SERVER['QUERY_STRING'], 0, 11)=="option=com_")
            {
                $botfound = "Rogue";
            }
        }
    }
    return $botfound;
}

//----------------------------------------------------------------------------------
//  is_ssl()
//
//  Based on Wordpress. See https://stackoverflow.com/a/7304239/935514
//
//  PARAMETERS: none
//
//  RETURN: true if request is running over a HTTPS connection
//-----------------------------------------------------------------------------------
function is_ssl() {
    if ( isset($_SERVER['HTTPS']) ) {
        if ( 'on' == strtolower($_SERVER['HTTPS']) )
            return true;
        if ( '1' == $_SERVER['HTTPS'] )
            return true;
    } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
        return true;
    }
    return false;
}
?>
