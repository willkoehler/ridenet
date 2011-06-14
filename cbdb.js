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
                {name: 'TotalRiders', type: 'int', sortDir: 'DESC'},
                {name: 'StarRiders', type: 'int', sortDir: 'DESC'},
                {name: 'CERides', type: 'int', sortDir: 'DESC'},
                {name: 'LastActivity', type: 'int', sortDir: 'ASC'}
            ],
            proxy: new Ext.data.HttpProxy({ url: '/data/list-cbdb.php' }),
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
                icon: '/images/search-icon.png',
                handler: this.filterList,
                scope: this
            }
         ]});

        // paging bar
        var paging = new Ext.PagingToolbar({
                pageSize: 150,
                store: this.ds,
                displayInfo: true,
                displayMsg: 'Displaying {0} - {1} of {2} Teams',
                emptyMsg: "No teams to display"
        })

        var ceRidesHeader =  '<span style="line-height:13px;position:relative;top:-1px">\
                                <img class="tight" src="/images/ridelog/commute.png" height=14><img class="tight" src="/images/ridelog/errand.png" height=14>\
                                <span style="padding-left:2px">Trips</span>\
                              </span>'
        var teamT = new Ext.XTemplate('<a href="{[buildTeamBaseURL(values.Domain)]}" target="_blank" style="color:#5260C1">{TeamName}</a>').compile();;
        var starT = new Ext.XTemplate('<tpl if="StarRiders">{StarRiders}</tpl><tpl if="StarRiders==0">-</tpl>').compile();
        var ceRidesT = new Ext.XTemplate('<tpl if="CERides">{CERides}</tpl><tpl if="CERides==0">-</tpl>').compile();
        var lastT = new Ext.XTemplate('<tpl if="LastActivity==1000">-</tpl><tpl if="LastActivity===0 | LastActivity===1 | LastActivity===2">RECENT</tpl><tpl if="LastActivity &gt; 2 && LastActivity &lt; 1000">{LastActivity} weeks</tpl>').compile();

        var columns = [
                {xtype: 'templatecolumn', header: 'Team', width: 120, dataIndex: 'TeamName', tpl: teamT, sortable: true, id: 'autoexpand' },
                {header: 'Riders', width: 55, dataIndex: 'TotalRiders', align: 'center', sortable: true},
                {xtype: 'templatecolumn', header: 'STARs', width: 60, dataIndex: 'StarRiders', tpl: starT, align: 'center', sortable: true},
                {xtype: 'templatecolumn', header: ceRidesHeader, width: 90, dataIndex: 'CERides', tpl: ceRidesT, align: 'center', sortable: true},
                {xtype: 'templatecolumn', header: 'Last Activity', width: 90, dataIndex: 'LastActivity', tpl: lastT, align: 'center', sortable: true}
            ]
        
        // create the grid
        this.report = new Ext.grid.GridPanel({
            frame: true,
            title: 'Consider Biking Dashboard',
            width: 570,
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
