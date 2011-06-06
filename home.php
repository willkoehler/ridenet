<?
require("script/app-master.php");
require("dynamic-sections/calendar-sidebar.php");
require("dynamic-sections/team-wall.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
// Get team information for this page
$sql = "SELECT IFNULL(HomePageType,1) AS HomePageType, IFNULL(HomePageMoreWrap, 1) AS HomePageMoreWrap,
               HomePageTitle, HomePageText, HomePageHTML, TeamName, TeamTypeID
        FROM Teams
        WHERE TeamID=$pt";
$rs = $oDB->query($sql, __FILE__, __LINE__);
$team = $rs->fetch_array();
$rs->free();
$TeamWallLength = 30;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, $pt)?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="/styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("/home.js")?>
  <?MinifyAndInclude("/dialogs/post-message-dialog.js")?>
  <?MinifyAndInclude("/script/ridenet-helpers.js")?>
<!-- Build javascript arrays for local/static combobox lookups -->
  <script type="text/javascript">
    <?SessionToJS()?>
    g_teamWallLength = <?=$TeamWallLength?>;
    g_fullDomainRoot="<?=GetFullDomainRoot()?>";
    g_pt = <?=$pt?>;
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
</head>

<body class="twoColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "Home")?>
  </div>

  <div id="sidebarHolderRight">
    <?if($team['TeamTypeID']==2) { ?>
      <?ColumbusFoundationSidebar($oDB)?>
    <? } else { ?>
      <?SignupSidebar($oDB)?>
    <? } ?>
    <?if($pt==2) { ?> <!--Team Echelon sponsors are hard-coded for now-->
      <?SponsorSidebar($oDB)?>
    <? } ?>
    <?AdSidebar($oDB)?>
    <?CalendarSidebar($oDB, $pt)?>
    <?MostViewedRiderSidebar($oDB, $pt)?>
  </div><!-- end right sidebar -->

  <div class="team-home" id="mainContent">
    
    <?if($team['HomePageType']==1) { ?>
      <!--=========== Simple Home Page ===========-->
      <h1>
        <?if(is_null($team['HomePageTitle'])) { ?>
          <?=SampleHomePageTitle($team['TeamTypeID'], $team['TeamName'])?>
        <? } else { ?>
          <?=$team['HomePageTitle']?>
        <? } ?>
      </h1>
      <div style="height:10px"></div>
      <div id='more-content'>
        <img class="photo" src="/imgstore/homepage/<?=$pt?>.jpg">
        <div class="text">
          <?if(is_null($team['HomePageText'])) { ?>
            <?=SampleHomePageText($team['TeamTypeID'])?>
          <? } else { ?>
            <?=$team['HomePageText']?>
          <? } ?>
        </div>
      </div><script type="text/javascript">createMoreWrapper('', 280, 'MORE');</script>
      <?if(is_null($team['HomePageText'])) { ?>
        <div class="help-info" style="margin-top:20px">
          This is a sample home page and stock photo. You can customize your homepage
          and upload a new photo using the <a href='/manage#2'>Team Manager</a>
        </div>
      <? } ?>
    <? } else { ?>
    <!--=========== Custom Home Page ===========-->
      <div id='more-content'>
        <?=$team['HomePageHTML']?>
      </div><?if($team['HomePageMoreWrap']) { ?><script type="text/javascript">createMoreWrapper('', 350, 'MORE');</script><? } ?>
    <? } ?>
    
    <div class="clearfloat" style="height:25px"></div>
    
    <!--=========== What's Happening ===========-->
    <?if(CheckLogin())
    { 
      $rs = $oDB->query("SELECT RiderID, RacingTeamID, CONCAT(FirstName, ' ', LastName) AS RiderName, TeamName
                         FROM rider LEFT JOIN teams ON (RacingTeamID = TeamID)
                         WHERE RiderID = " . GetUserID(), __FILE__, __LINE__);
      $loggedInRider = $rs->fetch_array();
      $rs->free();?>
      <div style="float:right;position:relative;top:6px;" class='action-btn' id='post-message-btn' onclick="clickPostMessage(this.id, { riderID:<?=$loggedInRider['RiderID']?>, racingTeamID: <?=$loggedInRider['RacingTeamID']?>, riderName: '<?=htmlentities(addslashes($loggedInRider['RiderName']))?>', teamName: '<?=htmlentities(addslashes($loggedInRider['TeamName']))?>', postingTo: '<?=htmlentities(addslashes($team['TeamName']))?>' });">
        + Post Message
      </div>
    <? } else { ?>
      <div style="float:right;position:relative;top:6px;" class='action-btn' onclick="window.location.href='/login?Goto=<?=urlencode($_SERVER['REQUEST_URI'])?>'">&nbsp;Login To Post Message&nbsp;</div>
    <? } ?>
    <div style="padding:5px;border-bottom:1px dotted #CCC;border-top:1px dotted #CCC">
      <h2>What's happening @ <?=$team['TeamName']?></h2>
      <div class="team-board-instructions">
        To log a ride or post a race result, go to <a href="/profile">Your Profile</a>.
        To post a message on this page, click "<b>Post Message</b>".
      </div>
    </div>
    <div class="clearfloat" style="height:1px"></div>
    <div id='team-wall' class='ridenet-wall' style="padding:0 50px 0 25px ">
      <? RenderTeamWall($oDB, $pt, $TeamWallLength) ?>
    </div>
    
  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>

</html>