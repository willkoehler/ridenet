<?
require("../script/app-master.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>

<body>

  <!-- Ride Widget includes. Place this anywhere in the page -->
  <script type='text/javascript' src='http://<?=GetDomainRoot()?>/ride-widget.js'></script> 
  <script type='text/javascript' src='http://<?=GetDomainRoot()?>/ExtCore3/ext-core.js'></script>
  <!-- Ride Widget. Place this script code wherever you want widget to appear in the page layout -->
  <script type="text/javascript">
      var rideWidget = new C_RideWidget({
          height: 250,      // height of widget
          width: 480,       // width of widget
          interval: 6000,   // rate at which new rides are added to the list
          domainRoot: '<?=GetDomainRoot()?>',
          preload: 3,        // number of rides that are loaded when widget is first displayed
          maxage: 10,        // maximum age of rides that will appear in the list (days)
          headfoot: true,    // hide/show widget header and footer
          scrollbar: false,  // hide/show scrollbar
          team: 'Cougar Cranks',  // filter rides by team name. Use '*' to show all teams
          rider: '*',             // filter rides by ride name. Use '*' to show all riders
          title: "Who's Riding at CSCC",   // message to show in header
          color: {          // customize widget colors
            background: '#FFF',
            text: '#444',
            links: '#007A87',
            widget: '#5F7F9D',
            title: '#FFF'
          },
          size: {           // customize sizes
            headerfont: 16,   // header font size in px
            font: 13,         // font size in px
            pic: 35,          // rider picture size in px
            icon: 15          // icon size in px
          }
      });
      rideWidget.create();
  </script>

</body>
</html>