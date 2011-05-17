function C_RideWidget(params)
{
    // need to store global reference to this object for newWidgetData callback
    g_widget = this;
    
    // initialize settings
    this.fullscreen = (typeof params.fullscreen === 'undefined') ? false : params.fullscreen;
    this.domainRoot = (params.domainRoot || 'ridenet.net');
    this.height = (params.height || 200) + "px";
    this.width = (params.width || 450) + "px";
    this.interval = (params.interval || 6000);
    this.preload = (params.preload || 1);
    this.maxage = (params.maxage || 10);
    this.title = (params.title || "Everyone's Riding... Are You?");
    this.headfoot = (typeof params.headfoot === 'undefined') ? true : params.headfoot;
    this.photos = (typeof params.photos === 'undefined') ? true : params.photos;
    this.scrollbar = (typeof params.scrollbar === 'undefined') ? ((this.fullscreen) ? false : true) : params.scrollbar;
    this.rider = (params.rider || '*');
    this.team = (params.team || '*');
    this.color = {
        widget : ((params.color && params.color.widget) || '#818CBF'),
        links : ((params.color && params.color.links) || '#058A12'),
        text : ((params.color && params.color.text) || '#444'),
        background : ((params.color && params.color.background) || '#FFF'),
        title : ((params.color && params.color.title) || '#FFF')
    }
    this.size = {
        headerfont: ((params.size && params.size.headerfont) || ((this.fullscreen) ? 26 : 16)),
        font : ((params.size && params.size.font) || ((this.fullscreen) ? 20 : 13)),
        pic : ((params.size && params.size.pic) || ((this.fullscreen) ? 80 : 35)),
        icon : ((params.size && params.size.icon) || ((this.fullscreen) ? 25 : 15))
    };
    this.size.picWidth = this.size.pic * .8
    this.size.picHeight = this.size.pic;

    // initialize state
    this.paused=false;

    this.create = function()
    {
        // create widget and insert it into the current document
        html='<div id="rwdgt" style="font:' + this.size.headerfont + 'px \'Lucida Grande\', \'Lucida Sans Unicode\', \'Lucida Sans\', Verdana, Tahoma, sans-serif;width:' + this.width + ';background-color:'+this.color.widget+'">';
        if(this.headfoot)
        {
            html+='<div id="rwdgt-head" style="padding:10px;color:'+this.color.title+'"> \
                     '+this.title+' \
                   </div>';
        }
        html+='<div id="rwdgt-list-holder" style="height:' + this.height + ';border:1px solid '+this.color.widget+';overflow-y:'+(this.scrollbar ? 'auto' : 'hidden')+';overflow-x:hidden;padding:0px 10px;margin:0px;position:relative;background-color:' + this.color.background + '"> \
                 <div id="rwdgt-list" style="position:realive"></div> \
               </div>';
        if(this.headfoot && !this.fullscreen)   // don't show footer in full-screen mode
        {
            html+='<div id="rwdgt-foot" style="font-size:12px;padding:4px 10px"> \
                     <div style="float:left"> \
                       <a href="http://ridenet.net" target="_blank"> \
                        <img src="http://'+this.domainRoot+'/images/widget-footer.png" border=0> \
                       </a> \
                     </div> \
                     <div style="float:right;padding-top:5px"> \
                       <a href="http://ridenet.net" target="_blank" style="text-decoration:none;color:#FFF"> \
                         Ride&bull;Post&bull;Connect \
                       </a> \
                     </div> \
                     <div style="clear:both"></div> \
                   </div>';
        }
        html+='</div>';
        // get script tag currently running
        var scripts = document.getElementsByTagName('script');
        var thisScriptTag = scripts[ scripts.length - 1 ];
        // add the widget just below the script tag
        Ext.DomHelper.insertAfter(thisScriptTag, html);

        if(this.fullscreen)
        {
            // Have browser set widget width
            Ext.fly('rwdgt').setWidth('100%');
            // Manually set widget height. Use DelayedTask to reduce number of resize events we handle
            this.resizeTask = new Ext.util.DelayedTask(function() {
                this.fitHeight();
            }, this);
            Ext.EventManager.on(window, "resize", function() { this.resizeTask.delay(100) }, this);
            // Set intial height
            this.fitHeight();
        }

    // --- Preload first batch of data from the server
        this.getMoreData();

    // --- Start update timer
        Ext.TaskMgr.start.defer(this.interval, this, [{
            run: this.nextRide,
            scope: this,
            args: [false],  // (preload = false)
            interval: this.interval
        }]);

    // --- setup handler to stop auto scrolling when mouse is hovering over panel
/*        if(!this.fullscreen)
        {
            Ext.fly('rwdgt').hover(
                function() { this.paused=true },
                function() { this.paused=false },
                this);
        }*/
    }
    
    this.nextRide = function(preload)
    {
        if(!this.paused || preload)
        {
            if(this.data && this.data.length>0)
            {
                var ride = this.data.pop();
                this.insertRide(this.buildRide(ride), preload);
                if(this.data.length==0)
                {
                    this.getMoreData();
                }
            }
        }
    }
    
    this.insertRide = function(html, preload)
    {
        var panel = Ext.get('rwdgt-list')
        // Preload rides, show immediately, no effects
        if(preload)
        {
            panel.insertFirst({tag:'div', html:html});
        }
        else
        // Subsequent rides do all the fancy animation stuff
        {
            // Add new ride div to the ride panel. We then calculate the height of the ride div and
            // immediately set the height to zero. This happens quickly enough the user doesn't see it
            var newRide = panel.insertFirst({tag:'div', style:'visibility:hidden;overflow:hidden', html:html});
            var rideHeight = newRide.getHeight();
            newRide.setHeight(0);
            // Create a mask. We fade out the mask rather than fading in the ride div to work around an opacity
            // bug in IE7/IE8. When opacity is applied in IE7/IE8, font smoothing is disabled, bold text looks
            // bad and image backgrounds turn black (WTF?)
            var mask = panel.insertFirst({tag:'div', style:'z-index:1;background-color:'+this.color.background+';position:absolute;width:100%;visibility:hidden;'});
            mask.setHeight(rideHeight);
            // Animate growing of new ride div. Since ride div is hidden this has the effect of
            // shifting existing rides down creating space for the new ride
            newRide.setHeight(rideHeight, {
                duration: 1,
                callback: function() {
                    mask.show();        // show the mask
                    newRide.show();     // show ride (hidden behind mask)
                    newRide.setStyle('height', 'auto');    // allow text to reflow when scroll bar appears
                    mask.fadeOut({ duration: .4, easing: 'easeNone', remove: true });   // fade out mask
                }
            });
        }
    }

    this.buildRide = function(r)
    {
        var ageText = (r.Age==0) ? "today" : ((r.Age==1) ? "yesterday" : r.Age + "&nbsp;days&nbsp;ago");
        if(r.Distance)
        {
            var distanceText = r.Distance + "&nbsp;mile&nbsp;" + r.RideLogType.toLowerCase();
        }
        else if(r.Duration)
        {
            var d = r.Duration;
            var distanceText = ((d <= 90) ? d + "&nbsp;minute&nbsp;" : (d/60.0).toFixed(1) + "&nbsp;hour&nbsp;") + r.RideLogType.toLowerCase();
        }
        else
        {
            var distanceText = r.RideLogType.toLowerCase();
        }
        html = '<div style="padding-top:.3em;font:'+this.size.font+'px helvetica, arial;line-height:1.25em;">';
        if(this.photos)
        {
            html+='<div style="margin-bottom:.1em;white-space:nowrap;color:#888"> \
                     <a style="text-decoration:none;color:'+this.color.links+'" href="http://'+r.Domain+'.ridenet.net/rider/' + r.RiderID + '" target="_blank">' + r.RiderName + '</a> &bull; ' + r.TeamName + ' \
                   </div> \
                   <div style="float:left"> \
                    <a href="http://'+r.Domain+'.ridenet.net/rider/' + r.RiderID + '" target="_blank"> \
                      <img style="vertical-align:bottom;border:0;width:'+this.size.picWidth+'px;height:'+this.size.picHeight+'px" src="http://'+this.domainRoot+'/imgstore/rider-portrait/' + r.RacingTeamID + '/' + r.RiderID + '.jpg"> \
                    </a> \
                  </div>'
        }
        html+='<div style="color:'+this.color.text+';margin-left:'+(this.photos ? this.size.picWidth+7 : 0 )+'px"> \
                 <img style="vertical-align:-.2em" src="http://'+this.domainRoot+'/images/ridelog/' + r.RideLogTypeImage + '" height='+this.size.icon+' title="' + r.RideLogType + '"> \
                 <img style="vertical-align:-.2em" src="http://'+this.domainRoot+'/images/weather/' + r.WeatherImage + '" height='+this.size.icon+' title="' + r.Weather + '"> '
                 + r.Comment +
                 '<span style="font-size:0.9em;color:#888">&nbsp;&bull; ' + distanceText + '&nbsp;&bull; ' + ageText + '</span> \
               </div> \
               <div style="clear:both;padding-top:.6em;border-bottom:1px dotted #CCC;" /> \
             </div>';
        return(html);
    }

    this.getMoreData = function()
    {
        var head = document.getElementsByTagName('head')[0];
        // Remove script tag created during previous data request
        if(this.widgetDataScript)
        {
            head.removeChild(this.widgetDataScript);
        }
        // generate URL for request
        var params = "callback=newWidgetData";
        params += "&rider=" + this.rider;
        params += "&team=" + this.team;
        params += "&maxage=" + this.maxage;
        params += "&_dc=" + new Date().getTime();
        var url = 'http://' + this.domainRoot + '/data/list-ride-widget-data.php?' + params;
        // Request data from the server. Need to use a script tag to get the data because we may be getting
        // data from a different domain than the page with the widget. AJAX doesn't work cross-domain
        this.widgetDataScript = document.createElement('script');
        this.widgetDataScript.type = 'text/javascript';
        this.widgetDataScript.src = url;
        // set timeout to retry after 30 seconds
        this.dataTimeout = this.getMoreData.defer(30000, this);
        // inserting the script tag into the DOM triggers the load. When the load is complete the javascript will be
        // executed, calling the global callback function newWidgetData()
        head.appendChild(this.widgetDataScript);
    }
    
    this.onNewData = function(data)
    {
        clearTimeout(this.dataTimeout);
        this.data = data;
        // insert preload rides
        while(this.preload>0)
        {
            this.nextRide(true);
            this.preload--;
        }
    }
    
    this.fitHeight = function()
    {
        var headHeight = this.headfoot ? Ext.fly('rwdgt-head').getHeight() : 0;
        Ext.fly('rwdgt-list-holder').setHeight(Ext.lib.Dom.getViewportHeight() - headHeight);
    }
}

// Global handler called by javascript code by script tag created in getMoreData()
function newWidgetData(data)
{
    g_widget.onNewData(data);
}
