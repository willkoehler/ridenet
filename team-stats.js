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
    this.firstLoad = true;

    // Sort column and range filter can be specified in the hash tag (i.e. rider-stats.php#s=CEDays&r=This%20Year&q=searchfor)
    // If there's no hash tag, use defaults
    var hash = Ext.urlDecode((window.location.hash) ? window.location.hash.substr(1) : "");
    var sort = (hash.s) || "StarRiders";
    var range = (hash.r) || "This Year";
    var search = (hash.q) || ""; 

    this.create = function()
    {
        // create the Data Store
        this.ds = new Ext.data.JsonStore({
            root: 'results',                // results array is returned in this property
            totalProperty: 'rowcount',      // total number of rows is returned in this property
            idProperty: 'RiderID',          // defines the primary key for the results
            remoteSort: true,
            baseParams: {Archived: '0'},
            fields: [
                {name: 'TeamID', type: 'int'},
                {name: 'TeamType'},
                {name: 'TeamName'},
                {name: 'Domain'},
                {name: 'Location'},
                {name: 'StarRiders', type: 'int', sortDir: 'DESC'},
                {name: 'CEDays', type: 'int', sortDir: 'DESC'},
                {name: 'CEDistance', type: 'int', sortDir: 'DESC'},
                {name: 'Distance', type: 'int', sortDir: 'DESC'}
            ],
            proxy: new Ext.data.HttpProxy({ url: '/data/list-team-stats.php' }),
            sortInfo: { field: sort, direction: 'desc' },
            listeners: { scope: this, load: function() {
                Ext.getCmp('rider-list').innerBody.select("dl:odd").addClass("x-grid3-row-alt");    // stripe rows
                if(this.firstLoad)
                {
                    this.firstLoad = false;
                }
                else
                {
                    this.updateHashTag();
                }
            }}

        });
        
        // Search bar
        var toolbar = new Ext.Toolbar({ style: 'padding: 4px 1px 4px 1px;', items: [
            {xtype: 'tbspacer', width: 5}, {
                xtype: 'localcombobox',
                id: 'DateRange',
                displayField: 'text',
                valueField: 'text',
                value: range,
                width: 85,
                editable:false,
                listeners: {scope: this, select: this.filterList},
                store: new Ext.data.ArrayStore({ fields: ['text'], data: this.rangeLookup })
            } , {xtype: 'tbspacer', width: 10}, {
                xtype: 'textfield',
                id: 'SearchFor',
                value: search,
                emptyText: 'Find a Team',
                width: 150,
                listeners: { scope: this, specialkey: function(ctrl, e) { if(e.getKey() == e.ENTER) { this.filterList() } } }
            } , ' ', {
                cls: 'x-btn-icon',
                icon: '/images/search-icon.png',
                handler: this.filterList,
                scope: this
            } ,  {xtype: 'tbspacer', width: 75}, '<span style="color:#AAA">(click column header to sort)</span>'
         ]});

        var ceDaysHeader =  '<span style="line-height:13px;position:relative;top:-1px">\
                               <img class="tight" src="/images/ridelog/commute.png" height=14>\
                               <img class="tight" src="/images/ridelog/errand.png" height=14>\
                               <span style="padding-left:2px">Days</span>\
                             </span>'
        var ceDistanceHeader =  '<span style="line-height:13px;position:relative;top:-1px">\
                                   <img class="tight" src="/images/ridelog/commute.png" height=14>\
                                   <img class="tight" src="/images/ridelog/errand.png" height=14>\
                                   <span style="padding-left:2px">Miles</span>\
                                 </span>'
        var teamT = new Ext.XTemplate('<table cellpadding=0 cellspacing=0><tr>\
                                          <td><div style="width:100px;overflow:hidden;text-align:center;margin:1px">\
                                            <img class="tight" src="{[getFullDomainRoot()]}/dynamic-images/team-logo-fit.php?T={TeamID}" border=0>\
                                          </div></td>\
                                          <td><div class="ellipses" style="padding-left:5px;width:215px">\
                                            <div class="find-name">{TeamName}</div>\
                                            <div class="find-info">{TeamType}</div>\
                                            <div class="find-info2">{Location}</div>\
                                          </div></td>\
                                        </tr></table>').compile();
        var distanceT = new Ext.XTemplate('<div style="font-size:1.3em;padding-top:10px">{Distance}</div>').compile();
        var ceDistanceT = new Ext.XTemplate('<div style="font-size:1.3em;padding-top:10px">{CEDistance}</div>').compile();
        var ceDaysT = new Ext.XTemplate('<div style="font-size:1.3em;padding-top:10px">{CEDays}</div>').compile();
        var starRidersT = new Ext.XTemplate('<div style="font-size:1.3em;padding-top:10px">{StarRiders}</div>').compile();

        var columns = [
                {header: 'Team', width: .52, dataIndex: 'TeamName', tpl: teamT },
                {header: 'All Miles', width: .11, dataIndex: 'Distance', align: 'center', tpl: distanceT },
                {header: ceDistanceHeader, width: .13, dataIndex: 'CEDistance', align: 'center', tpl: ceDistanceT },
                {header: ceDaysHeader, width: .13, dataIndex: 'CEDays', align: 'center', tpl: ceDaysT },
                {header: '# STARs', width: .11, dataIndex: 'StarRiders', align: 'center', tpl: starRidersT }
            ]
        
        // create the grid
        this.report = new Ext.Panel({
            xframe: true,
            width: 560,
            cls: 'centered',
            tbar: toolbar,
            items: [{
                xtype: 'listview',
                autoHeight: true,
                loadMask: true,
                id: 'rider-list',
                store: this.ds,
                columns: columns,
                listeners: {scope: this, click: function(dv, index, node, e) {
                    record = this.ds.getAt(index).data;
                    window.open(buildTeamBaseURL(record.Domain));
                }}
            }]
        });

    // --- Render the form
        this.report.render(this.holder);
        this.filterList();
    }
    
    this.filterList = function()
    {
        var endDate = new Date();
        switch(Ext.getCmp('DateRange').getValue()) {
            case "All Time":
                startDate = new Date(2000, 0, 1)
                break;
            case "This Year":
                startDate = new Date(endDate.getFullYear(), 0, 1);
                break;
            case "Last Year":
                year = endDate.getFullYear() - 1;
                startDate = new Date(year, 0, 1);
                endDate = new Date(year, 12, 31);
                break;
            case "This Month":
                startDate = new Date(endDate.getFullYear(), endDate.getMonth(), 1);
                break;
            case "Last Month":
                month = endDate.getMonth();
                year = endDate.getFullYear();
                startDate = new Date(year, month-1, 1);     // works even when month = 0
                endDate = new Date(year, month, 0);         // 0 ==> last day of the previous month
                break;
            default:
                startDate = new Date(2000, 0, 1)
                break;
        }
    // --- reload list filtering by search term
        this.ds.baseParams.SearchFor = Ext.getCmp('SearchFor').getValue();
        this.ds.baseParams.StartDate = startDate;
        this.ds.baseParams.EndDate = endDate;
        this.mask = new Ext.LoadMask(this.report.getEl(), { store: this.ds, msg:"Please Wait..." });
        this.ds.load({params: {start:0, limit:100} });
    }
    
    this.updateHashTag = function()
    {
    // --- put date range and sort info in hash tag so params are saved with the link
        hash = "s=" + this.ds.getSortState().field + 
               "&r=" + Ext.getCmp('DateRange').getValue() +
               "&q=" + Ext.getCmp('SearchFor').getValue();
        window.location.replace("#" + hash);
    }
    
    this.rangeLookup = [["All Time"], ["This Year"], ["Last Year"], ["This Month"], ["Last Month"]]

}
