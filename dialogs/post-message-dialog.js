function C_PostMessageDialog()
{
    this.window = null;

    // -------------------------------------------------------------------------------------------
    //  Show post message log dialog.
    //  params object has the following parameters:
    //      callback    - Function that will be called when message is posted
    //      scope       - scope in which to execute the callback function. (The callback function's
    //                    "this" context)
    //      animateTarget   - id of HTML target to animate opening/closing window
    //      riderID         - ID of rider posting the message
    //      racingTeamID    - Racing team ID of rider posting the message
    //      riderName       - name of the rider posting the message
    //      teamName        - name of the rider's team
    //      title           - Title of pop up window
    // -------------------------------------------------------------------------------------------
    this.show = function(params)
    {
        this.callback = params.callback;
        this.callbackScope = params.scope;

        if( ! this.window)
        {
            
            this.form = new Ext.form.FormPanel({
                baseCls: 'x-plain',     // (gives panel a gray background - by default panels have white backgrounds)
                url:'/data/post-message.php',
                labelAlign: 'right',
                bodyStyle:'padding:5px 5px 0',
                buttonAlign:'center',
                baseParams: { },    // additional parameters passed to post request
                items: [{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', width:55, items: [{
                        // === Rider photo ===
                            xtype: 'container',
                            html: '<img src="' + g_fullDomainRoot + '/imgstore/rider-portrait/' + params.racingTeamID + '/' + params.riderID + '.jpg" width=47 height=59>'
                        }]
                    },{
                        xtype:'container', layout:'form', hideLabels: true, items: [{
                            xtype: 'container',
                            style: 'font:13px "Helvetica Neue", Arial, sans-serif; color: #888; padding-bottom:3px',
                            html: '<a><b>' + params.riderName + '</b></a> - ' + params.teamName
                        },{
                        // === Comments ===
                            xtype: 'textarea',
                            name: 'Message',
                            maxLength: 140,
                            allowBlank: false,
                            blankText: 'You must enter a message',
                            width: 465,
                            height: 40
                        }]
                    }]
                },{
                    xtype: 'container', cls: 'form-spacer', height:2  // spacer row
                },{
            // === Message Field (just above buttons) ===
                    xtype: 'container',
                    id: 'status-msg',
                    style: 'display:none',   // start off hidden initially
                    cls: 'form-status'
                }],

                buttons: [{
                    text: 'Post Message',
                    width: 100,
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
                width: 555,             // (height will be calculated based on content)
                title: params.title,
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
                this.form.getForm().reset();  // clear form contents
                this.form.getForm().baseParams.PostedToID = g_pt;
                this.setMessage('', 'black');                               // clear message area
                this.form.getForm().findField('Message').focus(true, 300);     // set initial focus
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
        this.setMessage("Posting Message...", "black", true);
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
        // show success message and close dialog
        this.setMessage("Message Posted", "green");
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
                this.setMessage("Fields marked in red are required", "red");
                break;
            case Ext.form.Action.SERVER_INVALID:
            // --- failure message returned from code on the server
                this.setMessage("Error posting message: " + action.result.message, "red");
                break;
            case Ext.form.Action.CONNECT_FAILURE:
            // --- Failed to connect to server
                this.setMessage("Error posting message: Server did not respond", "red");
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