function C_SignupDialog()
{
    this.window = null;

    // -------------------------------------------------------------------------------------------
    //  Show signup dialog.
    //  params object has the following parameters:
    //      animateTarget   - id of HTML target to animate opening/closing window
    // -------------------------------------------------------------------------------------------
    this.show = function(params)
    {
        if( ! this.window)
        {
            
            this.form = new Ext.form.FormPanel({
                baseCls: 'x-plain',     // (gives panel a gray background - by default panels have white backgrounds)
                url:'data/rider-signup.php',
                labelAlign: 'right',
                bodyStyle:'padding: 7px 15px 0px 15px',
                buttonAlign:'center',
                defaults: {hideLabel: true},
                baseParams: { },    // additional parameters passed to post request
                items: [{
            // === Welcome Text ===
                    xtype: 'displayfield',
                    hideLabel: true,
                    style: 'margin:0 0 10px 0; font: 13px "Helvetica Neue", Arial; color:#444',
                    html: 'We are growing RideNet slowly so we can focus on creating a great experience for our users. \
                           Enter your name and email below and we\'ll invite you as soon as we can. <b>OR</b> find a team \
                           that is already on RideNet and ask them to create an account for you.'
                },{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:170, hideLabels: true, items: [{
                        // === Name ===
                            xtype: 'textfield',
                            emptyText: 'Full Name',
                            name: 'RiderName',
                            width: 160,
                            allowBlank: false,
                            blankText: 'You must enter your name'
                        }]
                    },{
                        xtype:'container', layout:'form', width:230, hideLabels: true, items: [{
                        // === Email ===
                            xtype: 'textfield',
                            emptyText: 'email (name@example.com)',
                            name: 'RiderEmail',
                            width: 230,
                            vtype: 'email',
                            allowBlank: false,
                            blankText: 'You must enter your email'
                        }]
                    }]
                },{
                // === Rider Description ===
                    xtype: 'textarea',
                    name: 'RiderDescription',
                    emptyText: 'Tell us what kind of riding you do',
                    width: 400,
                    height: 50
                },{
                    xtype: 'displayfield',
                    hideLabel: true,
                    style: 'margin:15px 0 0 0; font: 13px "Helvetica Neue", Arial; color:#444',
                    html: 'If you ride with a local team/club, enter the name.'
                },{
                // === Team Name ===
                    xtype: 'textfield',
                    emptyText: 'Team/Club Name',
                    name: 'TeamName',
                    width: 400
                },{
                    xtype: 'container', cls: 'form-spacer', height:5
                },{
            // === Message Field (just above buttons) ===
                    xtype: 'container',
                    id: 'status-msg',
                    style: 'display:none',   // start off hidden initially
                    cls: 'form-status'
                },{
                    xtype: 'container', cls: 'form-spacer', height:5
                }],

                buttons: [{
                    text: 'Request Membership',
                    width: 140,
                    handler: this.saveButtonClick,
                    scope: this
                },{
                    text: 'Cancel',
                    handler: this.cancelButtonClick,
                    scope: this
                }],
                
                listeners: {
                    scope: this,
                    // unmask form when load is completed
                    actioncomplete: function(form, action) { if(action.type == "load") {this.window.getEl().unmask(); } },
                    // redirect to login page if load returns an error (session expired)
                    actionfailed: function(form, action) { if(action.type == "load") {window.location.href = 'login.php?expired=1' } }
                }
            });

            this.window = new Ext.Window({
                title: 'RideNet Sign Up',
                width: 460,             // (height will be calculated based on content)
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
                this.form.getForm().reset();            // clear form contents
                this.setMessage('', 'black');           // clear message area
            }, this);
        }

        // open window
        this.window.show(params.animateTarget);
    }

    this.cancelButtonClick = function()
    {
        this.window.hide();
        // log an event in Google Analytics
        _gaq.push(['_trackEvent', 'Signup', 'Cancel']);
    }

    this.saveButtonClick = function()
    {
    // --- show sending message in message area
        this.setMessage("Processing Membership Request...", "black", true);
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
        Ext.Msg.show({
            title: "RideNet Sign Up",
            msg: "<span style='font-size:14px'>Thank you for signing up with RideNet! We will send you a welcome email with login information. This process may take up to 24 hours.</span>",
            closable: false,
            buttons: Ext.MessageBox.OK,
            fn: function(btn) { 
                this.window.getEl().unmask();
                this.window.hide();
                // log an event in Google Analytics
                _gaq.push(['_trackEvent', 'Signup', 'Request']);
            },
            scope: this
        });
    }

    this.onPostFailure = function(form, action)
    {
        this.window.getEl().unmask();   // enable form
        switch(action.failureType) {
            case Ext.form.Action.CLIENT_INVALID:
            // --- client-side validation failed
                this.setMessage("Fields marked in red are required.", "red");
                break;
            case Ext.form.Action.SERVER_INVALID:
            // --- failure message returned from code on the server
                this.setMessage("Error requesting membership: " + action.result.message, "red");
                break;
            case Ext.form.Action.CONNECT_FAILURE:
            // --- Failed to connect to server
                this.setMessage("Error requesting membership: Server did not respond", "red");
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