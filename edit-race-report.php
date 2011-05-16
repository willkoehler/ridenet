<?
require("script/app-master.php");
$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
CheckLoginAndRedirect();

$RaceID = SmartGetInt("RaceID");
$RiderID = SmartGetInt("RiderID");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, $pt, "Edit Race Report")?></title>
  <link href="/styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
</head>

<body class="oneColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "YourProfile")?>
    <?InsertMemberMenu($oDB, $pt, "UpdateResults")?>
  </div>
  
  <div id="mainContent">
    <h1>Edit Race Report</h1>
    <div align="center">
      <table border="0" cellpadding="3" cellspacing="1" width="100%">
        <tr>
          <td width="100%" align="center">
            <form name="f1" method="POST" action="action/edit-race-report.php">
            <!-- hidden controls to pass rider number and race id onto next page -->
            <input type="hidden" name="RiderID" value="<?=$RiderID?>">
            <input type="hidden" name="RaceID" value="<?=$RaceID?>">
            <table border="0" cellpadding="0" cellspacing="5" width="100%" height="105">
            <tr>
              <td align="center">
                <textarea name="Report" style="height:300px;width:600px;font:10pt arial"><?=$oDB->DBLookup("Report", "race_report", "RaceID=$RaceID AND RiderID=$RiderID")?></textarea>
              </td>
            </tr>
            <tr><td class="table-spacer" style="height:5px">&nbsp;</td></tr>
            <tr>
              <td align=center><input type="Submit" value="Save Report"></td>
            </tr>
          </table>
        </td></tr>
      </table>
    </div>
  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>