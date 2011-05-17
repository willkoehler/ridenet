Ext.onReady(function()
{
// --- Create roster panel
    var rosterPanel = new C_RosterPanel(Ext.get('roster-holder'));
    rosterPanel.create();
});


function C_RosterPanel(parentElement)
{
    this.holder = parentElement

    // Sort field can be specified in the hash tag (i.e. roster#s=sort-name)
    // If there's no hash tag, use defaults
    var hash = Ext.urlDecode((window.location.hash) ? window.location.hash.substr(1) : "");
    
    this.create = function()
    {
        this.ds = new Ext.data.ArrayStore({
            fields: [
                {name: 'RiderID', type: 'int'},
                {name: 'RacingTeamID', type: 'int'},
                {name: 'FullName'},
                {name: 'LastName'},
                {name: 'YearsCycling', type: 'int'},
                {name: 'Height'},
                {name: 'Weight', type: 'int'},
                {name: 'RiderTypeID', type: 'int'},
                {name: 'RiderType'},
                {name: 'Age', type: 'int'},
                {name: 'YTDRides', type: 'int'},
                {name: 'YTDMiles', type: 'int'},
                {name: 'CEDaysMonth', type: 'int'}
            ],
            data: rosterData
        });
    
        switch(g_teamTypeID) {
            case 1:
            // ===== Racing Teams ======
                this.initial_sort = (hash.s) || "sort-name";    // get sort from hash tag or use default
                var tbar = new Ext.Toolbar({ items: [
                    'Sort By:', this.makeSortButton('Name', 'sort-name'),
                    ' ', this.makeSortButton('Category', 'sort-category'),
                    ' ', this.makeSortButton('Miles', 'sort-ytdmiles'),
                    ' ', this.makeSortButton('Age', 'sort-age'),
                    ' ', this.makeSortButton('Years Cycling', 'sort-yc')
                ]});
                var rosterTpl = '<tpl for=".">\
                                    <div class="rider-wrap">\
                                      <div class="thumb-wrap">\
                                        <img id="img-{RiderID}" class=thumb src="' + g_fullDomainRoot + '/imgstore/rider-portrait/{RacingTeamID}/{RiderID}.jpg" height=100 width=80>\
                                      </div>\
                                      <span class=name>{FullName}</span>\
                                      <span class=category>{RiderType}</span>\
                                      <span class=details>{Height} {Weight} lbs Age {Age}</span>\
                                    </div>\
                                  </tpl>'
                break;
            case 2:
            // ===== Commuting teams ======
                this.initial_sort = (hash.s) || "sort-cedaysmonth";    // get sort from hash tag or use default
                var tbar = new Ext.Toolbar({ items: [
                    'Sort By:', this.makeSortButton('Days/Month', 'sort-cedaysmonth'),
                    ' ', this.makeSortButton('Name', 'sort-name'),
                    ' ', this.makeSortButton('Miles', 'sort-ytdmiles'),
                    ' ', this.makeSortButton('Years Cycling', 'sort-yc')
                ]});
                var rosterTpl = '<tpl for=".">\
                                    <div class="rider-wrap">\
                                      <div class="thumb-wrap">\
                                        <img id="img-{RiderID}" class=thumb src="' + g_fullDomainRoot + '/imgstore/rider-portrait/{RacingTeamID}/{RiderID}.jpg" height=100 width=80>\
                                      </div>\
                                      <span class=name>{FullName}</span>\
                                      <span class=category>\
                                        <img class="tight" src="/images/ridelog/tiny/commute.png" height=10>\
                                        <img class="tight" src="/images/ridelog/tiny/errand.png" height=10>\
                                        {CEDaysMonth:plural("day")}/month\
                                      </span>\
                                      <span class=category>{YTDMiles} miles YTD</span>\
                                    </div>\
                                  </tpl>'
                break;
            case 3:
            // ===== Recreational Teams ======
                this.initial_sort = (hash.s) || "sort-name";    // get sort from hash tag or use default
                var tbar = new Ext.Toolbar({ items: [
                    'Sort By:', this.makeSortButton('Name', 'sort-name'),
                    ' ', this.makeSortButton('Miles', 'sort-ytdmiles'),
                    ' ', this.makeSortButton('# Rides', 'sort-ytdrides'),
                    ' ', this.makeSortButton('Age', 'sort-age'),
                    ' ', this.makeSortButton('Years Cycling', 'sort-yc')
                ]});
                var rosterTpl = '<tpl for=".">\
                                    <div class="rider-wrap">\
                                      <div class="thumb-wrap">\
                                        <img id="img-{RiderID}" class=thumb src="' + g_fullDomainRoot + '/imgstore/rider-portrait/{RacingTeamID}/{RiderID}.jpg" height=100 width=80>\
                                      </div>\
                                      <span class=name>{FullName}</span>\
                                      <span class=category>YTD: {YTDRides:plural("ride")}, {YTDMiles} miles</span>\
                                    </div>\
                                  </tpl>'
                break;
        }
        // set initial sort
        this.updateSort(this.initial_sort);

        // Add animate button on right side of toolbar
        tbar.addItem('->')
        tbar.addItem({
            cls: 'x-btn-icon',
            icon: '/images/animate-icon.png',
            handler: this.onClickAnimate,
            scope: this
        });
    
        this.panel = new Ext.Panel({
            layout: 'fit',
            width : 572,
            tbar  : tbar,
            id: 'roster-view',
            items: [{
                xtype: 'dataview',
                store: this.ds,
                tpl: rosterTpl,
                plugins: [
                    new Ext.ux.DataViewTransition({
                        duration  : 540,
                        idProperty: 'RiderID'
                    })
                ],
                id:'rider',    // node element IDs are: "rider-[RecordID]"
                itemSelector: 'div.rider-wrap',
                overClass: 'x-view-over',
                singleSelect: true,
                multiSelect: true,
                listeners: { 
                    click: function(view, i, node, e) {
                    // get riderID from element ID - needed because DataViewTransition messes up the node indexes
                        var id = node.id.replace('rider-', '');     // get rider ID
                        var img = Ext.get('img-' + id);             // get rider image element
                        img.stopFx();
                        img.bigPuff({
                            easing: ('backIn'),
                            duration: .4,
                            remove: false,
                            useDisplay: false,
                            callback: function() {
                                window.location.href = "/rider/" + id;
                            }
                        });
                    }
                }
            }]
        });

    // Render the panel
        this.panel.render(this.holder);
    // Make overflow visible for the DataView panel. This allows the puff effect to expand
    // beyond the DataView panel. We can't do this with css because the auto-height calculation
    // requires overflow: hidden. We have to wait until after the panel is rendered to change
    // the overflow style.
        Ext.getCmp('rider').el.up('div.x-panel-body').setStyle("overflow", "visible");
        Ext.getCmp('rider').el.up('div.x-panel-bwrap').setStyle("overflow", "visible");
    }

    this.onToggleSort = function(btn_id)
    {
        this.updateSort(btn_id);
        this.updateHashTag(btn_id);
    }

    this.updateSort = function(btn_id)
    {
        switch(btn_id) {
            case 'sort-category':
                this.ds.sort([{ field: 'RiderTypeID', direction: 'ASC' }, { field: 'LastName', direction: 'ASC' }]);
                break;
            case 'sort-name':
                this.ds.sort([{ field: 'LastName', direction: 'ASC' }]);
                break;
            case 'sort-age':
                this.ds.sort([{ field: 'Age', direction: 'DESC' }, { field: 'LastName', direction: 'ASC' }]);
                break;
            case 'sort-yc':
                this.ds.sort([{ field: 'YearsCycling', direction: 'DESC' }, { field: 'LastName', direction: 'ASC' }]);
                break;
            case 'sort-ytdrides':
                this.ds.sort([{ field: 'YTDRides', direction: 'DESC' }, { field: 'LastName', direction: 'ASC' }]);
                break;
            case 'sort-ytdmiles':
                this.ds.sort([{ field: 'YTDMiles', direction: 'DESC' }, { field: 'LastName', direction: 'ASC' }]);
                break;
            case 'sort-cedaysmonth':
                this.ds.sort([{ field: 'CEDaysMonth', direction: 'DESC' }, { field: 'LastName', direction: 'ASC' }]);
                break;
        }
    }
    
    this.onClickAnimate = function()
    {
        this.ds.each(function(r) {
            var img = Ext.get('img-' + r.data.RiderID);             // get rider image element
            img.stopFx();
            img.sequenceFx();
            img.pause(Math.random()*1);
            img.slideOut('b', {
                easing: 'backIn',
                duration: .4,
                remove: false,
                useDisplay: false
            });
            img.pause(1.25);
            img.slideIn('t', {
                easing: 'bounceOut',
                duration: .4
            });
        })
    }
    
    this.makeSortButton = function(text, id)
    {
        return new Ext.Button({
            cls: 'x-btn-text',
            text: text,
            toggleGroup: 'sort',
            pressed: (id==this.initial_sort),
            toggleHandler: function(btn, state) { if(state) {this.onToggleSort(btn.id)} },
            id: id,
            allowDepress: false,
            scope: this
        });
    }

    this.updateHashTag = function(btn_id)
    {
    // --- Put sort info in hash tag so sorting selection is preserved if user comes
    // --- back to roster page from profile page
        if(Ext.isWebKit)
        {
            window.location.hash = "s=" + btn_id;       // Safari bug: replace() doesn't save page in history
        }
        else
        {
            window.location.replace("#s=" + btn_id);    // all other browsers replace current item in history
        }
    }
}