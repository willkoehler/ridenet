var g_HTMLRequest = Ext.urlDecode(querystring);

// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
    // --- If rider ID is passed in scroll to the rider's race report
    if(riderID=g_HTMLRequest['RiderID'])
    {
        scrollToRaceReport.defer(500, this, [riderID]);
    }
});


function scrollToRaceReport(riderID)
{
    var body = Ext.getBody();
    var scrollMax = body.getHeight() - body.getViewSize().height;
    if(el = Ext.get('R' + riderID))
    {
        var scrollTo = Math.min(el.getY() - 25, scrollMax);
        if (scrollTo > 0)
        {
            body.animate(
                { scroll: {to: [0, scrollTo]} },
                1.2,             // animation duration
                null,
                'easeOutStrong', 
                'scroll'
            );
        }
    }
}

