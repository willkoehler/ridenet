// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
// --- Turn on validation errors beside the field globally and enable quick tips that will
// --- popup tooltip when mouse is hovered over field
    Ext.form.Field.prototype.msgTarget = 'qtip';
    Ext.QuickTips.init();
// --- Create dialogs
    g_profileDialog = new C_ProfileDialog();
    g_rideLogDialog = new C_RideLogDialog();
    g_changeTeamsDialog = new C_ChangeTeamsDialog();
});


function clickEditProfile()
{
    g_profileDialog.show({
        animateTarget: 'edit-profile'
    });
}

function clickAddRide(id)
{
    g_rideLogDialog.show({
        animateTarget: id,
        callback: updateStatsAndLog
    });
}

function clickEditRide(rideLogID)
{
    g_rideLogDialog.show({
        rideLogID: rideLogID,
        makeCopy: false,
        animateTarget: 'edit-btn' + rideLogID,
        callback: updateStatsAndLog
    });
}

function clickCopyRide(rideLogID)
{
    g_rideLogDialog.show({
        rideLogID: rideLogID,
        makeCopy: true,
        animateTarget: 'copy-btn' + rideLogID,
        callback: updateStatsAndLog
    });
}

function updateStatsAndLog(result, stats)
{
    if(result)
    {
        // without call to String(), 0s are converted to empty strings
        Ext.fly('ytd-days').update(String(stats.YTDDays));
        Ext.fly('ytd-miles').update(String(stats.YTDMiles));
        Ext.fly('cedays-month').update(String(stats.CEDaysMonth));
    }
    updateRideLog();
}

function getMore(length)
{
    g_rideLogLength += length;
    updateRideLog();
}

function updateRideLog()
{
    // we need to use "ride-log-masker" div to work around IE7 bug. IE7 has problems with masks on divs with position:relative
    Ext.get('ride-log-masker').mask("Updating");
    var editable = g_editable ? "&edit" : "";
    Ext.Ajax.request({
        url: '/dynamic-sections/ride-log.php?pb&RiderID=' + g_riderID + '&l=' + g_rideLogLength + editable,
        success: function(response, options)
        {
            Ext.get('ride-log-holder').update(response.responseText);
            Ext.get('ride-log-masker').unmask();
        }
    });
}
