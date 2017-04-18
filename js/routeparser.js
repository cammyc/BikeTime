
function parseGPXasXML(text) {
    var parser = new DOMParser(),
        xmlDom = parser.parseFromString(text, "text/xml"); //get XML style GPX file contents


	var path = new google.maps.MVCArray();//create Array containing route coordinates

	var bounds = new google.maps.LatLngBounds(); //creaate boundary object for map

	var routeInfo = [];
	var coordResultArray = [];
	var elevationResultArray = [];
	var totaltime = 0;
	var totalDist = 0;

	var coordInfoArray =  $(xmlDom).find("trkpt");

    for (var i = 0; i<coordInfoArray.length; i++) {
    	var info = coordInfoArray[i] //info from single point recorded
	    var lat = $(info).attr("lat");
	    var lon = $(info).attr("lon");
	  	var coord = new google.maps.LatLng(lat,lon);//create google maps compliant coord
	  	path.push(coord);
	  	bounds.extend(coord);

	  	var latLong = {
	  		lat: lat,
	  		lon: lon
	  	}

	  	coordResultArray[i] = latLong;

	  	elevationResultArray[i] = $(info).find("ele").text();//creating array with all of the elevation data

    	var point = {
    		location: coord,
    		elevation: elevationResultArray[i]
    	}

    	routeInfo[i] = point;
    	if(i > 0){
    		var date1 = (new Date($(info).find("time").text()).getTime())/1000;
    		var date2 = (new Date($(coordInfoArray[i-1]).find("time").text()).getTime())/1000;

    		totalDist += calcDistance(coord,path.getArray()[i-1]);

	  		if((date1 - date2) < 10){ //if difference between two times is less then 10 seconds		
	  			totaltime += (date1 - date2);
	  		}else{
	  			totaltime += 1
	  		}
    	}
	}

	totalDist = Math.round(totalDist*100)/100;
	totaltime = Math.round(totaltime/60);

	showRouteInfoFromFile(totalDist,totaltime);

    biketime.googleMaps.map.fitBounds(bounds);

  	var polyline = new google.maps.Polyline({ //create line to put on map
      	path: path,
      	geodesic: true,
      	strokeColor: '#FF0000',
      	strokeOpacity: .7,
      	strokeWeight: 4
    });
  	directionsDisplay.setMap(null);//this is only called on createroute so this will remove directions if they have already started creating a route
    polyline.setMap(biketime.googleMaps.map);//add poly line to map

	uploadedRoute = true;


	route = {//return all the info needed from the file
		coords: coordResultArray,
		elevationData: elevationResultArray,
		distance: totalDist,
		time:  totaltime
	}

    $('#elevationGraph').height(120);
    initializeGraph(routeInfo,google.maps.ElevationStatus.OK);
    $("#uploadRoute").slideToggle();
}

function waitForTextReadComplete(reader) {
    reader.onloadend = function(event) {
        var xml = event.target.result;

        parseGPXasXML(xml);
    }
}

function handleFileSelection() {
	if(clickCount > 0){
		if(confirm('Are you sure you want to upload this route? It will delete the route you have created.')){
		$('#elevationGraph').empty();
			directionsDisplay.setMap(null);
		var file = fileChooser.files[0],
        	reader = new FileReader();

   		waitForTextReadComplete(reader);
    	reader.readAsText(file);
		}
	}else{
		var file = fileChooser.files[0],
        	reader = new FileReader();

   		waitForTextReadComplete(reader);
    	reader.readAsText(file);
	}
}

function calcDistance(p1, p2){
  return (google.maps.geometry.spherical.computeDistanceBetween(p1, p2) / 1000)
}
