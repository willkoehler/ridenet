<?
require("script/app-master.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
CheckLoginAndRedirect();
RecordPageView($oDB);

if(!isDesigner() && !isSystemAdmin() && !isTeamAdmin($oDB, $pt))
{
  $_SESSION['logonmsg'] = "This page requires team admin rights. Please login.";
  header("Location: login.php?Goto=" . urlencode($_SERVER['REQUEST_URI']));
  exit();
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?BuildPageTitle($oDB, $pt, "Team Manager")?></title>
<!-- Include common code and stylesheets -->
  <? IncludeExtJSFiles() ?>
<!-- Include site stylesheets -->
  <link href="styles.pcs?T=<?=$pt?>" rel="stylesheet" id="color-css" type="text/css" />
<!-- Buffer View -->
  <? echo "<script type='text/javascript' src='" . EXTBASE_URL . "examples/ux/BufferView.js'></script>\n" ?>
<!-- Code-behind modules for this page (minify before including)-->
  <?MinifyAndInclude("team-manager.js")?>
  <?MinifyAndInclude("sm-riders.js")?>
  <?MinifyAndInclude("sm-customize.js")?>
  <?MinifyAndInclude("sm-homepage.js")?>
  <?MinifyAndInclude("dialogs/rider-dialog.js")?>
  <?MinifyAndInclude("script/ridenet-helpers.js")?>
<!-- file upload field -->
  <?echo "<script type='text/javascript' src='" . EXTBASE_URL . "examples/ux/fileuploadfield/FileUploadField.js'></script>\n";?>
  <?echo "<link rel='stylesheet' type='text/css' href='" . EXTBASE_URL . "examples/ux/fileuploadfield/css/fileuploadfield.css'>\n";?>
<!-- color picker field -->
  <?echo "<script type='text/javascript' src='" . SHAREDBASE_URL . "colorpicker/colorpicker.js'></script>\n";?>
  <?echo "<script type='text/javascript' src='" . SHAREDBASE_URL . "colorpicker/colorpickerfield.js'></script>\n";?>
  <?echo "<link rel='stylesheet' type='text/css' href='" . SHAREDBASE_URL . "colorpicker/css/colorpicker.css'>\n";?>
<!-- Build javascript arrays for local/static combobox lookups -->
  <script type="text/javascript">
    <?SessionToJS()?>
    g_pt=<?=$pt?>;
    g_domainRoot="<?=GetDomainRoot()?>";
    riderTypeLookup = <?$oDB->DumpToJSArray("SELECT RiderTypeID, RiderType FROM ref_rider_type ORDER BY Sort")?>
    teamTypeLookup = <?$oDB->DumpToJSArray("SELECT TeamTypeID, TeamType FROM ref_team_type ORDER BY TeamType")?>
    teamLookup = <?$oDB->DumpToJSArray("SELECT TeamID, TeamName, Archived FROM teams ORDER BY TeamName")?>
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
    <?InsertMemberMenu($oDB, $pt, "SiteManager")?>
  </div>

  <div id="mainContent">

<!-- Sample Content For the Customize Page -->
    <div id='sample-holder' style="height:0px;overflow:hidden;width:100%">
      <div id='sample-content' style="border:1px dotted;width:698px;overflow:hidden;margin-bottom:10px">
        <div style="float:left;width:498px;padding:10px">
          <h1 style="margin-top:0px">Primary Header Uses Primary Color</h1>
          <p>
            This sample content will help you choose your color scheme. When picking colors, be sure to pay
            attention to how all areas of this page look including the menus and page footer.
          </p>
          <h2>Secondary header uses secondary color</h2>
          <p>
            Note that <a class='sample-link' href="">links</a> also have their own color which you can customize. Make
            sure the <a class='sample-link' href="">Link Color</a> works well both on the main page and the sidebar.
          </p>
          <table id='event-list' cellpadding=0 cellspacing=0>
            <tr><td class="table-divider" colspan=6>&nbsp;</td></tr>
            <tr>
              <td class=header style="padding:1px 2px;" align=left colspan=2>Date/Time</td>
              <td class=header style="padding:1px 0px;" align=left>Class</td>
              <td class=header style="padding:1px 0px;" align=left>Ride (click for more info)</td>
              <td class=header style="padding:1px 0px;" align=left colspan=2>Location [distance from you]</td>
            </tr>
            <tr><td class="table-spacer" style="height:5px" colspan=6>&nbsp;</td></tr>
            <tr class=data>
              <td width="65" style="padding:0px 2px;"><b>Tue 5/11</b></td>
              <td width="60">9:00am</td>
              <td width="50" style="font-weight:bold;font-family:courier new;">--BCD</td>
              <td width="200"><a class='sample-link' href="">Pataskala COP Ride</a></td>
              <td width="140">Pataskala, OH 43062</td>
              <td width="55" align=right>19 miles</td>
            </tr>
            <tr class=data>
              <td width="65" style="padding:0px 2px;"><b>Tue 5/11</b></td>
              <td width="60">6:00pm</td>
              <td width="50" style="font-weight:bold;font-family:courier new;">-AB--</td>
              <td width="200"><a class='sample-link' href="">Trek Store Shop Ride</a></td>
              <td width="140">Columbus, OH 43221</td>
              <td width="55" align=right>4 miles</td>
            </tr>
            <tr class=data>
              <td width="65" style="padding:0px 2px;"><b>Tue 5/11</b></td>
              <td width="60">6:15pm</td>
              <td width="50" style="font-weight:bold;font-family:courier new;">-ABC-</td>
              <td width="200"><a class='sample-link' href="">Hilliard COP Ride</a></td>
              <td width="140">Hilliard, OH 43206</td>
              <td width="55" align=right>7 miles</td>
            </tr>
          </table>

        </div>
        
        <div id="sidebarHolderRight">
          <div class="sidebarBlock">
            <h3 align=center>Sidebar Sample</h3>
            <p>
              This is how a sidebar will look. Make sure the link color works in the sidebar:
              <a class="sample-link" href="">Sample Link</a>
            </p>
          </div>
          <?MostViewedRiderSidebar($oDB, $pt)?>
        </div><!-- end right sidebar -->
        
      </div>
    </div>

    <div id="panel-holder"><!--team manager form will go here--></div>

  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>