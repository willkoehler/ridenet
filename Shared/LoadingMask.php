<?
//----------------------------------------------------------------------------------
//  PageLoadingMask()
//
//  Page loading mask. This should come before all script and css files so that it
//  is displayed immediately when page starts loading. The loading mask will be
//  displayed while the script and css files are downloaded. Since the page
//  loader comes before .css includes, we define the style manually in each element.
//
//  PARAMETERS:
//    message   - message to display
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function PageLoadingMask($message)
{
?>
  <div id="page-loading-mask" style="width:100%;height:100%;background:#c3daf9;position:absolute;z-index:50000;left:0;top:0;">&#160;</div>
    <div style="width:100%;position:absolute; left: 0px; top:0px; z-index:50001">
    <table id="page-loading" style="position:relative; top:250px; margin: 0 auto; border:1px solid #a3bad9; background:white" border=0 cellpadding=0 cellspacing=0>
      <tr height=35>
        <td style="padding-left:10px;"><img src="<?=SHAREDBASE_URL?>images/loading.gif"></td>
        <td style="color:#003366; font:bold 13px arial,helvetica; padding:0px 20px 0px 10px; text-align:left;"><?=$message?></td>
      </tr>
    </table>
    </div>
<?
}
?>