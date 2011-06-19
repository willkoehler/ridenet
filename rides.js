// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
// --- Turn on validation errors beside the field globally and enable quick tips that will
// --- popup tooltip when mouse is hovered over field
    Ext.form.Field.prototype.msgTarget = 'qtip';
    Ext.QuickTips.init();
// --- Create dialogs
    g_locationDialog = new C_LocationDialog();
    g_rideDialog = new C_RideDialog();
});


function clickAddRide(id)
{
    g_rideDialog.show({
        animateTarget: id,
        callback: updateRideCalendar
    });
}

function clickEditRide(calendarID)
{
    g_rideDialog.show({
        calendarID: calendarID,
        makeCopy: false,
        animateTarget: 'edit-btn' + calendarID,
        callback: updateRideCalendar
    });
}

function clickCopyRide(calendarID)
{
    g_rideDialog.show({
        calendarID: calendarID,
        makeCopy: true,
        animateTarget: 'copy-btn' + calendarID,
        callback: updateRideCalendar
    });
}

function getMore(weeks)
{
    g_calendarWeeks += weeks;
    updateRideCalendar();
}

function updateRideCalendar()
{
    Ext.get('container').mask("Updating");
    var editable = g_HTMLRequest['edit'] ? '&edit' : '';
    Ext.Ajax.request( {url: '/dynamic-sections/rides.php?pb&T=' + g_pt + '&w=' + g_calendarWeeks + editable, success: function(response, options) {
        Ext.get('ride-calendar-holder').update(response.responseText);
        Ext.get('container').unmask();
    }});
}
