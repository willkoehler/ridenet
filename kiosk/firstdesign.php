<?
require("../script/app-master.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>&nbsp;</title>
<!-- Insert tracker for Google Analytics -->
  <?//InsertGoogleAnalyticsTracker()?>
</head>

<body style="margin:0px;background:#CCC url('<?=GetFullDomainRoot()?>/kiosk/background.png') ">
  <div style="width:1000px;margin: 0 auto">
  <!-- Ride Widget -->
  <script type='text/javascript' src='/ride-widget.js'></script> 
  <script type='text/javascript' src='/ExtCore3/ext-core.js'></script>
  <script type="text/javascript">
      var rideWidget = new C_RideWidget({
          interval: 7000,
          domainRoot: '<?=GetDomainRoot()?>',
          title: '<div style="float:right;text-align:right;font-size:14px;padding-top:16px;color:#9dcf93"> \
                    Learn More at www.2by2012.com \
                  </div> \
                  <div style="float:left;text-align:left;font-size:14px;padding-top:16px;color:#9dcf93"> \
                    Hosted by RideNet ridenet.net \
                  </div> \
                  <div style="color:#dce0e4;text-align:center"> \
                    Everyone\'s Riding... Are You? \
                  </div> \
                  <div style="clear:both"></div>',
          photos: true,
          headfoot: true,
          preload: 4,
          fullscreen: true,
          size: {
            headerfont: 32,
            font: 26,
            pic: 95,
            icon: 30
          },
          color: {
            widget: '#007987'
          }
      });
      rideWidget.create();
  </script>
  </div>
  
  <script type="text/javascript">
  // Reload this page every 30 minutes
      var interval = 30 * 60 * 1000;
      Ext.TaskMgr.start.defer(interval, null, [{
          run: function() {
              window.location.reload();
          },
          interval: interval
      }]);
  </script>

</body>
</html>