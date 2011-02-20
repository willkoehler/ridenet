<?
require("script/app-master.php");
$oDB = oOpenDBConnection();
RecordPageView($oDB);
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, $pt, "Event Schedule")?></title>
  <link href="styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
</head>

<body class="oneColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "Schedule")?>
  </div>
  
<?$vRaceID = SmartGetInt("RaceID");
  $rs = $oDB->query("SELECT * FROM event WHERE RaceID = $vRaceID", __FILE__, __LINE__);
  $record = $rs->fetch_array();?>

  <div id="mainContent">
    <h1><?=$record['EventName']?></h1>
    <h2><?=date_create($record['RaceDate'])->format("F j, Y")?></h2>
    <div align=center>
      <div style="height:15px"><!--vertical spacer--></div>
<?    $sql = "SELECT RaceCategory, FirstName, LastName, RiderID, RaceID, TeamName, Domain
              FROM race_attendance
              LEFT JOIN rider USING (RiderID)
              LEFT JOIN teams ON (RacingTeamID = TeamID)
              WHERE RaceID=$vRaceID
              ORDER BY RaceCategory, LastName, FirstName";
      $rs = $oDB->query($sql, __FILE__, __LINE__);?>
      <div class="block-table" style="width:520px">
        <div class="header">Here's Who is Going so far</div>
        <table id="results" cellpadding=0 cellspacing=0 border=0 align=center>
        <?if(($record = $rs->fetch_array())!=false) { ?>
          <tr>
            <td class="header-sm" width=10>&nbsp;</td>
            <td class="header-sm" width=190>Rider</td>
            <td class="header-sm" width=220>Team</td>
            <td class="header-sm" style="text-align:center">Category</td>
          </tr>
          <? do { ?>
            <tr>
              <td class="data" width=10>&nbsp;</td>
              <td class="data"><div class=ellipses style="width:180px">
                <a href="<?=BuildTeamBaseURL($record['Domain'])?>/profile.php?RiderID=<?=$record['RiderID']?>">
                    <?=$record['FirstName'] . " " . $record['LastName']?>
                </a>
              </div></td>
              <td class="data"><div class=ellipses style="width:210px">
                <?=$record['TeamName']?>
              </div></td>
              <td class="data" style="text-align:center"><?=$record['RaceCategory']?></td>
            </tr>
          <? } while(($record = $rs->fetch_array())!=false) ?>
        <? } else { ?>
          <tr><td class="data" style="text-align:center">No riders yet</td></tr>
        <? } ?>
        </table>
      </div>
      <br>
      <div class=block-table style="width:220px">
        <div class="header">Do You Plan on Attending?</div>
        <? if(CheckLogin()) { ?>
          <form action="action/event-attendance.php?RaceID=<?=$vRaceID?>" method=post>
            <table id="event-detail" cellspacing=0 cellpadding=2>
              <tr>
                <td class=label><b>I</b></td>
                <td>
                  <select size="1" name="Attend">
                    <option value="Y" selected>Will</option>
                    <option value="N">Will not</option>
                  </select>
                </td>
                <td class=label><b>be there</b></td>  
              <tr>
            </table>
            <div style="height:5px"><!--vertical spacer--></div>
            <table id="event-detail" cellspacing=0 cellpadding=1>
              <tr>
                <td class=label><b>Racing category&nbsp;</b></td>
                <td>
                  <select size="1" name="Category">
                    <option value="A" selected>A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="J">Juniors</option>
                    <option value="W">Women</option>          
                  </select>
                </td>
              </tr>
            </table>
            <div style="height:7px"><!--vertical spacer--></div>
            <p style="font:11px arial" align=center>
              Category A = USCF Cat 1-2<br>
              Category B = USCF Cat 2,3,4 or 3-4<br>
              Category C = USCF Cat 4-5<br>
            </p>
            <input type="submit" value="Submit" name="btnSubmit">
            <div style="height:5px"><!--vertical spacer--></div>
          </form>
        <? } else { ?>
          <span style="font:12px verdana"><a href="login.php?Goto=<?=urlencode($_SERVER['REQUEST_URI'])?>">Login Required</a></span>
        <? } ?>
      </div>
    </div>
    <div style="height:15px"><!--vertical spacer--></div>
  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>