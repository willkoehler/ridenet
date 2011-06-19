// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
// --- Turn on validation errors beside the field globally and enable quick tips that will
// --- popup tooltip when mouse is hovered over field
    Ext.form.Field.prototype.msgTarget = 'qtip';
    Ext.QuickTips.init();
// --- Create dialogs
    g_filterDialog = new C_FilterDialog();
    g_eventDialog = new C_EventDialog();
});


function scrollToMonth(month)
{
    var body = Ext.getBody();
    var scrollMax = body.getHeight() - body.getViewSize().height;
    var scrollTo = Math.min(Ext.get('M' + month).getY() - 100, scrollMax);
    if (scrollTo > 0)
    {
        body.animate(
            { scroll: {to: [0, scrollTo]} },
            1.2,             // animation duration
            null,
            'easeOutStrong', 
            'scroll'
        );
    }
}

function clickAddEvent(id)
{
    g_eventDialog.show({
        animateTarget: id,
        callback: updateEventSchedule
    });
}

function clickEditEvent(raceID)
{
    g_eventDialog.show({
        raceID: raceID,
        makeCopy: false,
        animateTarget: 'edit-btn' + raceID,
        callback: updateEventSchedule
    });
}

function clickCopyEvent(raceID)
{
    g_eventDialog.show({
        raceID: raceID,
        makeCopy: true,
        animateTarget: 'copy-btn' + raceID,
        callback: updateEventSchedule
    });
}

function clickEventFilter(el)
{
    var checkboxes = Ext.select("#filter-holder input");
    var allSelected = true;
    // build list of event types that are checked
    var eventTypes = "";
    checkboxes.each(function(cb) {
        if(cb.dom.checked==true)
        {
            eventTypes += (cb.id.substring(6) + ",");
        }
        else
        {
            allSelected = false;
        }
    });
    if(eventTypes=="")
    {
        // no event types selected, warn user and re-check item was just cleared
        Ext.Msg.alert("Events", "You must select at least one event type");
        el.checked=true;
    }
    else
    {
        eventTypes = (allSelected) ? "All" : "(" + eventTypes.substr(0, eventTypes.length-1) + ")";
        // save event list filter in cookie and update event schedule
        var expires = new Date(new Date().getTime()+(1000*60*60*24*365*2)).toGMTString();   // expire in 2 years
        document.cookie = "ScheduleFilterTypes=" + eventTypes + "; expires=" + expires + "; domain=" + g_domainRoot;
        updateEventSchedule();
    }
    _gaq.push(['_trackEvent', 'Action', 'Filter Events', 'Types: ' + eventTypes]);   // log event in Google Analytics
}

function updateEventSchedule(mask)
{
    if(mask) Ext.get('container').mask("Updating");
    var editable = g_HTMLRequest['edit'] ? '&edit' : '';
    Ext.Ajax.request( {url: '/dynamic-sections/events.php?pb&T=' + g_pt + '&Y=' + g_showYear + editable, success: function(response, options) {
        Ext.get('event-schedule-holder').update(response.responseText);
        if(mask) Ext.get('container').unmask();
    }});
}

function C_FilterDialog()
{
    this.window = null;

    // -------------------------------------------------------------------------------------------
    //  Show calendar/event filter dialog.
    //  params object has the following parameters:
    //      animateTarget   - id of HTML target to animate opening/closing window
    //      ypos            - y position of the window
    // -------------------------------------------------------------------------------------------
    this.show = function(params)
    {
        if( ! this.window)
        {
            // Remote lookup for Zip Codes
            this.dsZipCodeLookup = new Ext.data.JsonStore({
                root: 'results',                // results array is returned in this property
                totalProperty: 'rowcount',      // total number of rows is returned in this property
                idProperty: 'ZipCodeID',        // defines the primary key for the results
                fields: [
                    {name: 'id', type: 'int'},
                    {name: 'text'}
                ],
                proxy: new Ext.data.HttpProxy({ url: '/data/lookup-zip-code.php' })
            });

            var sm1 = new Ext.grid.CheckboxSelectionModelFS({ width:25 });
            var sm2 = new Ext.grid.CheckboxSelectionModelFS({ width:25 });

            this.form = new Ext.form.FormPanel({
                baseCls: 'x-plain',     // (gives panel a gray background - by default panels have white backgrounds)
                labelAlign: 'top',
                bodyStyle:'padding:5px 5px 0',
                buttonAlign:'center',
                baseParams: { },    // additional parameters passed to post request
                items: [{
                    xtype:'container', title:'Regional Event Schedule', layout:'column', items: [{
                    // === State Selection ===
                        xtype:'container', layout:'form', width:220, items: [{
                            xtype: 'grid',
                            fieldLabel: 'Show events in these states',
                            cls: 'compact-grid',   // render grid more compactly
                            id: 'state-grid',
                            bodyStyle: 'border: 1px solid silver',
                            ds: new Ext.data.SimpleStore({ fields: ['id', 'name', 'abbr'], id: 0, data: stateLookup}),
                            columns: [
                                sm1,         // this renders the column with the row-selection checkboxes
                                {header: 'Select States', width: 100, dataIndex: 'name', sortable: true, id: 'autoexpand'}
                            ],
                            sm: sm1,
                            autoExpandColumn: 'autoexpand',
                            width: 200,
                            height: 300
                        }]
                    }] // end of fieldset
                },{
            // === Message Field (just above buttons) ===
                    xtype: 'container',
                    id: 'status-msg',
                    style: 'display:none',   // start off hidden initially
                    cls: 'form-status'
                }],

                buttons: [{
                    text: 'Update',
                    handler: this.saveButtonClick,
                    scope: this
                },{
                    text: 'Cancel',
                    handler: this.cancelButtonClick,
                    scope: this
                }],
                
                keys: [{
                    // Add keymap so pressing <Enter> saves changes
                    key: [10,13],
                    scope: this,
                    stopEvent: true,
                    fn: this.saveButtonClick
                }]
            });

            this.window = new Ext.Window({
                width: 235,             // (height will be calculated based on content)
                y: params.ypos,
                autoHeight: true,       // allows calls to syncSize() to resize the window based on content
                forceLayout: true,      // force window to calculate layout (i.e. height) before opening
                resizable: false,
                closeAction:'hide',     // hide instead of destroying window on close
                modal: true,
                bodyStyle:'padding:5px;',
                title: 'Regional Event Preferences',
                items: this.form
            });

            // perform actions when window opens
            this.window.on('show', function() {
            // select states in the list based on values in ScheduleFilterStates cookie
                if(g_stateFilter=='All')
                {
                    Ext.getCmp('state-grid').getSelectionModel().selectAll();
                }
                else
                {
                    states = g_stateFilter.substring(1, g_stateFilter.length-1);      // remove surrounding "(" ... ")"
                    states = states.split(",");                         // split into array
                    Ext.getCmp('state-grid').getSelectionModel().selectRowsById(states);
                }
            // clear status message
                this.setMessage('', 'black');                              // clear message area
            }, this);
        }

        this.window.show(params.animateTarget);     // open window
    }

    this.cancelButtonClick = function()
    {
        this.window.hide();
    }

    this.saveButtonClick = function()
    {
        if(Ext.getCmp('state-grid').getSelectionModel().hasSelection()==false)
        {
            Ext.Msg.alert("Preferences", "You must select at least one State");
        }
        else
        {
            var list="";
            var expires = new Date(new Date().getTime()+(1000*60*60*24*365*2)).toGMTString();   // expire in 2 years
        // save state selections in cookie
            if(Ext.getCmp('state-grid').getSelectionModel().getCount() == Ext.getCmp('state-grid').getStore().getCount())
            {
                list = "All";
            }
            else
            {
                list = Ext.getCmp('state-grid').getSelectionList();
            }
            document.cookie = "ScheduleFilterStates=" + list + "; expires=" + expires + "; domain=" + g_domainRoot;
            _gaq.push(['_trackEvent', 'Action', 'Filter Events', 'States: ' + list]);   // log event in Google Analytics
        // Reload page. Defer is needed to make sure spinning loading icon displays before reload starts
        // and to give brower time to send event to google
            this.setMessage('Updating...', 'black', true);
            (function() { window.location.reload(); }).defer(200);
        }
    }

    this.setMessage = function(message, color, loading)
    {
        setFormMessage(message,color,loading);
        // recalculate window size to fit new contents (needed in IE)
        this.window.syncSize();
    }
}