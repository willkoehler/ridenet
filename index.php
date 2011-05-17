<?
require("script/app-master.php");
require(SHAREDBASE_DIR . "ExtJSLoader.php");

$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta name="description" content="Create a rider bio, track your race results, keep a ride log, build a team page, find cycling events and rides in your area, connect with other riders.">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?BuildPageTitle($oDB, 0, "Home")?></title>
  <!-- Include common code and stylesheets -->
    <? IncludeExtJSFiles() ?>
  <!-- Include site stylesheets -->
    <link href="/styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
  <!-- Code-behind modules for this page (minify before including)-->
    <?MinifyAndInclude("/dialogs/signup-dialog.js")?>
    <?MinifyAndInclude("/script/ridenet-helpers.js")?>
  <!-- Build javascript arrays for local/static combobox lookups -->
    <script type="text/javascript">
      <?SessionToJS()?>
      g_source = '<?=isset($_REQUEST['s']) ? $_REQUEST['s'] : 'direct'?>';
    </script>
  <!-- Insert tracker for Google Analytics -->
    <?InsertGoogleAnalyticsTracker()?>
  <!-- facebook meta tags to link this site with the RideNet Facebook app -->
    <meta property="fb:app_id" content="147642135282357" />
  </head>

<body class="oneColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "Home")?>
  </div>

  <div id="extraWideContent" style="padding:30px">
    <div style="float:left;width:515px">
      <div style="margin-bottom:30px;-webkit-font-smoothing:antialiased;font:22px 'Helvetica Neue', Helvetica, Arial, sans-serif" class="primary-color">
        <span style="line-height:28px;">Create a rider bio, track your race results, keep a ride log, build a team page, find cycling events in your area, connect with other riders.</span>
        <a href="/about" style="font-size:14px;color:#058A12">Learn More...</a>
      </div>
      <div style="margin-left:20px">
        <script type='text/javascript' src='../ride-widget.js'></script> 
        <script type="text/javascript">
          var rideWidget = new C_RideWidget({
              height: 590,
              width: 450,
              interval: 6000,
              preload: 8,
              headfoot: false,
              scrollbar: false,
              domainRoot: '<?=GetDomainRoot()?>',
              size: {
                font: 13,
                pic: 45
              },
              color: {
                background: '#FFF',
                text: '#444',
                links: '#383',
                widget: '#EEE'
              }
            });
          rideWidget.create();
        </script>
      </div>
    </div>
    <div style="float:left;width:170px;margin-left:10px">
      <div style="width:180px;margin-top:10px;-moz-border-radius: 8px;border-radius: 8px;border: 1px solid #CCC;background-color:#EEE;padding:15px 10px">
        <div style="margin-top:0px;font:16px helvetica;color:#333">Sign Up for RideNet.</div>
        <div style="margin-top:5px;font:12px helvetica;color:#888">
            RideNet is FREE for teams and individuals. Create your RideNet profile today!
        </div>
        <div style="margin-top:15px;text-align:center">
          <span class="action-btn" style="font-size:15px" id='signup-btn' onclick="g_signupDialog.show({animateTarget:'signup-btn'})">&nbsp;&nbsp;Sign Up...&nbsp;&nbsp;</span>
        </div>
        <div id="join-btn"></div>
      </div>

      <div style="width:200px;margin-top:10px;-moz-border-radius: 8px;border-radius: 8px;border: 1px solid #CCC;background-color:#EEE;padding:15px 0 10px 0;text-align:center">
        <div style="margin-top:0px;font:16px helvetica;color:#333">Buy RideNet Clothing.</div>
        <a href="/clothing">
          <img style="margin:5px 0 0 0;" src="/images/clothing/ridenet-jersey1.png" Height=110 border=0>
        </a>
        <p style="margin:5px 0 0 0;font-size:12px;color:#888">
          Order by April 15th
        </p>
      </div>

      <div style="margin:20px 0px 2px 5px;font:16px 'Helvetica Neue', Helvetica, Arial, sans-serif" class="primary-color">
        Who's on RideNet...
      </div>
<?    $sql = "SELECT RiderID, RacingTeamID, Domain, SUM(LENGTH(Comment)) AS Comments
            FROM rider
            LEFT JOIN ride_log USING (RiderID)
            LEFT JOIN teams ON (CommutingTeamID = TeamID)
            WHERE rider.Archived=0 AND (ride_log.Date BETWEEN ADDDATE(NOW(), -31) AND NOW())
            GROUP BY RiderID
            ORDER BY Comments DESC
            LIMIT 0,24";
    $rs = $oDB->query($sql, __FILE__, __LINE__); ?>
      <div class="commute-ride-group" style="margin-left:5px;width:195px">
        <? while(($rider=$rs->fetch_array())!=false) { ?>
          <div id="R<?=$rider['RiderID']?>" class="photobox">
            <a href="<?=BuildTeamBaseURL($rider['Domain'])?>/rider/<?=$rider['RiderID']?>">
              <img class="tight" src="<?=GetFullDomainRoot()?>/imgstore/rider-portrait/<?=$rider['RacingTeamID']?>/<?=$rider['RiderID']?>.jpg" height=35 width=28 border="0">
            </a>
          </div><script type="text/javascript">riderInfoCallout(<?=$rider['RiderID']?>, '')</script>
        <? } ?>
        <? $rs->free() ?>
      </div>
      <div style="clear:both;"></div>
      <div style="margin:20px 0px 0px 0px;font:16px 'Helvetica Neue', Helvetica, Arial, sans-serif" class="primary-color">
        Featured Teams...
      </div>
      <div style="margin:0px 0px 0px 0px;text-align:center;font:12px 'Helvetica Neue', Helvetica, Arial, sans-serif" class="text75">
        <a href="<?=BuildTeamBaseURL('echeloncycling')?>/">
          <img style="border:1px solid #555;margin-top:5px" src="/images/featured-echelon.jpg">
        </a>
        <a href="<?=BuildTeamBaseURL('teamawesome')?>/">
          <img style="border:1px solid #555;margin-top:10px" src="/images/featured-teamawesome.jpg">
        </a>
        <a href="<?=BuildTeamBaseURL('pattycake')?>/">
          <img style="border:1px solid #555;margin-top:10px" src="/images/featured-pattycake.jpg">
        </a>
        <a href="<?=BuildTeamBaseURL('trekstorecolumbus')?>/">
          <img style="border:1px solid #555;margin-top:10px" src="/images/featured-trekstore.jpg">
        </a>
        
      </div>
    </div>
    <br class="clearfloat" /> 
    <div style="height:10px"><!--vertical spacer--></div>

  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->
</body>

<script type="text/javascript">
  // This will be called when DOM is loaded and ready
  Ext.onReady(function()
  {
  // --- Turn on validation errors beside the field globally and enable quick tips that will
  // --- popup tooltip when mouse is hovered over field
      Ext.form.Field.prototype.msgTarget = 'qtip';
      Ext.QuickTips.init();
  // --- create signup dialog
      g_signupDialog = new C_SignupDialog();
  });
</script>

</html>