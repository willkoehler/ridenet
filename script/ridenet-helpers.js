// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
    new C_SearchBox().create();     // render search box in menu bar
});


function C_SearchBox()
{
    this.create = function()
    {
        // Remote lookup for Teams and Riders
        var dsRiderLookup = new Ext.data.JsonStore({
            root: 'results',                // results array is returned in this property
            totalProperty: 'rowcount',      // total number of rows is returned in this property
            fields: [
                {name: 'RiderID', type: 'int'},
                {name: 'TeamID', type: 'int'},
                {name: 'Type'},
                {name: 'Domain'},
                {name: 'DisplayText'},
                {name: 'InfoText'},
                {name: 'InfoText2'}
            ],
            proxy: new Ext.data.HttpProxy({ url: '/data/lookup-search.php' })
        });

        this.form = new Ext.Container({
            items: [{
                xtype: 'container', cls: 'x-small-editor', items: [{
                // === Rider Selection ===
                    xtype: 'remotecombobox',
                    enableKeyEvents: true,
                    pageSize: 0,    // disables paging toolbar
                    maxHeight: 550,
                    triggerClass: 'x-form-search-trigger',
                    displayField: 'DisplayText',
                    forceSelection: true,
                    width: 200,
                    listWidth: 280,
                    emptyText: 'Search...',
                    store: dsRiderLookup,
                    listeners: { scope: this, select: function(c, r, i) {
                        _gaq.push(['_trackEvent', 'Action', 'Search', r.data.DisplayText]);
                        // defer slightly to allow browser to send event to google
                        (function(r) {
                            if(r.data.Type=='rider') {
                                window.location.href = buildTeamBaseURL(r.data.Domain) + "/rider/" + r.data.RiderID;
                            } else {
                                window.location.href = buildTeamBaseURL(r.data.Domain);
                            }
                        }).defer(200, this, [r]);
                    }},
                    tpl:'<tpl for="."><div class="x-combo-list-item"><table cellpadding=0 cellspacing=0><tr>\
                           <tpl if="Type==\'rider\'">\
                             <td><div style="width:40px;overflow:hidden;text-align:center;margin:1px">\
                               <img src="' + getFullDomainRoot() + '/imgstore/rider-portrait/{TeamID}/{RiderID}.jpg" height=50 width=40>\
                             </div></td>\
                             <td><div class="ellipses" style="padding-left:10px;width:215px">\
                               <div class="find-name">{DisplayText}</div>\
                               <div class="find-info">{InfoText}</div>\
                               <div class="find-info2">{InfoText2}</div>\
                             </div></td>\
                           </tpl>\
                           <tpl if="Type==\'team\'">\
                             <td><div style="width:40px;overflow:hidden;text-align:center;margin:1px">\
                               <img src="' + getFullDomainRoot() + '/imgstore/team-logo/sm/{TeamID}.png" height=40>\
                             </div></td>\
                             <td><div class="ellipses" style="padding-left:10px;width:215px">\
                               <div class="find-name">{DisplayText}</div>\
                               <div class="find-info">{InfoText}</div>\
                               <div class="find-info2">http://{Domain}.ridenet.net</div>\
                             </div></td>\
                           </tpl>\
                         </tr></table></div></tpl>'
                }]
            }],
            renderTo: 'search-box'
        });
    }
}


function createMoreWrapper(id, height, label)
{
    var content = Ext.get('more-content' + id);
    var moreBtnHeight = 11;
    // If content is taller than specified height, wrap content in fixed-height div with a "more" button. 
    if(content.getHeight() > height)
    {
        var wrapper = content.wrap({ tag:'div', style: 'height:' + (height-moreBtnHeight) + 'px;overflow:hidden'});
        wrapper.insertSibling({
            tag: 'div',
            cls: 'more-btn',
            onclick:'var button = Ext.get(this); \
                     var wrapper = button.prev(); \
                     var content = wrapper.first(); \
                     wrapper.setHeight(content.getHeight(), true); \
                     button.remove();',
            html: label },
        'after');
    }
}


function riderInfoCallout(riderID, idTag, type)
{
    new Ext.ToolTip({
        target: 'R' + riderID + idTag,
        id: 'C' + riderID + idTag,
        anchor: 'bottom',
        dismissDelay: 15000,
        hideDelay: 0,
        showDelay: 400,
        html: '<div class="loading-indicator">Loading...</div>',   // helps ExtJS set initial tooltip size correctly
        padding: 5,
        autoLoad: {
            url: "/dynamic-sections/get-callout-info.php",  // load tooltip content dynamically
            params: {
                RiderID: riderID,
                rt: (type || 0)
            },
            // re-show after content is loaded to force ExtJS to calculate new tooltip size
            callback: function(el) {
              var tooltip = Ext.getCmp('C' + riderID + idTag)
              if(!tooltip.hidden && !tooltip.hideTimer) { tooltip.show(); }
            }
        }
    });
}


function riderInfoCalloutSimple(riderID, riderName, teamName)
{
    new Ext.ToolTip({
        target: 'R' + riderID,
        anchor: 'bottom',
        dismissDelay: 0,
        showDelay: 200,
        html: '<div class="rider-callout">\
                <div class="primary">' + riderName + '</div>\
                <div class="secondary">' + teamName + '</div>\
               </div>',
        padding: 5
    });
}


function C_HelpDialog(msg)
{
    this.window=null;
    this.msg = msg;

    // -------------------------------------------------------------------------------------------
    //  Show help dialog.
    //  params object has the following parameters:
    //      animateTarget   - id of HTML target to animate opening/closing window
    //      ypos            - y position of the window
    //      width           - (optional) width of window
    // -------------------------------------------------------------------------------------------
    this.show = function(params)
    {
        if( ! this.window)
        {
            this.window = new Ext.Window({
                width: (params.width || 565),          // (height will be calculated based on content)
                y: params.ypos,
                autoHeight: true,       // allows calls to syncSize() to resize the window based on content
                forceLayout: true,      // force window to calculate layout (i.e. height) before opening
                resizable: false,
                closeAction:'hide',     // hide instead of destroying window on close
                modal: true,
                bodyStyle:'padding:5px;',
                border: false,
                items: [{
                    xtype: 'panel',
                    baseCls: 'x-plain',     // (gives panel a gray background - by default panels have white backgrounds)
                    labelAlign: 'top',
                    bodyStyle:'padding:5px 5px 0',
                    buttonAlign:'center',
                    items: [{
                        xtype: 'displayfield',
                        hideLabel: true,
                        html: this.msg
                    },{
                        xtype: 'container', cls: 'form-spacer', height:10
                    }],
                    buttons: [{
                        text: 'OK',
                        handler: function() { this.window.hide(); },
                        scope: this
                    }]
                }]
            });
        }

        _gaq.push(['_trackEvent', 'Action', 'Help Dialog']);    // log event in Google Analytics
        this.window.show(params.animateTarget);
    }
}


function buildTeamBaseURL(subdomain)
{
    var domain = document.domain;

    var parts = domain.split(".");
    var root = parts[parts.length-2] + "." + parts[parts.length-1]
    if(root.search(new RegExp(subdomain, 'i'))!==-1)
    {
        // this is a custom domain root and it contains subdomain, stay on the custom domain root
        return("http://www." + root);
    }
    else if(root.search(/local/i)!==-1)
    {
        // we are local and linking to a different sub-domain, go to team.ridenet.local
        return("http://" + subdomain + "." + "ridenet.local");
    }
    else
    {
        // we are remote and linking to a different sub-domain, go to team.ridenet.net
        return("http://" + subdomain + "." + "ridenet.net");
    }
}


function getFullDomainRoot()
{
    return(document.domain.search(/local/i)!==-1) ? "http://ridenet.local" : "http://ridenet.net";
} 
