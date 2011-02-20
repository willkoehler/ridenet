function C_LocationDialog()
{
    this.window = null;

    // -------------------------------------------------------------------------------------------
    //  Show calendar/event filter dialog.
    //  params object has the following parameters:
    //      callback    - Function that will be called when event is saved/created
    //      scope       - scope in which to execute the callback function. (The callback function's
    //                    "this" context)
    //      animateTarget   - id of HTML target to animate opening/closing window
    //      ypos            - y position of the window
    //      zipCodeText     - City and Zip code text to show in dialog
    //      zipCode         - Zip code value to show in dialog
    //      range           - range to show in dialog
    // -------------------------------------------------------------------------------------------
    this.show = function(params)
    {
        this.callback = params.callback;
        this.callbackScope = params.scope;
        this.zipCodeText = params.zipCodeText;
        this.zipCode = params.zipCode;
        this.range = params.range;

        if( ! this.window)
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
                proxy: new Ext.data.HttpProxy({ url: 'data/lookup-zip-code.php' })
            });

            this.form = new Ext.form.FormPanel({
                baseCls: 'x-plain',     // (gives panel a gray background - by default panels have white backgrounds)
                labelAlign: 'right',
                bodyStyle:'padding:5px 5px 0',
                buttonAlign:'center',
                baseParams: { },    // additional parameters passed to post request
                items: [{
                    xtype:'container', layout:'column', items: [{
                        xtype:'container', layout:'form', width:145, labelWidth:100, items: [{
                        // === Range ===
                            xtype: 'numberfield',
                            labelSeparator: '',
                            fieldLabel: 'Show rides within',
                            name: 'Range',
                            width: 40,
                            allowBlank: false,
                            blankText: 'You must enter a range'
                        }]
                    },{
                        xtype:'container', layout:'form', width:280, labelWidth:48, items: [{
                        // === Zip Code ===
                            xtype: 'remotecombobox',
                            fieldLabel: 'miles of',
                            labelSeparator: '',
                            displayField: 'text',
                            valueField: 'id',
                            hiddenName: 'ZipCodeID',
                            forceSelection: true,
                            width:220,
                            allowBlank: false,
                            blankText: 'You must enter your zip code',
                            store: this.dsZipCodeLookup
                        }]
                    },{
                        xtype:'container', layout:'form', width:65, hideLabels: true, items: [{
                        // === (zip code) ===
                            xtype: 'displayfield',
                            html: '(zip code)',
                            width: 60
                        }]
                    }] // end of container
                },{
            // === Message Field (just above buttons) ===
                    xtype: 'container',
                    id: 'status-msg',
                    style: 'display:none',   // start off hidden initially
                    cls: 'form-status'
                }],

                buttons: [{
                    text: 'Update',
                    handler: this.saveButtonClick,
                    scope: this
                },{
                    text: 'Cancel',
                    handler: this.cancelButtonClick,
                    scope: this
                }],
                
                keys: [{
                    // Add keymap so pressing <Enter> saves changes
                    key: [10,13],
                    scope: this,
                    stopEvent: true,
                    fn: this.saveButtonClick
                }]
            });

            this.window = new Ext.Window({
                width: 540,             // (height will be calculated based on content)
                y: params.ypos,
                autoHeight: true,       // allows calls to syncSize() to resize the window based on content
                forceLayout: true,      // force window to calculate layout (i.e. height) before opening
                resizable: false,
                closeAction:'hide',     // hide instead of destroying window on close
                modal: true,
                bodyStyle:'padding:5px;',
                title: 'Community Ride Preferences',
                items: this.form
            });

            // perform actions when window opens
            this.window.on('show', function() {
            // --- Initialize form values
                this.form.getForm().findField("Range").setValue(this.range);
                this.form.getForm().findField("ZipCodeID").setValue(this.zipCode);         // set underlying value
                this.form.getForm().findField("ZipCodeID").setRawValue(this.zipCodeText);  // set displayed text
                this.setMessage('', 'black');                              // clear message area
                this.form.getForm().findField('Range').focus(true, 200);  // set initial focus
            }, this);
        }

        // open window
        this.window.show(params.animateTarget);
    }

    this.cancelButtonClick = function()
    {
        this.window.hide();
    }

    this.saveButtonClick = function()
    {
        if(!this.form.getForm().isValid())
        {
            setFormMessage("Fields highlighted in red are required. Please fill in all required fields", "red");
        }
        else
        {
        // save values in cookies
            var expires = new Date(new Date().getTime()+(1000*60*60*24*365*2)).toGMTString();   // expire in 2 years
            document.cookie = "CalendarFilterZip=" + this.form.getForm().findField("ZipCodeID").getValue() + "; expires=" + expires + "; domain=" + g_domainRoot;
            document.cookie = "CalendarFilterRange=" + this.form.getForm().findField("Range").getValue() + "; expires=" + expires + "; domain=" + g_domainRoot;
        // reload page (defer is needed to make sure spinning loading icon displays before reload starts
            this.setMessage('Updating...', 'black', true);
            (function() { window.location.reload(); }).defer(200);
        }
    }

    this.setMessage = function(message, color, loading)
    {
        setFormMessage(message,color,loading);
        // recalculate window size to fit new contents (needed in IE)
        this.window.syncSize();
    }
}