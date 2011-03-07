function C_ResetPWDialog()
{
    this.window = null;

    // -------------------------------------------------------------------------------------------
    //  Show reset password dialog.
    //  params object has the following parameters:
    //      animateTarget   - id of HTML target to animate opening/closing window
    // -------------------------------------------------------------------------------------------
    this.show = function(params)
    {
        if( ! this.window)
        {
            
            this.form = new Ext.form.FormPanel({
                baseCls: 'x-plain',     // (gives panel a gray background - by default panels have white backgrounds)
                url:'data/reset-password.php',
                labelAlign: 'right',
                bodyStyle:'padding: 7px 15px 0px 15px',
                buttonAlign:'center',
                defaults: {hideLabel: true},
                baseParams: { },    // additional parameters passed to post request
                items: [{
            // === Instructions ===
                    xtype: 'displayfield',
                    hideLabel: true,
                    style: 'margin:0 0 10px 0; font: 13px "Helvetica Neue", Arial; color:#444',
                    html: 'Enter your email address and we will send you a new password. If you \
                           continue to have trouble logging in, send us an email at \
                           <a href="mailto:info@ridenet.net">info@ridenet.net</a>'
                },{
                // === Email ===
                    xtype: 'textfield',
                    emptyText: 'email (name@example.com)',
                    name: 'Email',
                    width: 330,
                    vtype: 'email',
                    allowBlank: false,
                    blankText: 'You must enter your email'
                },{
                    xtype: 'container', cls: 'form-spacer', height:3
                },{
            // === Message Field (just above buttons) ===
                    xtype: 'container',
                    id: 'status-msg',
                    style: 'display:none',   // start off hidden initially
                    cls: 'form-status'
                },{
                    xtype: 'container', cls: 'form-spacer', height:3
                }],

                buttons: [{
                    text: 'Reset Password',
                    width: 120,
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
                title: 'Reset Password',
                width: 390,             // (height will be calculated based on content)
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
                this.form.getForm().findField('Email').focus(true, 200);  // set initial focus
            }, this);
        }

        // open window
        this.window.show(params.animateTarget);
    }

    this.cancelButtonClick = function()
    {
        this.window.hide();
    }

    this.saveButtonClick = function()
    {
    // --- show sending message in message area
        this.setMessage("Resetting Password...", "black", true);
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
            title: "Reset Password",
            msg: "<span style='font-size:14px'>Your password was reset. Check your email for a new temporary password.</span>",
            closable: false,
            buttons: Ext.MessageBox.OK,
            fn: function(btn) { 
                this.window.getEl().unmask();
                this.window.hide();
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
                this.setMessage(action.result.message, "red");
                break;
            case Ext.form.Action.CONNECT_FAILURE:
            // --- Failed to connect to server
                this.setMessage("Error resetting password: Server did not respond", "red");
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