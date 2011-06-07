function C_SignupDialog()
{
    this.window = null;

    // -------------------------------------------------------------------------------------------
    //  Show signup dialog.
    //  params object has the following parameters:
    //      animateTarget   - id of HTML target to animate opening/closing window
    //      teamID          - id of default team
    //      teamName        - name of default team
    // -------------------------------------------------------------------------------------------
    this.show = function(params)
    {
        this.teamID = params.teamID;
        this.teamName = params.teamName;
        
        if( ! this.window)
        {
            
            this.dsTeamLookup = new Ext.data.JsonStore({
                root: 'results',                // results array is returned in this property
                totalProperty: 'rowcount',      // total number of rows is returned in this property
                idProperty: 'TeamID',           // defines the primary key for the results
                fields: [
                    {name: 'TeamID', type: 'int'},
                    {name: 'TeamName'},
                    {name: 'TeamType'},
                    {name: 'Domain'}
                ],
                proxy: new Ext.data.HttpProxy({ url: '/data/lookup-team.php' })
            });

            this.form = new Ext.form.FormPanel({
                baseCls: 'x-plain',     // (gives panel a gray background - by default panels have white backgrounds)
                url:'/data/signup.php',
                labelAlign: 'right',
                labelWidth: 40,
                bodyStyle:'padding: 7px 15px 0px 15px',
                buttonAlign:'center',
                baseParams: { },    // additional parameters passed to post request
                items: [{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:210, labelWidth: 40, items: [{
                        // === First Name ===
                            xtype: 'textfield',
                            fieldLabel: 'Name',
                            emptyText: 'First Name',
                            name: 'FirstName',
                            width: 160,
                            allowBlank: false,
                            blankText: 'You must enter your first name'
                        }]
                    },{
                        xtype:'container', layout:'form', width:165, hideLabels: true, items: [{
                        // === Last Name ===
                            xtype: 'textfield',
                            emptyText: 'Last Name',
                            name: 'LastName',
                            width: 165,
                            allowBlank: false,
                            blankText: 'You must enter your last name'
                        }]
                    }]
                },{
                // === Email ===
                    xtype: 'textfield',
                    fieldLabel: 'Email',
                    emptyText: 'name@example.com',
                    name: 'Email',
                    width: 330,
                    vtype: 'email',
                    allowBlank: false,
                    blankText: 'You must enter your email'
                },{
                // === Team ===
                    xtype: 'remotecombobox',
                    fieldLabel: 'Team',
                    displayField: 'TeamName',
                    valueField: 'TeamID',
                    hiddenName: 'TeamID',
                    forceSelection: true,
                    maxHeight: 400,
                    width: 330,
                    listWidth: 350,
                    pageSize: 50,
                    triggerClass: 'x-form-search-trigger',
                    emptyText: 'Type first few letters of your team name...',
                    store: this.dsTeamLookup,
                    tpl:'<tpl for="."><div class="x-combo-list-item" style="border-bottom:1px solid #ccc"><table cellpadding=0 cellspacing=0><tr>\
                           <td><div class="ellipses" style="padding-left:5px;width:210px">\
                             <div class="find-name">{TeamName}</div>\
                             <div class="find-info">{TeamType}</div>\
                           </div></td>\
                           <td style="height:32px;width:120px;text-align:center"><img src="' + getFullDomainRoot() + '/imgstore/team-logo/fit/{TeamID}.png"></td>\
                         </tr></table>\
                         </div></tpl>'
                },{
                    xtype: 'container',
                    style: 'padding:5px 0 5px 180px',
                    html: 'OR...'
                    
                },{
                    xtype: 'checkbox',
                    name: 'NoTeam',
                    boxLabel:'No team - just sign me up <span style="font-size:11px;line-height:12px;color:#888">(you can always join a team later)</span>'
                },{
                    xtype: 'checkbox',
                    name: 'CreateTeam',
                    boxLabel:'I want to create a new team <span style="font-size:11px;line-height:12px;color:#888">(we will contact you via email)</span>'
                },{
                    xtype: 'container', cls: 'form-spacer', height:5
                },{
            // === Message Field (just above buttons) ===
                    xtype: 'container',
                    id: 'status-msg',
                    style: 'display:none',   // start off hidden initially
                    cls: 'form-status'
                },{
                    xtype: 'container', cls: 'form-spacer', height:2
                }],

                buttons: [{
                    text: 'Sign Up!',
                    width: 140,
                    handler: this.saveButtonClick,
                    scope: this
                }]
            });

            this.window = new Ext.Window({
                title: 'RideNet Sign Up',
                width: 440,             // (height will be calculated based on content)
                y: 150,
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
                if(this.teamID)
                {
                    this.form.getForm().findField("TeamID").setValue(this.teamID);
                    this.form.getForm().findField("TeamID").setRawValue(this.teamName);
                }
                this.setMessage('', 'black');           // clear message area
            }, this);
        }

        // open window
        this.window.show(params.animateTarget);
    }

    this.saveButtonClick = function()
    {
    // --- show sending message in message area
        this.setMessage("Creating Profile...", "black", true);
    // --- disable dialog
        this.window.getEl().mask();
    // --- submit form data
        this.form.getForm().submit({
            reset: false,
            params: { Source: g_signupSource },
            success: this.onPostSuccess,
            failure: this.onPostFailure,
            scope: this
         });
    }

    this.onPostSuccess = function(form, action)
    {
        Ext.Msg.show({
            title: "RideNet Sign Up",
            msg: "<span style='font-size:14px'>Thank you for signing up with RideNet! We will send you a welcome email with login information.</span>",
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