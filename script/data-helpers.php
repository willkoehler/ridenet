<?

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
//    file  - the file where the error occurred (use __FILE__)
//    line  - the line # where the error occurred (use __LINE__)
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function CheckAndReportSQLError($oDB, $filename, $line)     //!!!! test this function
{
    if($oDB->errno)
    {
        exit("Problem running query. If you were submitting data, it may not have been saved<br><br>
              Please report this error: SQL Error [" . $oDB->errno . "] Line $line of " . basename($filename) . "<br><br>");
    }
}


//----------------------------------------------------------------------------------
//  BuildRideLogComment()
//
//  Builds the text for a ride log comment, inserting a link at the end of the
//  comment if applicable. We attempt to give the link a meaningful name based
//  on the URL.
//
//  PARAMETERS:
//    comment   - ride log comment
//    link      - link URL associated with the ride log
//
//  RETURN: ride log comment with link appended
//-----------------------------------------------------------------------------------
function BuildRideLogComment($comment, $link)
{
    if(is_null($link))
    {
        $result = $comment;
    }
    elseif(preg_match('/garmin/i', $link))
    {
        $result = $comment . " <a href=\"$link\" target=\"_blank\" title=\"$link\">[Garmin]</a>";
    }
    elseif(preg_match('/trainingpeaks/i', $link))
    {
        $result = $comment . " <a href=\"$link\" target=\"_blank\" title=\"$link\">[Power]</a>";
    }
    elseif(preg_match('/mapmyride|bikely/i', $link))
    {
        $result = $comment . " <a href=\"$link\" target=\"_blank\" title=\"$link\">[Map]</a>";
    }
    else
    {
        $result = $comment . " <a href=\"$link\" target=\"_blank\">$link</a>";
    }
    return($result);
}


//----------------------------------------------------------------------------------
//  SampleHomePageText()
//
//  Generates sample text for teams that have not customized their Home Page yet
//
//  PARAMETERS:
//    orgnizationID - Team's Organization ID
//
//  RETURN: sample homepage text
//-----------------------------------------------------------------------------------
function SampleHomePageText($organizationID)
{
    // home page text depends on Organization (Consider Biking, RideNet, etc)
    // Hardcode for now
    if($organizationID==2)
    {
        // 2 BY 20120
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
        // RideNet
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
//    orgnizationID - Team's Organization ID
//    teamName  - Name of the team
//
//  RETURN: sample homepage title
//-----------------------------------------------------------------------------------
function SampleHomePageTitle($organizationID, $teamName)
{
    // home page title depends on Organization (Consider Biking, RideNet, etc)
    // Hardcode for now
    if($organizationID==2)
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
          "    <img src=\"images/hosted/SharingTheRoad.jpg\"><br>\n" .
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
    // Get Days/Month over several time ranges and pick the biggest value. For the purposes of this
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
    $rs = $oDB->query($sql, __FILE__, __LINE__);
    $record = $rs->fetch_array();
    $CEDaysMonth365 = round($record['CEDays365']/(365/30.5));
    $CEDaysMonth180 = round($record['CEDays180']/(180/30.5));
    $CEDaysMonth60 = round($record['CEDays60']/(60/30.5));
    $CEDaysMonth30 = round($record['CEDays30']/(30/30.5));
    $CEDaysMonth = max($CEDaysMonth365, $CEDaysMonth180, $CEDaysMonth60, $CEDaysMonth30);
    $CMilesDay = ($record['CDays365']) ? round($record['CMiles365']/$record['CDays365']) : 0;
    $YTDMiles = intval($oDB->DBLookup("IFNULL(SUM(Distance),0)", "ride_log", "RiderID=$riderID AND Year(Date) = Year(NOW())"));
    // Store stats in rider record
    $sql = "UPDATE rider
            SET CEDaysMonth=$CEDaysMonth, CMilesDay=$CMilesDay, YTDMiles=$YTDMiles
            WHERE RiderID=$riderID";
    $oDB->query($sql, __FILE__, __LINE__);
    return(Array('YTDMiles' => $YTDMiles, 'CEDaysMonth' => $CEDaysMonth, 'CMilesDay' => $CMilesDay));
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
//  RETURN: Array containing rider's commuting and racing team ID
//-----------------------------------------------------------------------------------
function GetRiderTeamInfo($oDB, $riderID)
{
    $rs = $oDB->query("SELECT CommutingTeamID, RacingTeamID, sCommutingTeamAdmin, sRacingTeamAdmin
                       FROM rider
                       WHERE RiderID=$riderID", __FILE__, __LINE__);
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
    $oDB->query("UPDATE rider $updateCmds WHERE RiderID=$riderID", __FILE__, __LINE__);
    if($oDB->errno==0)
    {
        // Copy rider photo to new team if rider does not already have a photo for that team
        $existingPhoto = $oDB->DBCount("rider_photos", "RiderID=$riderID AND TeamID=$newRacingTeamID");
        if($newRacingTeamID!=$oldTeamInfo['RacingTeamID'] && $existingPhoto==0)
        {
            $oDB->query("INSERT rider_photos (RiderID, TeamID, Picture, ActionPicture)
                         SELECT $riderID, $newRacingTeamID, Picture, ActionPicture
                         FROM rider_photos
                         WHERE RiderID=$riderID AND TeamID={$oldTeamInfo['RacingTeamID']}", __FILE__, __LINE__);
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
//  RecordPageView()
//
//  This function is called at the beginning of each page. It records information about
//  the page view into the database
//
//  PARAMETERS:
//    oDB   - the database connection object
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function RecordPageView($oDB)
{
    if(!DetectBot())   // exclude visits from web bots
    {
        $browser = GetBrowserString($_SERVER['HTTP_USER_AGENT']);
        $oDB->query("INSERT INTO views (IPAddress, Browser, Host, Page, QueryString, HTTP_USER_AGENT, Date) VALUES (" . 
                    "'" . addslashes($_SERVER['REMOTE_ADDR']) . "', " .
                    "'$browser', " .
                    "'" . addslashes($_SERVER['HTTP_HOST']) . "', " .
                    "'" . addslashes($_SERVER['SCRIPT_NAME']) . "', " .
                    "'" . addslashes($_SERVER['QUERY_STRING']) . "', " .
                    "'" . addslashes(substr($_SERVER['HTTP_USER_AGENT'], 0, 200))  . "', " .
                    "NOW())", __FILE__, __LINE__);
    }
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
    $oDB->query($sql, __FILE__, __LINE__);
    CheckAndReportSQLError($oDB, __FILE__, __LINE__);
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