// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
// --- Turn on validation errors beside the field globally and enable quick tips that will
// --- popup tooltip when mouse is hovered over field
    Ext.form.Field.prototype.msgTarget = 'qtip';
    Ext.QuickTips.init();
});


function getMore(length)
{
    g_rideBoardLength += length;
    updateRideBoard();
}

function updateRideBoard()
{
    Ext.get('commuting-wall').mask("Updating");
    Ext.Ajax.request({
        url: '/dynamic-sections/commuting.php?pb&l=' + g_rideBoardLength,
        success: function(response, options)
        {
            Ext.get('commuting-wall').update(response.responseText);
            Ext.get('commuting-wall').unmask();
        }
    });
}
