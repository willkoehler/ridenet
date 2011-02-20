function C_RiderTab()
{
// --- Create details dialog
    this.riderDialog = new C_RiderDialog();

    this.create = function()
    {
        // create the Data Store
        this.ds = new Ext.data.JsonStore({
            root: 'results',                // results array is returned in this property
            totalProperty: 'rowcount',      // total number of rows is returned in this property
            idProperty: 'RiderID',           // defines the primary key for the results
            remoteSort: true,
            baseParams: {},
            fields: [
                {name: 'RiderID', type: 'int'},
                {name: 'RiderEmail'},
                {name: 'LastName'},
                {name: 'FirstName'},
                {name: 'RiderType'},
                {name: 'sTeamAdmin', type: 'bool'}
            ],
            sortInfo: { field: 'LastName', direction: 'asc' },
            proxy: new Ext.data.HttpProxy({ url: 'data/list-riders-sm.php' })
        });
        
        // rider search toolbar
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
            }, '->', {
                cls: 'x-btn-text-icon',
                id: 'btn-add-rider',
                text: '<span style="color:green">&nbsp;Add Rider</span>',
                icon: 'images/plus-icon.png',
                handler: this.onClickAddRider,
                scope: this
            }
         ]});
             
        // paging bar
        var paging = new Ext.PagingToolbar({
                pageSize: 1000,
                store: this.ds,
                displayInfo: true,
                displayMsg: 'Displaying {0} - {1} of {2} Riders',
                emptyMsg: "No riders to display"
        })

        // column definitions
        var columns = [
                {header: "Last Name", width: 120, dataIndex: 'LastName', sortable: true},
                {header: "First Name", width: 120, dataIndex: 'FirstName', sortable: true},
                {header: "Email", width: 90, dataIndex: 'RiderEmail', sortable: true, id: 'autoexpand'},
                {header: "Type", width: 120, dataIndex: 'RiderType', sortable: true},
                {xtype: 'booleancolumn', header: "Admin", width: 40, dataIndex: 'sTeamAdmin', trueText: '<span style="color:red">Y</span>', falseText: '', fixed:true, menuDisabled:true, align:'center'},
                {header: "Edit/Del", width: 70, renderer: this.drawButtons, align:'center', fixed:true, menuDisabled:true}
            ]
        
        // create the rider grid
        this.grid = new Ext.grid.GridPanel({
            frame: true,
            title: 'Rider Accounts',
            width: 700,
            autoExpandColumn: 'autoexpand',
            height: 450,
            margins:'3 3 3 3',
            stripeRows: true,
            view: new Ext.ux.grid.BufferView({
                scrollDelay: false,
                emptyText: 'No riders found matching the search criteria'
            }),
            loadMask: true,
            bbar: paging,
            tbar: toolbar,
            ds: this.ds,
            columns: columns,
            listeners: { scope: this,
                cellclick: function(grid, row, col, e) {
                    if (e.getTarget().id.search(/edit-btn/)==0) {   // did user click on edit button?
                        this.onClickEdit(grid.getStore().getAt(row).data.RiderID);
                    }
                    if (e.getTarget().id.search(/remove-btn/)==0) {   // did user click on remove button?
                        var r = grid.getStore().getAt(row);
                        this.onClickRemoveRider(r.data.RiderID, r.data.FirstName, r.data.LastName, r.id);
                    }
                },
                // load rider data the first time the tab is activated
                activate: function() { if(!this.loaded) { this.filterList(); this.loaded=true; } }
            }
        });
        
        return(this.grid);
    }
    
    this.drawButtons = function(value, meta, record)
    {
        meta.attr = "style='padding-top:4px'";
        var html = '<span class="grid-button" id="edit-btn' + record.data.RiderID + '" title="Edit this Rider">Edit</span>&nbsp;' + 
                   '<span class="grid-button" id="remove-btn' + record.data.RaceID + '" style="color:red" title="Remove this rider from your team">X</span>';

        return html;
    }
    
    this.filterList = function()
    {
        this.ds.baseParams.Name = Ext.getCmp('SearchFor').getValue();
        this.ds.baseParams.TeamID = g_pt;
    // --- reload list filtering by search term
        this.grid.getBottomToolbar().cursor = 0;      // reset paging bar back to page 1
        var bbar = this.grid.getBottomToolbar();
        this.ds.load({params: {start:bbar.cursor, limit:bbar.pageSize} });
    }

    this.onClickAddRider = function()
    {
        this.riderDialog.show({
            animateTarget: 'btn-add-rider',
            callback: function() {this.filterList()},
            scope:this
        });
    }

    this.onClickEdit = function(riderID)
    {
        this.riderDialog.show({
            riderID: riderID,
            animateTarget: 'edit-btn' + riderID,
            callback: function() {this.filterList()},
            scope:this
        });
    }

    this.onClickRemoveRider = function(riderID, firstName, lastName, recordID)
    {
        Ext.Msg.show({
            title: "Confirm Remove",
            msg: "Are you sure you want to remove " + firstName + " " + lastName + " from your team?  \
                  This will not delete " + firstName + " " + lastName + "'s RideNet account. " + firstName + " will be \
                  moved to the RideNet Sandbox team and will be able to join another team.",
            fn: function(btn) { if(btn=='yes') {
            // --- Mask this page and post remove rider request
                this.grid.getGridEl().mask("Removing...");
                Ext.Ajax.request({
                    url: 'data/remove-rider-sm.php',
                    params: {RiderID: riderID, TeamID: g_pt},
                    success: this.handleRemoveSuccess,
                    failure: this.handleRemoveFailure,
                    scope: this,
                    removedRowID: recordID
                });
            } },
            scope: this,
            buttons: {yes:'&nbsp;Remove&nbsp;', no:'Cancel'}
        });
        
    }

    this.handleRemoveSuccess = function(response, options)
    {
        this.grid.getGridEl().unmask();
    // --- decode JSON response string and check status of request
        var result = Ext.decode(response.responseText);
        if(result.success == false)
        {
            Ext.Msg.alert("Remove Rider Failed", "Error Removing Rider: " + result.message);
        }
        else
        {
        // --- Find record using the data store's record ID (For existing records, the data store record
        // --- ID matches our db record ID. But for added records, Ext generates its own ID which
        // --- does not match our ID and cannot be changed)
            var record = this.ds.getById(options.removedRowID);
        // --- remove record from the data store
            this.ds.remove(record);
        }
    }

    this.handleRemoveFailure = function(response)
    {
        this.grid.getGridEl().unmask();
        Ext.Msg.alert("Remove Rider Failed", "Error Removing Rider: Server did not repond");
    }
}
