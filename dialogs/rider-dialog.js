function C_RiderDialog()
{
    this.window = null;

    // -------------------------------------------------------------------------------------------
    //  Show rider dialog.
    //  params object has the following parameters:
    //      riderID     - ID of rider. If present this dialog opens an existing rider. If
    //                    ID is blank, this dialog creates a new rider
    //      callback    - Function that will be called when rider is saved/created
    //      scope       - scope in which to execute the callback function. (The callback function's
    //                    "this" context)
    //      animateTarget   - id of HTML target to animate opening/closing window
    // -------------------------------------------------------------------------------------------
    this.show = function(params)
    {
        this.riderID = params.riderID;
        this.callback = params.callback;
        this.callbackScope = params.scope;

        if( ! this.window)
        {
            
            // Json Reader to read data for dialog
            var reader = new Ext.data.JsonReader( { root: 'results', idProperty: 'RiderID' }, [
                {name: 'RiderID', type: 'int'},
                {name: 'RiderEmail'},
                {name: 'LastName'},
                {name: 'FirstName'},
                {name: 'PwUnencrypted'},
                {name: 'sTeamAdmin', type: 'bool'}
            ]);

            this.form = new Ext.form.FormPanel({
                baseCls: 'x-plain',     // (gives panel a gray background - by default panels have white backgrounds)
                url:'/data/post-rider-sm.php',
                labelAlign: 'right',
                bodyStyle:'padding:0px',
                buttonAlign:'center',
                reader: reader,
                baseParams: { },    // additional parameters passed to post request
                items: [{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:235, labelWidth:90, items: [{
                        // === First Name ===
                            xtype: 'textfield',
                            fieldLabel: 'First Name',
                            name: 'FirstName',
                            width: 135,
                            allowBlank: false,
                            blankText: 'You must enter a First Name for this rider'
                        }]
                    },{
                        xtype:'container', layout:'form', width:225, labelWidth:70, items: [{
                        // === Last Name ===
                            xtype: 'textfield',
                            fieldLabel: 'Last Name',
                            name: 'LastName',
                            width: 135,
                            allowBlank: false,
                            blankText: 'You must enter a Last Name for this rider'
                        }]
                    }]
                },{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:285, labelWidth:90, items: [{
                        // === Email ===
                            xtype: 'textfield',
                            fieldLabel: 'Email',
                            name: 'RiderEmail',
                            width: 190,
                            vtype: 'email',
                            allowBlank: false,
                            blankText: 'You must enter an email for this rider'
                        }]
                    },{
                        xtype:'container', layout:'form', width:180, labelWidth:70, items: [{
                        // === Password ===
                            xtype: 'textfield',
                            fieldLabel: 'Password',
                            name: 'PwUnencrypted',
                            inputType: 'password',
                            width: 85,
                            allowBlank: false,
                            blankText: 'You must enter a password for this rider'
                        }]
                    }]
                },{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:187, labelWidth:90, items: [{
                        // === Team Admin ===
                            xtype: 'checkbox',
                            fieldLabel: 'Security',
                            boxLabel: 'Team Admin',
                            name: 'sTeamAdmin',
                            width: 90
                        }]
                    },{
                        xtype:'container', items: [{
                        // === Description ===
                            xtype: 'displayfield',
                            style: 'padding-top:3px; color:#666',
                            html: '(this rider has access to the "Manage Team" page)'
                        }]
                    }]
                },{
            // === Tips ===
                    xtype: 'container',
                    id: 'nr-note',
                    style: 'border:1px dotted #BBB;background-color:#E4E5FB;padding:7px;margin:8px 30px 5px 30px;font-size:12px;text-align:center;color:#666',
                    html: '<b>Note:</b> An email with login instructions will be sent to the new rider.'
                },{
            // === Message Field (just above buttons) ===
                    xtype: 'container',
                    id: 'status-msg',
                    style: 'display:none',   // start off hidden initially
                    cls: 'form-status'
                }],

                buttons: [{
                    text: 'Save',
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
                }],
            
                listeners: {
                    scope: this,
                    // unmask form when load is completed
                    actioncomplete: function(form, action) { if(action.type == "load") {this.window.getEl().unmask(); } },
                    // redirect to login page if load returns an error (session expired)
                    actionfailed: function(form, action) { if(action.type == "load") {window.location.href = '/login?expired=1' } }
                }
            });

            this.window = new Ext.Window({
                width: 495,             // (height will be calculated based on content)
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
                if(this.riderID)
                {
                // --- This is an existing rider, load rider data
                    this.form.getForm().baseParams.RiderID = this.riderID;
                    this.form.getForm().baseParams.TeamID = g_pt;
                    this.window.getEl().mask("Loading..."); // mask form while loading form data from server
                    this.form.getForm().load({url:"/data/get-rider-sm.php"});
                    Ext.fly('nr-note').setVisibilityMode(Ext.Element.DISPLAY);
                    Ext.fly('nr-note').hide();
                    this.window.setTitle("Edit Rider");
                }
                else
                {
                // --- We are creating a new rider, initialize form with default values
                    this.form.getForm().reset();  // clear form contents
                    this.form.getForm().baseParams.RiderID = -1;
                    this.form.getForm().baseParams.TeamID = g_pt;
                    this.form.getForm().findField("PwUnencrypted").setValue('live2ride');   // default password
                    Ext.fly('nr-note').show();
                    this.window.setTitle("Add Rider");
                }
                this.setMessage('', 'black');                              // clear message area
                this.form.getForm().findField('FirstName').focus(true, 300);  // set initial focus
            }, this);
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
        this.setMessage("Saving Rider...", "black", true);
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
        this.setMessage("Rider Saved", "green");
        this.window.getEl().unmask();
        // hide window and call callback
        this.window.hide();
        this.callback.apply(this.callbackScope, [this, false]);
    }

    this.onPostFailure = function(form, action)
    {
        this.window.getEl().unmask();   // enable form
        switch(action.failureType) {
            case Ext.form.Action.CLIENT_INVALID:
            // --- client-side validation failed
                this.setMessage("Rider information is not complete. Fix fields marked in red.", "red");
                break;
            case Ext.form.Action.SERVER_INVALID:
            // --- failure message returned from code on the server
                this.setMessage("Error saving rider: " + action.result.message, "red");
                break;
            case Ext.form.Action.CONNECT_FAILURE:
            // --- Failed to connect to server
                this.setMessage("Error saving rider: Server did not respond", "red");
                break;
        }
    }

    this.setMessage = function(message, color, loading)
    {
        setFormMessage(message,color,loading);
        // recalculate window size to fit new contents (needed in IE)
        this.window.syncSize();
    }
}