<?
//$cRedirectEmailsTo="will@ridenet.net";

//----------------------------------------------------------------------------------
//   oOpenDBConnection()
//
//   This function is used at the beginning of each page to open the database
//   connection.
//
//  PARAMETERS: none
//
//  RETURN: The database connection object. Page script will halt with error
//          if the database connection fails to open. This connection will
//          automatically be closed when the script that created it ends
//-----------------------------------------------------------------------------------
function oOpenDBConnection()
{
    return(new DBConnection("localhost", "echelonuser", "blairsteam7", "echelon"));
}


//----------------------------------------------------------------------------------
//  CheckAndReportSQLError()
//
//  This function checks to see if the last MySQL operation generated an error. If
//  so, this function prints an error message and abort page generation.
//
//  This function is used in "old style" / non AXAJ post handlers. It can be phased
//  out as these pages are redesigned using ExtJS.
//
//  PARAMETERS:
//    oDB   - the database connection object
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function CheckAndReportSQLError($oDB)
{
    if($oDB->errno)
    {
        exit("Problem running query. If you were submitting data, it may not have been saved<br><br>
              Please report this error: SQL Error [" . $oDB->errno . "]");
    }
}


//----------------------------------------------------------------------------------
//  IsMapVisible()
//
//  Determines weather a map should be visible for the current site visitor
//
//  PARAMETERS:
//    mapPrivacy        - Map user's privacy selection.
//
//  RETURN: ride log comment with link appended
//-----------------------------------------------------------------------------------
function IsMapVisible($mapPrivacy)
{
    $mapVisible = false;
    if($mapPrivacy==0 && !is_null($mapPrivacy))  // publicly visible
    {
        $mapVisible=true;
    }
    elseif($mapPrivacy==1 && CheckLogin())  // visible to any RideNet user
    {
        $mapVisible=true;
    }
    return($mapVisible);
}


//----------------------------------------------------------------------------------
//  BuildRideLogComment()
//
//  Builds the text for a ride log comment, inserting a link at the end of the
//  comment if applicable. We attempt to give the link a meaningful name based
//  on the URL.
//
//  PARAMETERS:
//    comment     - ride log comment
//    link        - link URL associated with the ride log
//    mapid       - id of map to display (zero indicates there isn't a map)
//    mapVisible  - true if map is visible to current user
//
//  RETURN: ride log comment with link appended
//-----------------------------------------------------------------------------------
function BuildRideLogComment($comment, $link, $mapid, $mapVisible)
{
    $result = $comment;
    if(preg_match('/garmin/i', $link))
    {
        $result .= " <a href=\"$link\" target=\"_blank\" title=\"$link\">[Garmin]</a>";
    }
    elseif(preg_match('/trainingpeaks/i', $link))
    {
        $result .= " <a href=\"$link\" target=\"_blank\" title=\"$link\">[TrainingPeaks]</a>";
    }
    elseif(preg_match('/trimbleoutdoors/i', $link))
    {
        $result .= " <a href=\"$link\" target=\"_blank\" title=\"$link\">[GPS]</a>";
    }
    elseif(preg_match('/mapmyride|bikely|gmap-pedometer|bikeroutetoaster|ridewithgps/i', $link))
    {
        $result .= " <a href=\"$link\" target=\"_blank\" title=\"$link\">[Map]</a>";
    }
    elseif(preg_match('/runkeeper/i', $link))
    {
        $result .= " <a href=\"$link\" target=\"_blank\" title=\"$link\">[RunKeeper]</a>";
    }
    elseif(preg_match('/youtube/i', $link))
    {
        $result .= " <a href=\"$link\" target=\"_blank\" title=\"$link\">[Video]</a>";
    }
    elseif(preg_match('/.jpg|.jpeg|.png|.gif/i', $link))
    {
        $result .= " <a href=\"$link\" target=\"_blank\" title=\"$link\">[Photo]</a>";
    }
    elseif(preg_match('/strava/i', $link))
    {
        $result .= " <a href=\"$link\" target=\"_blank\" title=\"$link\">[Strava]</a>";
    }
    elseif($link!="")
    {
        $result .= " <a href=\"$link\" target=\"_blank\">[More]</a>";
    }
    if($mapid)
    {
        if($mapVisible)
        {
            $result .= " <a onClick=\"window.open('/map/$mapid', 'map_window', 'resizable')\">[Map]</a>";
        }
        else
        {
            $result .= " <span class=text50>[Map]</span>";
        }
    }
    return($result);
}


//----------------------------------------------------------------------------------
//  SandboxHomePage()
//
//  Generates contents of the sample home page
//
//  PARAMETERS:
//    oDB   - the database connection object
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function SandboxHomePage($oDB)
{ ?>
  <h1>Welcome to RideNet</h1>
  <p>
    You are currently in the RideNet Sandbox, a holding place for riders that are not yet a member of a RideNet team.
    Although you're welcome to stay in the Sandbox as long as you like, one of RideNet's coolest features is the ability
    to create and join teams. Below are some of the more popular RideNet teams in your area.
  </p>
  <div style="height:10px;clear:both"></div>
<?$sql = "SELECT TeamID, Domain, TeamName, TeamType, CONCAT(City, ', ', State, ' ', ZipCode) AS Location
          FROM teams
          JOIN ref_team_type USING (TeamTypeID)
          JOIN ref_zipcodes USING (ZipCodeID)
          WHERE TeamID IN (120, 122, 86, 115, 135, 130)
          ORDER BY TeamName ASC";
  $rs = $oDB->query($sql); ?>
  <table cellpadding=0 cellspacing=0 class="centered" style="border:1px solid #CCC;background-color:#EEE">
  <? while(($record = $rs->fetch_array())!=false) { ?>
    <tr>
      <td style="border-bottom:1px solid #CCC">
        <div style="width:100px;overflow:hidden;text-align:center;margin:1px">
          <a href="<?=BuildTeamBaseURL($record['Domain'])?>/" target="_blank">
            <img class="tight" src="<?=getFullDomainRoot()?>/imgstore/team-logo/fit/<?=$record['TeamID']?>.png" border=0>
          </a>
        </div>
      </td>
      <td style="border-bottom:1px solid #CCC">
        <div class="ellipses" style="padding:5px 5px;width:155px">
          <a href="<?=BuildTeamBaseURL($record['Domain'])?>/" target="_blank">
            <div class="find-name"><?=$record['TeamName']?></div>
          </a>
          <div class="find-info"><?=$record['TeamType']?></div>
          <div class="find-info2"><?=$record['Location']?></div>
        </div>
      </td>
      <td style="border-bottom:1px solid #CCC;text-align:center" valign=bottom width=160>
        <?if(CheckLogin()) { ?>
          <span class='action-btn' id='join-btn<?=$record['TeamID']?>' onclick="ChangeTeams(<?=$record['TeamID']?>, '<?=htmlentities(addslashes($record['TeamName']))?>', '<?=htmlentities(addslashes($record['Domain']))?>')">&nbsp;&nbsp;Join Team&nbsp;&nbsp;</span>
        <? } else { ?>
          <span class='action-btn' onclick="window.location.href='/login?Goto=<?=urlencode("/")?>'">&nbsp;&nbsp;Login to Join&nbsp;&nbsp;</span>
        <? } ?>
        <div style="margin:5px 0 5px 0" class="find-info2"><a href="<?=BuildTeamBaseURL($record['Domain'])?>/" target="_blank" style="color:#BBB">Learn more...</a></div>
      </td>
    </tr>
  <? } ?>
    <tr height=60>
      <td style="padding:10px 40px;" colspan=2>
        <p style="margin:0">
          Search for other teams on RideNet
        </p>
      </td>
      <td style="text-align:center">
        <?if(CheckLogin()) { ?>
          <span class="action-btn" id='ct-btn' onClick="g_changeTeamsDialog.show({ redirectToHome: true, animateTarget: 'ct-btn' })">&nbsp;&nbsp;Find a team...&nbsp;&nbsp;</span>
        <? } else { ?>
          <span class='action-btn' onclick="window.location.href='/login?Goto=<?=urlencode("/")?>'">&nbsp;&nbsp;Login to Find Teams&nbsp;&nbsp;</span>
        <? } ?>
      </td>
    </tr>
  </table>
  <div style="height:40px"></div>
  <div style="float:right;margin: 0px 0px 0px 20px">
    <img src="images/sandbox-profile.jpg" style="width:280px"><br>
  </div>
  <h1 style="margin-top:0px">Change Teams Any Time</h1>
  <p>
    You can change teams at any time. From Your Profile page, click "Edit Profile...", then click
    "Change Teams...".
  </p>
  <div style="clear:both;height:25px"></div>
  <div style="float:left;margin: 5px 20px 0px 0px;border:1px solid #DDD">
    <img src="images/sandbox-roster.jpg" style="width:200px"><br>
  </div>
  <h1 style="padding-top:0px">Start Something New</h1>
  <p>
     If you can't find a team on RideNet, why not create one? A RideNet team can be based
     around a real-world cycling club or could be a loosely affiliated group of riders that just want to stay
     connected. As a member of a team, you can see what everyone on your team is up to, track your team's race
     results (if you race), and organize rides together.<br><br>
     Creating a RideNet team is easy. Send us an email at <a href="mailto:info@ridenet.net">info@ridenet.net</a>
     and we'll take care of the rest.
  </p>
  <div style="clear:both;height:10px"></div>
<!--  <h1>Send Us Feedback</h1>
  <p>
    We are continually working to improve RideNet and add new features. If you have any comments, questions
    or suggestions (or just want to tell us how much you love the site) send us an email:
    <a href="mailto:info@ridenet.net">info@ridenet.net</a>
  </p>-->
<?  
}


//----------------------------------------------------------------------------------
//  SampleHomePageText()
//
//  Generates sample text for teams that have not customized their Home Page yet
//
//  PARAMETERS:
//    teamTypeID - Team Type
//
//  RETURN: sample homepage text
//-----------------------------------------------------------------------------------
function SampleHomePageText($teamTypeID)
{
    // home page text depends on team type (2 BY 2012, Racing, etc)
    // Hardcode for now
    if($teamTypeID==2)
    {
        // 2 BY 2012 team
        $text = "The goal of 2 BY 2012 is for each citizen of central Ohio to bicycle " .
                "to work or school, or run an errand by bike, two days a month by the " .
                "Columbus bicentennial in 2012.<br>\n" .
                "<br>\n" .
                "2 BY 2012 is both a challenge and a movement. As citizens of Columbus " .
                "rise to the challenge and change the way we get to work, we can start " .
                "a movement that will significantly benefit our lives, our economy and " .
                "our community. <b>Join the movement today</b> by going to 'Your Profile', " .
                "updating your rider bio and logging your first ride! Then get on your bike " .
                "and spread the word. When we ride, we make a difference.";
    }
    else
    {
        // RideNet default
        $text = "<b>Welcome to RideNet!</b> Get started by going to 'Your Profile' and " .
                "updating your rider bio. While you're there you can log rides and " .
                "post race results.<br>\n" .
                "<br>\n" .
                "Be sure to check out the Events page to find tours and races in your region. " .
                "Check out the Rides page to find out where other cyclists are riding in your area.<br>\n" .
                "<br>\n" .
                "Brand your RideNet site with custom colors are graphics. Upload your own photo " .
                "for the home page and use this space to tell the world something interesting " .
                "about your team. What's your mission? Why do you ride?";
    }
    return($text);
}


//----------------------------------------------------------------------------------
//  SampleHomePageTitle()
//
//  Generates sample page title for teams that have not customized their Home Page yet
//
//  PARAMETERS:
//    teamTypeID - Team Type
//    teamName  - Name of the team
//
//  RETURN: sample homepage title
//-----------------------------------------------------------------------------------
function SampleHomePageTitle($teamTypeID, $teamName)
{
    // home page text depends on team type (2 BY 2012, Racing, etc)
    // Hardcode for now
    if($teamTypeID==2)
    {
        // 2 BY 20120
        $title = "$teamName is a partner of 2 BY 2012";
    }
    else
    {
        // RideNet
        $title = "$teamName is on RideNet";
    }
    return($title);
}


//----------------------------------------------------------------------------------
//  SampleHomePageHTML()
//
//  Generates sample HTML for teams that have not customized their Home Page yet
//
//  PARAMETERS:
//    teamName  - Name of the team
//
//  RETURN: sample homepage HTML
//-----------------------------------------------------------------------------------
function SampleHomePageHTML($teamName)
{
    $hp = "<h1>$teamName</h1>\n" .
          "<p>\n" .
          "  Welcome to your team site. This is one possible layout for your home page. Just replace our sample\n" .
          "  content with your text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris et mauris lectus.\n" .
          "  Ut gravida, felis in commodo consectetur, quam augue tincidunt nulla, nec pellentesque orci ligula non\n" .
          "  lacus. Quisque non odio rutrum ligula auctor facilisis.\n" .
          "</p>\n" .
          "<h2>Riding for the Future</h2>\n" .
          "  <div style=\"float:left;margin: 3px 10px 0px 0px\">\n" .
          "    <img src=\"/images/hosted/SharingTheRoad.jpg\"><br>\n" .
          "    <p class=photo-caption>Enough Room for Everyone</p>\n" .
          "  </div>\n" .
          "<p>\n" .
          "  Aliquam erat volutpat. Nunc at risus ut sapien auctor laoreet. Aliquam ac eros augue. Fusce posuere nisl\n" .
          "  vitae turpis aliquam pharetra luctus lectus auctor. Phasellus euismod rhoncus lacus, quis suscipit velit\n" .
          "  eleifend id. Nam tincidunt scelerisque libero. Maecenas mi eros, porttitor at commodo eu, tincidunt et\n" .
          "  massa. Sed nec tellus eu dolor gravida consequat. Integer ante sapien, elementum in auctor a, condimentum\n" .
          "  id elit. Integer at rutrum orci. Nullam lobortis tempus nisi, id ultrices nulla porttitor sed. Nunc sit\n" .
          "  eleifend id. Nam tincidunt scelerisque libero. Maecenas mi eros, porttitor at commodo eu, tincidunt et\n" .
          "  amet commodo nibh. Maecenas tincidunt quam quis arcu iaculis vel laoreet libero fringilla.  iaculis vel\n" .
          "  vitae turpis aliquam pharetra luctus lectus auctor. Phasellus euismod rhoncus lacus, quis suscipit velit\n" .
          "  laoreet libero fringilla." .
          "<br class='clearfloat'>\n" .
          "</p>\n" .
          "<h2>Roots in the Past</h2>\n" .
          "<p>\n" .
          "  Mauris eget turpis ac tortor dignissim suscipit. Duis quis mi id ante blandit elementum. Nullam turpis tortor,\n" .
          "  rhoncus vehicula consequat at, molestie nec felis. Pellentesque sit amet sem sit amet tellus venenatis pulvinar.\n" .
          "  Sed aliquam semper eros, vel condimentum neque egestas nec. Nam vel sagittis diam. Phasellus aliquam, lacus sit\n" .
          "  amet porta vulputate, neque est bibendum lectus, at varius tortor velit sed nisi. Praesent ultrices luctus ornare.\n" .
          "  Sed tortor mauris, placerat quis gravida nec, bibendum quis nulla. Fusce purus massa, malesuada eu hendrerit\n".
          "  ultrices, rhoncus eu leo. Pellentesque ac metus in tellus auctor porta. Suspendisse potenti. Sed at arcu vel erat\n" .
          "  eleifend ultricies quis nec mi.\n" .
          "</p>\n";
    return($hp);
}


//----------------------------------------------------------------------------------
//  UpdateRiderStats()
//
//  Calculate stats for a rider and store the stats in the rider table. The rider
//  stats are complicated enough that we don't want to calculate them each time
//  we need them. This also keeps the rider stats consistent across all the pages
//  that use them.
//
//  This function fails silently but will put errors in the server error log
//
//  PARAMETERS:
//    oDB   - the database connection object
//    $riderID    - ID of rider to update stats for
//
//  RETURN: Array with new rider stats
//-----------------------------------------------------------------------------------
function UpdateRiderStats($oDB, $riderID)
{
    // Get Days/Month over several date ranges and pick the biggest value. For the purposes of this
    // calculation a month is 30.5 days
    $startDate365 = AddDays(date_create(), -364)->format("Y-m-d");
    $startDate180 = AddDays(date_create(), -179)->format("Y-m-d");
    $startDate60 = AddDays(date_create(), -59)->format("Y-m-d");
    $startDate30 = AddDays(date_create(), -29)->format("Y-m-d");
    $endDate = date_create()->format("Y-m-d");
    $sql = "SELECT SUM(IF(RideLogTypeID=1, Distance, 0)) AS CMiles365,
                   COUNT(DISTINCT IF(RideLogTypeID=1, Date, NULL)) AS CDays365,
                   COUNT(DISTINCT IF(RideLogTypeID=1 OR RideLogTypeID=3, Date, NULL)) AS CEDays365,
                   COUNT(DISTINCT IF(Date BETWEEN '$startDate180' AND '$endDate' AND (RideLogTypeID=1 OR RideLogTypeID=3), Date, NULL)) AS CEDays180,
                   COUNT(DISTINCT IF(Date BETWEEN '$startDate60' AND '$endDate' AND (RideLogTypeID=1 OR RideLogTypeID=3), Date, NULL)) AS CEDays60,
                   COUNT(DISTINCT IF(Date BETWEEN '$startDate30' AND '$endDate' AND (RideLogTypeID=1 OR RideLogTypeID=3), Date, NULL)) AS CEDays30
            FROM ride_log
            WHERE (Date BETWEEN '$startDate365' AND '$endDate') AND RiderID=$riderID";
    $rs = $oDB->query($sql);
    $record = $rs->fetch_array();
    $rs->close();
    $CEDaysMonth365 = round($record['CEDays365']/(365/30.5));
    $CEDaysMonth180 = round($record['CEDays180']/(180/30.5));
    $CEDaysMonth60 = round($record['CEDays60']/(60/30.5));
    $CEDaysMonth30 = round($record['CEDays30']/(30/30.5));
    $CEDaysMonth = max($CEDaysMonth365, $CEDaysMonth180, $CEDaysMonth60, $CEDaysMonth30);
    $CMilesDay = ($record['CDays365']) ? round($record['CMiles365']/$record['CDays365']) : 0;
    // Calculate rider stats over several different date ranges
    $today = new DateTime();
    $prevYear = intval($today->format("Y")) - 1;
    $sets['A'] = CalculateStatsSet($oDB, $riderID, new DateTime("2000-1-1"), $today);
    $sets['Y0'] = CalculateStatsSet($oDB, $riderID, FirstOfYear($today), $today);
    $sets['Y1'] = CalculateStatsSet($oDB, $riderID, new DateTime("1/1/$prevYear"), new DateTime("12/31/$prevYear"));
    $sets['M0'] = CalculateStatsSet($oDB, $riderID, FirstOfMonth($today), $today);
    $sets['M1'] = CalculateStatsSet($oDB, $riderID, FirstOfMonth(AddMonths($today,-1)), LastOfMonth(AddMonths($today,-1)));
    $sets['M2'] = CalculateStatsSet($oDB, $riderID, FirstOfMonth(AddMonths($today,-2)), LastOfMonth(AddMonths($today,-2)));
    // create rider stats record if it doesn't exist yet
    if($oDB->DBCount("rider_stats", "RiderID=$riderID")==0)
    {
        $oDB->query("INSERT INTO rider_stats (RiderID) VALUES($riderID)");
    }
    // Store stats in rider stats table.
    $sql = "UPDATE rider_stats SET CEDaysMonth = $CEDaysMonth, CMilesDay = $CMilesDay, ";
    foreach($sets as $range => $set)
    {
        foreach($set as $key => $value)
        {
            $sql .= "{$range}_$key = $value, ";
        }
    }
    $sql = substr($sql, 0, -2) . " WHERE RiderID=$riderID";
    $oDB->query($sql);
    // return a subset of the stats
    return(Array('YTDDays' => $sets['Y0']['Days'], 'YTDMiles' => $sets['Y0']['Miles'], 'CEDaysMonth' => $CEDaysMonth));
}


//----------------------------------------------------------------------------------
//  CalculateStatsSet()
//
//  Calculate set of rider stats over the given time range
//
//  PARAMETERS:
//    oDB         - the database connection object
//    $riderID    - ID of rider to calculate stats for
//    $startDate  - start of date range to calculate stats over
//    $endDate    - end of date range to calculate stats over
//
//  RETURN: Array with stats set
//-----------------------------------------------------------------------------------
function CalculateStatsSet($oDB, $riderID, $startDate, $endDate)
{
    $sql = "SELECT IFNULL(SUM(Distance),0) AS Miles,
                   IFNULL(SUM(IF(RideLogTypeID=1 OR RideLogTypeID=3, Distance, 0)),0) AS CEMiles,
                   COUNT(DISTINCT Date) AS Days,
                   COUNT(DISTINCT IF(RideLogTypeID=1 OR RideLogTypeID=3, Date, NULL)) AS CEDays,
                   COUNT(DISTINCT IF(RideLogTypeID=1 OR RideLogTypeID=3, RideLogID, NULL)) AS CERides
            FROM ride_log
            WHERE RiderID=$riderID AND Date BETWEEN '" . $startDate->format('Y-m-d') . "' AND '" . $endDate->format('Y-m-d') . "'
            GROUP BY RiderID";
    $rs = $oDB->query($sql);
    if($rs->num_rows)
    {
        $record = $rs->fetch_array(MYSQLI_ASSOC);
    }
    else
    {
        $record = array('Miles'=>0, 'CEMiles'=>0, 'Days'=>0, 'CEDays'=>0, 'CERides'=>0);
    }
    $rs->close();
    return($record);
}


//----------------------------------------------------------------------------------
//  GetRiderTeamInfo()
//
//  This function gets the rider's team IDs and team security rights from the database
//  and returns it as an associative array
//
//  PARAMETERS:
//    oDB   - the database connection object
//    riderID   - rider ID
//
//  RETURN: Array containing rider's commuting and racing team ID. Returns false
//          If rider record is not found.
//-----------------------------------------------------------------------------------
function GetRiderTeamInfo($oDB, $riderID)
{
    $rs = $oDB->query("SELECT CommutingTeamID, RacingTeamID, sCommutingTeamAdmin, sRacingTeamAdmin
                       FROM rider
                       WHERE RiderID=$riderID");
    $record = $rs->fetch_array(MYSQLI_ASSOC);
    $rs->free();
    return($record);
}


//----------------------------------------------------------------------------------
//  ChangeRiderTeam()
//
//  This function changes a rider's racing and commuting teams. The rider will not
//  have admin rights on the new team even if they had admin rights on the old team.
//  If the rider changes racing teams, their photo is copied to the new team
//
//  PARAMETERS:
//    oDB                 - the database connection object
//    riderID             - rider ID
//    newRacingTeamID     - New ID of rider's racing team
//    newCommutingTeamID  - New ID of rider's commuting team
//
//  RETURN: Result array containing information to encode into response
//-----------------------------------------------------------------------------------
function ChangeRiderTeam($oDB, $riderID, $newRacingTeamID, $newCommutingTeamID)
{
    $oldTeamInfo = GetRiderTeamInfo($oDB, $riderID);
    // build SQL to set new rider teams
    $updateCmds = "SET CommutingTeamID=$newCommutingTeamID, RacingTeamID=$newRacingTeamID";
    // remove admin rights when rider changes teams
    if($newRacingTeamID!=$oldTeamInfo['RacingTeamID'])
    {
        $updateCmds .= ", sRacingTeamAdmin=0";
    }
    if($newCommutingTeamID!=$oldTeamInfo['CommutingTeamID'])
    {
        $updateCmds .= ", sCommutingTeamAdmin=0";
    }
    // execute the commands to change teams
    $oDB->query("UPDATE rider $updateCmds WHERE RiderID=$riderID");
    if($oDB->errno==0)
    {
        // Copy rider photo to new team if rider does not already have a photo for that team
        $existingPhoto = $oDB->DBCount("rider_photos", "RiderID=$riderID AND TeamID=$newRacingTeamID");
        if($newRacingTeamID!=$oldTeamInfo['RacingTeamID'] && $existingPhoto==0)
        {
            $oDB->query("INSERT rider_photos (RiderID, TeamID, Picture, ActionPicture, LastModified)
                         SELECT $riderID, $newRacingTeamID, Picture, ActionPicture, LastModified
                         FROM rider_photos
                         WHERE RiderID=$riderID AND TeamID={$oldTeamInfo['RacingTeamID']}");
        }
    }
    // Build response array
    if($oDB->errno!=0)
    {
        $result['success'] = false;
        $result['message'] = "[" . $oDB->errno . "] SQL Error";// . $oDB->error;
    // --- needed so Ext returns failureType of 'server' (FYI: could also be used to do server-side field validation)
        $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );
    }
    else
    {
        $result['success'] = true;
    }
    return($result);
}


//----------------------------------------------------------------------------------
//  BuildRideClass()
//
//  Builds text for the ride classes given a ride calendar record
//
//  PARAMETERS:
//    r   - a ride calendar record
//
//  RETURN: ride class string ("-AB--")
//-----------------------------------------------------------------------------------
function BuildRideClass($record)
{
    $class = ($record['ClassX']) ? "X" : "-";
    $class .= ($record['ClassA']) ? "A" : "-";
    $class .= ($record['ClassB']) ? "B" : "-";
    $class .= ($record['ClassC']) ? "C" : "-";
    $class .= ($record['ClassD']) ? "D" : "-";
    return($class);
}


//----------------------------------------------------------------------------------
//  InsertRideClassKey()
//
//  Outputs ride class key onto the page
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function InsertRideClassKey()
{?>
    <a name="class-key"></a>
    <div id="ride-class-key" style="margin:0 auto;width:100%">
      <div class=header>* Ride Classes</div>
      <div class=details>
        <b>X: 23-28mph+</b> All-out, competitive style riding.&nbsp;&nbsp;&nbsp;
        <b>A: 19-23mph</b> Fast pace with limited regrouping.&nbsp;&nbsp;&nbsp;
        <b>B: 16-19mph</b> Less competitive. Brisk recreational ride.&nbsp;&nbsp;&nbsp;
        <b>C: 13-16mph</b> Recreational. Consistent, but social pace.&nbsp;&nbsp;&nbsp;
        <b>D: 10-13mph</b> Social pace. May include meal stop or sightseeing.
      </div>
    </div>
<?
}


//----------------------------------------------------------------------------------
//  InsertOrUpdateRecord()
//
//  This function either inserts a record into a table or updates an existing
//  record in the table. If $whereClause = "" then a new record is created, otherwise
//  a record matching $whereClause is updated. The $values array should contain an
//  associative array with the fields names and values to insert / update in the
//  table. The array keys are the field names and the array values are the field
//  values.
//
//  PARAMETERS:
//    $oDB          - database connection
//    $tableName    - the table name
//    $whereClause  - Where clause if updating item or "" to add a new item
//    $values       - associative array defining columns/values to insert/update
//                    in table.
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function InsertOrUpdateRecord($oDB, $tableName, $whereClause, $values)
{
    if ($whereClause == "")
    {
    // --- Record doesn't exist yet. Build INSERT query to create new record 
        $sql="INSERT INTO $tableName (";
        foreach($values as $colname => $value)
        {
            $sql .= "$colname,";
        }
        $sql = substr($sql, 0, -1);   // trim last "," from string
        $sql .= ") VALUES(";
        foreach($values as $colname => $value)
        {
            $sql .= $value . ",";
        }
        $sql = substr($sql, 0, -1);   // trim last "," from string
        $sql .= ")";
    }
    else
    {
    // --- Record already exists. Build update query to update existing record
        $sql = "UPDATE $tableName SET ";
        foreach($values as $colname => $value)
        {
            $sql .= "$colname = $value, ";
        }
        $sql = substr($sql, 0, -2);   // trim last ", " from string
        $sql .= " WHERE " . $whereClause;
    }
// --- execute query
//    exit($sql);
    $oDB->query($sql);
    CheckAndReportSQLError($oDB);
}


//----------------------------------------------------------------------------------
//  InsertOrUpdateRecord2()
//
//  This function either inserts a record into a table or updates an existing
//  record in the table. If $itemID = -1 then a new record is created, otherwise
//  a record matching ItemID = $itemID is updated. The $values array should contain
//  an associative array with the fields names and values to insert / update in the
//  table. The array keys are the field names and the array values are the field
//  values.
//
//  PARAMETERS:
//    $oDB          - an ApplicationData object
//    $tableName    - the table name
//    $idFieldName  - name of id field in the table
//    $itemID       - ID if item to update or -1 to add a new item
//    $values       - associative array defining columns/values to insert/update
//                    in table. This array must contain a 'PlantID' item
//
//  RETURN: Result array containing information to encode into response
//-----------------------------------------------------------------------------------
function InsertOrUpdateRecord2($oDB, $tableName, $idFieldName, $itemID, $values)
{
    if ($itemID == -1)
    {
    // --- Record doesn't exist yet. Build INSERT query to create new record 
        $sql="INSERT INTO $tableName (";
        foreach($values as $colname => $value)
        {
            $sql .= $colname . ",";
        }
        $sql = substr($sql, 0, -1);   // trim last "," from string
        $sql .= ") VALUES(";
        foreach($values as $colname => $value)
        {
            $sql .= $value . ",";
        }
        $sql = substr($sql, 0, -1);   // trim last "," from string
        $sql .= ")";
    // --- execute query
        $oDB->query($sql);
    // --- store ID of new item in result
        $result[$idFieldName] = $oDB->insert_id;
    // --- record activity
        $oDB->RecordActivityIfOK("Add [$tableName]", $result[$idFieldName]);
    }
    else
    {
    // --- Record already exists. Build update query to update existing record
        $sql = "UPDATE $tableName SET ";
        foreach($values as $colname => $value)
        {
            $sql .= "$colname = $value, ";
        }
        $sql = substr($sql, 0, -2);   // trim last ", " from string
        $sql .= " WHERE $idFieldName=$itemID";
    // --- execute query
        $oDB->query($sql);
    // --- store ID of existing item in result
        $result[$idFieldName] = $itemID;
    // --- record activity
        $oDB->RecordActivityIfOK("Update [$tableName]", $result[$idFieldName]);
    }
// --- check result of SQL operation
    if($oDB->errno!=0)
    {
        $result['success'] = false;
        $result['message'] = "[" . $oDB->errno . "] SQL Error";// . $oDB->error;
    // --- needed so Ext returns failureType of 'server' (FYI: could also be used to do server-side field validation)
        $result['errors'][] = array('id' => 'CtrlID', 'msg' => 'Error Msg' );
    }
    else
    {
        $result['success'] = true;
    }
    return($result);
}

?>