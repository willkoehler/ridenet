function C_HomePageTab()
{
    this.create = function()
    {
        // Json Reader to read data for dialog
        var reader = new Ext.data.JsonReader( { root: 'results', idProperty: 'TeamID' }, [
            {name: 'TeamID', type: 'int'},
            {name: 'TeamImage', mapping: 'TeamID', type: 'int'},
            {name: 'HomePageHTML'},
            {name: 'HomePageText'},
            {name: 'HomePageTitle'},
            {name: 'HomePageType', type: 'int'}
        ]);

        // create the form
        this.form = new Ext.form.FormPanel({
            frame: true,
            autoHeight: true,
            title: 'Home Page',
            url:'/data/post-sm-homepage.php',
            labelAlign: 'right',
            buttonAlign:'center',
            reader: reader,
            fileUpload: true,
            baseParams: { },    // additional parameters passed to post request
            items: [{
                xtype: 'container', cls: 'form-spacer', height:10
            },{
                xtype: 'container', layout: 'column', items: [{
                    // === Simple Home Page Radio ===
                    xtype: 'radio',
                    inputValue: '1',
                    style: 'margin-left: 15px',
                    name: 'HomePageType',
                    boxLabel: 'Simple Home Page',
                    listeners: { scope: this, check: this.checkHomePageType }
                },{
                    // === Custom Home Page Radio ===
                    xtype: 'radio',
                    inputValue: '2',
                    style: 'margin-left: 15px',
                    name: 'HomePageType',
                    boxLabel: 'Custom HTML Home Page',
                    listeners: { scope: this, check: this.checkHomePageType }
                },{
                    // === Preview Link ===
                    xtype: 'container',
                    style: 'font-size:13px;padding: 2px 0 0 15px',
                    html: '<a style="color:blue" target="_blank" href="index.php">Preview your homepage</a>'
                }]
            },{
                xtype: 'container', cls: 'form-spacer', height:20
            },{
                xtype: 'container', id: 'simplehome', layout: 'form', hideLabels: true, items: [{
                // === Home Page Title ===
                    xtype: 'textfield',
                    name: 'HomePageTitle',
                    style: 'margin-left:15px',
                    emptyText: 'Type a title for your page',
                    width: 655
                },{
                // === Home Page Text ===
                    xtype: 'textarea',
                    name: 'HomePageText',
                    style: 'margin-left:15px',
                    emptyText: 'Type Something About Your Team',
                    width: 655,
                    height: 180
                },{
                    xtype: 'container', cls: 'form-spacer', height:10
                },{
                    xtype: 'container', layout: 'column', items: [{
                        xtype: 'container', style:'margin-left:15px', width: 452, items: [{
                        // === Team Image ===
                            xtype: 'displayfield',
                            name: 'TeamImage',
                            height: 272,
                            width: 452,
                            style: 'font-size:14px;padding:0px;border:1px solid #888',
                            setValue: function(val) {
                                this.value = val;
                                if(this.rendered)
                                {   
                                    var pendingUpload = Ext.getCmp("image-file").getValue();
                                    if(pendingUpload!="")
                                    {
                                        this.el.update('The image "' + pendingUpload + '" will be uploaded when you click "Save"');
                                    }
                                    else
                                    {
                                        this.el.update('<table cellpadding=0 cellspacing=0 height=100% width=100%>\
                                                          <tr><td align=center>\
                                                            <img src="/dynamic-images/homepage-image.php?T=' + g_pt + '&x=' + Math.random() + '">\
                                                          </td></tr>\
                                                        </table>');
                                    }
                                }
                            }
                        },{
                        // === Select Team Image File to Upload ===
                            xtype: 'container', style: 'position:relative;left:5px;top:-29px;height:1px', items: [{
                                xtype: 'fileuploadfield',
                                name: 'ImageFile',
                                id: 'image-file',
                                emptyText: 'Choose new image file...',
                                width: 250,
                                buttonText: '',
                                buttonCfg: { icon: '/images/choose-picture-icon.png' },
                                listeners: { scope: this, 'fileselected' : function(fb, v) {
                                    this.form.getForm().findField("TeamImage").setValue(v);
                                }}
                            }]
                        },{
                            xtype: 'container', cls: 'text50', html: '(Picture will be resized and cropped to 450px high x 270px wide)'
                        }]
                    },{
                    // === HTML tips
                        xtype: 'container',
                        width: 200,
                        style: 'padding-left:8px;font-size:12px',
                        html: '<div style="margin-bottom:5px"><b>HTML Tips:</b></div>\
                               You can use HTML tags to format your home page text\
                               <ul class="tm-list">\
                               <li class="tm-list">Use a &lt;br&gt; tag to start a new line or insert a blank line.\
                               <li class="tm-list">Place text inside &lt;b&gt;&lt;/b&gt; tags to make it <b>bold</b>.\
                               <li class="tm-list">Place text inside &lt;i&gt;&lt;/i&gt; tags to make it <i>italic</i>.\
                               <li class="tm-list">Use a &lt;ul&gt; and &lt;li&gt; tags to create a bulleted list.<br>\
                               &lt;ul&gt;<br>&nbsp;&nbsp;&lt;li&gt;List Item 1&lt;/li&gt;<br>&nbsp;&nbsp;&lt;li&gt;List Item 2&lt;/li&gt;<br>&lt;/ul&gt;\
                               </ul>'
                               
                    }]
                }]
            },{
                xtype: 'container', id:'htmlhome', items: [{    
                    xtype: 'container',
                    cls: 'manage-site-instructions',
                    html: 'Use the sample page below as a model. Place titles inside &lt;h1&gt;&lt;/h1&gt; tags. \
                           Place secondary headers inside &lt;h2&gt;&lt;/h2&gt tags. Place text inside &lt;p&gt;&lt;/p&gt tags.'
                },{
                    xtype: 'container', cls: 'form-spacer', height:5
                },{
                    xtype: 'container', layout: 'form', hideLabels: true, items: [{
                    // === HTML Home Page Editor ===
                        xtype: 'textarea',
                        name: 'HomePageHTML',
                        enableKeyEvents: true,
                        width: 687,
                        height: 380,
                        listeners: {scope: this, render: function(ta) {
                        // for sites that edit the home page as HTML, turn off text wrapping
                            ta.el.setOverflow('auto');
                            if (!Ext.isIE) {
                               ta.el.set({wrap:'off'})
                            } else {
                              ta.el.dom.wrap = "off";
                            }
                        }}
                    }]
                }]
            },{
                xtype: 'container', cls: 'form-spacer', height:5
            },{
            // === Message Field (just above buttons) ===
                xtype: 'container',
                id: 'hp-status-msg',
                style: 'display: none',     // start with message field hidden
                cls: 'form-status'
            },{
                xtype: 'container', cls: 'form-spacer', height:10
            },{
                xtype: 'container', height: 25, layout:'vbox', layoutConfig: { align: 'center'}, items: [{
            // === Save button ===
                    xtype: 'button',
                    width: 70,
                    text: 'Save',
                    handler: this.saveButtonClick,
                    scope: this
                }]
            }],

            keys: [{
                // Add keymap so Ctrl-S saves changes
                key: 's',
                scope: this,
                ctrl: true,
                stopEvent: true,
                fn: this.saveButtonClick
            }],

            listeners: {
                scope: this,
                // unmask form when load is completed
                actioncomplete: function(form, action) { if(action.type == "load") { this.form.getEl().unmask(); } },
                // redirect to login page if load returns an error (session expired)
                actionfailed: function(form, action) { if(action.type == "load") {window.location.href = 'login.php?expired=1' } },
                render: function() {
                // --- Add listener to all fields to mark this form as dirty when any item changes
                // --- Both the 'change' and 'selected' events are needed to trap all changes. The 'check' event traps changes in check boxes
//                    this.form.getForm().items.each(function(item) { item.on('check', function() { this.onFormChanged(); }, this); }, this);
                    this.form.getForm().items.each(function(item) { item.on('fileselected', function() { this.onFormChanged(); }, this); }, this);
                    this.form.el.on('keydown', function() { this.onFormChanged(); }, this);
                },
                // load data the first time the tab is activated
                activate: function() {
                    if(!this.loaded) {
                        this.checkHomePageType();
                        // Mask form while data is loading
                        this.form.getEl().mask("Loading...");
                        // load data
                        this.form.baseParams.TeamID = g_pt;
                        this.form.load({url:"/data/get-sm-homepage.php"});
                        this.loaded=true;
                    }
                }
            }
        });

        return(this.form);
    }
    
    this.checkHomePageType = function()
    {
        if(this.form.getForm().findField('HomePageType').getValue()==1)
        {
            Ext.getCmp('simplehome').show();
            Ext.getCmp('htmlhome').hide();
        }
        else
        {
            Ext.getCmp('simplehome').hide();
            Ext.getCmp('htmlhome').show();
        }
    }


    this.saveButtonClick = function()
    {
        // Safari often hangs when uploading a file. To prevent this we first need to make
        // an ajax request to a page that closes the connection to the server. Then we can
        // proceed with the file upload post.
        Ext.Ajax.request( {url: '/data/close-connection.php', scope: this, success: function() {
        // --- show sending message in message area
            setFormMessage("Saving Home Page...", "black", true, 'hp-status-msg');
        // --- disable dialog
            this.form.getEl().mask();
        // --- submit form data
            this.form.getForm().submit({
                reset: false,
                success: this.onPostSuccess,
                failure: this.onPostFailure,
                scope: this
             });
        }});
    }

    this.onPostSuccess = function(form, action)
    {
        setFormMessage("Changes Saved", "green", false, 'hp-status-msg');
        this.form.getForm().findField("ImageFile").reset();          // clear action picture file selection
        this.form.getForm().findField("TeamImage").setValue(g_pt);   // refresh team image
        this.form.getEl().unmask();
        // force browser to update cached image
        forceReload.defer(700, this, ["/dynamic-images/cache-buster.php", "homepage-image.php?T=" + g_pt]);
        // force browser to update the dynamic images in its cache by reloading page.
//        window.location.hash="#2";  // stay on the homepage tab
//        window.location.reload();
    }

    this.onPostFailure = function(form, action)
    {
        this.form.getEl().unmask();   // enable form
        switch(action.failureType) {
            case Ext.form.Action.CLIENT_INVALID:
            // --- client-side validation failed
                setFormMessage("Fields highlighted in red are required. Please fill in all required fields", "red", false, 'hp-status-msg');
                break;
            case Ext.form.Action.SERVER_INVALID:
            // --- failure message returned from code on the server
                setFormMessage("Error saving: " + action.result.message, "red", false, 'hp-status-msg');
                break;
            case Ext.form.Action.CONNECT_FAILURE:
            // --- Failed to connect to server
                setFormMessage("Error saving: Server did not respond", "red", false, 'hp-status-msg');
                break;
        }
    }

    this.onFormChanged = function()
    {
        setFormMessage("Click 'Save' or press Ctrl-S to save changes", '#00F', false, 'hp-status-msg');    // clear status message
    }
}

