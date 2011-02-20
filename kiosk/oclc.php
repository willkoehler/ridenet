<?
require("../script/app-master.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>&nbsp;</title>
  <style type="text/css">
    .sidebar
    {
      background-color:#dce0e4;
    }
    .sidebar .header
    {
      font:28px 'lucida grande',lucida,tahoma,helvetica,arial,sans-serif;
      padding:10px;
      height: 35px;
      text-align:center;
      background-color:#5f7f9d;
      color:#DCE0E4
    }
    .sidebar .body
    {
      font:22px helvetica;
      line-height:24px;
      padding:10px;
    }
    .sidebar .url
    {
      font:34px arial;
      text-align:center;
    }
  </style>  
</head>

<body style="margin:0px">
  <!-- Trek Store TV cannot be zoomed to eliminate overscan. about 5% of the out border of the screen is
       not visible. These sizes and margins are required to center the content on the TV screen -->
  <!-- We use <table> layout because it results in significantly smoother scrolling animation 
       on the AppleTV vs floating <div>. Borders and border radius also have been eliminated
       to make animation smoother -->
  <table cellspacing=0 cellpadding=0 style="margin:0px 0px 0px 0px;width:1280px">
    <tr>
      <td valign=top>
        <div style="width:850px">   <!-- Need this <div> to chop extra-long unbreakable lines in ride logs-->
          <!-- Ride Widget -->
          <script type='text/javascript' src='/ride-widget.js'></script>
          <script type='text/javascript' src='/ExtCore3/ext-core.js'></script>
          <script type="text/javascript">
              var rideWidget = new C_RideWidget({
                  interval: 7000,
                  domainRoot: '<?=GetDomainRoot()?>',
                  title: '<div style="float:right;text-align:right;font-size:14px;padding-top:16px;color:#9dcf93"> \
                            www.ridenet.net \
                          </div> \
                          <div style="float:left;text-align:left;font-size:14px;padding-top:16px;color:#9dcf93"> \
                            Powered by RideNet \
                          </div> \
                          <div style="color:#dce0e4;text-align:center"> \
                            Everyone\'s Riding... Are You? \
                          </div>',
                  photos: true,
                  headfoot: true,
                  preload: 4,
                  fullscreen: true,
                  size: {
                    headerfont: 28,
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
      </td>
      <td class="sidebar" style="width:430px" valign=top>
        <div class="header">
          OCLC Supports 2 BY 2012.
        </div>
        <div class="body">
          <div style="height:0px"></div>
          <div style="float:left;padding:8px 20px 20px 0px"><img src="2by2012-logo.png" width=130></div>
          The goal of 2 BY 2012 is for each citizen of central Ohio to bicycle to work or school 2 days per month by the Columbus bicentennial in 2012.
          <p>
            2 BY 2012 is both a challenge and a movement. As citizens of Columbus rise to the challenge and change the way
            we get to work, we can start a movement that will significantly benefit our lives, our economy and our community.
          </p>
          <p>
            Columbus’ city leadership is actively working to support and encourage bicycling. And Columbus businesses, such as
            The Trek Stores, are taking advantage of 2 BY 2012 to implement Bike to Work programs. All we need now is you:
            Join the movement today!
          </p>
          <div style="float:right;padding:0px 50px 0px 10px"><img src="oclc-logo.png" width=150></div>
          Learn more on the OCLC RideNet site
          <div style="clear:both;height:20px"></div>
          <div class="url">http://oclc.ridenet.net</div>
          <div style="height:5px"></div>
        </div>
      </td>
    </tr>
  </table>
  
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