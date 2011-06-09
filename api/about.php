<?
require("../script/app-master.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>&nbsp;</title>
</head>

<body style="margin:0px">
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
          preload: 5,
          fullscreen: true,
          size: {
            headerfont: 28,
            font: 35,
            pic: 120,
            icon: 35
          },
          color: {
            widget: '#007987'
          }
      });
      rideWidget.create();
  </script>
</body>
</html>