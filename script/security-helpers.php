<?
//----------------------------------------------------------------------------------
//  isTeamAdmin()
//
//  This function checks to see whether the current user has team admin rights
//  for the passed in TeamID
//
//  PARAMETERS:
//    oDB   - the database connection object
//    TeamID  - TeamID to check
//
//  RETURN: true if user has team admin rights for this team
//-----------------------------------------------------------------------------------
function isTeamAdmin($oDB, $teamID)
{
    $teamIDs = GetRiderTeamInfo($oDB, GetUserID());
    if(($teamIDs['CommutingTeamID']==$teamID && $teamIDs['sCommutingTeamAdmin']==1) ||
       ($teamIDs['RacingTeamID']==$teamID && $teamIDs['sRacingTeamAdmin']==1))
    {
        return(true);
    }
    else
    {
        return(false);
    }
}


//----------------------------------------------------------------------------------
//  isSystemAdmin()
//
//  This function checks to see whether the current user has system admin rights
//
//  PARAMETERS: none
//
//  RETURN: true if user has system admin rights
//-----------------------------------------------------------------------------------
function isSystemAdmin()
{
    return((isset($_SESSION['sSystemAdmin']) && $_SESSION['sSystemAdmin']==1) ? true : false);
}


//----------------------------------------------------------------------------------
//  isDesigner()
//
//  This function checks to see whether the current user has designer rights
//
//  PARAMETERS: none
//
//  RETURN: true if user has designer rights
//-----------------------------------------------------------------------------------
function isDesigner()
{
    return((isset($_SESSION['sDesigner']) && $_SESSION['sDesigner']==1) ? true : false);
}


//----------------------------------------------------------------------------------
//  isMyTeam()
//
//  This function checks to see whether the logged in rider is currently viewing
//  their team site. Some controls (like the add ride button on the calendar
//  sidebar are only visible when you're viewing your own team page)
//
//  PARAMETERS:
//    oDB   - the database connection object
//    pt    - ID of team currently being presented to the user
//
//  RETURN: true if user has designer rights
//-----------------------------------------------------------------------------------
function isMyTeam($oDB, $pt)
{
    $teamInfo = GetRiderTeamInfo($oDB, GetUserID());
    return((CheckLogin() && ($teamInfo['CommutingTeamID']==$pt || $teamInfo['RacingTeamID']==$pt))  ? true : false);
}


//----------------------------------------------------------------------------------
//  GetUserID()
//
//  This function returns the ID of the current logged in user, or zero if no user
//  is logged in.
//
//  PARAMETERS: none
//
//  RETURN: ID of currently logged in user
//-----------------------------------------------------------------------------------
function GetUserID()
{
    return(isset($_SESSION['lcheck']) ? $_SESSION['lcheck'] : 0);
}



//----------------------------------------------------------------------------------
//  CheckLogin()
//
//  This function checks whether the site visitor is logged in to the system.
//
//  PARAMETERS: none
//
//  RETURN: true if site visitor is logged on
//-----------------------------------------------------------------------------------
function CheckLogin()
{
    if(isset($_SESSION['lcheck']) && $_SESSION['lcheck'] != 0)
    {
        return(true);
    }
    else
    {
        return(false);
    }
}


//----------------------------------------------------------------------------------
//  CheckLoginAndRedirect()
//
//  This function checks whether the site visitor is logged in to the system. If the
//  visitor is not logged in, they are redirected to the login page.
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function CheckLoginAndRedirect()
{
    if(!CheckLogin())
    {
        header("Location: login.php?Goto=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
    // --- Redirect user to password change page if they are required to change their password
    if(isset($_SESSION['MustChangePW']) && $_SESSION['MustChangePW'] == 1)
    {
        header("Location: /change-pw.php?Goto=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
}


//----------------------------------------------------------------------------------
//  SessionToJS()
//
//  Stores session information in JavaScript global variables that will be accessable
//  to JavaScript code.
//
//  NOTE: This function must be called within <script> tags in the HTML document
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function SessionToJS()
{
    if(CheckSession())
    {
        echo "g_SessionActive = true;\n";
        echo "g_Session = {\n";
        echo "sSystemAdmin: " . $_SESSION['sSystemAdmin'] . ",\n";
        echo "sDesigner: " . $_SESSION['sDesigner'] . ",\n";
        echo "riderID: " . GetUserID() . "\n";
        echo "};\n";
    }
    else
    {
        echo "g_SessionActive = false;\n";
    }
}


// Here is (yet another) short function that can convert php variables to javascript arrays.
// It recursively reads any php object, array, string or integer and returns (well packed)
// javascript code.
// Here is an example how to use it:
// $a = array(null,1,2,3,4,5);
// $b = array('foo','bar');
// $c = array(1,'foo','bar'=>$a,$b);
// echo "var myArray = ".php2js($c);
// var myArray = {0:1,1:"foo","bar":{0:null,1:1,2:2,3:3,4:4,5:5},2:{0:"foo",1:"bar"}}
function php2js($dta) {
  if(is_object($dta))
    $dta = get_object_vars($dta);
  if(is_array($dta)) {
    foreach($dta AS $k=>$d)
      $dta[$k] = php2js($k).":".php2js($d);
    return '{'.implode(',',$dta).'}';
  }
  elseif(is_numeric($dta))
    return "$dta";
  elseif(is_string($dta))
    return '"'.str_replace('"','\"',$dta).'"';
  else
    return 'null';
}


?>