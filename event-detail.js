// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
// --- Turn on validation errors beside the field globally and enable quick tips that will
// --- popup tooltip when mouse is hovered over field
    Ext.form.Field.prototype.msgTarget = 'qtip';
    Ext.QuickTips.init();
// --- Create dialogs
    g_updateDialog = new C_PostUpdateDialog();
    g_eventDialog = new C_EventDialog();
// --- add listeners to show/hide delete buttons
    addHoverListeners();
});


function clickEditEvent(raceID)
{
    g_eventDialog.show({
        raceID: raceID,
        animateTarget: 'edit-btn' + raceID,
        callback: function() { window.location.reload(); }
    });
}

function clickPostUpdate(id, params)
{
    g_updateDialog.show({
        animateTarget: id,
        callback: updateEventUpdates,
        riderID: params.riderID,
        racingTeamID: params.racingTeamID,
        postedToID: params.postedToID,
        postType: 2,
        riderName: params.riderName,
        teamName: params.teamName,
        title: 'Update for ' + params.postingTo
    });
}

function clickDeleteMessage(messageID)
{
    Ext.Msg.show({
        title: "Confirm Delete",
        msg: "Are you sure you want to delete this message?",
        fn: function(btn) { if(btn=='yes') {
        // --- Mask this page and post delete request
            Ext.get('event-updates').mask("Deleting");
            Ext.Ajax.request({
                url: '/data/delete-message.php',
                params: {ID: messageID},
                success: handleDeleteSuccess,
                failure: handleDeleteFailure,
                scope: this
            });
        } },
        scope: this,
        buttons: {yes:'&nbsp;Delete&nbsp;', no:'Cancel'}
    });
}

function handleDeleteSuccess(response, options)
{
// --- decode JSON response string and check status of delete
    var result = Ext.decode(response.responseText);
    if(result.success == false)
    {
        Ext.get('event-updates').unmask();
        Ext.Msg.alert("Delete Message Failed", "Error deleting message: " + result.message);
    }
    else
    {
        updateEventUpdates();
    }
}

function handleDeleteFailure(response)
{
    Ext.get('event-updates').unmask();
    Ext.Msg.alert("Delete Message Failed", "Error deleting message. Server did not respond");
}

function addHoverListeners()
{
    // Add listeners to show delete button when mouse hovers over a posted message
    // target can vary with mouseenter events. Sometimes it's a child of the intended
    // object. However "this" is always the element the event was registered for
    var wrappers = Ext.select('div.ridenet-wall .wrapper')
    wrappers.on('mouseenter', function(event, target) {
        var deletex=(Ext.fly(this).down(".delete-x"));
        if(deletex) deletex.show();
    })
    wrappers.on('mouseleave', function(event, target) {
        var deletex=(Ext.fly(this).down(".delete-x"));
        if(deletex) deletex.hide();
    })
}

function getMoreUpdates(length)
{
    g_eventUpdatesLength += length;
    updateEventUpdates();
}

function updateEventUpdates()
{
    Ext.get('event-updates').mask("Updating");
    Ext.Ajax.request({
        url: '/dynamic-sections/event-wall.php?pb&RaceID=' + g_raceID + '&l=' + g_eventUpdatesLength,
        success: function(response, options)
        {
            Ext.get('event-updates').update(response.responseText);
            addHoverListeners();    // add listeners to hide/show delete buttons
            Ext.get('event-updates').unmask();
        }
    });
}

function C_Attendance()
{
    this.form = null;
    
    // -------------------------------------------------------------------------------------------
    //  Create the event attendance form.
    //  params object has the following parameters:
    //      parent          - id of div to render form in
    //      raceID          - id of the event
    //      attendanceID    - id of event_attendance record, or -1 if no record exist
    //      attending       - true = rider is attending this event
    //      notify          - true = rider will be notified of updates posted to this event
    // -------------------------------------------------------------------------------------------
    this.create = function(params)
    {
        this.holder = params.parent;
        this.raceID = params.raceID;

        this.form = new Ext.FormPanel({
            baseCls: 'x-plain',     // (gives panel a transparent background)
            cls: 'centered',        // center this panel on the page
            url: '/data/post-event-attendance.php',          // URL used to submit results of form
            bodyStyle:'padding-top:5px',
            hideLabels: 'true',
            width: 330,
            layout: 'column',
            baseParams: { AttendanceID: params.attendanceID, RaceID: params.raceID },
            items: [{
                xtype: 'container',
                width: 100,
                style: 'margin-top:11px',
                html: '<b>Are you going?</b>'
            },{
                xtype: 'container', width: 160, layout:'form', cls: 'compact-form', items: [{
            // === Attending Checkbox ===
                    xtype: 'checkbox',
                    hideLabel: true,
                    name: 'Attending',
                    checked: params.attending,
                    boxLabel: '&nbsp;I\'ll be there',
                    listeners: { scope: this, check: function(cb, checked) {
                        // by defauly chance the nofity check box to match the attending checkbox
                        this.form.getForm().findField("Notify").setValue(checked);
                        Ext.getCmp('save-status-btn').enable();
                    }}
                },{
            // === Email Updates Checkbox ===
                    xtype: 'checkbox',
                    hideLabel: true,
                    name: 'Notify',
                    checked: params.notify,
                    boxLabel: '&nbsp;Email me event updates',
                    listeners: { scope: this, check: function(cb, checked) {
                        Ext.getCmp('save-status-btn').enable();
                    }}
                }]
            },{
                xtype: 'container', width: 60, items: [{
                    xtype: 'button',
                    style: 'margin-top: 10px',
                    minWidth: 60,
                    disabled: true,
                    id: 'save-status-btn',
                    text: 'Save',
                    scope: this,
                    handler: this.onClickSave
                }]
            }]
        });
    // --- render the form
        this.form.render(this.holder);
    }

    this.onClickSave = function()
    {
    // --- disable entire event details block containing the form
        Ext.fly(this.holder).up(".block-table").mask("Please Wait...");
    // --- submit form data
        this.form.getForm().submit({ reset: false, success: this.onPostSuccess, failure: this.onPostFailure, scope: this });
    }

    this.onPostSuccess = function(form, action)
    {
        Ext.getCmp('save-status-btn').disable();
        // Save attendance ID for next save operation
        this.form.getForm().baseParams.AttendanceID = action.result.AttendanceID;
        // Update list of attending riders. Hide list if there are no riders attending
        Ext.Ajax.request({
            url: '/dynamic-sections/event-attendance.php?pb&RaceID=' + this.raceID,
            scope: this,
            success: function(response, options)
            {
                Ext.fly('attending-holder').update(response.responseText, true);    // true-->eval scripts in response text to generate new rider callouts
                Ext.fly('attending-holder').up("tr").setStyle("display", (response.responseText) ? "table-row" : "none");
                Ext.fly(this.holder).up(".block-table").unmask();
            }
        });
    }

    this.onPostFailure = function(form, action)
    {
        Ext.fly(this.holder).up(".block-table").unmask();
        switch(action.failureType) {
            case Ext.form.Action.SERVER_INVALID:
            // --- failure message returned from code on the server
                Ext.Msg.alert("RideNet", "Error updating status: " + action.result.message);
                break;
            case Ext.form.Action.CONNECT_FAILURE:
            // --- Failed to connect to server
                Ext.Msg.alert("RideNet", "Error updating status: Server did not respond");
                break;
        }
    }
}

