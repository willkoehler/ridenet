// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
// --- Turn on validation errors beside the field globally and enable quick tips that will
// --- popup tooltip when mouse is hovered over field
    Ext.form.Field.prototype.msgTarget = 'qtip';
    Ext.QuickTips.init();

    // Remote lookup for Riders
    this.dsRiderLookup = new Ext.data.JsonStore({
        root: 'results',                // results array is returned in this property
        totalProperty: 'rowcount',      // total number of rows is returned in this property
        idProperty: 'RiderID',          // defines the primary key for the results
        fields: [
            {name: 'RiderID', type: 'int'},
            {name: 'RacingTeamID', type: 'int'},
            {name: 'TeamName'},
            {name: 'RiderName'}
        ],
        proxy: new Ext.data.HttpProxy({ url: '/data/lookup-rider.php' })
    });

// --- Create rider search box and follow button
    var form = new Ext.Container({
        layout: 'column',
        items: [{
            xtype: 'container', width: 260, items: [{
            // === Rider Selection ===
                xtype: 'remotecombobox',
                displayField: 'RiderName',
                valueField: 'RiderID',
                hiddenName: 'RiderID',
                forceSelection: true,
                width: 250,
                listWidth: 370,
                emptyText: 'Start typing rider name or team name...',
                store: this.dsRiderLookup,
                tpl:'<tpl for="."><div class="x-combo-list-item"><table cellpadding=0 cellspacing=0><tr>\
                       <td><img src="' + getFullDomainRoot() + '/dynamic-images/rider-portrait.php?RiderID={RiderID}&T={RacingTeamID}" height=40 width=32></td>\
                       <td><div class="ellipses" style="padding-left:15px;width:300px">{RiderName} - <span style="color:#888">{TeamName}</span></div></td>\
                     </tr></table>\
                     </div></tpl>'
            }]
        },{
            xtype: 'container', layout: 'form', items: [{
                xtype: 'button',
                text: '<span style="color:#94302E">&nbsp;Follow</span>',
                icon: '/images/plus-icon.png',
                width: 70
//        handler: clickAddRide
            }]
        }],
        renderTo: 'form-holder'
    });
});
