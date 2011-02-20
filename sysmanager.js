// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
// --- Turn on validation errors beside the field globally and enable quick tips that will
// --- popup tooltip when mouse is hovered over field
    Ext.form.Field.prototype.msgTarget = 'qtip';
    Ext.QuickTips.init();
// --- Create form
    var teamForm = new C_TeamForm(Ext.get('manager-form'));
    teamForm.create();
});


function C_TeamForm(parentElement)
{
    this.holder = parentElement;    // div in center of form that holds form content
    this.form = null;
// --- Create details dialog
    this.teamDialog = new C_TeamDialog();

    this.create = function()
    {
        // create the Data Store
        this.ds = new Ext.data.JsonStore({
            root: 'results',                // results array is returned in this property
            totalProperty: 'rowcount',      // total number of rows is returned in this property
            idProperty: 'TeamID',       // defines the primary key for the results
            remoteSort: true,
            baseParams: {Archived: '0'},
            fields: [
                {name: 'TeamID', type: 'int'},
                {name: 'Archived', type: 'int'},
                {name: 'bRacing', type: 'int'},
                {name: 'bCommuting', type: 'int'},
                {name: 'Organization'},
                {name: 'SiteLevelID', type: 'int'},
                {name: 'SiteLevel'},
                {name: 'TeamName'},
                {name: 'Domain'}
            ],
            proxy: new Ext.data.HttpProxy({ url: 'data/list-teams.php' }),
            sortInfo: { field: 'TeamName', direction: 'asc' }
        });
        
        // toolbar
        var toolbar = new Ext.Toolbar({ style: 'padding: 4px 1px 4px 1px;', items: [
            'Search:', ' ', {
                xtype: 'textfield',
                id: 'SearchFor',
                width:120,
                listeners: { scope: this, specialkey: function(ctrl, e) { if(e.getKey() == e.ENTER) { this.filterList() } } }
            } , ' ', {
                cls: 'x-btn-icon',
                icon: 'images/search-icon.png',
                handler: this.filterList,
                scope: this
            }, 'Show:', {
                xtype: 'localcombobox',
                id: 'Archived',
                displayField: 'text',
                valueField: 'id',
                width:110,
                forceSelection: true,
                value: '0',
                listeners: {scope: this, select: this.filterList},
                store: new Ext.data.ArrayStore({ fields: ['id', 'text'], data: this.statusLookup })
            }, '->', {
                cls: 'x-btn-text-icon',
                id: 'btn-add-team',
                text: '<span style="color:green">&nbsp;Add Team</span>',
                icon: 'images/plus-icon.png',
                handler: this.onClickAddTeam,
                scope: this
            }
         ]});
             
        // paging bar
        var paging = new Ext.PagingToolbar({
                pageSize: 1000,
                store: this.ds,
                displayInfo: true,
                displayMsg: 'Displaying {0} - {1} of {2} Teams',
                emptyMsg: "No teams to display"
        })

        // column definitions
        var columns = [
                {header: "Team", width: 140, dataIndex: 'TeamName', id: 'autoexpand', sortable: true},
                {header: "Organization", width: 110, dataIndex: 'Organization', sortable: false},
                {header: "Domain", width: 110, dataIndex: 'Domain', sortable: true},
                {header: "Site Level", width: 100, dataIndex: 'SiteLevel', sortable: false},
                {xtype: 'booleancolumn', header: "R", width: 20, dataIndex: 'bRacing', trueText: '<b>&bull;</b>', falseText: '', fixed:true, menuDisabled:true, align:'center'},
                {xtype: 'booleancolumn', header: "C", width: 20, dataIndex: 'bCommuting', trueText: '<b>&bull;</b>', falseText: '', fixed:true, menuDisabled:true, align:'center'},
                {xtype: 'booleancolumn', header: "A", width: 20, dataIndex: 'Archived', trueText: '<span style="color:red">Y</span>', falseText: '', fixed:true, menuDisabled:true, align:'center'},
                {header: "Edit/Home/Mng/Arc", width: 110, renderer: this.drawButtons, align:'center', fixed:true, menuDisabled:true}
            ]
        
        // create the grid
        this.grid = new Ext.grid.GridPanel({
            frame: true,
            title: 'Team Manager',
            width: 740,
            autoExpandColumn: 'autoexpand',
            height: 500,
            margins:'3 3 3 3',
            cls: 'centered',
            stripeRows: true,
            view: new Ext.ux.grid.BufferView({
                scrollDelay: false,
                emptyText: 'No teams found matching the search criteria'
            }),
            loadMask: true,
            bbar: paging,
            tbar: toolbar,
            ds: this.ds,
            columns: columns,
            listeners: { scope: this,
                cellclick: function(grid, row, col, e) {
                    if (e.getTarget().id.search(/edit-btn/)==0) {   // did user click on edit button?
                        this.onClickEdit(grid.getStore().getAt(row).data.TeamID);
                    }
                    if (e.getTarget().id.search(/home-btn/)==0) {   // did user click on home button?
                        window.open(buildTeamBaseURL(grid.getStore().getAt(row).data.Domain) + "/index.php", "manager");
                    }
                    if (e.getTarget().id.search(/manage-btn/)==0) {   // did user click on manage button?
                        window.open(buildTeamBaseURL(grid.getStore().getAt(row).data.Domain) + "/team-manager.php", "manager");
                    }
                    if (e.getTarget().id.search(/delete-btn/)==0) {   // did user click on delete button?
                        this.onClickDelete(grid.getStore().getAt(row));
                    }
                }
            }
        });

    // --- Render the form
        this.grid.render(this.holder);
        this.filterList();
    }
    
    this.drawButtons = function(value, meta, record)
    {
        meta.attr = "style='padding-top:4px'";
        var html = '<span class="grid-button" id="edit-btn' + record.data.TeamID + '" title="Edit this Team">E</span>' +
                   '<span class="grid-button" id="home-btn' + record.data.TeamID + '" title="View Team\'s Home Page">H</span>' +
                   '<span class="grid-button" id="manage-btn' + record.data.TeamID + '" title="Manage Team">M</span>&nbsp;' +
                   '<span class="grid-button" id="delete-btn' + record.data.TeamID + '" style="color:red" title="Archive this Team">X</span>';
        return html;
    }
    
    this.filterList = function()
    {
        this.ds.baseParams.SearchFor = Ext.getCmp('SearchFor').getValue();
        this.ds.baseParams.Archived = Ext.getCmp('Archived').getValue();
    // --- reload list filtering by search term
        this.grid.getBottomToolbar().cursor = 0;      // reset paging bar back to page 1
        var bbar = this.grid.getBottomToolbar();
        this.ds.load({params: {start:bbar.cursor, limit:bbar.pageSize} });
    }

    this.onClickAddTeam = function()
    {
        this.teamDialog.show({
            animateTarget: 'btn-add-team',
            callback: function() {this.filterList()},
            scope:this
        });
    }

    this.onClickEdit = function(teamID)
    {
        this.teamDialog.show({
            teamID: teamID,
            animateTarget: 'edit-btn' + teamID,
            callback: function() {this.filterList()},
            scope:this
        });
    }

    this.onClickDelete = function(r)
    {
        Ext.Msg.show({
            title: "Confirm Archive",
            msg: "Are you sure you want to archive \"" + r.data.TeamName + "\"?",
            fn: function(btn) { if(btn=='yes') {
            // --- Mask this page and post delete request
                this.grid.getGridEl().mask("Archiving...");
                Ext.Ajax.request({
                    url: 'data/archive-team.php',
                    params: {ID: r.data.TeamID},
                    success: this.handleDeleteSuccess,
                    failure: this.handleDeleteFailure,
                    scope: this,
                    deletedRowID: r.id
                });
            } },
            scope: this,
            buttons: {yes:'&nbsp;Archive&nbsp;', no:'Cancel'}
        });
        
    }

    this.handleDeleteSuccess = function(response, options)
    {
        this.grid.getGridEl().unmask();
    // --- decode JSON response string and check status of delete
        var result = Ext.decode(response.responseText);
        if(result.success == false)
        {
            Ext.Msg.alert("Archive Failed", "Error Archiving Item: " + result.message);
        }
        else
        {
        // --- Find record using the data store's record ID (For existing records, the data store record
        // --- ID matches our db record ID. But for added records, Ext generates its own ID which
        // --- does not match our ID and cannot be changed)
            var record = this.ds.getById(options.deletedRowID);
        // --- remove record from the data store
            this.ds.remove(record);
        }
    }

    this.handleDeleteFailure = function(response)
    {
        this.grid.getGridEl().unmask();
        Ext.Msg.alert("Delete Failed", "Error Deleting Item: Server did not repond");
    }

    this.statusLookup = [["0", "Active Teams"], ["1", "Archived Teams"], ["-1", "All Teams"]];
}
