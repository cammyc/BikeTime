<!DOCTYPE html>
<?php
	include_once('databasehelper/databasehelper.php');
	session_start();
 //    $timezone = isset($_SESSION['time']) ? $_SESSION['time'] : ;
	// $timezoneName = timezone_name_from_abbr("", $timezone*3600, false); //make sure $timezone session variable doesnt get GMT text in it
	// date_default_timezone_set($timezoneName);

	// $today = date("Y/m/d");
	// $tomorrow = date('Y/m/d',strtotime("+1 day",strtotime($today)));
	$userID = indexLoginCheck();
?>
<html>
<title>Bike Time</title>
<head>
<!-- 	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
 -->
	<link href="css/styleindex.css" rel="stylesheet" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="datetimepicker/jquery.datetimepicker.css"/>
	<link rel="stylesheet" type="text/css" href="timepicker/jquery.timepicker.css" />
 	<link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>
 	<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css" />
	
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAmLcM0z1weNroKQhtoe4VFurx3zljP_3s">
	</script>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  	<script src="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js"></script>
    <script src="datetimepicker/jquery.js"></script>

    <script src="datetimepicker/jquery.datetimepicker.js"></script>
    <script type="text/javascript" src="timepicker/jquery.timepicker.js"></script>
    <script type="text/javascript" src="js/checktime.js"></script>
    <script type="text/javascript" src="js/jstz-1.0.4.min.js"></script>
    <script type="text/javascript" src="js/displayrides.js"></script>
    <script type="text/javascript" src="js/toolbox.js"></script>
	<script type="text/javascript" src="js/materialize.min.js"></script>
	<script type="text/javascript" src="js/ridehelper.js"></script>


	<script src="bin/oms.min.js"></script>


	<script type="text/javascript">
		var map;
		var oms;
		var markers = [];

		var today = new Date();
		var tomorrow = new Date(today.getTime() +  24* 60 * 60 * 1000);
		var userID = <?php echo $userID ?>;

		 $(document).ready(function() {
	        $("#datetimepicker1").val(today.getFullYear() + "/" + ('0' + (today.getMonth()+1)).slice(-2) + "/" + ('0' + today.getDate()).slice(-2));
			$("#datetimepicker2").val(tomorrow.getFullYear() + "/" + ('0' + (tomorrow.getMonth()+1)).slice(-2) + "/" +('0' + tomorrow.getDate()).slice(-2));//month is +1 because it starts at 0
    	});

		function initialize() {
			var mapOptions = {
				zoom: 10,
				 mapTypeControl: true,
			    mapTypeControlOptions: {
			        style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
			        position: google.maps.ControlPosition.TOP_RIGHT
			    },
			    panControl: true,
			    panControlOptions: {
			        position: google.maps.ControlPosition.TOP_RIGHT
			    },
			    zoomControl: true,
			    zoomControlOptions: {
			        position: google.maps.ControlPosition.TOP_RIGHT
			    },
			    scaleControl: true,
			    scaleControlOptions: {
			        position: google.maps.ControlPosition.TOP_LEFT
			    },
			    streetViewControl: true,
			    streetViewControlOptions: {
			        position: google.maps.ControlPosition.TOP_RIGHT
			    }
  
			};
			map = new google.maps.Map(document.getElementById('map-canvas'),
				mapOptions);

			var gm = google.maps;

			var iw = new google.maps.InfoWindow();


			google.maps.event.addListener(iw, 'domready', function() {

				if(userID == -1){
					$('#bRideDetails').hide()//eventually show this, I just have to make adjustmens to the ride page for if no one is logged in.
				}else{
					var intervals = $('#intervals').val();
					var rideID = $('#rideID').val()
						hasUserJoinedRide(userID,rideID,intervals,'');//if this wasnt called from the homepage the last arg would be ../
					$('#bJoinRide, #bLeaveRide').click(function(e){
						joinRide(userID,rideID,intervals,false,'');
					});
					console.log(intervals);
				}
			});

     		oms = new OverlappingMarkerSpiderfier(map,{markersWontMove: true, markersWontHide: true});


     		 oms.addListener('click', function(marker) {
		        iw.setContent(marker.desc);
		        iw.open(map, marker);
     			});

     		  oms.addListener('spiderfy', function(markers) {
		        // for(var i = 0; i < markers.length; i ++) {
		        // 	// if(markers[i].ID = userID){
		        // 	// 	markers[i].setIcon("images/cycling_inverse_green.png");
		        // 	// }else{
		        // 	// 	markers[i].setIcon("images/cycling_inverse_blue.png");
		        // 	// }
		        // 	markers[i].setIcon(markers[i].image);
		        // } 
		        iw.close();
		      });
		      oms.addListener('unspiderfy', function(markers) {
		        // for(var i = 0; i < markers.length; i ++) {
		        // 	markers[i].setIcon(markers[i].image);

		        //  //   if(markers[i].ID = userID){
		        // 	// 	markers[i].setIcon("images/cycling_green.png");
		        // 	// }else{
		        // 	// 	markers[i].setIcon("images/cycling_blue.png");
		        // 	// }
		        // }
		      });


			
		  // Try HTML5 geolocation
		  if(navigator.geolocation) {
		  	navigator.geolocation.getCurrentPosition(function(position) {
		  		var pos = new google.maps.LatLng(position.coords.latitude,
		  			position.coords.longitude);

		  	 var infowindow = new google.maps.InfoWindow({
	      			content: '<div style="width: 150px; height: 30px;"><center><p>Your Location</p></center></div>'
	  			});
	  		
			var marker = new google.maps.Marker({
	    	position: pos,
	    	map: map,
	    	animation: google.maps.Animation.DROP
			});

			google.maps.event.addListener(marker, 'mouseover', function() {
				infowindow.open(map, this);
			});

			google.maps.event.addListener(marker, 'mouseout', function() {
				infowindow.close();
			});


		  	map.setCenter(pos);
		  	}, function() {
		  		handleNoGeolocation(true);
		  	});
		  } else {
	    // Browser doesn't support Geolocation
		    handleNoGeolocation(false);
		}
			showRides(getArgs());
			showFeed(getArgs(), "#rideStream");
		}
	
	function toggleBounce() {

  if (marker.getAnimation() != null) {
    	marker.setAnimation(null);
 	 } else {
   		 marker.setAnimation(google.maps.Animation.BOUNCE);
  		}
	}

	function handleNoGeolocation(errorFlag) {
		$.getJSON("http://ipinfo.io", function(ipinfo){
		    var latLong = ipinfo.loc.split(",");

		    var pos = new google.maps.LatLng(latLong[0],
	  			latLong[1]);

		  	var infowindow = new google.maps.InfoWindow({
	      			content: '<div style="width: 150px; height: 30px;"><center><p>Your General Location</p></center></div>'
	  			});
	  		
			var marker = new google.maps.Marker({
	    	position: pos,
	    	map: map,
	    	animation: google.maps.Animation.DROP
			});

			google.maps.event.addListener(marker, 'mouseover', function() {
				infowindow.open(map, this);
			});

			google.maps.event.addListener(marker, 'mouseout', function() {
				infowindow.close();
			});


		  	map.setCenter(pos);
		});
	}

	function getArgs(){
		var args = {
			ajaxURL: "http://www.fondobike.com/ajax/ajax-all-rides.php",
			startDate: document.getElementById('datetimepicker1').value,
			endDate: document.getElementById('datetimepicker2').value,
			rideType: document.getElementById('rideType').value,
			startTime: getTimepickerTime("#startTime"),
			endTime: getTimepickerTime("#endTime"),
			level: document.getElementById('level').value,
			publicRides: getCheckboxVal('publicRides'),
			groupRides: getCheckboxVal('groupRides'),
			friendRides: getCheckboxVal('friendRides'),
			userID: <?php echo $userID; ?>,
			timezone: jstz.determine().name(),
			page: 0
		};

		return args;
	}

	function getCheckboxVal(id){
		if (document.getElementById(id).checked){
			return document.getElementById(id).value;
		}else{
			return null;
		}
	}

	function loadScript() {
		initialize();
		/*
		$.get("http://ipinfo.io", function(response) {
			    alert(response.city);
			}, "jsonp");
		*/
	}

	window.onload = loadScript;
</script>

<?php
include 'menu.php';
?>

</head>
<body>
	<div id="map-canvas"> </div>

	<ul class="collapsible toggle" data-collapsible="expandable">
    <li>
      <div class="collapsible-header"><i class="mdi-action-settings"></i>View Settings<i class="mdi-hardware-keyboard-arrow-down right blue-text"></i></div>
      <div class="collapsible-body">
			<div class="row">
				<div class="col s12">
					<label>Date Range</label>
					<div class="row">
						<input  type="text" id="datetimepicker1" class = "datepicker col s5"/>
						<p class="center-align col s2" id="dash"> - </p>
						<input type="text" id="datetimepicker2" class="datepicker col s5"/>
					</div>
				</div>
			</div>
			<div class="row">

				<div class="input-field col s6">
				
					<select id = "rideType" name="rideType" >
			              <option value="-1" selected="selected">All Types</option>
			              <option value="0">Easy Recovery</option>
			              <option value="1">Interval Training</option>
			              <option value="2">Long Endurance Ride</option>
			              <option value="3">KOM Hunting</option>
			              <option value="4">Group Ride</option><!-- //change to name like looking for friends, to confusing -->
			              <option value="5">Unstructered/Other</option>
			        </select>
			        <label>Ride Type</label>
					
				</div>

				<div class="input-field col s6">
					<select id = "level" name="level">
			              <option value="-1"selected="selected">All Levels</option>
			              <option value="Z">Z (Mixed Levels)</option>
			              <option value="A">A (33+ km/h)</option>
			              <option value="B">B (30-32 km/h)</option>
			              <option value="C">C (28-30 km/h)</option>
			              <option value="D">D (26-28 km/h)</option>
			              <option value="E">E (less then 25km/h)</option>
			        </select>
					<label>Ride Level</label>

				</div>
				
			</div>

			<div class="row" style="margin-bottom: 0;"><!-- margin removes jump at end of animation -->

				<div class="input-field col s5">
					<input name="startTime" id="startTime" type="text">
         			<label for="startTime" class="active">Starting After</label>
					
				</div>

				<div class="input-field col s2">
					<center><p id="dash"> - </p></center>
				</div>

				<div class="input-field col s5">
					<input name="endTime" id="endTime" type="text" value="Not Sure">
         			<label for="endTime" class="active">And Before</label>
					
				</div>
				
			</div>
      </div>
    </li>
    <li>
      <div class="collapsible-header active"><i class="mdi-maps-place"></i>Upcoming Rides<i class="mdi-hardware-keyboard-arrow-down right blue-text"></i></div>
      <div class="collapsible-body"><div class="feed-content"><ul id="rideStream"></ul></div></div>
    </li>
  </ul>


	<div id="creatorType" class="card">
		<div class="row center-align">
			<div class="switch col s4">
			    <label>
			      <input name="groupRides" id="groupRides" type="checkbox" value="true" checked>
			      <span class="lever"></span>
			      <br>
			      Group Rides
			    </label>
	  		</div>

	  		<div class="switch col s4">
			    <label>
			      <input name="friendRides" id="friendRides" type="checkbox" value="true" checked>
			      <span class="lever"></span>
			      <br>
			      Friends Rides
			    </label>
	  		</div>

	  		<div class="switch col s4">
			    <label>
			      <input name="publicRides" id="publicRides" type="checkbox" value="true">
			      <span class="lever"></span>
			      <br>
			      Public Rides
			    </label>
	  		</div>
		</div>
	</div>

	<div class="socialFeed card">
	</div>


	<div class="fixed-action-btn" style="bottom: 24px; right: 24px;">
      <span title="Show/Hide Map">
      	<a class="btn-floating btn-large purple" id="bShowMap">
        	<i class="large mdi-communication-location-on"></i>
      	</a>
      </span>
    </div>

	
</body>
	<script type="text/javascript">

		$(document).ready(function() {
	    	$('select').material_select();
	    	$('ul.tabs').tabs();
	    	$('#startTime').val(new Date().timeNow())

	    	jQuery('#datetimepicker1').datetimepicker({
	          lang:'en',
	           timepicker:false,
	           format:'Y/m/d',
	           scrollInput: false,
	           closeOnDateSelect: true,
	           onSelectDate:function(current_time,$input){
	           		date1 = current_time.dateFormat('Y/m/d');
	           		date2 = new Date(current_time.getTime() + (86400 * 1000));
	           		date2 = date2.dateFormat('Y/m/d');

		           	if(current_time  > new Date(jQuery('#datetimepicker2').val())){
							jQuery('#datetimepicker2').val(date2)
		           		}

		           	if(checkTimeFields() == true){
	        			showRides(getArgs());
	        			showFeed(getArgs(), "#rideStream");
	        		}
				}
	        });

	        jQuery('#datetimepicker2').datetimepicker({
	          lang:'en',
	           timepicker:false,
	           format:'Y/m/d',
	           minDate:'0',
	           scrollInput: false,
	           closeOnDateSelect: true,
	           onShow:function( ct ){
				   this.setOptions({
				    minDate:jQuery('#datetimepicker1').val()?jQuery('#datetimepicker1').val():false
				   })
			  	},
			  	 onSelectDate:function(current_time,$input){
		           	if(checkTimeFields() == true){
	        			showRides(getArgs());
	        			showFeed(getArgs(), "#rideStream");
	        		}
				}
	        });

	        $('#bShowMap').click(function(){
				$('.socialFeed').slideToggle( "slow", function() {
				  //   $('#map-canvas').css('opacity','1');
				  //   $('#map-canvas').hide();
				  //   $('#styleContainer').css('pointer-events','auto');
				  //   $('#timerange').slideDown( "slow");
				 	// $('#map-canvas').slideDown( "slow",function(){
				 	// 	google.maps.event.trigger(map, 'resize');
				 	// });
				 	// $("#creatorType").slideDown( "slow");
				 	// $("#toggleOptions").slideDown( "slow");
				 });
			});

	        $('#startTime').timepicker({ 'scrollDefault': 'now' });

	        var time = $('#startTime').timepicker('getSecondsFromMidnight') == 0 ? '12:30am' : $('#startTime').timepicker('getTime');//if starting after midnight make 'and Before' 12:30 to fix bug

	        $('#endTime').timepicker({ 
	        	'scrollDefault': 'now',
	        	'noneOption':[
			        {
			            'label': 'Not Sure',
			            'className' : 'notSure',
			            'value': 'Not Sure'
			        }
		    	], 
		    	'minTime': time, 
		    	'maxTime': '11:30pm' });//no need for max time to be 12:00am, can just set to not sure

	        $("#startTime, #endTime").change(function(){

	        	$('#endTime').timepicker('remove');

	        	var time = $('#startTime').timepicker('getSecondsFromMidnight') == 0 ? '12:30am' : $('#startTime').timepicker('getTime');//if starting after midnight make 'and Before' 12:30 to fix bug

		        $('#endTime').timepicker({ 
		        	'scrollDefault': 'now',
		        	'noneOption':[
				        {
				            'label': 'Not Sure',
				            'className' : 'notSure',
				            'value': 'Not Sure'
				        }
			    	], 
			    	'minTime': time, 
			    	'maxTime': '11:30pm' });//no need for max time to be 12:00am, can just set to not sure

	        	if(checkTimeFields() == true){
	        		showRides(getArgs());
	        		showFeed(getArgs(), "#rideStream");
	        	}
	        	
	        });

	        $("select, :checkbox").change(function(){
	        	if(checkTimeFields() == true){
	        		showRides(getArgs());
	        		showFeed(getArgs(), "#rideStream");
	        	}
	        });


	        

			$(".button-collapse").sideNav();
			$(".dropdown-button").dropdown({hover:true});

		 });

			function getTimepickerTime(id){
				re = /^\d{1,2}:\d{2}([ap]m)?$/;

				if(!$(id).val().match(re) && id == "#endTime"){
					return "Not Sure";
				}

				var x = $(id).timepicker('getTime', new Date());

				try{
					return ('0' + x.getHours()).slice(-2)+":" + ('0' + x.getMinutes()).slice(-2) + ":00";
				}catch(err){
					Materialize.toast('Invalid Time Field', 4000);//this only detects if the actual time if invalid, not the format Ex: 1:70pm
				}
			}

			function checkTimeFields(){
	        	re = /^\d{1,2}:\d{2}([ap]m)?$/;
	        	var valid = true;

	        	if(!$("#startTime").val().match(re)){
	        		 Materialize.toast('Invalid Time Field', 4000);
	        		 valid = false;
				}

				if(!$("#endTime").val().match(re) && $("#endTime").val().toUpperCase() != ("Not Sure").toUpperCase()){
	        		 Materialize.toast('Invalid Time Field', 4000);
	        		 valid = false;
				}
				return valid;
	        }

			Date.prototype.timeNow = function () {
			     return ((this.getHours() < 10)?"0":"") + (this.getHours() > 12 ? this.getHours()-12 : this.getHours()) +":"+ ('0' + (Math.floor(this.getMinutes()/30) * 30) % 60).slice(-2) + (this.getHours() >= 12 ? "pm" : "am");
			}
		
	</script>
	<!-- <div id="center"><img style="height: 128px; width: 128px;"src="images/loading.gif"></div> -->
</html>