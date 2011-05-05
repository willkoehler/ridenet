function C_ProfileDialog()
{
    this.window = null;

    // -------------------------------------------------------------------------------------------
    //  Show rider dialog.
    //  params object has the following parameters:
    //      riderID     - ID of rider. 
    //      callback    - Function that will be called when rider is saved
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
                {name: 'RacingTeamID', type: 'int'},
                {name: 'CommutingTeamID', type: 'int'},
                {name: 'RacingTeamName'},
                {name: 'CommutingTeamName'},
                {name: 'RiderEmail'},
                {name: 'DateOfBirth', type: 'date', dateFormat: 'Y-m-d'},
                {name: 'FavoriteQuote'},
                {name: 'FavoriteRide'},
                {name: 'FavoriteFood'},
                {name: 'WhyIRide'},
                {name: 'MyCommute'},
                {name: 'BornIn'},
                {name: 'ResideIn'},
                {name: 'Occupation'},
                {name: 'RiderTypeID', type: 'int'},
                {name: 'YearsCycling', type: 'int'},
                {name: 'Height'},
                {name: 'Weight', type: 'int'},
                {name: 'URL'},
                {name: 'CommuteMapURL'},
                {name: 'Archived', type: 'int'},
                {name: 'RiderPictureID', mapping: 'RiderPictureID'},
                {name: 'RiderActionPictureID', mapping: 'RiderPictureID'}
            ]);

            this.form = new Ext.form.FormPanel({
                url:'/data/post-rider.php',
                frame:true,
                bodyStyle:'padding:5px 5px 0',
                labelAlign: 'right',
                labelWidth: 110,
                buttonAlign:'center',
                cls: 'centered',
                reader: reader,
                fileUpload: true,
                baseParams: { },    // additional parameters passed to post request
                items: [{
                    xtype:'container', layout:'column', items: [{
                    xtype:'container', width: 555, style:"padding-top:7px", items: [{
                        xtype:'container', layout:'column', items: [{
                            xtype:'container', layout:'form', width: 330, items: [{
                            // === Email ===
                                xtype: 'textfield',
                                fieldLabel: 'Email',
                                name: 'RiderEmail',
                                width: 210,
                                vtype: 'email',
                                allowBlank: false,
                                blankText: 'You must enter an email'
                            }]
                        },{
                            xtype:'container', width: 120, items: [{
                            // === Info ===
                                xtype: 'displayfield',
                                style: 'padding-top:3px; color:#666',
                                html: '(not shared publicly)'
                            }]
                        },{
                            xtype:'container', width: 100, items: [{    // extra layer needed for IE7
                            // === Change PW button ===
                                xtype: 'button',
                                text: '&nbsp;Password...&nbsp;',
                                handler: function() { this.window.getEl().mask(); window.location.href="/change-pw.php" },
                                scope: this,
                                width: 100
                            }]
                        }]
                    },{
                        xtype:'container', layout:'column', items: [{
                            xtype:'container', layout:'form', width: 450, items: [{
                            // === Team Name ===
                                xtype: 'displayfield',
                                fieldLabel: 'Team',
                                style: 'padding:3px 5px 0px 0px;font-weight:bold',
                                name: 'TeamName'
                            }]
                        },{
                            xtype:'container', width: 100, items: [{    // extra layer needed for IE7
                            // === Change teams button ===
                                xtype: 'button',
                                text: '&nbsp;Change Teams...',
                                width: 100,
                                handler: this.changeTeamsButtonClick,
                                scope: this
                            }]
                        }]
                    },{
                        xtype:'container', layout:'column', items: [{
                            xtype:'container', layout:'form', width: 225, items: [{
                            // === Birth date ===
                                xtype: 'datefield',
                                fieldLabel: 'Date of Birth',
                                format: 'n/j/Y',
                                name: 'DateOfBirth',
                                width: 100
                            }]
                        },{
                        // === Info ===
                            xtype: 'displayfield',
                            style: 'padding-top:3px; color:#666',
                            html: '(only your age will be shared publicly)'
                        }]
                    },{
                        xtype:'container', layout:'form', items: [{
                        // === Born In ===
                            xtype: 'textfield',
                            fieldLabel: 'Born In',
                            name: 'BornIn',
                            width: 320
                        }]
                    },{
                        xtype:'container', layout:'form', items: [{
                        // === Resides In ===
                            xtype: 'textfield',
                            fieldLabel: 'Resides In',
                            name: 'ResideIn',
                            width: 320
                        }]
                    },{
                        xtype:'container', layout:'form', items: [{
                        // === Occupation (shown for teams with only commuter features) ===
                            xtype: 'textfield',
                            fieldLabel: 'Occupation',
                            name: 'Occupation',
                            width: 320
                        }]
                    },{
                        xtype:'container', layout:'column', items: [{
                            xtype:'container', layout:'form', items: [{
                            // === RiderType ===
                                xtype: 'localcombobox',
                                fieldLabel: 'Rider Type',
                                displayField: 'text',
                                valueField: 'id',
                                hiddenName: 'RiderTypeID',
                                forceSelection: true,
                                width: 140,
                                allowBlank: false,
                                blankText: 'You must select a rider type',
                                store: new Ext.data.ArrayStore({ fields: ['id', 'text'], data: riderTypeLookup })
                            }]
                        },{
                            xtype:'container', layout:'form', labelWidth:95, items: [{
                            // === Years Cycling ===
                                xtype: 'numberfield',
                                fieldLabel: 'Years Cycling',
                                name: 'YearsCycling',
                                width: 40
                            }]
                        }]
                    },{
                        xtype:'container', layout:'column', items: [{
                            xtype:'container', layout:'form', items: [{
                            // === Height ===
                                xtype: 'textfield',
                                fieldLabel: 'Height',
                                name: 'Height',
                                width: 40
                            }]
                        },{
                            xtype:'container', layout:'form', labelWidth:55, width: 290, items: [{
                            // === Weight ===
                                xtype: 'numberfield',
                                fieldLabel: 'Weight',
                                name: 'Weight',
                                width: 40
                            }]
                        }]
                    },{
                        xtype:'container', layout:'form', items: [{
                        // === Favorite Food ===
                            xtype: 'textfield',
                            fieldLabel: 'Favorite Food',
                            name: 'FavoriteFood',
                            width: 430
                        }]
                    },{
                        xtype:'container', layout:'form', items: [{
                        // === Why I Ride (only shown for commuter teams) ===
                            xtype: 'textarea',
                            fieldLabel: 'Why I Ride',
                            name: 'WhyIRide',
                            style: 'overflow:hidden',
                            maxLength: 500,
                            width: 430,
                            height: 60
                        }]
                    },{
                        xtype:'container', layout:'form', items: [{
                        // === Quote (not shown for commuter teams) ===
                            xtype: 'textarea',
                            fieldLabel: 'Favorite Quote',
                            name: 'FavoriteQuote',
                            style: 'overflow:hidden',
                            maxLength: 500,
                            width: 430,
                            height: 60
                        }]
                    },{
                        xtype:'container', layout:'form', items: [{
                        // === Favorite Ride (not shown for commuter teams) ===
                            xtype: 'textarea',
                            fieldLabel: 'Favorite Ride',
                            name: 'FavoriteRide',
                            style: 'overflow:hidden',
                            maxLength: 500,
                            width: 430,
                            height: 60
                        }]
                    },{
                        xtype:'container', layout:'form', items: [{
                        // === My Commute (only shown for commuter teams) ===
                            xtype: 'textarea',
                            fieldLabel: 'Describe Your Commute',
                            name: 'MyCommute',
                            style: 'overflow:hidden',
                            maxLength: 500,
                            width: 430,
                            height: 60
                        }]
                    },{
                        xtype:'container', layout:'column', items: [{
                            xtype:'container', layout:'form', items: [{
                            // === Commute Route URL ===
                                xtype: 'textfield',
                                fieldLabel: 'Commute Map',
                                name: 'CommuteMapURL',
                                width: 280,
                                vtype: 'url'
                            }]
                        },{
                        // === Info ===
                            xtype: 'displayfield',
                            style: 'padding:3px 0 0 10px; color:#666',
                            html: '(link to route on <a style="color:#6761BA" href="http://www.mapmyride.com" target="_blank">MapMyRide</a>)'
                        }]
                    },{
                        xtype:'container', layout:'form', items: [{
                        // === URL ===
                            xtype: 'textfield',
                            fieldLabel: 'Website/Blog URL',
                            name: 'URL',
                            width: 430,
                            vtype: 'url'
                        }]
                    }]
                },{
                    xtype:'container', width: 190, items: [{

                            // === Rider Portrait Fieldset ===
                            xtype: 'fieldset', height: 279, title: 'Portrait', cls: 'tighten-fieldset-legend', style: 'padding-bottom: 5px', items: [{
                                xtype:'container', items: [{
                                // === Rider Photo ===
                                    xtype: 'displayfield',
                                    name: 'RiderPictureID',
                                    height: 202,
                                    width: 162,
                                    style: 'padding: 0px; text-align:center; border: 1px solid black;',
                                    setValue: function(val) {
                                        this.value = val;
                                        if(this.rendered) 
                                        {   
                                            var pendingUpload = Ext.getCmp("portrait-file").getValue();
                                            if(pendingUpload!="")
                                            {
                                                this.el.update('The image "' + pendingUpload + '" will be uploaded when you click "Save"');
                                            }
                                            else
                                            {
                                                params = (val!="") ? val.split(",") : [[0],[0]];    // value will be "RiderID, TeamID"
                                                this.el.update('<img src="' + getFullDomainRoot() + '/dynamic-images/rider-portrait.php?RiderID=' + params[0] + '&T=' + params[1] + '&x=' + Math.random() + '" Height=200 Width=160>');
                                            }
                                        }
                                    }
                                }]
                            },{
                            // === Instructions ===
                                xtype: 'container',
                                style: 'padding-top:2px',
                                html: 'Recommended size 160w x 200h'
                            },{    
                                xtype: 'container', width: 86, style: 'margin: 8px auto 0px;', items: [{
                                // === Select File to Upload ===
                                    xtype: 'fileuploadfield',
                                    name: 'PictureFile',
                                    id: 'portrait-file',
                                    buttonOnly: true,
                                    buttonCfg: { icon: '/images/choose-picture-icon.png', text: '&nbsp;Upload New'},
                                    listeners: { scope: this, 'fileselected' : function(fb, v) {
                                        this.form.getForm().findField("RiderPictureID").setValue(v);
                                    }}
                                }]
                            }]  // end of fieldset
                        },{
                            // === Rider Action Shot Fieldset ===
                            xtype: 'fieldset', height: 198, title: 'Action Shot', cls: 'tighten-fieldset-legend', style: 'padding-bottom: 5px', items: [{
                                xtype:'container', items: [{
                                // === Action Shot ===
                                    xtype: 'displayfield',
                                    name: 'RiderActionPictureID',
                                    height: 122,
                                    width: 162,
                                    style: 'padding: 0px; text-align:center; border: 1px solid black;',
                                    setValue: function(val) {
                                        this.value = val;
                                        if(this.rendered) 
                                        {   
                                            var pendingUpload = Ext.getCmp("action-shot-file").getValue();
                                            if(pendingUpload!="")
                                            {
                                                this.el.update('The image "' + pendingUpload + '" will be uploaded when you click "Save"');
                                            }
                                            else
                                            {
                                                params = (val!="") ? val.split(",") : [[0],[0]];    // value will be "RiderID, TeamID"
                                                this.el.update('<img src="' + getFullDomainRoot() + '/dynamic-images/rider-action-shot-sm.php?RiderID=' + params[0] + '&T=' + params[1] + '&x=' + Math.random() + '">');
                                            }
                                        }
                                    }
                                }]
                            },{
                            // === Instructions ===
                                xtype: 'container',
                                style: 'padding-top:2px',
                                html: 'Recommended size 600w x 450h'
                            },{
                                xtype: 'container', width: 86, style: 'margin: 8px auto 0px;', items: [{
                                // === Select File to Upload ===
                                    xtype: 'fileuploadfield',
                                    name: 'ActionPictureFile',
                                    id: 'action-shot-file',
                                    buttonOnly: true,
                                    buttonCfg: { icon: '/images/choose-picture-icon.png', text: '&nbsp;Upload New'},
                                    listeners: { scope: this, 'fileselected' : function(fb, v) {
                                        this.form.getForm().findField("RiderActionPictureID").setValue(v);
                                    }}
                                }]
                            }]  // end of fieldset
                        },{
                        // === Info ===
                            xtype: 'displayfield',
                            style: 'padding:0 0 0 5px; color:#666',
                            html: '<b>NOTE:</b> This is your public profile. All information is optional except Rider Type and Email Address.'
                        }]  // end of column
                    }]  // end of column container
                },{
            // === Message Field (just above buttons) ===
                    xtype: 'container',
                    id: 'status-msg',
                    style: 'display: none',     // start with message field hidden
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
                        // build team name to display
                        var racingTeam = action.reader.jsonData.results.RacingTeamName;
                        var commutingTeam = action.reader.jsonData.results.CommutingTeamName;
                        var team = (racingTeam==commutingTeam) ? racingTeam : racingTeam + ' | ' + commutingTeam;
                        this.form.getForm().findField('TeamName').setValue(team);
                        this.form.getEl().unmask();
                    }},
                    // redirect to login page if load returns an error (session expired)
                    actionfailed: function(form, action) { if(action.type == "load") {window.location.href = 'login.php?expired=1' } }
                }
            });

            this.window = new Ext.Window({
                width: 795,             // (height will be calculated based on content)
                y: 25,
                title: "Edit Your Profile",
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
            // --- Mask form while data is loading
                this.form.getEl().mask("Loading...");
            // --- load rider record
                this.form.baseParams.RiderID = g_Session.riderID;
                this.form.load({url:"/data/get-rider.php"});
                this.setMessage('', 'black');             // clear message area
                this.form.getForm().findField('RiderEmail').focus(true, 300);  // set initial focus
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
        if(!this.form.getForm().isValid())
        {
            this.setMessage("Profile information is not complete. Fix fields marked in red.", "red");
        }
        else
        {
            // Safari often hangs when uploading a file. To prevent this we first need to make
            // an ajax request to a page that closes the connection to the server. Then we can
            // proceed with the file upload post.
            Ext.Ajax.request( {url: '/data/close-connection.php', scope: this, success: function() {
            // --- show sending message in message area
                this.setMessage("Saving Rider Profile...", "black", true);
            // --- disable dialog
                this.form.getEl().mask();
            // --- submit form data
                this.form.getForm().submit({
                    reset: false,
                    success: this.onPostSuccess,
                    failure: this.onPostFailure,
                    scope: this
                 });
            }});
        }
    }
    
    this.changeTeamsButtonClick = function(btn)
    {
        var r = this.form.reader.jsonData.results;
        g_changeTeamsDialog.show({
            racingTeamID: r.RacingTeamID,
            racingTeamName: r.RacingTeamName,
            commutingTeamID: r.CommutingTeamID,
            commutingTeamName: r.CommutingTeamName,
            animateTarget: btn.el
        });
    }

    this.onPostSuccess = function(form, action)
    {
        this.form.getForm().findField("PictureFile").reset();           // clear picture file selection
        this.form.getForm().findField("ActionPictureFile").reset();     // clear action picture file selection
        this.form.getForm().findField("RiderPictureID").setValue(g_Session.riderID + ", " + g_pt);
        this.form.getForm().findField("RiderActionPictureID").setValue(g_Session.riderID + ", " + g_pt);
        // reload page - this must be done to force browser to refresh profile images in the cache
        window.location.reload();
    }

    this.onPostFailure = function(form, action)
    {
        this.form.getEl().unmask();   // enable form
        switch(action.failureType) {
            case Ext.form.Action.CLIENT_INVALID:
            // --- client-side validation failed
                this.setMessage("Profile information is not complete. Fix fields marked in red.", "red");
                break;
            case Ext.form.Action.SERVER_INVALID:
            // --- failure message returned from code on the server
                this.setMessage("Error saving profile: " + action.result.message, "red");
                break;
            case Ext.form.Action.CONNECT_FAILURE:
            // --- Failed to connect to server
                this.setMessage("Error saving profile: Server did not respond", "red");
                break;
        }
    }

    this.setMessage = function(message, color, loading)
    {
        setFormMessage(message,color,loading);
        // recalculate window size to fit new contents (needed in IE)
        this.window.syncSize();
    }

    this.onFormChanged = function()
    {
        this.setMessage("", 'black');    // clear status message
    }

}

