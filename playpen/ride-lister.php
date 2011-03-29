<?
require("script/app-master.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>

<body style="margin:0px">
  <!-- Ride Widget -->
  <script type='text/javascript' src='/ride-widget.js'></script> 
  <script type='text/javascript' src='/ExtCore3/ext-core.js'></script>
  <script type="text/javascript">
      var rideWidget = new C_RideWidget({
          height: 200,
          width: 480,
          interval: 7000,
          domainRoot: '<?=GetDomainRoot()?>',
          photos: true,
          headfoot: true,
          preload: 4,
          fullscreen: true
      });
      rideWidget.create();
  </script>

</body>
</html>