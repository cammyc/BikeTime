function showRoutes(routes,t,h){
	var i = (t<routes.length) ? t : routes.length-1;
	var h = (h<routes.length) ? h : routes.length;
	for (i; i < h; i++) {

      var r = routes[routes.length-1-i];//show newest routes
      var rName = "<p class='routeTitle'>"+r['name']+"</p>";
      var mapThumbnail = "<div class='mapThumbnail' id='map"+i+"'/>";
      var time = rideLength(r['time']);
      var distance = r['distance'];
      var routeInfo = "<div class='routeInfo' id='routeOverlay"+i+"'><p>Time: "+time+"</p><p>Distance: "+distance+"km</p></div>"

      $('#routeList').append("<li id='liMap"+i+"'>"+rName+routeInfo+mapThumbnail+"</li>"); //creating each list item

      var map = L.map('map'+i,{zoomControl:false}).setView([r['routeCoords'][0][0], r['routeCoords'][0][1]], 13); //initializing leaflet map

      L.tileLayer('https://api.tiles.mapbox.com/v4/cmtc.nf454on2/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoiY210YyIsImEiOiJjaWpudGhhNmcwMHB1dGhtNTl5Y2Y5cmh0In0.suSXm4r9RKPb_vUr2FZp9g', {
          maxZoom: 18,
          id: 'cmtc.nf454on2',
          accessToken: 'pk.eyJ1IjoiY210YyIsImEiOiJjaWpudGhhNmcwMHB1dGhtNTl5Y2Y5cmh0In0.suSXm4r9RKPb_vUr2FZp9g'
      }).addTo(map);
      //var polyline = L.polyline(JSON.parse(r['routeCoords']), {color: 'red'}).addTo(map);

      var coords = r['routeCoords'];
      latlngs = [];
      for(var k = 0; k < coords.length; k++){
        var coord = new L.LatLng(coords[k][0],coords[k][1]);//converting coords to format usable by leaflet maps
        latlngs[k] = coord;
      }

      var polyline = L.polyline(latlngs, {color: 'red',weight:4,opacity:.6}).addTo(map); //adjusting style of polyline on map

      map.fitBounds(polyline.getBounds());
      map.dragging.disable();
      map.touchZoom.disable();
      map.doubleClickZoom.disable();
      map.scrollWheelZoom.disable();

      $('#routeOverlay'+i).hide();

      

      (function(i){
        $("#liMap"+i).hover(function(){
          $('#routeOverlay'+i).fadeIn();
        },function(){
          $('#routeOverlay'+i).fadeOut();
        });
      })(i);

       //$('#map'+i).find('a').hide(); only hide once I have changed style

    }
}

function rideLength(minutes){
  var text = "hours"
  if(parseInt(minutes/60) == 1){
    text = "hour";
  }
  return parseInt(minutes/60)+" "+text+" "+minutes%60+" min";
}