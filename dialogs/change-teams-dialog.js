function C_ChangeTeamsDialog()
{
    this.window = null;
    var helpmsg = "<div class=help-title>Share Your Profile with Two Teams</div> \
                   <div class=help-body> \
                   If you belong to a racing team and a workplace commuter team, you can share your \
                   profile between both teams by checking \"share my profile between two teams\" \
                   <ul class=help>\
                   <li class=help>Your profile will appear in both team rosters.\
                   <li class=help>Your race results will appear on your racing team\'s page.\
                   <li class=help>Your commute and errand rides will count towards your commuting team\'s stats.\
                   </ul></div>";
    g_helpDialog = new C_HelpDialog(helpmsg);

    // -------------------------------------------------------------------------------------------
    //  Show change team dialog.
    //  params object has the following parameters:
    //      racingTeamID        - ID of rider's racing team
    //      racingTeamName      - Name of rider's racing team
    //      commutingTeamID     - ID of rider's commuting team
    //      commutingTeamName   - Name of rider's commuting team
    //      animateTarget   - id of HTML target to animate opening/closing window
    // -------------------------------------------------------------------------------------------
    this.show = function(params)
    {
        this.riderID = params.riderID;
        this.racingTeamID = params.racingTeamID;
        this.racingTeamName = params.racingTeamName;
        this.commutingTeamID = params.commutingTeamID;
        this.commutingTeamName = params.commutingTeamName;

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
                url:'/data/change-teams.php',
                labelAlign: 'right',
                labelWidth: 110,
                bodyStyle:'padding:0px',
                buttonAlign:'center',
                baseParams: { },    // additional parameters passed to post request
                items: [{
                    xtype: 'container', cls: 'form-spacer', height:10
                },{
                // === Racing Team ===
                    xtype: 'remotecombobox',
                    fieldLabel: 'Racing Team',
                    displayField: 'TeamName',
                    valueField: 'TeamID',
                    hiddenName: 'RacingTeamID',
                    forceSelection: true,
                    maxHeight: 450,
                    width: 300,
                    listWidth: 350,
                    pageSize: 100,
                    triggerClass: 'x-form-search-trigger',
                    emptyText: 'Type the first few letters of the team name...',
                    allowBlank: false,
                    blankText: 'You must select a team',                    
                    store: this.dsTeamLookup,
                    listeners: { scope: this, select: function(c, r, i) {
                        if(!Ext.getCmp('two-teams-cb').getValue())
                        {
                            // User is only member of one team. Keep both team combos in sync
                            this.form.getForm().findField('CommutingTeamID').setValue(r.data.TeamID);
                            this.form.getForm().findField('CommutingTeamID').setRawValue(r.data.TeamName);
                        }
                    }},
                    tpl:'<tpl for="."><div class="x-combo-list-item" style="border-bottom:1px solid #ccc"><table cellpadding=0 cellspacing=0><tr>\
                           <td><div class="ellipses" style="padding-left:5px;width:210px">\
                             <div class="find-name">{TeamName}</div>\
                             <div class="find-info">{TeamType}</div>\
                           </div></td>\
                           <td style="height:32px;width:120px;text-align:center"><img src="' + getFullDomainRoot() + '/dynamic-images/team-logo-fit.php?T={TeamID}"></td>\
                         </tr></table>\
                         </div></tpl>'
                },{
                    xtype: 'panel', id: 'team2', layout: 'form', baseCls: 'x-plain', collapsed: true, items: [{
                    // === Commuting Team ===
                        xtype: 'remotecombobox',
                        fieldLabel: 'Commuting Team',
                        displayField: 'TeamName',
                        valueField: 'TeamID',
                        hiddenName: 'CommutingTeamID',
                        forceSelection: true,
                        maxHeight: 450,
                        width: 300,
                        listWidth: 350,
                        pageSize: 100,
                        triggerClass: 'x-form-search-trigger',
                        emptyText: 'Type the first few letters of the team name...',
                        allowBlank: false,
                        blankText: 'You must select a team',                    
                        store: this.dsTeamLookup,
                        tpl:'<tpl for="."><div class="x-combo-list-item" style="border-bottom:1px solid #ccc"><table cellpadding=0 cellspacing=0><tr>\
                               <td><div class="ellipses" style="padding-left:5px;width:210px">\
                                 <div class="find-name">{TeamName}</div>\
                                 <div class="find-info">{TeamType}</div>\
                               </div></td>\
                               <td style="height:32px;width:120px;text-align:center"><img src="' + getFullDomainRoot() + '/dynamic-images/team-logo-fit.php?T={TeamID}"></td>\
                             </tr></table>\
                             </div></tpl>'
                    }]
                },{
                    xtype: 'container', cls: 'form-spacer', height:5
                },{
                    xtype: 'container', layout: 'column', items: [{
                        xtype: 'container',
                        html: '&nbsp;',
                        width: 15
                    },{
                        xtype: 'checkbox',
                        id: 'two-teams-cb',
                        hideLabel: true,
                        boxLabel: 'Share my profile between two teams',
                        width: 200,
                        listeners: { scope: this, check: this.checkMutipleTeams }
                    },{
                        xtype: 'container',
                        style: 'padding-top:3px',
                        html: '<span id="help-btn" onclick="g_helpDialog.show({ ypos:100, width:530, animateTarget: \'help-btn\' });">info</span>'
                    }]
                },{
                    xtype: 'container', cls: 'form-spacer', height:15
                },{
                    xtype: 'fieldset', title: 'Create New Team', style: 'margin-bottom:5px', items: [{
                        xtype: 'displayfield',
                        hideLabel: true,
                        style: 'xmargin:20px 0px 10px 15px; font: 12px arial;color:#666',
                        html: 'If your team is not on RideNet yet, send as an email <a href="mailto:info@ridenet.net" style="color:blue"> \
                               info@ridenet.net</a> and we\'ll create a team for you.'
                    }]
                },{
            // === Message Field (just above buttons) ===
                    xtype: 'container',
                    id: 'status-msg2',
                    style: 'display:none',   // start off hidden initially
                    cls: 'form-status'
                }],

                buttons: [{
                    text: 'Change Teams',
                    width: 100,
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
                title: 'Change Teams',
                width: 460,             // (height will be calculated based on content)
                y: 55,
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
                this.form.getForm().reset();
                Ext.getCmp('two-teams-cb').setValue((this.racingTeamID==this.commutingTeamID) ? 0 : 1);
                this.checkMutipleTeams();
                this.setMessage('', 'black');           // clear message area
            }, this);
        }

        // open window
        this.window.show(params.animateTarget);
    }
    
    this.checkMutipleTeams = function()
    {
        if(Ext.getCmp('two-teams-cb').getValue())
        {
            // show selection for both racing and commuting teams
            Ext.getCmp('team2').expand(true);
            this.form.getForm().findField('RacingTeamID').label.update('Racing Team:');
        }
        else
        {
            // show single team selection
            Ext.getCmp('team2').collapse(true);
            this.form.getForm().findField('RacingTeamID').label.update('Choose New Team:');
            // synchronize the two team selections
            if(this.form.getForm().findField('RacingTeamID').getValue())
            {
                this.form.getForm().findField('CommutingTeamID').setValue(this.form.getForm().findField('RacingTeamID').getValue());
                this.form.getForm().findField('CommutingTeamID').setRawValue(this.form.getForm().findField('RacingTeamID').getRawValue());
            }
            else
            {
                this.form.getForm().findField('CommutingTeamID').reset();
            }
        }
    }

    this.cancelButtonClick = function()
    {
        this.window.hide();
    }

    this.saveButtonClick = function()
    {
        this.setMessage("Changing Teams...", "black", true);
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
        // reload page - this will redirect rider to their new team site
        window.location.reload();
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
                this.setMessage("Error changing teams: " + action.result.message, "red");
                break;
            case Ext.form.Action.CONNECT_FAILURE:
            // --- Failed to connect to server
                this.setMessage("Error changing teams: Server did not respond", "red");
                break;
        }
    }

    this.setMessage = function(message, color, loading)
    {
        setFormMessage(message,color,loading,'status-msg2');
        // recalculate window size to fit new contents (needed in IE)
        this.window.syncSize();
    }
}
