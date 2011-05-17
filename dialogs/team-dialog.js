function C_TeamDialog()
{
    this.window = null;

    // -------------------------------------------------------------------------------------------
    //  Show team dialog.
    //  params object has the following parameters:
    //      teamID      - ID of team. If present this dialog opens an existing team. If
    //                    ID is blank, this dialog creates a new team
    //      callback    - Function that will be called when team is saved/created
    //      scope       - scope in which to execute the callback function. (The callback function's
    //                    "this" context)
    //      animateTarget   - id of HTML target to animate opening/closing window
    // -------------------------------------------------------------------------------------------
    this.show = function(params)
    {
        this.teamID = params.teamID;
        this.callback = params.callback;
        this.callbackScope = params.scope;

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
            var reader = new Ext.data.JsonReader( { root: 'results', idProperty: 'TeamID' }, [
                {name: 'TeamID', type: 'int'},
                {name: 'Archived', type: 'int'},
                {name: 'bRacing', type: 'int'},
                {name: 'bCommuting', type: 'int'},
                {name: 'TeamTypeID', type: 'int'},
                {name: 'ZipCodeID', type: 'int'},
                {name: 'ZipCodeText'},
                {name: 'SiteLevelID', type: 'int'},
                {name: 'SiteLevel'},
                {name: 'TeamName'},
                {name: 'Domain'},
                {name: 'URL', mapping: 'Domain'}
            ]);

            this.form = new Ext.form.FormPanel({
                baseCls: 'x-plain',     // (gives panel a gray background - by default panels have white backgrounds)
                url:'/data/post-team.php',
                labelAlign: 'right',
                bodyStyle:'padding:5px 5px 0',
                buttonAlign:'center',
                reader: reader,
                baseParams: { },    // additional parameters passed to post request
                items: [{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:395, labelWidth:70, items: [{
                        // === Team Name ===
                            xtype: 'textfield',
                            fieldLabel: 'Team Name',
                            name: 'TeamName',
                            width: 300,
                            allowBlank: false,
                            blankText: 'You must enter a name this team'
                        }]
                    },{
                        xtype:'container', layout:'form', width:135, labelWidth:50, items: [{
                        // === Current / Archived Selection ===
                            xtype: 'localcombobox',
                            fieldLabel: 'Status',
                            hiddenName: 'Archived',
                            displayField: 'text',
                            valueField: 'id',
                            width:80,
                            listWidth:300,
                            editable:false,
                            listeners: {scope: this, select: this.onSelectStatus},
                            store: new Ext.data.ArrayStore({ fields: ['id', 'text', 'desc', 'icon', 'color'], data: this.statusLookup }),
                            tpl:'<tpl for="."><div class="x-combo-list-item"><table cellpadding=0 cellspacing=0><tr>\
                                   <td class="item-status-label" style="width:17px;color:{color}"><img src="{icon}"></td>\
                                   <td class="item-status-label" style="width:283px;color:{color}">{text}</td>\
                                 </tr><tr>\
                                   <td colspan=2 class="item-status-desc" style="width:240px">{desc}</td>\
                                 </tr></table></div></tpl>'
                        }]
                    }] // end of column container
                },{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:215, labelWidth:70, items: [{
                        // === Domain ===
                            xtype: 'textfield',
                            fieldLabel: 'Domain',
                            name: 'Domain',
                            enableKeyEvents: true,
                            width: 130,
                            listeners: { scope: this, 'keyup' : function(field, v) {
                                this.form.getForm().findField("URL").setValue(field.getValue());
                            }}
                        }]
                    },{
                        xtype: 'container', layout: 'form', width: 330, labelWidth: 75, items: [{
                        // === Team URL ===
                            xtype: 'displayfield',
                            fieldLabel: 'Homepage',
                            name: 'URL',
                            id: 'team-home',
                            style: 'padding-top: 3px',
                            setValue: function(val) {
                                this.value = val;
                                var home = (val) ? "http://" + val + "." + g_domainRoot : "(no domain specified)";
                                this.el.update("<a style='color:blue' href='" + home + "' target='_blank'>" + home + "</a>&nbsp;&nbsp;");
                            }
                        }]
                    }] // end of column container
                },{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:230, labelWidth:70, items: [{
                        // === Team Type ===
                            xtype: 'localcombobox',
                            fieldLabel: 'Type',
                            displayField: 'text',
                            valueField: 'id',
                            hiddenName: 'TeamTypeID',
                            forceSelection: true,
                            value: 2,       // default value
                            width: 130,
                            allowBlank: false,
                            blankText: 'Please choose a team type',
                            store: new Ext.data.ArrayStore({ fields: ['id', 'text'], data: teamTypeLookup })
                        }]
                    },{
                        xtype: 'container', layout: 'form', width: 135, hideLabels: true, items: [{
                        // === Racing Checkbox ===
                            xtype: 'checkbox',
                            boxLabel: 'Show Racing Page',
                            name: 'bRacing'
                        }]
                    },{
                        xtype: 'container', layout: 'form', width: 150, hideLabels: true, items: [{
                        // === Commuting Checkbox ===
                            xtype: 'checkbox',
                            boxLabel: 'Show Commuting Page',
                            name: 'bCommuting'
                        }]
                    }] // end of column container
                },{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:215, labelWidth:70, items: [{
                        // === Site Level ===
                            xtype: 'localcombobox',
                            fieldLabel: 'Site Level',
                            displayField: 'text',
                            valueField: 'id',
                            hiddenName: 'SiteLevelID',
                            forceSelection: true,
                            value: 0,       // default value
                            width: 130,
                            store: new Ext.data.ArrayStore({ fields: ['id', 'text'], data: siteLevelLookup })
                        }]
                    },{
                        xtype: 'container', layout: 'form', labelWidth:65, width: 325, items: [{
                        // === Zip Code ===
                            xtype: 'remotecombobox',
                            fieldLabel: 'Location',
                            displayField: 'text',
                            valueField: 'id',
                            hiddenName: 'ZipCodeID',
                            forceSelection: true,
                            id: 'zip-code',
                            width: 245,
                            listWidth: 260,
                            allowBlank: false,
                            blankText: 'Please enter the zip code for your team',
                            store: this.dsZipCodeLookup
                        }]
                    }] // end of column container
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
                    actioncomplete: function(form, action) { if(action.type == "load") {
                        this.window.getEl().unmask(); this.onSelectStatus(); }
                        // ZipCode combo is a remote combo box so on load we need to manually set the
                        // display value of the combo box. setRawValue() updates the displayed text
                        // while leaving the underlying zip code value unchanged
                        if(form.reader.jsonData.results.ZipCodeText!="")
                        {
                            Ext.getCmp('zip-code').setRawValue(form.reader.jsonData.results.ZipCodeText);
                        }
                    },
                    // redirect to login page if load returns an error (session expired)
                    actionfailed: function(form, action) { if(action.type == "load") {window.location.href = '/login?expired=1' } }
                }
            });

            this.window = new Ext.Window({
                width: 590,             // (height will be calculated based on content)
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
                if(this.teamID)
                {
                // --- This is an existing team, load team data
                    this.form.getForm().baseParams.TeamID = this.teamID;
                    this.window.getEl().mask("Loading..."); // mask form while loading form data from server
                    this.window.setTitle("Edit Team");
                    this.form.getForm().load({url:"/data/get-team.php"});
                }
                else
                {
                // --- We are creating a new team, initialize form with default values
                    this.form.getForm().reset();  // clear form contents
                    this.form.getForm().baseParams.TeamID = -1;
                    this.form.getForm().findField("Archived").setValue(0);
                    this.window.setTitle("Add Team");
                    this.onSelectStatus();
                }
                this.setMessage('', 'black');                              // clear message area
                this.form.getForm().findField('TeamName').focus(true, 200);  // set initial focus
            }, this);
        }
        else
        {
            this.window.center();   // recenter dialog in browser window
        }

        // open window
        this.window.show(params.animateTarget);
    }

    this.onSelectStatus = function()
    {
        ctrlArchived = this.form.getForm().findField("Archived");
        if(ctrlArchived.value==1)
        {
            ctrlArchived.getEl().setStyle("color", "red");
        }
        else
        {
            ctrlArchived.getEl().setStyle("color", "green");
        }
    }

    this.cancelButtonClick = function()
    {
        this.window.hide();
        this.callback.apply(this.callbackScope, [this, false]);
    }

    this.saveButtonClick = function()
    {
    // --- show sending message in message area
        this.setMessage("Saving Team...", "black", true);
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
        this.setMessage("Team Saved", "green");
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
                this.setMessage("Team information is not complete. Fix fields marked in red.", "red");
                break;
            case Ext.form.Action.SERVER_INVALID:
            // --- failure message returned from code on the server
                this.setMessage("Error saving team: " + action.result.message, "red");
                break;
            case Ext.form.Action.CONNECT_FAILURE:
            // --- Failed to connect to server
                this.setMessage("Error saving team: Server did not respond", "red");
                break;
        }
    }

    this.setMessage = function(message, color, loading)
    {
        setFormMessage(message,color,loading);
        // recalculate window size to fit new contents (needed in IE)
        this.window.syncSize();
    }

    this.statusLookup = [[0, "Active", "Team is active and visible online", "/images/active-icon.png", "green"],
                         [1, "Archived", "Team is not visible online. All team data will be archived", "/images/archived-icon.png", "red"]];
}