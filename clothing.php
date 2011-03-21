<?
require("script/app-master.php");
require("dynamic-sections/commuting.php");
require("dynamic-sections/calendar-sidebar.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
RecordPageView($oDB);
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
$RideBoardLength = 30;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, 0, "Clothing")?></title>
<!-- Include site stylesheets -->
  <link href="styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
<!-- facebook meta tags to provide information for the like button -->
  <meta property="og:title" content="Buy RideNet Clothing" />
  <meta property="og:image" content="http://ridenet.net/images/clothing/ridenet-jersey1.jpg" />
  <meta property="og:site_name" content="RideNet" />
  <meta property="og:description" content="We're selling clothing to promote RideNet. A portion of each order will be donated to Consider Biking to support 2BY2012" />
  <meta property="fb:app_id" content="147642135282357" />
</head>

<body class="oneColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt)?>
  </div>

  <div id="mainContent">
    <div style="float:left">
      <h1>RideNet Clothing</h1>
    </div>
    <div style="float:left;margin-left:10px;position:relative;left:0px;top:12px">
      <?SocialMediaButtons("RideNet clothing for sale")?>
    </div>
    <div class='clearfloat' style="height:10px"></div>
    <p class="newp">
      We're selling clothing to promote RideNet! Orders are due by April 1st and will be available
      for pickup in May (we'll e-mail you when they are ready).
    </p>
    <div style="float:right;margin:0px 0px 0 0;text-align:center">
      <a style="color:#B42A37;font: 16px arial" href="https://www.bikereg.com/events/register.asp?eventid=13099" target="_blank">
        Click to Order Now!<br>
      </a>
      <a href="https://www.bikereg.com/events/register.asp?eventid=13099" target="_blank">
        <img src="images/clothing/order-btn3.png" border=0 width=155>
      </a>
    </div>
    <h2><ul style="list-style: square;line-height:25px;font-size:16px">
      <li>High quality fabric and construction - Verge Elite Collection
      <li>Flat lock stitching. Tagless labeling. Hidden zipper
      <li>Orders are handled securely through BikeReg.com
      <li>Orders Due April 1st - Delivery in May
      <li>A portion of each sale supports <a href="http://www.2by2012.com/" target="_blank">Consider Biking's 2BY2012 program</a>
    </ul></h2>
    <div style="height:20px"></div>
    
    <div style="float:left">
      <a href="https://www.bikereg.com/events/register.asp?eventid=13099" target="_blank">
        <img src="images/clothing/ridenet-jersey1.png" border=0>
      </a>
      <p class=photo-caption style="text-align:center">Jersey Front</p>
    </div>
    <div style="float:left">
      <a href="https://www.bikereg.com/events/register.asp?eventid=13099" target="_blank">
        <img src="images/clothing/ridenet-jersey2.png" border=0>
      </a>
      <p class=photo-caption style="text-align:center">Jersey Back</p>
    </div>
    <div style="float:left">
      <a href="https://www.bikereg.com/events/register.asp?eventid=13099" target="_blank">
        <img src="images/clothing/ridenet-shorts.png" border=0>
      </a>
      <p class=photo-caption style="text-align:center">Shorts</p>
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
