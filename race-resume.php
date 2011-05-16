<?
require("script/app-master.php");
$oDB = oOpenDBConnection();
$pt = GetPresentedTeamID($oDB);   // determine the ID of the team currently being presented
CheckLoginAndRedirect();
?>

<html>
<head>
  <title>Race Resume</title>
  <link rel='stylesheet' type='text/css' href='Echelon.css'>
<!-- Insert tracker for Google Analytics -->
  <?InsertGoogleAnalyticsTracker()?>
</head>

<?
$riderID = SmartGetInt("RiderID");
$riderName = $oDB->DBLookup("CONCAT(FirstName, ' ', LastName)", "rider", "RiderID=$riderID");
$roadCategory = $oDB->DBLookup("RiderType", "rider LEFT JOIN ref_rider_type USING (RiderTypeID)", "RiderID=$riderID");
?>

<body topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">

  <table border="0">
    <tr>
      <td colspan=4 align="center"><font face="Verdana" size="5"><b><?=$riderName?></b></font></td></tr>
    <tr><td colspan=4 align="center"><font face="Verdana" size="3"><?=$roadCategory?></font></td></tr>
    <tr><td colspan=4>&nbsp;</td></tr>
   
    <tr font face="Verdana" size="3">
      <th align=left width=90>Date</th>
      <th align=left width=400>Race</th>
      <th align=left width=90>Place</th>
      <th align=left width=90>Category</th> 
    </tr>

<?  $sql="SELECT YEAR(RaceDate) AS Year, RaceDate, PlaceName, CategoryName, EventName
          FROM results
          LEFT JOIN event USING (RaceID)
          LEFT JOIN ref_placing USING (PlaceID)
          LEFT JOIN ref_race_category USING (CategoryID)
          WHERE RiderID=$riderID
          ORDER by RaceDate";
    $rs = $oDB->query($sql, __FILE__, __LINE__);
    $previousYear = 0;
    while(($record=$rs->fetch_array())!=false)
    {
      if($record['Year']!=$previousYear)
      {
        $previousYear = $record['Year']; ?>
        <tr>
          <td colspan=4 bgcolor="black" align="center"><font face="Verdana" color="white" size="4"><b><?=$record['Year']?></b></font></td>
        </tr>
      <? } ?>
      <tr>
        <td><font face="Verdana" size="2"><?=date_create($record['RaceDate'])->format("n/j/Y")?></font></td>
        <td><font face="Verdana" size="2"><?=$record['EventName']?></font></td>
        <td><font face="Verdana" size="2"><?=$record['PlaceName']?></font></td>
        <td><font face="Verdana" size="2"><?=$record['CategoryName']?></font></td>
      </tr>
    <? } ?>
  </table>
</body>
