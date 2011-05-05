function C_CustomizeTab()
{
    this.create = function()
    {
        // Remote lookup for Zip Codes
        this.dsZipCodeLookup = new Ext.data.JsonStore({
            root: 'results',                // results array is returned in this property
            totalProperty: 'rowcount',      // total number of rows is returned in this property
            idProperty: 'ZipCodeID',        // defines the primary key for the results
            fields: [
                {name: 'id', type: 'int'},
                {name: 'text'}
            ],
            proxy: new Ext.data.HttpProxy({ url: '/data/lookup-zip-code.php' })
        });

        // Json Reader to read data for dialog
        var reader = new Ext.data.JsonReader( { root: 'results', idProperty: 'TeamID' }, [
            {name: 'TeamID', type: 'int'},
            {name: 'TeamName'},
            {name: 'Domain'},
            {name: 'bRacing', type: 'int'},
            {name: 'bCommuting', type: 'int'},
            {name: 'TeamTypeID', type: 'int'},
            {name: 'ZipCodeID', type: 'int'},
            {name: 'ZipCodeText'},
            {name: 'ShowLogo', type: 'int'},
            {name: 'URL', mapping: 'Domain'},
            {name: 'BannerImg', mapping: 'TeamID', type: 'int'},
            {name: 'LogoImg', mapping: 'TeamID', type: 'int'},
            {name: 'PrimaryColor'},
            {name: 'SecondaryColor'},
            {name: 'BodyBGColor'},
            {name: 'PageBGColor'},
            {name: 'LinkColor'}
        ]);
        
        // create a color picker object so we can call static member functions
        this.cp = new Ext.ux.ColorPicker;

        // create the form
        this.form = new Ext.form.FormPanel({
            frame: true,
            autoHeight: true,
            title: 'Customize',
            url:'/data/post-sm-customize.php',
            labelAlign: 'right',
            buttonAlign:'center',
            reader: reader,
            fileUpload: true,
            baseParams: { },    // additional parameters passed to post request
            items: [{
                xtype: 'fieldset', title: "Team Information", layout: 'form', labelWidth: 65, items: [{
                    xtype: 'container', layout: 'column', items: [{
                        xtype: 'container', layout: 'form', width: 310, labelWidth: 65, items: [{
                        // === Team Name ===
                            xtype: 'textfield',
                            fieldLabel: 'Name',
                            name: 'TeamName',
                            allowBlank: false,
                            blankText: 'You must enter a team name',
                            width: 230
                        }]
                    },{
                        xtype: 'container', layout: 'form', width: 285, items: [{
                        // === Zip Code ===
                            xtype: 'remotecombobox',
                            fieldLabel: 'Location',
                            displayField: 'text',
                            valueField: 'id',
                            hiddenName: 'ZipCodeID',
                            forceSelection: true,
                            id: 'zip-code',
                            width: 210,
                            listWidth: 260,
                            allowBlank: false,
                            blankText: 'Please enter the zip code for your team',
                            store: this.dsZipCodeLookup
                        }]
                    },{
                        xtype: 'container',
                        style: 'padding-top:4px;color:#888',
                        html: '(zip code)'
                    }]
                },{
                    xtype: 'container', layout: 'column', items: [{
                        xtype:'container', layout:'form', width:230, items: [{
                        // === Team Type ===
                            xtype: 'localcombobox',
                            fieldLabel: 'Type',
                            displayField: 'text',
                            valueField: 'id',
                            hiddenName: 'TeamTypeID',
                            forceSelection: true,
                            width: 130,
                            allowBlank: false,
                            blankText: 'Please choose a team type',
                            store: new Ext.data.ArrayStore({ fields: ['id', 'text'], data: teamTypeLookup })
                        }]
                    },{
                        xtype: 'container', layout: 'form', hideLabels: true, width: 140, items: [{
                        // === Racing Checkbox ===
                            xtype: 'container', layout: 'form', hideLabels: true, style: 'padding-top: 2px', items: [{    // shift down slighly
                                xtype: 'checkbox',
                                boxLabel: 'Show Racing Page',
                                name: 'bRacing'
                            }]
                        }]
                    },{
                        xtype: 'container', layout: 'form', width: 150, hideLabels: true, items: [{
                        // === Commuting Checkbox ===
                            xtype: 'container', layout: 'form', hideLabels: true, style: 'padding-top: 2px', items: [{    // shift down slighly
                                xtype: 'checkbox',
                                boxLabel: 'Show Commuting Page',
                                name: 'bCommuting'
                            }]
                        }]
                    }]
                },{
                    xtype:'container', layout:'column', items: [{
                        xtype: 'container', layout: 'form', width: 225, items: [{
                        // === Team Domain ===
                            xtype: 'textfield',
                            fieldLabel: 'Domain',
                            name: 'Domain',
                            width: 130,
                            enableKeyEvents: true,
                            allowBlank: false,
                            blankText: 'You must enter a domain name',
                            listeners: { scope: this, 'keyup' : function(field, v) {
                                this.form.getForm().findField("URL").setValue(field.getValue());
                            }}
                        }]
                    },{
                        xtype: 'container', layout: 'form', width: 355, items: [{
                        // === Team URL ===
                            xtype: 'displayfield',
                            fieldLabel: 'Homepage',
                            name: 'URL',
                            id: 'team-home',
                            style: 'padding-top: 3px',
                            setValue: function(val) {
                                this.value = val;
                                var home = (val) ? "http://" + val + "." + g_domainRoot : "(no domain specified)";
                                this.el.update("<a style='color:blue' href='" + home + "' target='_blank'>" + home + "</a>&nbsp;&nbsp;");
                            }
                        }]
                    }]
                }]
            },{
                xtype:'container', layout:'column', items: [{
                    xtype: 'container', items: [{
                        xtype: 'fieldset', width: 190, height: 157, title: 'Team Logo', items: [{
                            xtype:'container', items: [{
                            // === Logo ===
                                xtype: 'displayfield',
                                name: 'LogoImg',
                                height: 72,
                                width: 167,
                                style: 'font-size: 10px;padding: 0px',
                                setValue: function(val) {
                                    this.value = val;
                                    if(this.rendered)
                                    {   
                                        var pendingUpload = Ext.getCmp("logo-file").getValue();
                                        if(pendingUpload!="")
                                        {
                                            this.el.update('The image "' + pendingUpload + '" will be uploaded when you click "Save"');
                                        }
                                        else
                                        {
                                            this.el.update('<table cellpadding=0 cellspacing=0 height=100% width=100%>\
                                                              <tr><td align=center>\
                                                                <img src="/dynamic-images/team-logo-sm.php?T=' + g_pt + '&x=' + Math.random() + '">\
                                                              </td></tr>\
                                                            </table>');
                                        }
                                    }
                                }
                            }]
                        },{
                            xtype: 'container', cls: 'form-spacer', height:4
                        },{
                            xtype:'container', layout:'form', width:235, hideLabels: true, items: [{
                            // === Select Logo File to Upload ===
                                xtype: 'fileuploadfield',
                                name: 'LogoFile',
                                id: 'logo-file',
                                emptyText: 'Choose logo file...',
                                width: 165,
                                buttonText: '',
                                buttonCfg: { icon: '/images/choose-picture-icon.png' },
                                listeners: { scope: this, 'fileselected' : function(fb, v) {
                                    this.form.getForm().findField("LogoImg").setValue(v);
                                }}
                            }]
                        },{
                            xtype:'container', layout:'form', width:165, hideLabels: true, items: [{
                            // === Show team logo ===
                                xtype: 'checkbox',
                                boxLabel: 'Show Logo in Page Banner',
                                name: 'ShowLogo',
                                width: 165
                            }]
                        }]
                    }]
                },{
                    xtype: 'container', width: 5, height: 10
                },{
                    xtype: 'container', items: [{
                        xtype: 'fieldset', width:490, height: 157, title: 'Colors & Background', items: [{
                            xtype: 'container', layout:'column', items: [{
                                xtype:'container', layout:'form', width:220, labelWidth: 120, items: [{
                                // === Primary Color ===
                                    xtype: 'colorpickerfield',
                                    fieldLabel: 'Primary Color',
                                    name: 'PrimaryColor',
                                    editMode: 'picker',
                                    hideHtmlCode: true,
                                    width: 80,
                                    listeners: { scope: this, change: function(m, c) {
                                            // calculate menu text color (make sure to copy any changes to this formula to styles.pcs)
                                            var cHSV = this.cp.rgbToHsv(this.cp.hexToRgb(c));
                                            cHSV = { h: cHSV[0], s:cHSV[1], v:cHSV[2]};
                                            var darkMenuText = ((cHSV.v > .65 && cHSV.s < .35) || (cHSV.v > .65 && cHSV.h > 42 && cHSV.h < 185 ));
                                            var menuText = darkMenuText ? "333333" : "EEEEEE";
                                            cHSV.v = (cHSV.v > .5) ? cHSV.v * .85 : cHSV.v * 1.5;
                                            var menuHLColor = this.cp.rgbToHex(this.cp.hsvToRgb(cHSV.h, cHSV.s, cHSV.v));
                                            // Update elements in real time as color changes
                                            Ext.fly('footer').setStyle("background-color", "#" + c);
                                            Ext.fly('topmenu').setStyle("background-color", "#" + c);
                                            Ext.select('#nav a#active').setStyle("color", "#" + menuText);
                                            Ext.select('#nav a#active').setStyle("background-color", "#" + menuHLColor);
                                            Ext.select('#subnav a#active').setStyle("color", "#" + c);
                                            Ext.select('h1').setStyle("color", "#" + c);
                                            Ext.select('#footer p').setStyle("color", "#" + menuText);
                                            Ext.select('#nav li a[id!=active]').setStyle("color", "#" + menuText);
                                            this.menuHLColor = menuHLColor;
                                        },
                                        select: function(m, c) {
                                            // CSS rule updates are slow - don't do this in real-time
                                            Ext.util.CSS.updateRule(['#nav li a:hover', '#nav li a:hover, #nav a#active'], 'background-color', '#' + this.menuHLColor);
                                            Ext.util.CSS.updateRule(['#subnav li a:hover', '#subnav li a:hover, #subnav a#active'], 'color', '#' + c);
                                        }
                                    
                                    }
                                },{
                                // === Page Background Color ===
                                    xtype: 'colorpickerfield',
                                    fieldLabel: 'Page Color',
                                    name: 'PageBGColor',
                                    editMode: 'palette',
                                    hideHtmlCode: true,
                                    customPalette: ['E1E1E1', 'E6E6E6', 'EAEAEA', 'F8F8F8', 'FFFFFF'],
                                    width: 80,
                                    listeners: { scope: this, select: function(m, c) {
                                        // update color changes in real time
                                        Ext.fly("mainContent").setStyle("background-color", "#" + c);
                                        Ext.select('#subnav a#active').setStyle("background-color", "#" + c);
                                        Ext.util.CSS.updateRule(['#subnav li a:hover', '#subnav li a:hover, #subnav a#active'], 'background-color', '#' + c);
                                    }}
                                },{
                                // === Body Background Color ===
                                    xtype: 'colorpickerfield',
                                    fieldLabel: 'Background Color',
                                    name: 'BodyBGColor',
                                    editMode: 'picker',
                                    hideHtmlCode: true,
                                    width: 80,
                                    listeners: { scope: this, change: function(m, c) {
                                        // update color changes in real time
                                        Ext.getBody().setStyle("background-color", "#" + c);
                                    }}
                                }]
                            },{
                                xtype:'container', layout:'form', width:240, labelWidth: 130, items: [{
                                // === Secondary Color ===
                                    xtype: 'colorpickerfield',
                                    fieldLabel: 'Secondary Color',
                                    name: 'SecondaryColor',
                                    editMode: 'picker',
                                    hideHtmlCode: true,
                                    width: 80,
                                    listeners: { scope: this, change: function(m, c) {
                                            // calculate secondary background color (make sure to copy any changes to this formula to styles.pcs)
                                            var cHSV = this.cp.rgbToHsv(this.cp.hexToRgb(c));
                                            cHSV = { h: cHSV[0], s:cHSV[1], v:cHSV[2]};
                                            cHSV.s *= .12;
                                            cHSV.v = (cHSV.s >= .03) ? cHSV.v * .75 + .42 : cHSV.v * .186 + .787;
                                            cHSV.v = Math.max(cHSV.v, .75);
                                            cHSV.v = Math.min(cHSV.v, 1);
                                            var bgColor = this.cp.rgbToHex(this.cp.hsvToRgb(cHSV.h, cHSV.s, cHSV.v));
                                            // update color changes in real time
                                            Ext.select('#subnav li a[id!=active]').setStyle("color", "#" + c);
                                            Ext.fly("submenu").setStyle("background-color", "#" + bgColor);
                                            Ext.select('h2').setStyle("color", "#" + c);
                                            Ext.select('h3').setStyle("color", "#" + c);
                                            Ext.select('#sidebarHolderRight').setStyle({ borderColor: "#" + c, backgroundColor: "#" + bgColor});
                                            Ext.select('.sidebarBlock').setStyle("border-color", "#" + c);
                                            Ext.select('table#event-list .header').setStyle("background-color", "#" + bgColor);
                                        }
                                    }
                                },{
                                // === Link Color ===
                                    xtype: 'colorpickerfield',
                                    fieldLabel: 'Link Color',
                                    name: 'LinkColor',
                                    editMode: 'picker',
                                    hideHtmlCode: true,
                                    width: 80,
                                    listeners: { scope: this, change: function(m, c) {
                                        // update color changes in real time
                                        Ext.select('a.sample-link').setStyle("color", "#" + c);
                                    }}
                                },{
                                // === Select Body Image File to Upload ===
                                    xtype: 'container', layout: 'column', items: [{
                                        xtype: 'fileuploadfield',
                                        name: 'PageBGFile',
                                        id: 'page-bg-file',
                                        emptyText: 'Set background image...',
                                        hideLabel: true,
                                        width: 190,
                                        buttonText: '',
                                        buttonCfg: { icon: '/images/choose-picture-icon.png' }
                                    },{
                                        xtype: 'button',
                                        style: 'margin-left: 5px',
                                        icon: '/images/delete-icon-small.png',
                                        tooltip: 'Clear background image',
                                        handler: this.onClickClearBodyImage,
                                        scope: this
                                    }]
                                }]
                            }]
                        },{
                            xtype: 'container', layout: 'column', items: [{
                            // === Show Sample Content ===
                                xtype: 'container', items: [{
                                    xtype: 'button',
                                    style: 'margin-left:10px',
                                    text: '&nbsp;Sample Content&nbsp;',
                                    listeners: { scope: this, click: function() {
                                        var height = Ext.fly('sample-content').getHeight();
                                        Ext.fly('sample-holder').setHeight(height+15, true);
                                    }}
                                }]
                            },{
                                xtype: 'container', items: [{
                                // === Show Sample Content ===
                                    xtype: 'container',
                                    style: 'color:#888;margin-left:10px;margin-top:4px',
                                    html: "Colors will update instantly but they're not saved until you click 'Save'"
                                }]
                            }]
                        }]
                    }]
                }]
            },{
                xtype: 'fieldset', title: 'Page Banner', items: [{
                    xtype:'container', hideLabels: true, items: [{
                    // === Page Banner ===
                        xtype: 'displayfield',
                        name: 'BannerImg',
                        width: 662,
                        style: 'padding: 0px; text-align:center; border: 1px solid black;',
                        setValue: function(val) {
                            this.value = val;
                            if(this.rendered) 
                            {   
                                var pendingUpload = Ext.getCmp("banner-file").getValue();
                                if(pendingUpload!="")
                                {
                                    this.el.update('<div style="height:70px">The image "' + pendingUpload + '" will be uploaded when you click "Save"</div>');
                                }
                                else
                                {
                                    this.el.update('<img class="tight tm-banner-bg" src="/dynamic-images/page-banner.php?T=' + g_pt + '&x=' + Math.random() + '" Width=660>');
                                }
                            }
                        }
                    }]
                },{
                    xtype: 'container', cls: 'form-spacer', height:5
                },{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:235, hideLabels: true, items: [{
                        // === Select Banner File to Upload ===
                            xtype: 'fileuploadfield',
                            id: 'banner-file',
                            name: 'BannerFile',
                            emptyText: 'Choose banner file...',
                            width: 225,
                            buttonText: '',
                            buttonCfg: { icon: '/images/choose-picture-icon.png' },
                            listeners: { scope: this, 'fileselected' : function(fb, v) {
                                this.form.getForm().findField("BannerImg").setValue(v);
                            }}
                        }]
                    },{
                        xtype:'container', layout:'form', width:350, hideLabels: true, items: [{
                        // === Instructions ===
                            xtype: 'container',
                            style: 'padding-top:2px',
                            html: '(Banner image must be 780px wide. Recommended height is 85px)'
                        }]
                    }]
                }]
            },{
            // === Message Field (just above buttons) ===
                xtype: 'container',
                id: 'sc-status-msg',
                style: 'display: none',     // start with message field hidden
                cls: 'form-status'
            }],

            buttons: [{
            // === Save button ===
                width: 70,
                text: 'Save',
                handler: this.saveButtonClick,
                scope: this
            },{
            // === Cancel button ===
                width: 70,
                text: 'Cancel',
                handler: this.cancelButtonClick,
                scope: this
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
                actioncomplete: function(form, action) { if(action.type == "load") {
                    this.form.getEl().unmask();
                    this.originalDomain = form.reader.jsonData.results.Domain;
                    // ZipCode combo is a remote combo box so on load we need to manually set the
                    // display value of the combo box. setRawValue() updates the displayed text
                    // while leaving the underlying zip code value unchanged
                    if(form.reader.jsonData.results.ZipCodeText!="")
                    {
                        Ext.getCmp('zip-code').setRawValue(form.reader.jsonData.results.ZipCodeText);
                    }
                }},
                // redirect to login page if load returns an error (session expired)
                actionfailed: function(form, action) { if(action.type == "load") {window.location.href = 'login.php?expired=1' } },
                render: function() {
                // --- Add listener to all fields to mark this form as dirty when any item changes
                // --- Both the 'change' and 'selected' events are needed to trap all changes. The 'check' event traps changes in check boxes
                    this.form.getForm().items.each(function(item) { item.on('select', function() { this.onFormChanged(); }, this); }, this);
                    this.form.getForm().items.each(function(item) { item.on('change', function() { this.onFormChanged(); }, this); }, this);
                    this.form.getForm().items.each(function(item) { item.on('fileselected', function() { this.onFormChanged(); }, this); }, this);
                    this.form.el.on('keydown', function() { this.onFormChanged(); }, this);
                },
                // load data the first time the tab is activated
                activate: function() {
                    if(!this.loaded) {
                        // Mask form while data is loading
                        this.form.getEl().mask("Loading...");
                        // load data
                        this.form.baseParams.TeamID = g_pt;
                        this.form.load({url:"/data/get-sm-customize.php"});
                        this.loaded=true;
                    }
                }
            }
        });

        return(this.form);
    }


    this.saveButtonClick = function()
    {
        // Safari often hangs when uploading a file. To prevent this we first need to make
        // an ajax request to a page that closes the connection to the server. Then we can
        // proceed with the file upload post.
        Ext.Ajax.request( {url: '/data/close-connection.php', scope: this, success: function() {
        // --- show sending message in message area
            setFormMessage("Saving Site Customizations...", "black", true, 'sc-status-msg');
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
    
    this.cancelButtonClick = function()
    {
        window.location.hash="#0";  // stay on the customize tab
        window.location.reload();
    }

    this.onPostSuccess = function(form, action)
    {
        var domain = this.form.getForm().findField("Domain").getValue()
        if(domain!=this.originalDomain)
        {
            // user changed the domain, redirect user's browser to the new domain
            window.location.hash="#0";  // stay on the customize tab
            window.location.href = window.location.href.replace(this.originalDomain, domain)
            
        }
        else
        {
            // force browser to update the dynamic images in its cache by reloading page.
            window.location.hash="#0";  // stay on the customize tab
            window.location.reload();
        }
    }

    this.onPostFailure = function(form, action)
    {
        this.form.getEl().unmask();   // enable form
        switch(action.failureType) {
            case Ext.form.Action.CLIENT_INVALID:
            // --- client-side validation failed
                setFormMessage("Fields highlighted in red are required. Please fill in all required fields", "red", false, 'sc-status-msg');
                break;
            case Ext.form.Action.SERVER_INVALID:
            // --- failure message returned from code on the server
                setFormMessage("Error saving: " + action.result.message, "red", false, 'sc-status-msg');
                break;
            case Ext.form.Action.CONNECT_FAILURE:
            // --- Failed to connect to server
                setFormMessage("Error saving: Server did not respond", "red", false, 'sc-status-msg');
                break;
        }
    }

    this.onFormChanged = function()
    {
        setFormMessage("Click 'Save' to save changes. Press 'Cancel' to undo changes", '#00F', false, 'sc-status-msg');    // clear status message
    }


    this.onClickClearBodyImage = function()
    {
        Ext.Msg.show({
            title: "Clear Background Image",
            msg: "Do you want to clear the background image?",
            fn: function(btn) { if(btn=='yes') {
                this.form.getEl().mask("Clearing...");
                Ext.Ajax.request({
                    url: '/data/clear-body-image.php',
                    params: {
                        TeamID: g_pt
                    },
                    scope: this,
                    success: this.handleClearBodyImageSuccess,
                    failure: this.handleClearBodyImageFailure
                });
            } },
            scope: this,
            buttons: {yes:'&nbsp;Clear&nbsp;', no:'&nbsp;Cancel&nbsp;'}
        });
    }

    this.handleClearBodyImageSuccess = function(response, options)
    {
        this.form.getEl().unmask();
    // --- decode JSON response string and check status of delete
        var result = Ext.decode(response.responseText);
        if(result.success == false)
        {
            Ext.Msg.alert("Operation Failed", "Error Clearing Body Image: " + result.message);
        }
        else
        {
            setFormMessage("Body Image Cleared", "green", false, 'sc-status-msg');
            // force browser to recognize new (blank) body image
            forceReload.defer(350, this, ["/dynamic-images/cache-buster.php", "body-bg-image.php?T=" + g_pt]);
            Ext.get(document.body).setStyle("background-image", "url('/dynamic-images/body-bg-image.php?T=" + g_pt + "&x=" + Math.random() + "')");
        }
    }

    this.handleClearBodyImageFailure = function(response)
    {
        this.form.getEl().unmask();
        Ext.Msg.alert("Operation Failed", "Error Clearing Body Image: Server did not repond");
    }
}

