<?
require("script/app-master.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
CheckLoginAndRedirect();
RecordPageView($oDB);

if(!isSystemAdmin())
{
  $_SESSION['logonmsg'] = "This page requires admin rights. Please login.";
  header("Location: login.php?Goto=" . urlencode($_SERVER['REQUEST_URI']));
  exit();
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, 0, "System Manager")?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Buffer View -->
  <?echo "<script type='text/javascript' src='" . EXTBASE_URL . "examples/ux/BufferView.js'></script>\n"?>
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("script/ridenet-helpers.js")?>
  <?MinifyAndInclude("sysmanager.js")?>
  <?MinifyAndInclude("dialogs/team-dialog.js")?>
<!-- Build javascript arrays for local/static combobox lookups -->
  <script type="text/javascript">
    <?SessionToJS()?>
    siteLevelLookup = <?$oDB->DumpToJSArray("SELECT SiteLevelID, SiteLevel FROM ref_site_level ORDER BY SiteLevelID")?>;
    teamTypeLookup = <?$oDB->DumpToJSArray("SELECT TeamTypeID, TeamType FROM ref_team_type ORDER BY TeamType")?>;
    g_domainRoot="<?=GetDomainRoot()?>";
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
</head>

<body class="oneColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "YourProfile")?>
    <?InsertMemberMenu($oDB, $pt, "TeamManager")?>
  </div>
  
  <div id="extraWideContent">
    <div style="height:20px"><!--vertical spacer--></div>
    <div id="manager-form"><!--manager form will go here--></div>
    <div style="height:20px"><!--vertical spacer--></div>
  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>