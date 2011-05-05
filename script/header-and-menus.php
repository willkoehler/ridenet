<?
//----------------------------------------------------------------------------------
//  GetPresentedTeamID()
//
//  This function determines the ID of the team currently being presented.
//
//  PARAMETERS:
//    oDB       - database connection (mysqli object)
//
//  RETURN: ID of team currently being presented
//-----------------------------------------------------------------------------------
function GetPresentedTeamID($oDB)
{
    // Extract the domain name from URL. Domain name may be "www.teamname.com" or
    // "teamname.com" or "teamname.ridenet.net". In all cases we want to extract "teamname"
    $domain = $_SERVER['HTTP_HOST'];
    $domain = str_replace("www.", "", $domain);
    $parts = explode(".", $domain);
    $domain = $parts[0];
    if(isset($_REQUEST['T']))
    {
    // TeamID query parameter is set. Use this
        return(SmartGetInt("T"));
    }
    elseif(($teamID = $oDB->DBLookup("TeamID", "teams", "Domain='$domain'"))!=false)
    {
    // Found matching domain name in team table. Use this TeamID
        return($teamID);
    }
    elseif($domain=="ridenet")
    {
        return(0);
    }
    else
    {
        exit("The RideNet subdomain \"$domain\" is not valid.<br><br>
              Visit <a href=\"http://ridenet.net\">RideNet Home</a> and use the search box in the menu
              to search for the team by name." );
    }
}


//----------------------------------------------------------------------------------
//  InsertPageBanner()
//
//  This function inserts the page banner onto the page. If a team logo has been
//  uploaded and ShowLogo is 1, the team logo will be superimposed over the
//  center of the banner.
//
//  PARAMETERS:
//    oDB   - database connection (mysqli object)
//    pt    - ID of team to display banner for. Use pt=0 to display the
//            default "RideNet" banner
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function InsertPageBanner($oDB, $pt=0)
{
    $showLogo = $oDB->DBLookup("IF(ShowLogo=1 AND Logo IS NOT NULL, 1, 0)", "teams", "TeamID=$pt", 0);?>
    <div style="position:relative">
      <img id="page-banner" src="/dynamic-images/page-banner.php?T=<?=$pt?>" class="tight" />
      <? if($showLogo) { ?>
        <table cellpadding=0 cellspacing=0 style="height:70px;width:300px;text-align:center;position:absolute;top:10px;left:220px"><tr><td>
          <img style="vertical-align:middle;" src="/dynamic-images/team-logo.php?T=<?=$pt?>" />
        </td></tr></table>
      <? } ?>
    </div>
      
<?
}


//----------------------------------------------------------------------------------
//  BuildPageTitle()
//
//  This function builds a title for inside the <title></title> page tags
//
//  PARAMETERS:
//    oDB   - database connection (mysqli object)
//    pt    - ID of team currently being presented to the user.
//    page  - name of the page
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function BuildPageTitle($oDB, $pt=0, $page="")
{
    $teamName = $oDB->DBLookup("TeamName", "teams", "TeamID=$pt");
    echo ($teamName!=NULL) ? "RideNet | " . $teamName : "RideNet";
    echo ($page!="") ? " | " . $page : "";
}


//----------------------------------------------------------------------------------
//  InsertGoogleAnalyticsTracker()
//
//  Insert tracking code for Google Analytics into the current page. This should be
//  placed just before the closing </head> tag.
//
//  Rather than hardcode the domain name to "ridenet.net", we use the root of the
//  domain the user is visiting from. Mostly this will be "ridenet.net". But this
//  also allows us to pick up visitors from custom root domains such as
//  echeloncycling.com. We will get cross-domain referrals when visitors cross from
//  custom root domains to ridenet.net. But we will not get cross-domain referrals
//  when visitors stay within subdomains of ridenet.net
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function InsertGoogleAnalyticsTracker()
{
?>
  <script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-18436859-1']);
    _gaq.push(['_setDomainName', '.<?=GetDomainRoot()?>']);   // use domain root to treat all subdomains as a single site
    _gaq.push(['_setAllowHash', false]);  // treat all subdomains as part of a single ridenet.net site
    _gaq.push(['_trackPageview']);
    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
  </script>
<?
}


//----------------------------------------------------------------------------------
//  InsertPageFooter()
//
//  This function inserts the page footer onto the page
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function InsertPageFooter()
{
?>
    <div style="height:10px"></div> <!-- Have to create space with divs - padding doesn't work in IE7 -->
    <p>
      Hosted by RideNet (<a href="http://ridenet.net">ridenet.net</a>)
      |
      <a href="http://ridenet.net">Home</a>
      |
      <a href="http://ridenet.net/about.php">About</a>
      |
      <a href="http://ridenet.net/contact.php">Contact</a>
    </p>
    <div style="height:10px"></div> <!-- Have to create space with divs - padding doesn't work in IE7 -->
<?
}


//----------------------------------------------------------------------------------
//  InsertMainMenu()
//
//  This function inserts the main menu onto the page
//
//  PARAMETERS:
//    oDB         - database connection (mysqli object)
//    pt          - ID of team currently being presented to the user.
//    highlight   - menu item to highlight
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function InsertMainMenu($oDB, $pt, $highlight="")
{
    $bRacing = $oDB->DBLookup("bRacing", "teams", "TeamID=$pt", 0);
    $bCommuting = $oDB->DBLookup("bCommuting", "teams", "TeamID=$pt", 0);
?>
    <div id="topmenu">
      <ul id="nav">
        <?if($pt==0) { ?>
        <!-- Default RideNet Menu -->
          <li><a href="/index.php" <?if($highlight=="Home") {?>id="active"<?}?>>HOME</a></li>
          <li><a href="/event-schedule.php" <?if($highlight=="Schedule") {?>id="active"<?}?>>EVENTS</a></li>
          <li><a href="/calendar.php" <?if($highlight=="Calendar") {?>id="active"<?}?>>RIDES</a></li>
          <li><a href="/racing-results.php" <?if($highlight=="Results") {?>id="active"<?}?>>RACING</a></li>
          <li><a href="/commuting.php" <?if($highlight=="Ranking") {?>id="active"<?}?>>COMMUTING</a></li>
        <? } else { ?>
        <!-- Team Site Menu -->
          <li><a href="/home.php" <?if($highlight=="Home") {?>id="active"<?}?>>HOME</a></li>
          <li><a href="/roster.php" <?if($highlight=="Roster") {?>id="active"<?}?>>ROSTER</a></li>
          <li><a href="/event-schedule.php" <?if($highlight=="Schedule") {?>id="active"<?}?>>EVENTS</a></li>
          <li><a href="/calendar.php" <?if($highlight=="Calendar") {?>id="active"<?}?>>RIDES</a></li>
          <? if($bRacing) { ?>
            <li><a href="/racing-results.php" <?if($highlight=="Results") {?>id="active"<?}?>>RACING</a></li>
          <? } ?>
          <? if($bCommuting) { ?>
            <li><a href="/commuting.php" <?if($highlight=="Ranking") {?>id="active"<?}?>>COMMUTING</a></li>
          <? } ?>
        <? } ?>
        <li><a href="profile.php" <?if($highlight=="YourProfile") {?>id="active"<?}?>>YOUR PROFILE</a></li>
        <? if(CheckLogin()) { ?>
          <li style="float:right"><a href="login.php?logoff" id="logoff">Logout</a></li>
        <? } else { ?>
          <li style="float:right"><a href="login.php" id="login">Login</a></li>
        <? } ?>
        <li id="search-box"></li>
      </ul>
    </div>
<?
}


//----------------------------------------------------------------------------------
//  InsertMemberMenu()
//
//  This function inserts the members-only sub-menu onto the page
//
//  PARAMETERS:
//    oDB         - database connection (mysqli object)
//    pt          - ID of team currently being presented to the user.
//    highlight   - menu item to highlight
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function InsertMemberMenu($oDB, $pt, $highlight="")
{
?>
    <div id="submenu">
      <ul id="subnav">
        <li><a href="profile.php" <?if($highlight=="YourProfile") {?>id="active"<?}?>>YOUR PROFILE</a></li>
        <li><a href="update-results.php" <?if($highlight=="UpdateResults") {?>id="active"<?}?>>YOUR RESULTS</a></li>
        <? if(isDesigner() || isSystemAdmin() || isTeamAdmin($oDB, $pt)) { ?>
          <li><a href="team-manager.php" <?if($highlight=="SiteManager") {?>id="active"<?}?>>MANAGE TEAM</a></li>
          <li><a href="commute-report.php" <?if($highlight=="CommuteReport") {?>id="active"<?}?>>COMMUTING REPORT</a></li>
        <? } ?>
        <? if(isSystemAdmin()) { ?>
          <li><a href="syslog.php" <?if($highlight=="SystemLog") {?>id="active"<?}?>>SYSTEM LOG</a></li>
          <li><a href="sysmanager.php" <?if($highlight=="TeamManager") {?>id="active"<?}?>>SYSTEM ADMIN</a></li>
        <? } ?>
      </ul>
    </div>
<?  
}


//----------------------------------------------------------------------------------
//  InsertResultsMenu()
//
//  This function inserts the rankings sub-menu onto the page
//
//  PARAMETERS:
//    year        - calendar year
//    highlight   - menu item to highlight
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function InsertResultsMenu($year, $highlight="")
{ ?>
    <div id="submenu">
      <ul id="subnav">
        <li><a href="racing-results.php?Year=<?=$year?>" <?if($highlight=="Results") {?>id="active"<?}?>>RESULTS SUMMARY</a></li>
        <li><a href="racing-rider-rank.php?Year=<?=$year?>" <?if($highlight=="Ranking") {?>id="active"<?}?>>RIDER RANKINGS</a></li>
      </ul>
    </div>
<?  
}


//----------------------------------------------------------------------------------
//  InsertCommutingMenu()
//
//  This function inserts the rankings sub-menu onto the page
//
//  PARAMETERS:
//    highlight   - menu item to highlight
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function InsertCommutingMenu($highlight="")
{ ?>
    <div id="submenu">
      <ul id="subnav">
        <li><a href="commuting.php" <?if($highlight=="Commuting") {?>id="active"<?}?>>HOME</a></li>
        <li><a href="rider-stats.php" <?if($highlight=="Riders") {?>id="active"<?}?>>RIDERS</a></li>
        <li><a href="team-stats.php" <?if($highlight=="Teams") {?>id="active"<?}?>>TEAMS</a></li>
        <li><a href="rider-groups.php?g=1" <?if($highlight=="1") {?>id="active"<?}?>>&nbsp;<img src='/images/stars/star1.png' width=10>&nbsp;1 to 4</a></li>
        <li><a href="rider-groups.php?g=2" <?if($highlight=="2") {?>id="active"<?}?>>&nbsp;<img src='/images/stars/star2.png' width=10>&nbsp;5 to 9</a></li>
        <li><a href="rider-groups.php?g=3" <?if($highlight=="3") {?>id="active"<?}?>>&nbsp;<img src='/images/stars/star3.png' width=10>&nbsp;10 to 14</a></li>
        <li><a href="rider-groups.php?g=4" <?if($highlight=="4") {?>id="active"<?}?>>&nbsp;<img src='/images/stars/star4.png' width=10>&nbsp;15 to 19</a></li>
        <li><a href="rider-groups.php?g=5" <?if($highlight=="5") {?>id="active"<?}?>>&nbsp;<img src='/images/stars/star5.png' width=10>&nbsp;20+</a></li>
      </ul>
    </div>
<?  
}


//----------------------------------------------------------------------------------
//  BuildTeamBaseURL()
//
//  This function builds the base of a team URL. The URL base depends on where we are
//  are where we are going
//
//    - We are on a custom domain root (i.e. team.com) and the root contains $subdomain.
//      In this case we want to stay on the custom domain.
//    - Domain root does not contain $subdomain. It doesn't matter if we are going to
//      or from a custom domain root. We use team.ridenet.net which is always safe
//
//  PARAMETERS:
//    domain    - domain name of team
//
//  RETURN: href to link to team's base url
//-----------------------------------------------------------------------------------
function BuildTeamBaseURL($subdomain)
{
    $strProtocol = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on") ? "https://" : "http://";
    $root = GetDomainRoot();
    if(strpos($root, $subdomain)!==false)
    {
        // this is a custom domain root and it contains $subdomain, stay on the custom domain root
        return($strProtocol . "www." . $root);
    }
    elseif(strpos($root, ".local"))
    {
        // we are linking to a different sub-domain, go to team.ridenet.local
        return($strProtocol . $subdomain . "." . "ridenet.local");
    }
    else
    {
        // we are linking to a different sub-domain, go to team.ridenet.net
        return($strProtocol . $subdomain . "." . "ridenet.net");
    }
}


//----------------------------------------------------------------------------------
//  SocialMediaButtons()
//
//  This function inserts Tweet and Like buttons into the current page
//
//  PARAMETERS:
//    text    - Text to share. Defaults to the title of the page the button is on
//    url     - URL to share. Defaults to the page the buttons are on
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function SocialMediaButtons($text=false, $url=false)
{
    $url = ($url) ? $url : GetFullURL();
?>
    <div class="tw-wrap">
      <a href="http://twitter.com/share" class="twitter-share-button" data-url="<?=$url?>" <?if($text) {?>data-text="<?=htmlentities($text)?>"<?}?> data-count="none">Tweet</a>
      <script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
    </div>
    <div class="fb-wrap">
      <a name="fb_share" type="button" share_url="<?=$url?>" style="position:relative;top:1px"></a> 
      <script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
<!--      <div id="fb-root"></div>
      <script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>
      <script>
        FB.init({appId: '12345', status: true, cookie: true, xfbml: true});
      </script>
      <fb:like href="<?=$url?>" layout="button_count" width=90></fb:like>-->
    </div>
<?
}


//----------------------------------------------------------------------------------
//  MostViewedRiderSidebar()
//
//  This function inserts the most viewed rider sidebar block into the current page
//
//  PARAMETERS:
//    oDB   - database connection (mysqli object)
//    pt    - ID of team being presented to the user
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function MostViewedRiderSidebar($oDB, $pt=0)
{
    // --- Filter results by team ID if one was passed in
    $teamFilter = ($pt==0) ? "" : "AND (RacingTeamID=$pt OR CommutingTeamID=$pt)";
    // --- Open a recordset containing the rider with the most views in the last 7 days
    $sql="SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderID, RacingTeamID, Domain, Count(*) AS ViewCount
          FROM rider_view_log
          LEFT JOIN rider USING (RiderID)
          LEFT JOIN teams ON (RacingTeamID = TeamID)
          WHERE (DateViewed BETWEEN ADDDATE(NOW(), -7) AND NOW()) AND rider.Archived=0 $teamFilter
          GROUP BY RiderID
          ORDER BY Count(*) DESC
          LIMIT 0,1";
    $rs = $oDB->query($sql, __FILE__, __LINE__);
    if(($record = $rs->fetch_array())==false)
    {
    // --- No riders have been viewed in the last 7 days. Select random rider.
        $rs->free();
        $sql="SELECT CONCAT(FirstName, ' ', LastName) AS RiderName, RiderID, 0 AS ViewCount, RacingTeamID, Domain
              FROM rider LEFT JOIN teams ON (RacingTeamID = TeamID)
              WHERE rider.Archived=0 $teamFilter
              ORDER BY RAND()
              LIMIT 0,1";
        $rs = $oDB->query($sql, __FILE__, __LINE__);
        $record = $rs->fetch_array();
        $rs->free();
    }?>
    <div class="sidebarBlock">
      <h3 align="center">Most-Viewed Rider</h3>
      <div style="height:5px"></div>
      <div align="center">
        <a href="<?=BuildTeamBaseURL($record['Domain'])?>/profile.php?RiderID=<?=$record['RiderID']?>">
          <img src="<?=GetFullDomainRoot()?>/dynamic-images/rider-portrait.php?RiderID=<?=$record['RiderID']?>&T=<?=$record['RacingTeamID']?>" height=100 width=80 border="0">
        </a>
        <div class=profile-photo-caption><?=$record['RiderName'] . " (" . $record['ViewCount'] . ")"?></div>
        <div style="height:8px"></div>
      </div>
    </div>
<?
}


//----------------------------------------------------------------------------------
//  SignupSidebar()
//
//  This function inserts the signup invitation sidebar block into the current page
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function SignupSidebar()
{
?>
    <div class="sidebarBlock">
      <p style="text-align:center">
        <a href="http://ridenet.net" target="_blank"><img border=0 src="/images/ads/ridenetad.png" alt="RideNet" /><br></a>
      </p>
      <p style="text-align:center">
        Free rider profiles and team pages. <a href="http://ridenet.net" target="_blank">Join RideNet Today!</a>
      </p>
    </div>

<?
}


//----------------------------------------------------------------------------------
//  AdSidebar()
//
//  This function inserts the ad sidebar block into the current page
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function AdSidebar()
{
?>
    <div class="sidebarBlock">
      <h3 style="text-align:center">RideNet Clothing</h3>
      <div style="text-align:center">
        <a href = "/clothing.php">
          <img src="/images/clothing/ridenet-jersey1.png" id="ad-clothing" border=0 height=100>
        </a>
        <div style="height:4px"></div>
        <p style="margin:0">
          Order by April 15th
        </p>
      </div>
      <script type="text/javascript">
          new Ext.ToolTip({
              target: 'ad-clothing',
              anchor: 'top',
              anchorOffset: 50,
              dismissDelay: 0,
              showDelay: 200,
              width: 250,
              html: "<b>RideNet Cycling Kits:</b> We're selling cycling clothing to promote RideNet.\
                     A portion of each order will be donated to Consider Biking to support 2BY2012. ",
              padding: 5
            });
      </script>
    </div>
<?
}


//----------------------------------------------------------------------------------
//  ColumbusFoundationSidebar()
//
//  This function inserts the Columbus Foundation sidebar into the current page
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function ColumbusFoundationSidebar()
{
?>
    <div class="sidebarBlock">
      <h3 style="text-align:center">Special Thanks To</h3>
      <div style="height:5px"></div>
      <p align="center" id='cfad'>
        <a href="http://www.columbusfoundation.org"><img border=0 src="/images/ads/ColumbusFoundation.png" alt="The Columbus Foundation" /></a>
      </p>
      <script type="text/javascript">
          new Ext.ToolTip({
              target: 'cfad',
              anchor: 'top',
              anchorOffset: 50,
              dismissDelay: 0,
              showDelay: 200,
              width: 250,
              html: '2 BY 2012 is made possible by the Robert Bartels, William C. and Naoma W. Denison, Charlotte R. Hallel, \
                     Robert B. Hurst and Martha G. Staub funds of The Columbus Foundation',
              padding: 5
            });
      </script>
    </div>
<?
}


//----------------------------------------------------------------------------------
//  SponsorSidebar()
//
//  This function inserts the sponsor sidebar block into the current page
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function SponsorSidebar()
{
?>
    <div class="sidebarBlock">
      <h3 align="center">Team Sponsors</h3>
      <div style="height:5px"></div>
      <a href="http://trekstorecolumbus.com/">
        <p align="center"><img border=0 src="/images/ads/TrekStoreColumbus.png" alt="Trek Store" /></p>
      </a>
      <a href="http://www.trekbikes.com/us/en/">
        <p align="center"><img border=0 src="/images/ads/TrekLogo2.png" alt="Trek Bikes" /></p>
      </a>
    </div>
<?
}
?>