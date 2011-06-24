<?
require("script/app-master.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
$RideBoardLength = 30;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, 0, "iPhone App")?></title>
<!-- Include site stylesheets -->
  <link href="/styles.pcs?T=0" rel="stylesheet" type="text/css" />
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
<!-- facebook meta tags to provide information for the like button -->
  <meta property="og:title" content="RideNet iPhone App" />
  <meta property="og:image" content="http://ridenet.net/images/mobile/iphone.png" />
  <meta property="og:site_name" content="RideNet" />
  <meta property="og:description" content="RideNet iPhone app is now available. Record your rides - with route maps - and upload to RideNet. Help us gather data to improve biking infrastructure in Central Ohio" />
  <meta property="fb:app_id" content="147642135282357" />
</head>

<body class="oneColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, 0)?>
    <?InsertMainMenu($oDB, $pt)?>
  </div>

  <div id="mainContent">
    <div style="float:left">
      <h1>RideNet iPhone App</h1>
    </div>
    <div style="float:left;margin-left:10px;position:relative;left:0px;top:12px">
      <?SocialMediaButtons("The RideNet iPhone app is now available. Record your rides - with route maps - and upload to RideNet.")?>
    </div>
    <div class='clearfloat' style="height:10px"></div>
    <div style="float:left;margin:0 10px 0 0">
      <a href="http://itunes.apple.com/us/app/ridenet/id444003332" target="_blank">
        <img src="/images/mobile/iphone.png" border=0>
      </a>
      <p class=photo-caption style="text-align:center">RideNet iPhone</p>
    </div>
    <div style="float:left;width:450px">
      <div style="height:15px"></div>
      <h2><ul style="line-height:25px;font-size:16px">
        <li>Record rides with route maps and upload to RideNet
        <li>Share your route maps with others or keep them private
        <li>Manage your ride log and view past rides
        <li>Help us improve biking infrastructure in Central Ohio
      </ul></h2>
      <p class="newp" style="margin-left:20px">
        When you log a ride in the RideNet iPhone app, a map of your ride is uploaded to RideNet
        and stored in your profile. These route maps are aggregated into a city-wide map showing
        the primary biking routes in Central Ohio which provide city leaders with vital information
        to plan future biking improvements.
      </p>
      <p class="newp" style="margin-left:20px">
        Each time you record a ride in the iPhone app you give us the data we need to
        make Columbus the #1 bike city in the USA!
      </p>
      <div style="height:10px"></div>
      <a href="http://itunes.apple.com/us/app/ridenet/id444003332" target="_blank" style="margin-left:120px">
        <img class="tight" src="/images/mobile/download.png">
      </a>
    </div>
    
    <div class='clearfloat' style="height:20px"></div>
  

  </div><!-- end #mainContent -->
  <br class="clearfloat" />  <!-- clear all floating elements -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>
