// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
// --- Turn on validation errors beside the field globally and enable quick tips that will
// --- popup tooltip when mouse is hovered over field
    Ext.form.Field.prototype.msgTarget = 'qtip';
    Ext.QuickTips.init();
});


function getMore(length)
{
    g_rideWallLength += length;
    updateRideWall();
    // log an event in Google Analytics
    _gaq.push(['_trackEvent', 'Action', 'More', 'RidingWall-'+g_rideWallLength]);
}

function updateRideWall()
{
    Ext.get('riding-wall').mask("Updating");
    Ext.Ajax.request({
        url: '/dynamic-sections/riding.php?pb&l=' + g_rideWallLength,
        success: function(response, options)
        {
            Ext.get('riding-wall').update(response.responseText);
            Ext.get('riding-wall').unmask();
        }
    });
}
