// This will be called when DOM is loaded and ready
Ext.onReady(function()
{
// --- Turn on validation errors beside the field globally and enable quick tips that will
// --- popup tooltip when mouse is hovered over field
  Ext.form.Field.prototype.msgTarget = 'qtip';
  Ext.QuickTips.init();
// --- create dialogs
  g_signupDialog = new C_SignupDialog();
  g_resetPWDialog = new C_ResetPWDialog();
});


function OnLoad()
{
  ctrlEmail = document.getElementById("id");
  ctrlPW = document.getElementById("pw");
  if(ctrlEmail.value=='')
  {
      // email address is blank, put cursor in email field
      ctrlEmail.focus();
  }
  else
  {
      // email address is filled in (from cookie), put cursor in password field
      ctrlPW.focus();
  }
}
