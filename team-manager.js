// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
// --- Turn on validation errors beside the field globally and enable quick tips that will
// --- popup tooltip when mouse is hovered over field
    Ext.form.Field.prototype.msgTarget = 'qtip';
    Ext.QuickTips.init();
// --- Create form
    var managerForm = new C_SiteManager(Ext.get('panel-holder'));
    managerForm.create();
});


function C_SiteManager(parentElement)
{
    // active tab can be specified in the hash tag (i.e. /manage#2)
    var activeTab = (window.location.hash) ? parseInt(window.location.hash.substr(1,1)) : 0
    this.create = function()
    {
        // --- create the Tab Panel
        this.tabs = new Ext.TabPanel({
            activeTab: activeTab,
            width: 700,
            autoHeight: true,
            plain: false,
            items: [
                new C_CustomizeTab().create(),
                new C_RiderTab().create(),      // IE7 bug - this cannot be the first tab - WTF!?
                new C_HomePageTab().create()
/*                {
                    title: 'News',
                    html: 'Coming Soon...'
                }, {
                    title: 'Pictures',
                    html: 'Coming Soon...'
                },{
                    title: 'Event Tab',
//                    listeners: {activate: handleActivate},
                    html: "I am tab 4's content. I also have an event listener attached."
                },{
                    title: 'Disabled Tab',
                    disabled:true,
                    html: "Can't see me cause I'm disabled"*/
//                }
//                new C_AccountTab().create()
            ]
        });        
        
    // --- Render the form
        this.tabs.render(parentElement);
//        this.filterList();
    }
    
}

