<?
require("script/app-master.php");
$oDB = oOpenDBConnection();
RecordPageView($oDB);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta name="description" content="Create a rider bio, track your race results, keep a ride log, build a team page, find cycling events and rides in your area, connect with other riders.">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, 0, "About")?></title>
<!-- Include site stylesheets -->
  <link href="/styles.pcs?T=0" rel="stylesheet" type="text/css" />
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
</head>

<body class="oneColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, 0)?>
    <?InsertMainMenu($oDB, 0, "about")?>
  </div>
  <!-- style="font-size:14px;line-height:22px" -->
  <div id="mainContent">
    <div id="about">
      <h1>Create a rider bio, track your race results, keep a ride log, build a team page, find cycling events and rides in your area, connect with other riders.</h1>
      <div style="height:30px"></div>

      <img style="float:right;width:300px;margin:0px 0px 0px 40px" src="/images/about/profile.jpg">
      <div style="margin:10px 0px;">
        <h2>Rider Profiles:</h2>
        <p>
          RideNet is built around riders like you. It's simple: each RideNet member has a profile with
          a bio, race results, and a ride log. Everything else in RideNet is built on this
          basic set of data.
        </p>
      </div>

      <div class="spacer"></div>

      <img style="float:left;width:280px;margin:0px 40px 0px 0px" src="/images/about/team.jpg">
      <div style="margin:20px 0px;">
        <h2>Cycling Clubs &amp; Teams</h2>
        <p>
          Riders join together on RideNet to form teams. RideNet teams can be based on real-world cycling clubs
          or can be a loosely affiliated group of riders that just want to hang together. Each team has a
          site hosted within RideNet (http://<i>yourteam</i>.ridenet.net) with a home page, team roster page, and
          team race results page. Each team site can be branded with custom colors and graphics to give it a unique look.
        </p>
      </div>

      <div class="spacer"></div>
      
      <img style="float:right;width:250px;margin:0px 0px 0px 40px" src="/images/about/results.jpg">
      <div style="margin:10px 0px;">
        <h2>Race Results</h2>
        <p>
          After each race, you can post your result on RideNet along with a brief race report.
          Your result appears on the event page alongside other RideNet members. We also summarize your
          results on your profile page and your team's results on the team site. It's a perfect way for
          sponsors and friends to track how you and your team are doing during the season. We keep an
          archive of all race results posted on RideNet. So you can always find race results from years
          ago, preserved for building a complete race resume or just reminiscing.
        </p>
      </div>

      <div class="spacer"></div>

      <img style="float:left;width:325px;margin:0px 40px 0px 0px" src="/images/about/ridelog.jpg">
      <div style="margin:10px 0px;">
        <h2>Ride Log</h2>
        <p>
          We think simple is better. Our ride log tracks distance, time, type of ride, weather and a
          brief comment. The comment is the fun part. This is where you share with the world something
          interesting about your ride: where you went, what you saw, how nice (or bad) the weather was. It's a
          fun way to keep tabs on where your friends and teammates are riding and learn about other riders in
          your area.
        </p>
      </div>

      <div class="spacer"></div>

      <img style="float:right;width:275px;margin:0px 0px 0px 40px" src="/images/about/schedule.jpg">
      <div style="margin:20px 0px;">
        <h2>Regional Event Schedule</h2>
        <p>
          RideNet hosts a Regional Event Schedule listing promoted events, such as races and organized tours. You
          can filter events by state and event type so it's easy to find events you're interested in. The event
          schedule is shared and maintained by the members of RideNet. Any RideNet member can add events to the
          schedule.
        </p>
      </div>

      <div class="spacer"></div>

      <img style="float:left;width:250px;margin:0px 40px 0px 0px" src="/images/about/calendar.jpg">
      <div style="margin:30px 0px;">
        <h2>Community Ride Calendar</h2>
        <p>
          RideNet hosts a Community Ride Calendar that lists club rides, training rides, and casual gatherings.
          Rides are classified by ability level and can be filtered by location so it's easy to find rides that
          fit your interests. If you want other people to join your ride, posting it on RideNet is a good way
          to reach a large audience.
        </p>
      </div>

      <div class="spacer"></div>

      <img style="float:right;width:325px;margin:0px 0px 0px 40px" src="/images/about/commuting.jpg">
      <div style="margin:0px 0px">
        <h2>Advocacy</h2>
        <p>
          RideNet is working with Consider Biking and the 2 BY 2012 program to make a significant shift in the
          transportation mode of our city, Columbus Ohio. The goal of 2 BY 2012 is for every citizen of Central
          Ohio to ride to work or school 2 days a month by the Columbus bicentennial in 2012. Consider Biking
          uses RideNet to build corporate teams of bike commuters. These teams compete against each other to see
          whose employees ride to work the most. It's a fun way to build camaraderie and motivate people to keep
          riding. But there's a practical side as well. Consider Biking uses ride log data of participating teams
          to show city planners where people are riding and to make the case for better biking infrastructure in
          our city.
        </p>
      </div>
      
      <div class="spacer"></div>

      <h2>RideNet is Free</h2>
      <p>
        RideNet is free for individuals and teams. Everything you see here is free and always will be.
      </p>

    </div>  
  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>