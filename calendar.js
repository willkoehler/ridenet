// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
var helptext = "<div class=help-title>Community Ride Calendar</div>\
               <div class=help-body>\
               The Community Ride Calendar is shared and maintained by all the teams on RideNet. It lists\
               training rides, group rides, and casual gatherings in your area. \
               <ul class=help>\
               <li class=help>Click the \"Set Location...\" button to find rides in your area.\
               <li class=help>To add a ride to the calendar, click the \"+&nbsp;Add&nbsp;Ride\" link on\
               this page. To edit a ride, click the \"Edit\" link next to the ride. To make a copy of a ride,\
               click the \"Copy\" link next to the ride.\
               <li class=help>Rides are categorized by class to help you find rides matching your ability level.\
               See the class key at the bottom of the page for more information.\
               <li class=help>Click the ride name for more information including a list of riders that plan on attending. You\
               can add your name to the list of riders attending.\
               </ul></div>";
// --- Turn on validation errors beside the field globally and enable quick tips that will
// --- popup tooltip when mouse is hovered over field
    Ext.form.Field.prototype.msgTarget = 'qtip';
    Ext.QuickTips.init();
// --- Create dialogs
    g_locationDialog = new C_LocationDialog();
    g_helpDialog = new C_HelpDialog(helptext);
    g_rideDialog = new C_RideDialog();
// --- add listners to show/hide copy buttons
    addHoverListeners();
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


function addHoverListeners()
{
    // Add listeners to show copy button when mouse hovers over a ride row
    // target can vary with mouseenter events. Sometimes it's a child of the intended
    // object. However "this" is always the element the event was registered for
    var rows = Ext.select('tr[riderow]')
    rows.on('mouseenter', function(event, target) {
        var copybtn=null;
        if(copybtn=Ext.fly(this).child("span[copybtn]")) {
            copybtn.show();
        }
    })
    rows.on('mouseleave', function(event, target) {
        var copybtn=null;
        if(copybtn=Ext.fly(this).child("span[copybtn]")) {
            copybtn.hide();
        }
    })
}

function getMore(weeks)
{
    g_calendarWeeks += weeks;
    updateRideCalendar();
}

function updateRideCalendar()
{
    Ext.get('container').mask("Updating");
    Ext.Ajax.request( {url: 'dynamic-sections/ride-calendar.php?pb&w=' + g_calendarWeeks + '&T=' + g_teamFilter, success: function(response, options) {
        Ext.get('ride-calendar-holder').update(response.responseText);
        addHoverListeners();    // add listeners to hide/show copy buttons
        Ext.get('container').unmask();
    }});
}
