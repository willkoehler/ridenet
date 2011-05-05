// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
// --- Turn on validation errors beside the field globally and enable quick tips that will
// --- popup tooltip when mouse is hovered over field
    Ext.form.Field.prototype.msgTarget = 'qtip';
    Ext.QuickTips.init();
// --- Create form
    new C_ReportForm(Ext.get('report-form')).create();
});



function C_ReportForm(parentElement)
{
    this.holder = parentElement;    // div in center of form that holds form content
    this.form = null;
    var endDate = new Date();
    var startDate = new Date();
    startDate.setDate(endDate.getDate() - 28);

    this.create = function()
    {
        // create the Data Store
        this.ds = new Ext.data.JsonStore({
            root: 'results',                // results array is returned in this property
            idProperty: 'RiderID',       // defines the primary key for the results
            remoteSort: true,
            baseParams: {Archived: '0'},
            fields: [
                {name: 'RiderID', type: 'int'},
                {name: 'LastName'},
                {name: 'FirstName'},
                {name: 'CDays', type: 'int'}
            ],
            proxy: new Ext.data.HttpProxy({ url: '/data/list-commute-report.php?T=' + g_teamID }),
            sortInfo: { field: 'CDays', direction: 'desc' }
        });
        
        // toolbar
        var toolbar = new Ext.Toolbar({ style: 'padding: 4px 1px 4px 1px;', items: [
            '&nbsp;', {
                xtype: 'datefield',
                id: 'StartDate',
                format: 'n/j/Y',
                value: startDate,
                width: 85,
                listeners: {scope: this, select: this.filterList}
            } , ' ', 'to&nbsp;', {
                xtype: 'datefield',
                id: 'EndDate',
                format: 'n/j/Y',
                value: endDate,
                width: 85,
                listeners: {scope: this, select: this.filterList}
            } , '&nbsp;&nbsp;', 'Days Required:', {
                xtype: 'numberfield',
                id: 'Tolerance',
                value: 12,
                width: 30,
                listeners: { scope: this,
                    specialkey: function(ctrl, e) { if(e.getKey() == e.ENTER) { this.filterList() } },
                    change: function(ctrl, e) { this.filterList() }
                }
            }
         ]});
             
        // column definitions
        var columns = [
                {header: "Last Name", width: 100, dataIndex: 'LastName', sortable: true},
                {header: "First Name", width: 100, dataIndex: 'FirstName', sortable: true},
                {header: "Commuting Days", width: 110, dataIndex: 'CDays', renderer: function (value) { return (value>0) ?  value : "-" }, sortable: true, align:'center'},
                {header: "Qualifies", width: 60, dataIndex: 'CDays', fixed:true, menuDisabled:true, align:'center', renderer: function (value) {
                        return (value >= Ext.getCmp('Tolerance').getValue()) ? "<span style='color:red'>Y</span>" : "";
                    }
                },
                {header: "", width: 70, dataIndex: '', renderer: this.drawButtons, align:'center', fixed:true, menuDisabled:true}
            ]
        
        // create the grid
        this.grid = new Ext.grid.GridPanel({
            frame: true,
            title: 'Commuting Report',
            width: 460,
            autoHeight: true,
            margins:'3 3 3 3',
            cls: 'centered',
            loadMask: true,
            tbar: toolbar,
            ds: this.ds,
            columns: columns,
            listeners: { scope: this,
                cellclick: function(grid, row, col, e) {
                    if (e.getTarget().id.search(/profile-btn/)==0) {   // did user click on profile button?
                        window.open("profile.php?RiderID=" + grid.getStore().getAt(row).data.RiderID, "profile");
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
        var html = '<span class="grid-button" id="profile-btn' + record.data.RiderID + '" title="View Rider Profile">Profile...</span>';
        return html;
    }
    
    this.filterList = function()
    {
        this.ds.baseParams.StartDate = Ext.getCmp('StartDate').getRawValue();
        this.ds.baseParams.EndDate = Ext.getCmp('EndDate').getRawValue();
        this.ds.baseParams.Tolerance = Ext.getCmp('Tolerance').getValue();
        this.ds.load();
    }
}
