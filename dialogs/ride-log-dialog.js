function C_RideLogDialog()
{
    this.window = null;

    // -------------------------------------------------------------------------------------------
    //  Show ride log dialog.
    //  params object has the following parameters:
    //      rideLogID   - ID of ride log entry. If present this dialog opens an existing ride log
    //                    entry. If ID is blank, this dialog creates a new ride log entry
    //      callback    - Function that will be called when ride log entry is saved/created
    //                    callback will be called with a stats parameter container the rider's
    //                    updated ride stats
    //      scope       - scope in which to execute the callback function. (The callback function's
    //                    "this" context)
    //      animateTarget   - id of HTML target to animate opening/closing window
    //      makeCopy    - set true to make a copy of an existing event
    // -------------------------------------------------------------------------------------------
    this.show = function(params)
    {
        this.rideLogID = params.rideLogID;
        this.callback = params.callback;
        this.callbackScope = params.scope;
        this.makeCopy = params.makeCopy;

        if( ! this.window)
        {
            
            // Json Reader to read data for dialog
            var reader = new Ext.data.JsonReader( { root: 'results', idProperty: 'RideLogID' }, [
                {name: 'RideLogID', type: 'int'},
                {name: 'Date', type: 'date', dateFormat: 'Y-m-d'},
                {name: 'RideLogTypeID', type: 'int'},
                {name: 'Distance', type: 'int'},
                {name: 'Duration'},
                {name: 'WeatherID', type: 'int'},
                {name: 'Comment'},
                {name: 'Link'}
            ]);

            this.form = new Ext.form.FormPanel({
                baseCls: 'x-plain',     // (gives panel a gray background - by default panels have white backgrounds)
                url:'/data/post-ride-log.php',
                labelAlign: 'right',
                bodyStyle:'padding:5px 5px 0',
                buttonAlign:'right',
                reader: reader,
                baseParams: { },    // additional parameters passed to post request
                items: [{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:170, labelWidth:60, items: [{
                        // === Ride Date ===
                            xtype: 'datefield',
                            fieldLabel: 'Ride Date',
                            name: 'Date',
                            format: 'n/j/Y',
                            width: 95,
                            validator: function(value) {
                                if(value=='')  return ("You must enter a ride date");
                                if(new Date(value) > new Date()) return("Date must be in the past");
                                return(true);
                            }
                        }]
                    },{
                        xtype:'container', layout:'form', width:120, labelWidth:30, items: [{
                        // === Ride Type ===
                            xtype: 'localcombobox',
                            fieldLabel: 'Type',
                            displayField: 'text',
                            valueField: 'id',
                            hiddenName: 'RideLogTypeID',
                            forceSelection: true,
                            width: 85,
                            listWidth: 270,
                            allowBlank: false,
                            blankText: 'You must select a ride type',
                            store: new Ext.data.ArrayStore({ fields: ['id', 'text', 'img', 'desc'], data: rideLogTypeLookup }),
                            tpl:'<tpl for="."><div class="x-combo-list-item"><table><tr>\
                                   <td><img src="/images/ridelog/{img}"></td>\
                                   <td class="item-status-label" style="width:60px">{text}</td>\
                                   <td class="item-status-desc" style="width:160px">{desc}</td>\
                                 </tr></table>\
                                 </div></tpl>'
                        }]
                    },{
                        xtype:'container', layout:'form', width:150, labelWidth:60, items: [{
                        // === Weather ===
                            xtype: 'localcombobox',
                            fieldLabel: 'Weather',
                            displayField: 'text',
                            valueField: 'id',
                            hiddenName: 'WeatherID',
                            emptyText: 'optional',
                            forceSelection: true,
                            width: 85,
                            listWidth: 120,
                            store: new Ext.data.ArrayStore({ fields: ['id', 'text', 'img'], data: weatherLookup }),
                            tpl:'<tpl for="."><div class="x-combo-list-item"><table cellpadding=0 cellspacing=0><tr>\
                                   <td><img style="padding-right:5px" src="/images/weather/{img}" height=25 width=25></td>\
                                   <td class="item-status-label" style="width:60px">{text}</td>\
                                 </tr></table>\
                                 </div></tpl>'
                        }]
                    },{
                        xtype:'container', layout:'form', width:85, labelWidth:40, items: [{
                        // === Distance ===
                            xtype: 'textfield',
                            fieldLabel: 'Miles',
                            name: 'Distance',
                            emptyText: 'opt.',
                            width: 40
                        }]
                    },{
                        xtype:'container', layout:'form', width:110, labelWidth:60, items: [{
                        // === Duration ===
                            xtype: 'textfield',
                            fieldLabel: 'Duration',
                            name: 'Duration',
                            emptyText: 'opt.',
                            width: 45,
                            listeners: { scope: this,
                                focus: function(field) { if(field.getValue()=="") { field.setValue("H:MM"); field.selectText(); } },
                                blur: function(field) { if(field.getValue()=="H:MM") { field.reset(); } }
                            }
                        }]
                    }] // end of column container
                },{
                    xtype:'container', layout:'form', labelWidth:60, items: [{
                    // === Comments ===
                        xtype: 'textarea',
                        fieldLabel: 'Comment',
                        name: 'Comment',
                        maxLength: 140,
                        emptyText: '140 characters max',
                        width: 570,
                        height: 40
                    }]
                },{
                    xtype:'container', layout:'form', labelWidth:60, items: [{
                    // === Link to Something ===
                        xtype: 'textfield',
                        fieldLabel: 'Link',
                        name: 'Link',
                        maxLength: 255,
                        vtype: 'url',
                        emptyText: 'Link to something: Route map, Garmin Connect, TrainingPeaks, power file, etc.',
                        width: 570
                    }]
                },{
            // === Message Field (just above buttons) ===
                    xtype: 'container',
                    id: 'status-msg',
                    cls: 'form-status',
                    style: 'display:none'   // start off hidden initially
                }],

                buttons: [{
                    text: 'Log Ride',
                    id: 'save-btn',
                    handler: this.saveButtonClick,
                    scope: this
                },{
                    text: 'Cancel',
                    handler: this.cancelButtonClick,
                    scope: this
                },{
                    xtype: 'container', width: 145   // spacer to center Save and Cancel buttons
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
                        // we are making a copy, set rideLogID to -1 so a new event is created and set
                        // date to today
                            this.form.getForm().findField('Date').setValue(new Date());
                            this.form.getForm().baseParams.RideLogID = -1;
                        }
                        this.window.getEl().unmask();
                    } },
                    // redirect to login page if load returns an error (session expired)
                    actionfailed: function(form, action) { if(action.type == "load") {window.location.href = '/login?expired=1' } }
                }
            });

            this.window = new Ext.Window({
                width: 675,             // (height will be calculated based on content)
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
                if(this.rideLogID)
                {
                // --- This is an existing ride log entry, load data
                    this.form.getForm().baseParams.RideLogID = this.rideLogID;
                    this.window.getEl().mask("Loading..."); // mask form while loading form data from server
                    this.form.getForm().load({url:"/data/get-ride-log.php"});
                    if(this.makeCopy)
                    {
                        this.window.setTitle("Copy This Ride");
                        Ext.get('delete-btn').hide();
                        Ext.getCmp('save-btn').setText("Log Ride");
                    }
                    else
                    {
                        this.window.setTitle("Edit Ride Log");
                        Ext.get('delete-btn').show();
                        Ext.getCmp('save-btn').setText("Save");
                    }
                }
                else
                {
                // --- We are creating a new ride log entry, initialize form with default values
                    this.form.getForm().reset();  // clear form contents
                    this.form.getForm().findField('Date').setValue(new Date());
                    this.form.getForm().baseParams.RideLogID = -1;
                    this.window.setTitle("Log a Ride");
                    Ext.get('delete-btn').hide();
                    Ext.getCmp('save-btn').setText("Log Ride");
                }
                this.setMessage('', 'black');                               // clear message area
                this.form.getForm().findField('Date').focus(true, 300);     // set initial focus
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
        this.callback.apply(this.callbackScope, [false, null]);
    }

    this.saveButtonClick = function()
    {
    // --- show sending message in message area
        this.setMessage("Saving Ride Log...", "black", true);
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
        // log an event in Google Analytics
        if(form.baseParams.RideLogID==-1)
        {
            _gaq.push(['_trackEvent', 'Action', 'Log Ride', (this.makeCopy) ? 'Copy' : 'New']);
        }
        // show success message and close dialog
        this.setMessage("Ride Log Saved", "green");
        this.window.getEl().unmask();
        this.window.hide();
        this.callback.apply(this.callbackScope, [true, action.result.stats]);
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
                this.setMessage("Error saving ride log: " + action.result.message, "red");
                break;
            case Ext.form.Action.CONNECT_FAILURE:
            // --- Failed to connect to server
                this.setMessage("Error saving ride log: Server did not respond", "red");
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
                this.window.getEl().mask("");
                this.setMessage("Deleting Ride...", "black", true);
                Ext.Ajax.request({
                    url: '/data/delete-ride-log.php',
                    params: {ID: this.rideLogID},
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
            this.callback.apply(this.callbackScope, [true, result.stats]);
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