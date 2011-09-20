function C_EventDialog()
{
    this.window = null;

    // -------------------------------------------------------------------------------------------
    //  Show event dialog.
    //  params object has the following parameters:
    //      raceID      - ID of event. If present this dialog opens an existing event. If
    //                    ID is blank, this dialog creates a new event
    //      callback    - Function that will be called when event is saved/created
    //      scope       - scope in which to execute the callback function. (The callback function's
    //                    "this" context)
    //      animateTarget   - id of HTML target to animate opening/closing window
    //      makeCopy    - set true to make a copy of an existing event
    // -------------------------------------------------------------------------------------------
    this.show = function(params)
    {
        this.raceID = params.raceID;
        this.callback = params.callback;
        this.callbackScope = params.scope;
        this.makeCopy = params.makeCopy;

        if( ! this.window)
        {
            
            // Json Reader to read data for dialog
            var reader = new Ext.data.JsonReader( { root: 'results', idProperty: 'RaceID' }, [
                {name: 'RaceID', type: 'int'},
                {name: 'RaceDate', type: 'date', dateFormat: 'Y-m-d'},
                {name: 'EventName'},
                {name: 'City'},
                {name: 'StateID'},
                {name: 'WebPage'},
                {name: 'RideTypeID'}
            ]);

            this.form = new Ext.form.FormPanel({
                baseCls: 'x-plain',     // (gives panel a gray background - by default panels have white backgrounds)
                url:'/data/post-schedule-event.php',
                labelAlign: 'right',
                bodyStyle:'padding:5px 5px 0',
                buttonAlign:'right',
                reader: reader,
                labelWidth: 80,
                baseParams: { },    // additional parameters passed to post request
                items: [{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:200, labelWidth:80, items: [{
                        // === Event Date ===
                            xtype: 'datefield',
                            fieldLabel: 'Event Date',
                            name: 'RaceDate',
                            format: 'n/j/Y',
                            width: 100,
                            validator: function(value) {
                                if(value=='')  return ("You must enter an event date");
                                // date must be no more than 365 days in the past
                                if(new Date(value) <= new Date().add(Date.DAY, -366)) return("Date is more than 1 year in the past");
                                return(true);
                            }
                        }]
                    },{
                        xtype:'container', layout:'form', width:195, labelWidth:70, items: [{
                        // === Event Type ===
                            xtype: 'localcombobox',
                            fieldLabel: 'Event Type',
                            displayField: 'text',
                            valueField: 'id',
                            hiddenName: 'RideTypeID',
                            forceSelection: true,
                            width: 120,
                            listWidth: 150,
                            allowBlank: false,
                            blankText: 'You must select an event type',
                            store: new Ext.data.ArrayStore({ fields: ['id', 'text', 'img'], data: eventTypeLookup }),
                            tpl:'<tpl for="."><div class="x-combo-list-item"><table cellpadding=0 cellspacing=0><tr>\
                                   <td xclass="item-status-label" style="width:90px;padding-left:5px">{text}</td>\
                                   <td><img xstyle="padding-right:10px" src="/images/event-types/{img}"></td>\
                                 </tr></table>\
                                 </div></tpl>'
                        }]
                    }] // end of column container
                },{
                    // === Event Name ===
                        xtype: 'textfield',
                        fieldLabel: 'Event Name',
                        name: 'EventName',
                        width: 310,
                        allowBlank: false,
                        blankText: 'You must enter a name for this event'
                },{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:313, labelWidth:80, items: [{
                        // === City ===
                            xtype: 'textfield',
                            fieldLabel: 'City',
                            name: 'City',
                            width: 220,
                            allowBlank: false,
                            blankText: 'You must enter the city of this event'
                        }]
                    },{
                        xtype:'container', layout:'form', width:82, labelWidth:32, items: [{
                        // === State ===
                            xtype: 'localcombobox',
                            fieldLabel: 'State',
                            displayField: 'abbr',
                            valueField: 'id',
                            hiddenName: 'StateID',
                            forceSelection: true,
                            width: 45,
                            listWidth: 120,
                            allowBlank: false,
                            blankText: 'You must enter the state of this event',
                            store: new Ext.data.ArrayStore({ fields: ['id', 'name', 'abbr'], data: stateLookup })
                        }]
                    }] // end of column container
                },{
                // === Web Site ===
                    xtype: 'textfield',
                    fieldLabel: 'Web Site',
                    name: 'WebPage',
                    width: 310,
                    vtype: 'url',
                    allowBlank: false,
                    blankText: 'You must enter the event website'
                },{
                    xtype: 'container', cls: 'form-spacer', height:5
                },{
            // === Tips ===
                    xtype: 'displayfield',
                    hideLabel: true,
                    style: 'padding-left:5px; font: 11px arial,helvetica;color:#555',
                    html: '<b>NOTE:</b> This event will be listed on the Regional Event Schedule and will be\
                           seen by many other riders. Please ensure the accuracy of the event information and\
                           search existing events before adding a new event to avoid duplicates.<br>'
                },{
            // === Message Field (just above buttons) ===
                    xtype: 'container',
                    id: 'status-msg',
                    style: 'display:none',   // start off hidden initially
                    cls: 'form-status'
                }],

                buttons: [{
                    text: 'Save',
                    id: 'save-btn',
                    handler: this.saveButtonClick,
                    scope: this
                },{
                    text: 'Cancel',
                    handler: this.cancelButtonClick,
                    scope: this
                },{
                    xtype: 'container', width: 30   // spacer to center Save and Cancel buttons
                },{
                    text: '&nbsp;Delete',
                    id: 'delete-btn',
                    icon: '/images/delete-icon-small.png',
                    handler: this.deleteButtonClick,
                    scope: this
                }],
                
                keys: [{
                    // Add keymap so pressing <Enter> saves changes
                    key: [10,13],
                    scope: this,
                    stopEvent: true,
                    fn: this.saveButtonClick
                }],

                listeners: {
                    scope: this,
                    // unmask form when load is completed
                    actioncomplete: function(form, action) { if(action.type == "load") {
                        if(this.makeCopy)
                        {
                        // we are making a copy, set RaceID to -1 so a new event is created and expand
                        // date selection field drop-down
                            this.form.getForm().findField('RaceDate').onTriggerClick();
                            this.form.getForm().baseParams.RaceID = -1;
                        }
                        this.window.getEl().unmask();}
                    },
                    // redirect to login page if load returns an error (session expired)
                    actionfailed: function(form, action) { if(action.type == "load") {window.location.href = '/login?expired=1' } }
                }
            });

            this.window = new Ext.Window({
                width: 435,             // (height will be calculated based on content)
                autoHeight: true,       // allows calls to syncSize() to resize the window based on content
                forceLayout: true,      // force window to calculate layout (i.e. height) before opening
                resizable: false,
                closeAction:'hide',     // hide instead of destroying window on close
                modal: true,
                bodyStyle:'padding:5px;',
                items: this.form
            });

            // perform actions when window opens
            this.window.on('show', function() {
                Ext.get('delete-btn').setVisibilityMode(Ext.Element.VISIBILITY);    // still take up space when hidden
                if(this.raceID)
                {
                // --- This is an existing event, load event data
                    this.form.getForm().baseParams.RaceID = this.raceID;
                    this.window.getEl().mask("Loading..."); // mask form while loading form data from server
                    this.form.getForm().load({url:"/data/get-schedule-event.php"});
                    if(this.makeCopy)
                    {
                        this.window.setTitle("Copy Event");
                        Ext.get('delete-btn').hide();
                        Ext.getCmp('save-btn').setText("Save Copy");
                    }
                    else
                    {
                        this.window.setTitle("Edit Event");
                        Ext.get('delete-btn').show();
                        Ext.getCmp('save-btn').setText("Save");
                    }
                }
                else
                {
                // --- We are creating a new event, initialize form with default values
                    this.form.getForm().reset();  // clear form contents
                    this.form.getForm().baseParams.RaceID = -1;
                    this.window.setTitle("Add Event");
                    Ext.get('delete-btn').hide();
                    Ext.getCmp('save-btn').setText("Save");
                }
                this.setMessage('', 'black');                              // clear message area
                this.form.getForm().findField('RaceDate').focus(true, 200);  // set initial focus
            }, this);
        }
        else
        {
            this.window.center();   // recenter dialog in browser window
        }

        // open window
        this.window.show(params.animateTarget);
    }

    this.cancelButtonClick = function()
    {
        this.window.hide();
        this.callback.apply(this.callbackScope, [this, false]);
    }

    this.saveButtonClick = function()
    {
    // --- show sending message in message area
        this.setMessage("Saving Event...", "black", true);
    // --- disable dialog
        this.window.getEl().mask();
    // --- submit form data
        this.form.getForm().submit({
            reset: false,
            success: this.onPostSuccess,
            failure: this.onPostFailure,
            scope: this
         });
    }

    this.onPostSuccess = function(form, action)
    {
        this.setMessage("Event Saved", "green");
        this.window.getEl().unmask();
        this.window.hide();
        this.callback.apply(this.callbackScope, [this, false]);
    }

    this.onPostFailure = function(form, action)
    {
        this.window.getEl().unmask();   // enable form
        switch(action.failureType) {
            case Ext.form.Action.CLIENT_INVALID:
            // --- client-side validation failed
                this.setMessage("Event information is not complete. Fix fields marked in red.", "red");
                break;
            case Ext.form.Action.SERVER_INVALID:
            // --- failure message returned from code on the server
                this.setMessage("Error saving event: " + action.result.message, "red");
                break;
            case Ext.form.Action.CONNECT_FAILURE:
            // --- Failed to connect to server
                this.setMessage("Error saving event: Server did not respond", "red");
                break;
        }
    }

    this.deleteButtonClick = function()
    {
        Ext.Msg.show({
            title: "Confirm Delete",
            msg: "Are you sure you want to delete this event?",
            fn: function(btn) { if(btn=='yes') {
            // --- Mask this page and post delete request
                this.window.getEl().mask();
                this.setMessage("Deleting Event...", "black", true);
                Ext.Ajax.request({
                    url: '/data/archive-schedule-event.php',
                    params: {ID: this.raceID},
                    success: this.handleDeleteSuccess,
                    failure: this.handleDeleteFailure,
                    scope: this
                });
            } },
            scope: this,
            buttons: {yes:'&nbsp;Delete&nbsp;', no:'Cancel'}
        });
        
    }

    this.handleDeleteSuccess = function(response, options)
    {
        this.window.getEl().unmask();
    // --- decode JSON response string and check status of delete
        var result = Ext.decode(response.responseText);
        if(result.success == false)
        {
            this.setMessage("Error deleting event: " + result.message, "red");
        }
        else
        {
            this.window.hide();
            this.callback.apply(this.callbackScope, [this, false]);
        }
    }

    this.handleDeleteFailure = function(response)
    {
        this.window.getEl().unmask();
        this.setMessage("Error deleting event: Server did not respond", "red");
    }

    this.setMessage = function(message, color, loading)
    {
        setFormMessage(message,color,loading);
        // recalculate window size to fit new contents (needed in IE)
        this.window.syncSize();
    }
}