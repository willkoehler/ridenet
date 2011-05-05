// Entry point. Will be called when DOM is loaded and ready
Ext.onReady(function()
{
    new C_Map(Ext.get('map_canvas')).create();
});


function C_Map(holder)
{
    this.holder = holder
    
    this.create = function()
    {
        var myOptions = {
          zoom: 2,
          center: new google.maps.LatLng(0, 0),
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        this.map = new google.maps.Map(this.holder.dom, myOptions);
        
        // --- Parse contents of query string
        var query = Ext.urlDecode(window.location.search.substring(1));
    
        // load route data
        Ext.Ajax.request({
           url: '/data/get-map-data.php',
           params: { RideLogID: query['RideLogID'] },
           callback: this.display_route,
           scope: this
        });
    }
    
    
    this.display_route = function(opt, success, response)
    {
        var points = Ext.util.JSON.decode(response.responseText);
        var path=[]
        var bounds = new google.maps.LatLngBounds();
        for(var i=0; i < points.length; i++)
        {
            var point = new google.maps.LatLng(points[i][0], points[i][1]);
            bounds.extend(point);
            path.push(point);
        }
        
        var route = new google.maps.Polyline({
          path: path,
          strokeColor: "#FF0000",
          strokeOpacity: .5,
          strokeWeight: 3
        });
        
        this.map.fitBounds(bounds);     // zoom map to fit route
        route.setMap(this.map);         // add route to the map
    }
}
