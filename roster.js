Ext.onReady(function()
{
// --- Create roster panel
    var rosterPanel = new C_RosterPanel(Ext.get('roster-holder'));
    rosterPanel.create();
});


function C_RosterPanel(parentElement)
{
    this.holder = parentElement
    
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
                var tbar = new Ext.Toolbar({ items: [
                    'Sort By:', this.makeSortButton('Name', 'btn-sort-name', true),
                    ' ', this.makeSortButton('Category', 'btn-sort-category', false),
                    ' ', this.makeSortButton('Miles', 'btn-sort-ytdmiles', false),
                    ' ', this.makeSortButton('Age', 'btn-sort-age', false),
                    ' ', this.makeSortButton('Years Cycling', 'btn-sort-yc', false)
                ]});
                this.ds.sort([{ field: 'LastName', direction: 'ASC' }]);
                var rosterTpl = '<tpl for=".">\
                                    <div class="rider-wrap">\
                                      <div class="thumb-wrap">\
                                        <img id="img-{RiderID}" class=thumb src="' + g_fullDomainRoot + '/dynamic-images/rider-portrait.php?RiderID={RiderID}&T={RacingTeamID}" height=100 width=80>\
                                      </div>\
                                      <span class=name>{FullName}</span>\
                                      <span class=category>{RiderType}</span>\
                                      <span class=details>{Height} {Weight} lbs Age {Age}</span>\
                                    </div>\
                                  </tpl>'
                break;
            case 2:
            // ===== Commuting teams ======
                var tbar = new Ext.Toolbar({ items: [
                    'Sort By:', this.makeSortButton('Days/Month', 'btn-sort-cedaysmonth', true),
                    ' ', this.makeSortButton('Name', 'btn-sort-name', false),
                    ' ', this.makeSortButton('Miles', 'btn-sort-ytdmiles', false),
                    ' ', this.makeSortButton('Years Cycling', 'btn-sort-yc', false)
                ]});
                this.ds.sort([{ field: 'CEDaysMonth', direction: 'DESC'}]);
                var rosterTpl = '<tpl for=".">\
                                    <div class="rider-wrap">\
                                      <div class="thumb-wrap">\
                                        <img id="img-{RiderID}" class=thumb src="' + g_fullDomainRoot + '/dynamic-images/rider-portrait.php?RiderID={RiderID}&T={RacingTeamID}" height=100 width=80>\
                                      </div>\
                                      <span class=name>{FullName}</span>\
                                      <span class=category>\
                                        <img class="tight" src="images/ridelog/tiny/commute.png" height=10>\
                                        <img class="tight" src="images/ridelog/tiny/errand.png" height=10>\
                                        {CEDaysMonth:plural("day")}/month\
                                      </span>\
                                      <span class=category>{YTDMiles} miles YTD</span>\
                                    </div>\
                                  </tpl>'
                break;
            case 3:
            // ===== Recreational Teams ======
                var tbar = new Ext.Toolbar({ items: [
                    'Sort By:', this.makeSortButton('Name', 'btn-sort-name', true),
                    ' ', this.makeSortButton('Miles', 'btn-sort-ytdmiles', false),
                    ' ', this.makeSortButton('# Rides', 'btn-sort-ytdrides', false),
                    ' ', this.makeSortButton('Age', 'btn-sort-age', false),
                    ' ', this.makeSortButton('Years Cycling', 'btn-sort-yc', false)
                ]});
                this.ds.sort([{ field: 'LastName', direction: 'ASC'}]);
                var rosterTpl = '<tpl for=".">\
                                    <div class="rider-wrap">\
                                      <div class="thumb-wrap">\
                                        <img id="img-{RiderID}" class=thumb src="' + g_fullDomainRoot + '/dynamic-images/rider-portrait.php?RiderID={RiderID}&T={RacingTeamID}" height=100 width=80>\
                                      </div>\
                                      <span class=name>{FullName}</span>\
                                      <span class=category>YTD: {YTDRides:plural("ride")}, {YTDMiles} miles</span>\
                                    </div>\
                                  </tpl>'
                break;
        }
    
        // Add animate button on right side of toolbar
        tbar.addItem('->')
        tbar.addItem({
            cls: 'x-btn-icon',
            icon: 'images/animate-icon.png',
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
                                window.location.href = "profile.php?RiderID=" + id;
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

    this.onToggleSort = function(btn, state)
    {
        switch(btn.id) {
            case 'btn-sort-category':
                this.ds.sort([{ field: 'RiderTypeID', direction: 'ASC' }, { field: 'LastName', direction: 'ASC' }]);
                break;
            case 'btn-sort-name':
                this.ds.sort([{ field: 'LastName', direction: 'ASC' }]);
                break;
            case 'btn-sort-age':
                this.ds.sort([{ field: 'Age', direction: 'DESC' }, { field: 'LastName', direction: 'ASC' }]);
                break;
            case 'btn-sort-yc':
                this.ds.sort([{ field: 'YearsCycling', direction: 'DESC' }, { field: 'LastName', direction: 'ASC' }]);
                break;
            case 'btn-sort-ytdrides':
                this.ds.sort([{ field: 'YTDRides', direction: 'DESC' }, { field: 'LastName', direction: 'ASC' }]);
                break;
            case 'btn-sort-ytdmiles':
                this.ds.sort([{ field: 'YTDMiles', direction: 'DESC' }, { field: 'LastName', direction: 'ASC' }]);
                break;
            case 'btn-sort-cedaysmonth':
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
    
    this.makeSortButton = function(text, id, pressed)
    {
        return new Ext.Button({
            cls: 'x-btn-text',
            text: text,
            toggleGroup: 'sort',
            pressed: pressed,
            toggleHandler: this.onToggleSort,
            id: id,
            allowDepress: false,
            scope: this
        });
    }

}