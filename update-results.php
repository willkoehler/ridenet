<?
require("script/app-master.php");
$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
CheckLoginAndRedirect();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?BuildPageTitle($oDB, $pt, "Edit Your Results")?></title>
  <link href="/styles.pcs?T=<?=$pt?>" rel="stylesheet" type="text/css" />
  <script type="text/javascript">
  function checkFields() 
  {
      var form = document.getElementById('results-form');
      if(form.RaceID.value==0)
      {
          alert("You must select an event");
          form.RaceID.focus();
          return(false);
      }
      if(form.CategoryID.value==0)
      {
          alert("You must select a field");
          form.CategoryID.focus();
          return(false);
      }
      if(form.PlaceID.value==0)
      {
          alert("You must select a place");
          form.PlaceID.focus();
          return(false);
      }
      return(true);
  }
  </script>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
</head>

<body class="oneColFixHdr">
<?IE6Check();?>   <!--Display warning message for IE6 and older -->

<div id="container">
  <div id="header">
    <?InsertPageBanner($oDB, $pt)?>
    <?InsertMainMenu($oDB, $pt, "YourProfile")?>
    <?InsertMemberMenu($oDB, $pt, "UpdateResults")?>
  </div>
  
  <div id="mainContent">
    <h1>Your Results</h1>
    <h2>You may edit results and race reports up to 14 days after you post them</h2>
    <div align="center">
      <form method="post" action="action/update-results.php" onsubmit="return(checkFields())" id="results-form">
        <table id=post-results border="0" cellpadding="0" cellspacing="2">
          <tr>
            <td class="header">Event</td>
            <td class="header">Field</td>
            <td class="header">Place</td>
          </tr>
          <tr align=left>
            <td><select name="RaceID" style="width:420px">
              <option value="0">select an event...</option>
<?            $sql = "SELECT RaceID, RaceDate, EventName, City, StateAbbr
                      FROM event LEFT JOIN ref_states USING (StateID) 
                      WHERE DATEDIFF(RaceDate,NOW()) BETWEEN -365 AND 0 AND RideTypeID NOT IN (4,5) AND Archived=0
                      ORDER BY RaceDate DESC";
              $rs = $oDB->query($sql, __FILE__, __LINE__);
              while(($record=$rs->fetch_array())!=false) { ?>
                <option value="<?=$record['RaceID']?>">
                  <?=date_create($record['RaceDate'])->format("M j")?> | <?=LimitString($record['City'],25)?>, <?=$record['StateAbbr']?> | <?=LimitString($record['EventName'],60)?>
                </option>
              <? } ?>
            </select></td>
            <td><select size="1" name="CategoryID">
                <option value="0">select a field...</option>
<?            $rs = $oDB->query("SELECT * FROM ref_race_category", __FILE__, __LINE__);
              while(($record=$rs->fetch_array())!=false) { ?>
                <option value="<?=$record['CategoryID']?>"><?=$record['CategoryName']?></option>
              <? } ?>
            </select></td>
            <td><select size="1" name="PlaceID">
                <option value="0">select...</option>
<?            $rs = $oDB->query("SELECT * FROM ref_placing WHERE PlaceID NOT IN (26,23) ORDER BY PlaceOrdinal", __FILE__, __LINE__);
              while(($record=$rs->fetch_array())!=false) { ?>
                <option value="<?=$record['PlaceID']?>"><?=$record['PlaceName']?></option>
              <? } ?>
            </select></td>
          </tr>
          <tr>
            <td class="header" style="padding-top:10px;text-align:center" colspan=3>Race Report</td>
          </tr>
          <tr>
            <td colspan=3 align="center">
              <textarea name="Report" style="width:650px;height:175px"></textarea>
            </td>
          </tr>
          <tr>
            <td colspan=3 align="left" width=650>
              <p class=text75 style="font: 11px verdana;line-height: 14px;">A race report should tell the story of the race from your
              perspective. Use humor or drama to make it interesting. Ultimately we are all on the *same* team. <b>Be respectful of
              your fellow competitors</b> and keep in mind this site is visited by riders of all ages. Inappropriate or abusive
              language will be removed.</p>
            </td>
          </tr>
          <tr>
            <td colspan="4"><p align="center"><input type="Submit" style="width:100px" value="Save Result"></p></td>
          </tr>
        </table>
        <br><br>
        <table id="post-results" cellpadding=0 cellspacing=0><tr>
          <td class=header>Your Results - Year:</td>
          <td style="padding-left:8px">
<?          // if race year is passed in use it. Otherwise default to current year
            $ShowYear = (isset($_REQUEST['Year'])) ? SmartGetInt("Year") : date("Y");?>
            <SELECT style='font-size=8pt' name='Year' onChange="window.location.href=('update-results.php?Year=' + options[selectedIndex].value)">
            <?for($year=START_YEAR; $year <= date("Y"); $year++) {?>
              <OPTION value='<?=$year?>' <?if($year==$ShowYear) {?>selected<? } ?>><?=$year?>
            <? } ?>
            </SELECT>
          </td>
        </tr></table>
        <table id="results" border=0 cellspacing=0 cellpadding=0>
          <tr align=left>
            <td class=header-sm width="50">Actn</td>
            <td class=header-sm width="100">Date</td>
            <td class=header-sm width="320">Event</td>
            <td class=header-sm width="75">Place</td>
            <td class=header-sm width="100">Category</td>
          </tr>
          <tr><td class="table-spacer" style="height:2px" colspan="5">&nbsp;</td></tr>
<?        $sql = "SELECT PlaceName, CategoryID, CategoryName, RaceID,
                         RiderID, EventName, RaceDate, PlaceOrdinal, PlaceID, Report,
                         DATEDIFF(NOW(), results.DateAdded) AS ResultsAge
                  FROM results
                  LEFT JOIN race_report USING (RaceID, RiderID)
                  LEFT JOIN event USING (RaceID)
                  LEFT JOIN ref_placing USING (PlaceID)
                  LEFT JOIN ref_race_category USING (CategoryID)
                  WHERE Year(RaceDate) = $ShowYear AND RiderID=" . GetUserID() . " AND event.Archived=0
                  ORDER by RaceDate DESC";
          $rs = $oDB->query($sql, __FILE__, __LINE__);
          if($rs->num_rows==0) { ?>
            <tr class="data"><td colspan=5>No results found for <?=$ShowYear?></td></tr>          
<?        }
          while(($record=$rs->fetch_array())!=false) { ?>
            <tr>
              <td class=data>
              <!-- show edit race report option if there is a race report and result was posted less than 14 days ago -->
              <?if($record['ResultsAge'] <= 14 && !is_null($record['Report'])) { ?>
                <span class="edit-report-btn">[<a title="Edit Race Report" href="edit-race-report.php?RaceID=<?=$record['RaceID']?>&RiderID=<?=$record['RiderID']?>" class="edit-report-btn">R</a></span><span class="edit-report-btn">]</span>
              <? } ?>
              <!-- show delete option if results was posted less than 14 days ago -->
              <?if($record['ResultsAge'] <= 14) { ?>
                <span class="delete-x-btn">[<a title="Delete this result" href="action/delete-result.php?RaceID=<?=$record['RaceID']?>&RiderID=<?=$record['RiderID']?>&CategoryID=<?=$record['CategoryID']?>" class="delete-x-btn">X</a></span><span class="delete-x-btn">]</span>
              <? } else { ?>
                &nbsp;&nbsp;-
              <? } ?>
              </td>
              <td class=data><?=date_create($record['RaceDate'])->format("n/j/Y")?></td>
              <td class=data><div class=ellipses style="width:310px">
                <a href="results-detail.php?RaceID=<?=$record['RaceID']?>&RiderID=<?=$record['RiderID']?>">
                  <?=$record['EventName']?>
                </a>
              </div></td>
              <td class=data><?=$record['PlaceName']?></td>
              <td class=data><?=$record['CategoryName']?></td>
            </tr>
          <? } ?>
          <tr><td class="table-spacer" style="height:5px" colspan="5">&nbsp;</td></tr>
          <tr><td class="table-divider" colspan="5">&nbsp;</td></tr>
          <tr><td class="table-spacer" style="height:3px" colspan="5">&nbsp;</td></tr>
          <tr><td colspan=5 align=left>
            <span class="delete-x-btn">[X]</span><span class="data"> - Delete Result (does not delete race report)</span><br>
            <span class="edit-report-btn">[R]</span><span class="data"> - Edit Race Report</span>
          </td></tr>
          <tr><td class="table-spacer" style="height:5px" colspan="5">&nbsp;</td></tr>
          <tr><td class="table-divider" colspan="5">&nbsp;</td></tr>
          <tr><td class="table-spacer" style="height:10px" colspan="5">&nbsp;</td></tr>
          <tr><td colspan=5>
            <div align=center>
              <input type="button" value="Create Race Resume" style="width:150px" onClick="window.open('race-resume.php?RiderID=<?=GetUserID()?>', 'resume')">
            </div>
          </td></tr>
        </table>
      </form>
    </div>
  </div><!-- end #mainContent -->

  <div id="footer">
    <?InsertPageFooter()?>
  </div><!-- end #footer -->

</div><!-- end #container -->

</body>
</html>