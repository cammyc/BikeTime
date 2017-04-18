var page = null;//page is the page this is called from (home index, group page, etc...)
var aborted = false;

function showRides(args){
 
	page = args['page'];
	deleteMarkers();

	var getRidesRequest = getRides(args);

		getRidesRequest.done(function(result) {
			// console.log(result)
	    var rides = JSON.parse(result);

        for(var i = 0; i<rides.length;i++){
      		displayMarkers(i,rides[i],args['startDate'])
      	}

		}).fail(function (jqXHR, textStatus, errorThrown){
			if(!aborted){
           		alert("Unable to retrieve rides. Please check your internet connection.")
			}
		});
	}

function showFeed(args, listID){
	var list = $(listID);
		list.empty();
	page = args['page'];
	var getRidesForStreamRequest = getRides(args);

	getRidesForStreamRequest.done(function(result) {
		// console.log(result)
	    var rides = JSON.parse(result);

        if(rides.length > 0){
        	for(var i = 0; i < rides.length; i++){
        		var ride = rides[i];
        		var lat = ride['startLat'];
				var longs = ride['startLon'];
				var creatorProfile = ride['creatorProfile'];
				var group = ride['group']
				var description = ride['description'];
				var startDate = Number(ride['startDate']);//date used for getting the time-offset when the ride was created (avoid daylight savings bugs)
				var endDate = Number(ride['endDate']);
				var repeatInterval = Number(ride['repeatInterval']);
				var startTime = ride['startTime'];
				var endTime = ride['endTime'];
				var level = ride['level'];
				var fbID = ride['facebookID'];
				var rideType = getRideType(ride['rideType']);
				var creatorType = ride['creatorType'];
				var title = ride['title'];
				var rideID = ride['rideID']

				var rootURL = page == 0 ? "" : "../";

				var utcStartDate = new Date((Number(ride['startDate']) + 3600*(new Date().getTimezoneOffset()/60))*1000)
				var formatedStartDate = formatDate(utcStartDate,0,0);
				var repeatEndDate = getIntervalAdjustedDate(endDate,repeatInterval);
				var startDateRange = args['startDate'];

				var color = ""
				// var hms = startTime;   // your input string
				// var a = hms.split(':'); // split it at the colons

				// // minutes are worth 60 seconds. Hours are worth 60 minutes.
				// var seconds = (+a[0]) * 60 * 60 + (+a[1]) * 60; 
				// var d = new Date().setHours(seconds*3600);
				// var timeDiff =  ((new Date(d).getTime()/1000) - ((new Date().getTime())/1000))/60
				// var tempDate = new Date();
				// var sameDay = (utcStartDate.getDate() == tempDate.getDate() && utcStartDate.getMonth() == tempDate.getMonth && utcStartDate.getYear() == tempDate.getYear())
				// console.log(timeDiff + "-" + startTime)
				// if(sameDay){
				// 	if(timeDiff <= 90){
				// 		color = "red"
				// 	}else{
				// 		color = "yellow"
				// 	}
				// }else{
				// 	color = "green"
				// }

				startDateRange = new Date(startDateRange);
				startDateRange.setHours(0)
				var dateURL = (startDateRange / 1000) - 3600*(new Date().getTimezoneOffset()/60);//converting to UTC

				var intervals = getIntervalsSinceDate(startDate,repeatInterval,dateURL); //using same date that is passed on to ride info page

				var groupLogo = (creatorType == 1) ? ((group['logoURL'] == "") ? 'group.png' : group['logoURL'] ) : 'group.png'
				var image = (creatorType == 0) ? rootURL + 'profilepics/' + creatorProfile['profPicURL'] : rootURL + 'grouplogos/' + groupLogo;

				var pic = "<img src='"+image+ "'/>";

				var dateInfo = '<p><b>Date:</b> ' + formatedStartDate + '</p>';

				var urgency = '<div style="width:100%; float:left;"><p style="display:inline;float:left;margin:0px 5px 0px 0px;"><b>Urgency:</b></p> <div style="width:20px;height:20px;background:'+urgencyColor(startTime,startDate)+'; display:inline;border-radius:50%;float:left;"/><p style="display:inline;float:left; font-size:10pt;margin:0px 0px 0px 5px;">'+urgencyText(startTime,startDate)+'</p></div>';

				var info = '<div class="infoContainer">' +
					    	'<p><b>Time:</b> ' + formatTime(startDate,startTime,endTime) + '</p>' +//dont want to use repeat date for time because it will change the date and effect daylight savings timezone offset
					    	'<p><b>Level:</b> ' + level + '</p>' +
					    	'<p><b>Ride Type:</b> ' + rideType + '</p></div>';

				var mapContainer = '<div class="mapContainer" id="feedMap'+i+'"></div>'

				var creatorLink = (creatorType == 0) ? rootURL+"profile/?id="+creatorProfile['userID'] : rootURL+"group/?id="+group['groupID']
				var name = (creatorType == 0) ? creatorProfile["firstName"] + " " + creatorProfile["lastName"] : group['name'];


        		list.append("<li class='card "+color+"'>" + pic + "<h5 class='creatorName'><a href="+creatorLink+">" + name + "</a></h5>" + dateInfo + urgency + info + mapContainer + "</li>");
        	
	        	var map = L.map('feedMap'+i,{zoomControl:false}).setView(new L.LatLng(lat, longs), 13); //initializing leaflet map

	        	L.tileLayer('https://api.tiles.mapbox.com/v4/cmtc.nf454on2/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoiY210YyIsImEiOiJjaWpudGhhNmcwMHB1dGhtNTl5Y2Y5cmh0In0.suSXm4r9RKPb_vUr2FZp9g', {
		          	maxZoom: 18,
		          	id: 'cmtc.nf454on2',
		          	accessToken: 'pk.eyJ1IjoiY210YyIsImEiOiJjaWpudGhhNmcwMHB1dGhtNTl5Y2Y5cmh0In0.suSXm4r9RKPb_vUr2FZp9g'
		      	}).addTo(map);

	        	var coords = [];

	        	(function(map, lat, longs){
			        
				    var getRouteCoordsRequest = getRouteCoords(ride['rideID']);

		        	getRouteCoordsRequest.done(function(result) {
						coords = JSON.parse(result);
						if(coords.length > 0){
							latlngs = [];
					      	for(var k = 0; k < coords.length; k++){
					        	var coord = new L.LatLng(coords[k][0],coords[k][1]);//converting coords to format usable by leaflet maps
					        	latlngs[k] = coord;
					      	}

					      	var greenIcon = L.icon({
							    iconUrl: rootURL+'images/routeStart.png',
							    iconSize:     [20, 20]
							});

							var redIcon = L.icon({
							    iconUrl: rootURL+'images/routeEnd.png',
							    iconSize:     [20, 20]
							});

					   		L.marker(latlngs[coords.length-1], {icon: redIcon}).addTo(map);
					 		L.marker([lat, longs], {icon: greenIcon}).addTo(map);

					      	var polyline = L.polyline(latlngs, {color: 'blue',weight:4,opacity:.6}).addTo(map); //adjusting style of polyline on map

					      	map.fitBounds(polyline.getBounds());
						}else{
							var newMarker = new L.marker(new L.LatLng(lat, longs)).addTo(map);
						}
					}).fail(function (jqXHR, textStatus, errorThrown){
						console.log("faaaail")
					});

				})(map, lat, longs);

				

	        	map.dragging.disable();
				map.touchZoom.disable();
		      	map.doubleClickZoom.disable();
		      	map.scrollWheelZoom.disable();
			}
		        	
        }else{

        }

		}).fail(function (jqXHR, textStatus, errorThrown){
			if(!aborted){
           		//alert("Unable to retrieve rides. Please check your internet connection.")
           		//not needed because showRides already does this
			}
		});
}

function getRides(args){
	window.onbeforeunload = confirmExit;
	function confirmExit(){
		aborted = true;
	}

	return $.ajax({
        url: args['ajaxURL'],
        data: args,
        success: function(response) {
            result = response;
        }
    });
}

function getRouteCoords(_rideID){
	window.onbeforeunload = confirmExit;
	function confirmExit(){
		aborted = true;
	}

	return $.ajax({
        url: 'http://www.fondobike.com/ajax/ajax_get_route_coords.php',
        data: {rideID: _rideID},
        success: function(response) {
            result = response;
        }
    });
}

	function formatDate(startDate, interval, endDate){
		var objStart = startDate;
        var weekday = new Array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        var months = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');


        try{
			var dayOfWeekStart = weekday[objStart.getDay()],
		        dayOfMonthStart =  objStart.getDate(),
		        curMonthStart = months[ objStart.getMonth()],
		        curYearStart =  objStart.getFullYear();
        }catch (err){
        }
       

        var initialDate = dayOfWeekStart + ", " + curMonthStart + " " + dayOfMonthStart + ", " + curYearStart;

		if(interval == 0){
			return initialDate;
		}else{
			  var objEnd = endDate;
                dayOfWeekEnd = weekday[objEnd.getDay()],
                dayOfMonthEnd =  objEnd.getDate(),
                curMonthEnd = months[ objEnd.getMonth()],
                curYearEnd =  objEnd.getFullYear();

                var finalRepeatDate = curMonthEnd + " " + dayOfMonthEnd + ", " + curYearEnd;

            	if(interval/604800 == 1){
					return "Every " + dayOfWeekStart + " until " + finalRepeatDate;//repeats every week
            	}else{
            		return initialDate + " - Every " + interval/604800 + " weeks until " + finalRepeatDate;//repeats every x weeks on y day (dont want to give past date)
            	}
		}
	}

	function adjustTimeFormat(time){
		var initialTime = new Date("1/1/1970 " + time)//.getTime()/1000;
		// 	console.log(time);
		// var d = new Date(startDate*1000);
		// var offset = (d.getTimezoneOffset()*-1*60);

		// startDate = new Date((startDate*1000) - offset*1000);//converting date format

		// d = new Date(startDate);//THIS IS VERY IMPORTANT, SET THE DATE TO WHEN THE RIDE WAS CREATED SO THE TIMEZONE OFFSET IS CONSTANT (same as the day the ride was created)

		// var unixTime = (d.getTimezoneOffset()*-1*60) + initialTime;
		// var finalTime = new Date(unixTime*1000);

		var hours = initialTime.getHours();//none of above is needed now
		var minutes = initialTime.getMinutes();

		curMeridiem = hours >= 12 ? "PM" : "AM";


		if (hours > 12) {
			hours -= 12;
		}else if (hours == 0){
			hours = 12;
		}

		if(minutes == 0){
			minutes = "00";
		}

		return hours + ":" + minutes + curMeridiem;
	}

	function urgencyColor(time, _startDate){

		var startDate = new Date((_startDate + 3600*(new Date().getTimezoneOffset()/60))*1000);
		var currentTime = new Date();


		if(startDate.getDate() === currentTime.getDate() && startDate.getFullYear() === currentTime.getFullYear() && startDate.getMonth() === currentTime.getMonth()){
			var startTime = new Date("1/1/1970 " + time)//.getTime()/1000;

			var hourDiff = startTime.getHours() - currentTime.getHours();

			if(hourDiff > 1){//more than 2 hour left
				return	"#2ecc71";//green
			}else{
				if(hourDiff <= 1){//less than an hour left
					return "#f1c40f";//yellow
				}else{ 
					return "#e74c3c";//red, missed ride
				}
			}
		}else{
			return "#9b59b6";
		}
	}

	function urgencyText(time, _startDate){

		var startDate = new Date((_startDate + 3600*(new Date().getTimezoneOffset()/60))*1000);
		var currentTime = new Date();


		if(startDate.getDate() === currentTime.getDate() && startDate.getFullYear() === currentTime.getFullYear() && startDate.getMonth() === currentTime.getMonth()){
			var startTime = new Date("1/1/1970 " + time)//.getTime()/1000;

			var hourDiff = startTime.getHours() - currentTime.getHours();

			if(hourDiff > 1){//more than 2 hour left
				return "> 1 hour to start";//green
			}else{
				if(hourDiff <= 1){//less than an hour left
					return "< 1 hour to start";//yellow
				}else{ 
					return "Ride has started!";//red, missed ride
				}
			}
		}else{
			return "Ride isn't today";
		}
	}

	function formatTime(startDate,startTime, endTime){
		if(endTime == null){
			return adjustTimeFormat(startTime) + " - " + "Not Sure";
		}else{
			return adjustTimeFormat(startTime) + ' - ' + adjustTimeFormat(endTime);
		}
	}

	function setAllMap(map) {
	  for (var i = 0; i < markers.length; i++) {
	    markers[i].setMap(map);
	  }
	}

	// Removes the markers from the map, but keeps them in the array.
	function clearMarkers() {
	  setAllMap(null);
	}

	// Shows any markers currently in the array.
	function showMarkers() {
	  setAllMap(map);
	}

	// Deletes all markers in the array by removing references to them.
	function deleteMarkers() {
	  clearMarkers();
	  markers = [];
	}

	function getRideType(type){
		switch (type){
			case "0":
				return "Easy Recovery";
			case "1":
				return "Interval Training";
			case "2":
				return "Long Endurance Ride";
			case "3":
				return "KOM Hunting";
			case "4":
				return "Group Ride";
			case "5":
				return "Unstructured/Other";
		}
	}

	function displayMarkers(ID,ride, startDateRange){
		var lat = ride['startLat'];
		var longs = ride['startLon'];
		var creatorProfile = ride['creatorProfile'];
		var group = ride['group']
		var description = ride['description'];
		var startDate = Number(ride['startDate']);//date used for getting the time-offset when the ride was created (avoid daylight savings bugs)
		var endDate = Number(ride['endDate']);
		var repeatInterval = Number(ride['repeatInterval']);
		var startTime = ride['startTime'];
		var endTime = ride['endTime'];
		var level = ride['level'];
		var fbID = ride['facebookID'];
		var rideType = getRideType(ride['rideType']);
		var creatorType = ride['creatorType'];
		var title = ride['title'];
		var rideID = ride['rideID']

		var repeatStartDate = getIntervalAdjustedDate(startDate,repeatInterval,startDateRange);//before startdate and enddate
		var repeatEndDate = getIntervalAdjustedDate(endDate,repeatInterval);


		startDateRange = new Date(startDateRange);
		startDateRange.setHours(0)
		var dateURL = (startDateRange / 1000) - 3600*(new Date().getTimezoneOffset()/60);//converting to UTC

		var intervals = getIntervalsSinceDate(startDate,repeatInterval,dateURL); //using same date that is passed on to ride info page

		//page == 2 is same style as homepage just different rideDetailsURL. page = 2 is group page
		var rootURL = page == 0 ? "" : "../";

		var pic = (creatorType == 0) ? '<img style="width: 50px; height: 50px;; margin-right: 15px" src="'+rootURL+'profilepics/' + creatorProfile['profPicURL'] + '"/>' : '';

		var header = (creatorType == 0) ? "<a href='"+rootURL+"profile/?id="+creatorProfile['userID']+"'>"+creatorProfile['firstName'] + ' ' + creatorProfile['lastName'] + "</a>" : "<a href='"+rootURL+"group/?id="+group['groupID']+"'>"+group['name'];
		var title = (title != null && title.trim() != "") ? '<p><b>Title:</b> ' + title + '</p>' : '';

		var rideDetailsURL = rootURL + "ride/?rideID=" + rideID + "&d=" + dateURL;
	    	
		
		var contentString = '<div id="content" style = " min-width: 500px; width: auto; min-height: 100px; height:auto;">'+
							'<table>' +
							'<tr><td style="padding-bottom:0; padding-left: 0;"><h4 style="margin: 0;">' + pic + header + '</h4></td></tr>'+ 
					    	'</table>'+
					    	'<div id="bodyContent">'+
					    	title +
					    	'<p style="max-height:180px; overflow: auto;">' + description + '</p>'+
					    	'<p><b>Date:</b> ' + formatDate(repeatStartDate,repeatInterval,repeatEndDate) + '</p>' +
					    	'<p><b>Time:</b> ' + formatTime(startDate,startTime,endTime) + '</p>' +//dont want to use repeat date for time because it will change the date and effect daylight savings timezone offset
					    	'<p><b>Level:</b> ' + level + '</p>' +
					    	'<p><b>Ride Type:</b> ' + rideType + '</p>' +
					    	'<a href="' + rideDetailsURL + '" target="_blank" class="waves-effect waves-light btn " id="bRideDetails"><i class="mdi-action-info-outline left"></i>Ride Details</a> ' +
					    	'<a class="waves-effect waves-light btn" id="bJoinRide" style="display: none;"><i class="mdi-social-person-add left"></i>Join Ride</a>' +
          					'<a class="waves-effect waves-light btn" id="bLeaveRide" style="display: none;"><i class="mdi-content-clear left"></i>Leave Ride</a>' +
					    	'<input type="hidden" id="intervals" value="'+intervals+'"/>' +
					    	'<input type="hidden" id="rideID" value="'+rideID+'"/>' +
					    	'</div>' +
					    	'</div>';
	

		var myLatlng = new google.maps.LatLng(lat,longs);


		//var image = (creatorProfile['userID'] == 1) ? 'images/cycling_green.png' : 'images/cycling_blue.png';
		var groupLogo = (creatorType == 1) ? ((group['logoURL'] == "") ? 'group.png' : group['logoURL'] ) : 'group.png'
		var image = (creatorType == 0) ? rootURL + 'profilepics/' + creatorProfile['profPicURL'] : rootURL + 'grouplogos/' + groupLogo;

		var _icon = {
		    url: image, // url
		    scaledSize: new google.maps.Size(30, 30), // scaled size
		};

		markers[ID] = new google.maps.Marker({
			icon: _icon,
	    	position: myLatlng,
	    	map: map,
	    	animation: google.maps.Animation.DROP,
	    	title: name
		});

		markers[ID].desc = contentString;

		oms.addMarker(markers[ID]);
	}

	function getIntervalAdjustedDate(date,interval,startDateRange){//startDateRange makes it so the date for the next ride is greater then the startDate in the timerange above the map
		var repeatStartDate = date;

		var now = new Date();
		var startOfDay = new Date(startDateRange);
		startOfDay.setHours(0)
		var timestamp = (startOfDay / 1000) - 3600*(new Date().getTimezoneOffset()/60);

		if(interval != 0){
			while(repeatStartDate < timestamp){
				repeatStartDate += interval;//TIMESTAMP NOT WORKKKKKING
			}
		}

		var d = new Date(date*1000);
		var offset = (d.getTimezoneOffset()*-1*60);

		return new Date((repeatStartDate*1000) - offset*1000);//is needed
	}

	function getIntervalsSinceDate(date,interval,startDateRange){//startDateRange makes it so the date for the next ride is greater then the startDate in the timerange above the map
		var repeatStartDate = date;
		
		var t1 = new Date(startDateRange*1000);
		var t2 = new Date(repeatStartDate*1000);

		
		
		if(t1.getDay() != 6){
			t1.setDate(t1.getDate() - (t1.getDay()+1));
			startDateRange = t1.getTime()/1000; 
		}
		

		if(t2.getDay() != 6){
			t2.setDate(t2.getDate() - (t2.getDay()+1));
			repeatStartDate = t2.getTime()/1000;
		}

		//dont need to adjust offset for this method because date is modified before it is sent to this method

		var i = 0;
		if(interval != 0){
			while(repeatStartDate < startDateRange){
				repeatStartDate += interval;
				i++;
			}
		}

		return i;
	}