<?
require("script/app-master.php");
$oDB = oOpenDBConnection();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Contact RideNet</title>
<!-- Include site stylesheets -->
  <link href="/styles.pcs?T=0" rel="stylesheet" type="text/css" />
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
</head>

<body class="oneColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, 0)?>
    <?InsertMainMenu($oDB, 0, "about")?>
  </div>
  <!-- style="font-size:14px;line-height:22px" -->
  <div id="mainContent">
    <div id="xabout">
      <h1>Contact Us</h1>
      <h2 class="text75" xstyle="font-size:15px">
        For support, feedback, questions, problems and praise, please contact us via email<br><br>
        email: <a href="mailto:info@ridenet.net">info@ridenet.net</a><br><br>
        Questions about RideNet clothing: <a href="mailto:clothing@ridenet.net">clothing@ridenet.net</a>
      </h2>
      <div style="height:60px"></div>

    </div>  
  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>