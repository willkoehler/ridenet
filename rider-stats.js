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
    var sort = (hash.s) || "CEDays";
    var range = (hash.r) || "A";
    var search = (hash.q) || ""; 

    Ext.fly('date-range').on('change', function() { this.filterList() }, this);
    Ext.fly('date-range').dom.value = range;

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
                {name: 'RiderID', type: 'int'},
                {name: 'RiderName'},
                {name: 'RiderType'},
                {name: 'TeamID', type: 'int'},
                {name: 'TeamName'},
                {name: 'Domain'},
                {name: 'Distance', type: 'int', sortDir: 'DESC'},
                {name: 'Days', type: 'int', sortDir: 'DESC'},
                {name: 'CEDays', type: 'int', sortDir: 'DESC'},
                {name: 'CEDaysMonth', type: 'int', sortDir: 'DESC'}
            ],
            proxy: new Ext.data.HttpProxy({ url: '/data/list-rider-stats.php' }),
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
                xtype: 'textfield',
                id: 'SearchFor',
                value: search,
                emptyText: 'Find Rider or Team',
                width: 150,
                listeners: { scope: this, specialkey: function(ctrl, e) { if(e.getKey() == e.ENTER) { this.filterList() } } }
            } , ' ', {
                cls: 'x-btn-icon',
                icon: '/images/search-icon.png',
                handler: this.filterList,
                scope: this
            } ,  {xtype: 'tbspacer', width: 100}, '<span style="color:#AAA">(click column header to sort)</span>'
         ]});

        var ceDaysHeader =  '<span style="line-height:13px;position:relative;top:-1px">\
                               <img class="tight" src="/images/ridelog/commute.png" height=14><img class="tight" src="/images/ridelog/errand.png" height=14>\
                               <span style="padding-left:2px">Days</span>\
                             </span>'
        var ceDaysMonthHeader =  '<span style="line-height:13px;position:relative;top:-1px">\
                                    <img class="tight" src="/images/ridelog/commute.png" height=14><img class="tight" src="/images/ridelog/errand.png" height=14>\
                                    <span style="padding-left:2px">D/M</span>\
                                  </span>'
        var riderT = new Ext.XTemplate('<table cellpadding=0 cellspacing=0><tr>\
                                          <td><div style="width:40px;overflow:hidden;text-align:center;margin:1px">\
                                            <img class="tight" src="{[getFullDomainRoot()]}/dynamic-images/rider-portrait.php?RiderID={RiderID}&T={TeamID}" width=30 border=0>\
                                          </div></td>\
                                          <td><div class="ellipses" style="padding-left:5px;width:215px">\
                                            <div class="find-name">{RiderName}</div>\
                                            <div class="find-info">{TeamName}</div>\
                                            <div class="find-info2">{RiderType}</div>\
                                          </div></td>\
                                        </tr></table>').compile();
        var distanceT = new Ext.XTemplate('<div style="font-size:1.3em;padding-top:10px">{Distance}</div>').compile();
        var daysT = new Ext.XTemplate('<div style="font-size:1.3em;padding-top:10px">{Days}</div>').compile();
        var ceDaysT = new Ext.XTemplate('<div style="font-size:1.3em;padding-top:10px">{CEDays}</div>').compile();
        var ceDaysMonthT = new Ext.XTemplate('<div style="font-size:1.3em;padding-top:10px">{CEDaysMonth}</div>').compile();

        var columns = [
                {header: 'Rider/Team', width: .47, dataIndex: 'RiderName', tpl: riderT },
                {header: 'Total Miles', width: .13, dataIndex: 'Distance', align: 'center', tpl: distanceT },
                {header: 'Total Days', width: .13, dataIndex: 'Days', align: 'center', tpl: daysT },
                {header: ceDaysHeader, width: .14, dataIndex: 'CEDays', align: 'center', tpl: ceDaysT },
                {header: ceDaysMonthHeader, width: .12, dataIndex: 'CEDaysMonth', align: 'center', tpl: ceDaysMonthT }
            ]
        
        // create the grid
        this.report = new Ext.Panel({
            xframe: true,
            width: 500,
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
                    window.open(buildTeamBaseURL(record.Domain) + '/profile.php?RiderID=' + record.RiderID);
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
        var range = Ext.fly('date-range').dom.value;
        var type = range.substring(0,1);             // A, Y, M
        var offset = parseInt(range.substring(1));   // 0, -1, -2, etc
        switch(type) {
            case "A":
                startDate = new Date(2000, 0, 1);
                break;
            case "Y":
                var year = endDate.getFullYear() + offset;
                startDate = new Date(year, 0, 1);
                endDate = new Date(year, 11, 31);
                break;
            case "M":
                month = endDate.getMonth();
                year = endDate.getFullYear();
                startDate = new Date(year, month+offset, 1);        // works even when month = 0
                endDate = new Date(year, month+offset+1, 0);        // 0 ==> last day of the previous month
                break;
            default:
                startDate = new Date(2000, 0, 1);
                break;
        }
    // --- reload list filtering by search term
        this.ds.baseParams.SearchFor = Ext.getCmp('SearchFor').getValue();
        this.ds.baseParams.StartDate = startDate.format('n/j/Y');
        this.ds.baseParams.EndDate = endDate.format('n/j/Y');
        this.mask = new Ext.LoadMask(this.report.getEl(), { store: this.ds, msg:"Please Wait..." });
        this.ds.load({params: {start:0, limit:100} });
    }
    
    this.updateHashTag = function()
    {
    // --- put date range and sort info in hash tag so params are saved with the link
        hash = "s=" + this.ds.getSortState().field + 
               "&r=" + Ext.fly('date-range').dom.value +
               "&q=" + Ext.getCmp('SearchFor').getValue();
        window.location.replace("#" + hash);
    }
}
