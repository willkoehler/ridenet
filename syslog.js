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
            idProperty: 'ActivityID',       // defines the primary key for the results
            remoteSort: true,
            baseParams: {Archived: '0'},
            fields: [
                {name: 'Date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'RiderID', type: 'int'},
                {name: 'RiderName'},
                {name: 'TeamName'},
                {name: 'Domain'},
                {name: 'Description'},
                {name: 'ReferenceID', type: 'int'},
                {name: 'IPAddress'}
            ],
            proxy: new Ext.data.HttpProxy({ url: 'data/list-syslog.php' }),
            sortInfo: { field: 'Date', direction: 'desc' }
        });
        
        // paging bar
        var paging = new Ext.PagingToolbar({
                pageSize: 100,
                store: this.ds,
                displayInfo: true,
                displayMsg: 'Displaying {0} - {1} of {2} Actions',
                emptyMsg: "No actions to display"
        })

        var dateT = new Ext.XTemplate('{Date:date("Y-m-d")} &bull; {Date:date("g:ia")}').compile();
        var riderT = new Ext.XTemplate('<a href="{[buildTeamBaseURL(values.Domain)]}/profile.php?RiderID={RiderID}" target="_blank" style="color:#5260C1">{RiderName}</a>').compile();;
        var teamT = new Ext.XTemplate('<a href="{[buildTeamBaseURL(values.Domain)]}" target="_blank" style="color:#5260C1">{TeamName}</a>').compile();;
        var DescriptionT = new Ext.XTemplate('{Description:htmlEncode}').compile();

        var columns = [
                {header: 'Date/Time', width: .175, dataIndex: 'Date', tpl: dateT},
                {header: 'Rider', width: .17, dataIndex: 'RiderName', tpl: riderT },
                {header: 'Team', width: .22, dataIndex: 'TeamName', tpl: teamT },
                {header: 'Description', dataIndex: 'Description', tpl: DescriptionT},
                {header: 'Ref', width: .07, dataIndex: 'ReferenceID', sortable: false},
                {header: 'IP', width: .1, dataIndex: 'IPAddress'}
            ]
        
        // create the grid
        this.report = new Ext.Panel({
            xframe: true,
            title: 'System Activity',
            width: 740,
            cls: 'centered',
            tbar: paging,
            items: [{
                xtype: 'listview',
                autoHeight: true,
                loadMask: true,
                store: this.ds,
                columns: columns
//                listeners: { scope: this,
//                    click: function(list, index, node, e) {
//                        var row = list.getStore().getAt(index);
//                        window.open(buildTeamBaseURL(row.data.Domain) + "/profile.php?RiderID=" + row.data.RiderID, "profile");
//                    }
//                }
            }]
        });

    // --- Render the form
        this.report.render(this.holder);
        this.mask = new Ext.LoadMask(this.report.getEl(), { store: this.ds, msg:"Please Wait..." });
        var bbar = this.report.getTopToolbar();
        this.ds.load({params: {start:bbar.cursor, limit:bbar.pageSize} });
    }
}
