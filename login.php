<?
require("script/app-master.php");
$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented

if(isset($_REQUEST['logoff']))
{
    EndSession();
    header("Location: index.php");
    exit();
}

if(!isset($_SESSION['logonmsg']))
{
    $msg="Enter your email address and password to login";
}
else
{
    $msg = $_SESSION['logonmsg'];
    unset($_SESSION['logonmsg']);
}

$riderEmail = isset($_COOKIE['RiderEmail']) ? $_COOKIE['RiderEmail'] : "";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, $pt, "Login")?></title>
  <link href="styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
</head>

<body class="oneColFixHdr" onload="OnLoad()">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "MembersOnly")?>
  </div>
  
  <div id="mainContent">
    <h1>Login</h1>
    <div align="center">
      <div class="block-table" style="width:420px">
        <form method="POST" action="action/dbcheck.php">
          <?if(isset($_REQUEST['Goto'])) { ?>  
            <input type="hidden" name="Goto" value="<?=$_REQUEST['Goto']?>">
          <? } ?>
          <div style="height:5px"><!--vertical spacer--></div>
          <div class="header"><?=$msg?>&nbsp;</div>
          <table id="login" cellpadding=0 cellspacing=0 width=100%>
            <tr>
              <td width=275 class="label">EMAIL ADDRESS:</td>
              <td width=275 align=left><input type="text" name="id" id="id" style="width:175px" value="<?=$riderEmail?>"></td>
            </tr>
            <tr><td colspan=2 class="table-spacer" style="height:5px">&nbsp;</td></tr>
            <tr>
              <td width=275 class="label">PASSWORD:</td>
              <td width=275 align=left><input type="password" name="pw" id="pw" style="width:175px"></td>
            </tr>
            <tr><td colspan=2 class="table-spacer" style="height:5px">&nbsp;</td></tr>
            <tr>
              <td colspan=2 align=center>
                <table cellpadding=0 cellspacing=0 border=0><tr>
                  <td><input type="checkbox" name="StayLoggedIn">&nbsp;</td>
                  <td style="font:13px arial">Keep me logged in on this machine</td>
                </tr></table>
              </td>
            </tr>
            <tr><td colspan=2 class="table-spacer" style="height:10px">&nbsp;</td></tr>
            <tr>
              <td width="50%" align=center colspan=2><input type="submit" value="Login" style="width:70px"></td>
            </tr>
          </table
        </form>
      </div>
    </div>
    <div style="height:15px"><!--vertical spacer--></div>
  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>

<script language=javascript>
function OnLoad()
{
    ctrlEmail = document.getElementById("id");
    ctrlPW = document.getElementById("pw");
    if(ctrlEmail.value=='')
    {
        // email address is blank, put cursor in email field
        ctrlEmail.focus();
    }
    else
    {
        // email address is filled in (from cookie), put cursor in password field
        ctrlPW.focus();
    }
}
</script>
