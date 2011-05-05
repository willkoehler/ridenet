// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
// --- Turn on validation errors beside the field globally and enable quick tips that will
// --- popup tooltip when mouse is hovered over error icon
    Ext.form.Field.prototype.msgTarget = 'side';
    Ext.QuickTips.init();
// --- Create change password form
    new C_PasswordForm(Ext.get('form-holder')).create();
});


function C_PasswordForm(parentElement)
{
    this.holder = parentElement;    // div in center of form that holds form content
    this.form = null;
    
    this.create = function()
    {

        this.form = new Ext.FormPanel({
            labelWidth: 140,
            url: '/data/change-pw-act.php',          // URL used to submit results of form
            frame:true,
            title: 'Change Password',
            bodyStyle:'padding:10px 10px 0px 10px',
            labelAlign: 'right',
            width: 390,
            cls: 'centered',        // center this panel on the page
            buttonAlign: 'center',

            items: [{
            // === Message Field (just above buttons) ===
                    hideLabel: true,
                    xtype: 'displayfield',
                    height: 45,
                    hidden: !g_mustChangePW,
                    html: 'Your account is using a temporary password. You must choose a new password before continuing.'
                },{
            // === Old Password ===
                    xtype: 'textfield',
                    fieldLabel: 'Old Password',
                    name: 'OldPW',
                    allowBlank: false,
                    blankText: 'You must enter your old password',
                    width: 170,
                    inputType: 'password'
                },{
            // === New Password ===
                    xtype: 'textfield',
                    fieldLabel: 'New Password',
                    name: 'NewPW1',
                    allowBlank: false,
                    blankText: 'You must enter a new password',
                    width: 170,
                    inputType: 'password'
                },{
            // === Confirm New Password ===
                    xtype: 'textfield',
                    fieldLabel: 'Confirm New Password',
                    name: 'NewPW2',
                    allowBlank: false,
                    blankText: 'You must enter a new password',
                    width: 170,
                    inputType: 'password'
                },{
                    xtype: 'container', cls: 'form-spacer', height:2  // spacer row
                },{
            // === Message Field (just above buttons) ===
                    xtype: 'container',
                    id: 'status-msg',
                    style: 'display: none',     // start with message field hidden
                    cls: 'form-status'
                }
            ],

            buttons: [{
                text: 'Change Password',
                minWidth: 100,
                handler: this.onChangePWButtonClick,
                scope: this
            },{
                text: 'Cancel',
                minWidth: 60,
                hidden: g_mustChangePW,
                handler: function() {history.back(1);},
                scope: this
            }]
        });
        // perform actions when form is rendered
        this.form.on('render', function() {
            this.form.getForm().findField('OldPW').focus(true,50);
        }, this);
    // --- render the form
        this.form.render(this.holder);
    // --- setup key listener to submit form when enter key is pressed
        this.holder.addKeyListener([10,13], function() {this.onChangePWButtonClick()}, this);
    }

    this.onChangePWButtonClick = function()
    {
        if(!this.form.getForm().isValid())
        {
            setFormMessage("There are errors: Fix fields marked in red", "red");
        }
        else if(this.form.getForm().findField('OldPW').getValue() == this.form.getForm().findField('NewPW1').getValue())
        {
            setFormMessage("The new password must be different from the old password", "red");
        }
        else if(this.form.getForm().findField('NewPW1').getValue() != this.form.getForm().findField('NewPW2').getValue())
        {
            setFormMessage("The new passwords don't match. Please renter and confirm the new password", "red");
        }
        else
        {
        // --- show saving message in form message area
            setFormMessage("<span class='form-status-loading'>Changing Password. Please wait...</span>", "black");
        // --- disable form
            this.form.getEl().mask();
        // --- submit form data
            this.form.getForm().submit({ reset: false, success: this.onPostSuccess, failure: this.onPostFailure, scope: this });
        }
    }


    this.onPostSuccess = function(form, action)
    {
    // --- redirect user back to page where they came from
        if(g_HTMLRequest["Goto"])
        {
            window.location.href = g_HTMLRequest["Goto"];
        }
        else
        {
            history.back(1);
        }
    }

    this.onPostFailure = function(form, action)
    {
        this.form.getEl().unmask();   // enable form
        switch(action.failureType) {
            case Ext.form.Action.CLIENT_INVALID:
            // --- client-side validation failed
                setFormMessage("There are errors: Fix fields marked in red", "red");
                break;
            case Ext.form.Action.SERVER_INVALID:
            // --- failure message returned from code on the server
                setFormMessage("Error changing password: " + action.result.message, "red");
                break;
            case Ext.form.Action.CONNECT_FAILURE:
            // --- Failed to connect to server
                setFormMessage("Error changing password: Server did not respond", "red");
                break;
        }
    }
}
