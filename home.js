// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
// --- Turn on validation errors beside the field globally and enable quick tips that will
// --- popup tooltip when mouse is hovered over field
    Ext.form.Field.prototype.msgTarget = 'qtip';
    Ext.QuickTips.init();
// --- Create dialogs
    g_messageDialog = new C_PostMessageDialog();
// --- add listeners to show/hide delete buttons
    addHoverListeners();
});

function clickPostMessage(id, params)
{
    g_messageDialog.show({
        animateTarget: id,
        callback: updateTeamWall,
        riderID: params.riderID,
        racingTeamID: params.racingTeamID,
        riderName: params.riderName,
        teamName: params.teamName,
        title: 'Message for ' + params.postingTo
    });
}

function clickDeleteMessage(messageID)
{
    Ext.Msg.show({
        title: "Confirm Delete",
        msg: "Are you sure you want to delete this message?",
        fn: function(btn) { if(btn=='yes') {
        // --- Mask this page and post delete request
            Ext.get('team-wall').mask("Deleting");
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
        Ext.get('team-wall').unmask();
        Ext.Msg.alert("Delete Message Failed", "Error deleting message: " + result.message);
    }
    else
    {
        updateTeamWall();
    }
}

function handleDeleteFailure(response)
{
    Ext.get('team-wall').unmask();
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

function getMore(length)
{
    g_teamWallLength += length;
    updateTeamWall();
    // log an event in Google Analytics
    _gaq.push(['_trackEvent', 'Action', 'More', 'TeamWall-'+g_teamWallLength]);
}

function updateTeamWall()
{
    Ext.get('team-wall').mask("Updating");
    Ext.Ajax.request({
        url: '/dynamic-sections/team-wall.php?pb&TeamID=' + g_pt + '&l=' + g_teamWallLength,
        success: function(response, options)
        {
            Ext.get('team-wall').update(response.responseText);
            addHoverListeners();    // add listeners to hide/show delete buttons
            Ext.get('team-wall').unmask();
        }
    });
}
