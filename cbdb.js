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
                {name: 'TeamName'},
                {name: 'Domain'},
                {name: 'TotalRiders', type: 'int'},
                {name: 'ActiveRiders', type: 'int'},
                {name: 'TotDaysMonth', type: 'int'},
                {name: 'AvgDaysMonth', type: 'float'},
                {name: 'LastActivity', type: 'int'}
            ],
            proxy: new Ext.data.HttpProxy({ url: 'data/list-cbdb.php' }),
            sortInfo: { field: 'TeamName', direction: 'asc' }
        });
        
        // Team search bar
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
            }
         ]});

        // paging bar
        var paging = new Ext.PagingToolbar({
                pageSize: 100,
                store: this.ds,
                displayInfo: true,
                displayMsg: 'Displaying {0} - {1} of {2} Teams',
                emptyMsg: "No teams to display"
        })

        var teamT = new Ext.XTemplate('<a href="{[buildTeamBaseURL(values.Domain)]}" target="_blank" style="color:#5260C1">{TeamName}</a>').compile();;
        var activeT = new Ext.XTemplate('<tpl if="ActiveRiders">{ActiveRiders}</tpl><tpl if="ActiveRiders==0">-</tpl>').compile();
        var totdmT = new Ext.XTemplate('<tpl if="TotDaysMonth">{TotDaysMonth}</tpl><tpl if="TotDaysMonth==0">-</tpl>').compile();
        var avgdmT = new Ext.XTemplate('<tpl if="AvgDaysMonth">{AvgDaysMonth:number("0,000.00")}</tpl><tpl if="AvgDaysMonth==0">-</tpl>').compile();
        var lastT = new Ext.XTemplate('<tpl if="LastActivity===\'\'">-</tpl><tpl if="LastActivity===0 | LastActivity===1 | LastActivity===2">RECENT</tpl><tpl if="LastActivity &gt; 2">{LastActivity} weeks</tpl>').compile();

        var columns = [
                {xtype: 'templatecolumn', header: 'Team', width: 120, dataIndex: 'TeamName', tpl: teamT, sortable: true, id: 'autoexpand' },
                {header: 'Riders', width: 50, dataIndex: 'TotalRiders', align: 'center', sortable: true},
                {xtype: 'templatecolumn', header: 'Active', width: 60, dataIndex: 'ActiveRiders', tpl: activeT, align: 'center', sortable: true},
                {xtype: 'templatecolumn', header: 'Tot D/M', width: 60, dataIndex: 'TotDaysMonth', tpl: totdmT, align: 'center', sortable: true},
                {xtype: 'templatecolumn', header: 'Avg D/M', width: 60, dataIndex: 'AvgDaysMonth', tpl: avgdmT, align: 'center', sortable: true},
                {xtype: 'templatecolumn', header: 'Last Activity', width: 70, dataIndex: 'LastActivity', tpl: lastT, align: 'center', sortable: true}
            ]
        
        // create the grid
        this.report = new Ext.grid.GridPanel({
            frame: true,
            title: 'Consider Biking Dashboard',
            width: 740,
            autoExpandColumn: 'autoexpand',
            height: 550,
            cls: 'centered',
            margins:'3 3 3 3',
            stripeRows: true,
            loadMask: true,
            tbar: toolbar,
            bbar: paging,
            ds: this.ds,
            columns: columns
        });

    // --- Render the form
        this.report.render(this.holder);
        this.filterList();
    }
    
    this.filterList = function()
    {
        this.ds.baseParams.SearchFor = Ext.getCmp('SearchFor').getValue();
    // --- reload list filtering by search term
        this.report.getBottomToolbar().cursor = 0;      // reset paging bar back to page 1
        var bbar = this.report.getBottomToolbar();
        this.ds.load({params: {start:bbar.cursor, limit:bbar.pageSize} });
    }
    
}
