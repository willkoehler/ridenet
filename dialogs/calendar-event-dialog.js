function C_RideDialog()
{
    this.window = null;

    // -------------------------------------------------------------------------------------------
    //  Show event dialog.
    //  params object has the following parameters:
    //      calendarID  - ID of event. If present this dialog opens an existing event. If
    //                    ID is blank, this dialog creates a new event
    //      callback    - Function that will be called when event is saved/created
    //      scope       - scope in which to execute the callback function. (The callback function's
    //                    "this" context)
    //      animateTarget   - id of HTML target to animate opening/closing window
    //      makeCopy    - set true to make a copy of an existing event
    // -------------------------------------------------------------------------------------------
    this.show = function(params)
    {
        this.calendarID = params.calendarID;
        this.callback = params.callback;
        this.callbackScope = params.scope;
        this.makeCopy = params.makeCopy;

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

            // Json Reader to read data for dialog
            var reader = new Ext.data.JsonReader( { root: 'results', idProperty: 'CalendarID' }, [
                {name: 'CalendarID', type: 'int'},
                {name: 'CalendarDate', mapping: 'CalendarDate', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'CalendarTime', mapping: 'CalendarDate', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'EventName'},
                {name: 'Location'},
                {name: 'Comments'},
                {name: 'ZipCodeID', type: 'int'},
                {name: 'ZipCodeText'},
                {name: 'MapURL'},
                {name: 'Attending'},
                {name: 'ClassX', type: 'bool'},
                {name: 'ClassA', type: 'bool'},
                {name: 'ClassB', type: 'bool'},
                {name: 'ClassC', type: 'bool'},
                {name: 'ClassD', type: 'bool'}
            ]);

            this.form = new Ext.form.FormPanel({
                baseCls: 'x-plain',     // (gives panel a gray background - by default panels have white backgrounds)
                url:'/data/post-calendar-event.php',
                labelAlign: 'right',
                bodyStyle:'padding:5px 5px 0',
                buttonAlign:'right',
                labelWidth:80,
                reader: reader,
                baseParams: { },    // additional parameters passed to post request
                items: [{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:180, labelWidth:80, items: [{
                        // === Event Date ===
                            xtype: 'datefield',
                            fieldLabel: 'Date',
                            name: 'CalendarDate',
                            format: 'n/j/Y',
                            width: 95,
                            validator: function(value) {
                                if(value=='')  return ("You must enter a ride date");
                                if(new Date(value + ' 23:59:00') < new Date()) return("Date must be in the future");
                                return(true);
                            }
                        }]
                    },{
                        xtype:'container', layout:'form', width:170, labelWidth:50, items: [{
                        // === Event Time ===
                            xtype: 'timefield',
                            fieldLabel: 'Time',
                            name: 'CalendarTime',
                            format: 'g:i a',
                            width: 85,
                            allowBlank: false,
                            blankText: 'You must enter a ride time'
                        }]
                    }] // end of column container
                },{
                    // === Ride Name ===
                        xtype: 'textfield',
                        fieldLabel: 'Ride Name',
                        name: 'EventName',
                        width: 310,
                        allowBlank: false,
                        blankText: 'You must enter a name for this ride'
                },{
                    // === Location ===
                        xtype: 'textfield',
                        fieldLabel: 'Meet At',
                        name: 'Location',
                        width: 310,
                        allowBlank: false,
                        blankText: 'You must enter the location of this ride'
                },{
                    // === Zip Code ===
                        xtype: 'remotecombobox',
                        fieldLabel: 'Zip Code',
                        displayField: 'text',
                        valueField: 'id',
                        hiddenName: 'ZipCodeID',
                        forceSelection: true,
                        id: 'zip-code',
                        width:310,
                        allowBlank: false,
                        blankText: 'Zip code is required so rides can be filtered by location',
                        store: this.dsZipCodeLookup
                },{
                    // === Comments ===
                        xtype: 'textarea',
                        fieldLabel: 'Comments',
                        name: 'Comments',
                        width: 310,
                        height: 120
                },{
                    // === Map URL ===
                        xtype: 'textfield',
                        fieldLabel: 'Map URL',
                        name: 'MapURL',
                        width: 310,
                        vtype: 'url'
                },{
                    // === I'll Be There ===
                        xtype: 'checkbox',
                        name: 'Attending',
                        width: 310,
                        boxLabel: 'I\'ll be there <span style="color:#888"> - check if you are attending this ride</span>'
                },{
                    xtype: 'container', cls: 'form-spacer', height:7
                },{
                    xtype: 'fieldset',  title: 'Ride Class (choose 1 or more)', width: 395, cls: 'tighten-fieldset-legend', items: [{
                        xtype:'container', layout:'form', labelAlign: 'right', width:385, hideLabels: true, items: [{
                        // === Class X ===
                            xtype: 'checkbox',
                            boxLabel: 'X: 23-28mph+ All-out, competitive style riding.',
                            name: 'ClassX',
                            labelAlign: 'right',
                            width: 370
                        }]
                    },{
                        xtype:'container', layout:'form', width:385, hideLabels: true, items: [{
                        // === Class A ===
                            xtype: 'checkbox',
                            boxLabel: 'A: 19-23mph Fast pace with limited regrouping.',
                            name: 'ClassA',
                            labelAlign: 'right',
                            width: 375
                        }]
                    },{
                        xtype:'container', layout:'form', width:385, hideLabels: true, items: [{
                        // === Class B ===
                            xtype: 'checkbox',
                            boxLabel: 'B: 16-19mph Brisk recreational ride. Less competitive.',
                            name: 'ClassB',
                            width: 375
                        }]
                    },{
                        xtype:'container', layout:'form', width:385, hideLabels: true, items: [{
                        // === Class C ===
                            xtype: 'checkbox',
                            boxLabel: 'C: 13-16mph Recreational. Consistent, but social pace.',
                            name: 'ClassC',
                            width: 375
                        }]
                    },{
                        xtype:'container', layout:'form', width:385, hideLabels: true, items: [{
                        // === Class D ===
                            xtype: 'checkbox',
                            boxLabel: 'D: 10-13mph Social pace. May include meal stop or sightseeing.',
                            name: 'ClassD',
                            width: 375
                        }]
                    }]  // end of ride class container
                },{
            // === Tips ===
                    xtype: 'displayfield',
                    hideLabel: true,
                    style: 'padding-left:5px; font: 11px arial;color:#555',
                    html: '<b>NOTE:</b> This ride will be listed on the Community Ride Calendar and will be\
                           seen by many other riders. Please ensure the accuracy of the ride information and\
                           search existing rides before adding a new ride to avoid duplicates.<br>'
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
                        // we are making a copy, set CalendarID to -1 so a new event is created and expand
                        // date selection field drop-down
                            this.form.getForm().findField('CalendarDate').onTriggerClick();
                            this.form.getForm().baseParams.CalendarID = -1;
                        }
                        // ZipCode combo is a remote combo box so on load we need to manually set the
                        // display value of the combo box. setRawValue() updates the displayed text
                        // while leaving the underlying zip code value unchanged
                        if(form.reader.jsonData.results.ZipCodeText!="")
                        {
                            Ext.getCmp('zip-code').setRawValue(form.reader.jsonData.results.ZipCodeText);
                        }
                        this.window.getEl().unmask();
                    } },
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
                if(this.calendarID)
                {
                // --- This is an existing event, load event data
                    this.form.getForm().baseParams.CalendarID = this.calendarID;
                    this.window.getEl().mask("Loading..."); // mask form while loading form data from server
                    this.form.getForm().load({url:"/data/get-calendar-event.php"});
                    if(this.makeCopy)
                    {
                        this.window.setTitle("Copy Ride");
                        Ext.get('delete-btn').hide();
                        Ext.getCmp('save-btn').setText("Save Copy");
                    }
                    else
                    {
                        this.window.setTitle("Edit Ride");
                        Ext.get('delete-btn').show();
                        Ext.getCmp('save-btn').setText("Save");
                    }
                }
                else
                {
                // --- We are creating a new event, initialize form with default values
                    this.form.getForm().reset();  // clear form contents
                    this.form.getForm().findField('Attending').setValue(1);     // default to attending
                    this.form.getForm().baseParams.CalendarID = -1;
                    this.window.setTitle("Add Ride");
                    Ext.get('delete-btn').hide();
                    Ext.getCmp('save-btn').setText("Save");
                }
                this.setMessage('', 'black');                              // clear message area
                this.form.getForm().findField('CalendarDate').focus(true, 200);  // set initial focus
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
        this.setMessage("Saving Ride...", "black", true);
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
        this.setMessage("Ride Saved", "green");
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
                this.setMessage("Ride information is not complete. Fix fields marked in red.", "red");
                break;
            case Ext.form.Action.SERVER_INVALID:
            // --- failure message returned from code on the server
                this.setMessage("Error saving ride: " + action.result.message, "red");
                break;
            case Ext.form.Action.CONNECT_FAILURE:
            // --- Failed to connect to server
                this.setMessage("Error saving ride: Server did not respond", "red");
                break;
        }
    }

    this.deleteButtonClick = function()
    {
        Ext.Msg.show({
            title: "Confirm Delete",
            msg: "Are you sure you want to delete this ride?",
            fn: function(btn) { if(btn=='yes') {
            // --- Mask this page and post delete request
                this.window.getEl().mask();
                this.setMessage("Deleting Ride...", "black", true);
                Ext.Ajax.request({
                    url: '/data/archive-calendar-event.php',
                    params: {ID: this.calendarID},
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
            this.setMessage("Error deleting ride: " + result.message, "red");
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
        this.setMessage("Error deleting ride: Server did not respond", "red");
    }

    this.setMessage = function(message, color, loading)
    {
        setFormMessage(message,color,loading);
        // recalculate window size to fit new contents (needed in IE)
        this.window.syncSize();
    }
}