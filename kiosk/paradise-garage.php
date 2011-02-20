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
      background-color: #9EA2AD;
      font:23px 'Helvetica Neue', Arial, sans-serif;
      line-height:26px;
      padding:20px;
      color: #333;
    }
    .sidebar .message-wrap
    {
      font: 42px Helvetica, arial, sans-serif;
      position:relative;
      text-align:center;
    }
    .sidebar .message-shadow
    {
      position:absolute;
      left:+1px;top:+2px;
      width:100%;
      color: #777;
      z-index: 1;
    }
    .sidebar .message
    {
      position:relative;
      width:100%;
      color: #FFF;
      z-index: 2;
    }
    .sidebar .url-wrap
    {
      font: 42px Helvetica, arial, sans-serif;
      position:relative;
      text-align:center;
    }
    .sidebar .url-shadow
    {
      position:absolute;
      left:+1px;top:+2px;
      width:100%;
      color: #777;
      z-index: 1;
    }
    .sidebar .url
    {
      position: relative;
      width:100%;
      color: #DDD;
      z-index: 2;
    }
  </style>  
</head>

<body style="margin:0px">
  <!-- PG TV cannot be zoomed to eliminate overscan. about 5% of the out border of the screen is
       not visible. These sizes and margins are required to center the content on the TV screen -->
  <!-- We use <table> layout because it results in significantly smoother scrolling animation 
       on the AppleTV vs floating <div>. Borders and border radius also have been eliminated
       to make animation smoother -->
  <table cellspacing=0 cellpadding=0 style="margin:10px 0px 0px 64px;width:1155px">
    <tr>
      <td valign=top>
        <div style="width:745px">   <!-- Need this <div> to chop extra-long unbreakable lines in ride logs-->
          <!-- Ride Widget -->
          <script type='text/javascript' src='/ride-widget.js'></script>
          <script type='text/javascript' src='/ExtCore3/ext-core.js'></script>
          <script type="text/javascript">
              var rideWidget = new C_RideWidget({
                  interval: 7000,
                  domainRoot: '<?=GetDomainRoot()?>',
                  title: '<div style="color:#dce0e4;text-align:center"> \
                            Who\'s Riding Right Now in Central Ohio . . . \
                          </div>',
                  photos: true,
                  headfoot: true,
                  preload: 4,
                  fullscreen: true,
                  size: {
                    headerfont: 28,
                    font: 25,
                    pic: 85,
                    icon: 26
                  },
                  color: {
                    widget: '#007987'
                  }
              });
              rideWidget.create();
          </script>
        </div>
      </td>
      <td class="sidebar" style="width:400px" valign=top>

        <div style="width:230px;margin:0 auto">
          <img src="2by2012-logo.png">
        </div>
        <div style="height:40px"></div>

        <div class="message-wrap">
          <div class="message-shadow">
            <b>Ride Your Bike</b>
            <div style="font-size:.8em;line-height:1em">
              Work &bull; School &bull; Errands<br>
              Twice a Month<br>
            </div>
          </div>
          <div class="message">
            <b>Ride Your Bike</b>
            <div style="font-size:.8em;line-height:1em">
              Work &bull; School &bull; Errands<br>
              Twice a Month<br>
            </div>
          </div>
        </div>
        <div style="height:25px"></div>

        <div style="text-align:left;margin-left: 15px">
          The goal of 2 BY 2012 is for each citizen of Central Ohio to ride their bike to work, school
          or for local errands twice a month by the Columbus Bicentennial in 2012
        </div>
        <div style="height:20px"></div>

        <div class="url-wrap">
          <div class="url-shadow">www.2by2012.com</div>
          <div class="url">www.2by2012.com</div>
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