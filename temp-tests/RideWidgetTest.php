<?
require("../script/app-master.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>

<body>
  <!-- Ride Widget -->
<script type='text/javascript' src='http://ridenet.local/ride-widget.js'></script> 
<script type='text/javascript' src='http://ridenet.local/ExtCore3/ext-core.js'></script>
<script type="text/javascript">
    var rideWidget = new C_RideWidget({
        height: 250,      // height and width of widget
        width: 480,       // height and width of widget
        interval: 6000,   // rate at which new rides are added to the list
        domainRoot: '<?=GetDomainRoot()?>',
        preload: 3,       // number of rides that are loaded when widget is first displayed
        headfoot: true,   // hide/show widget header and footer
        scrollbar: true,
        title: "Everyone's Riding... Are You?",   // message to show in header
        color: {          // customize widget colors
          background: '#FFF',
          text: '#444',
          links: '#007A87',
          widget: '#5F7F9D'
        },
        size: {           // customize sizes
          font: 13,       // font size in px
          pic: 35,        // rider picture size in px
          icon: 15        // icon size in px
        }
    });
    rideWidget.create();
</script>

</body>
</html>