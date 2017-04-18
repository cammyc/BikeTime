<!DOCTYPE html>
<html>
  <head>
  <?php
    include_once("../databasehelper/databasehelper.php");
    loginCheck();
  ?>

    <link href="../css/stylecreateroute.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="../datetimepicker/jquery.datetimepicker.css"/>
    <link rel="stylesheet" type="text/css" href="../timepicker/jquery.timepicker.css" />
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
    <link type="text/css" rel="stylesheet" href="../css/styled3graphhelper.css"  media="screen,projection"/>

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

    <script src="../datetimepicker/jquery.js"></script>
    <script src="../datetimepicker/jquery.datetimepicker.js"></script>
    <script type="text/javascript" src="../timepicker/jquery.timepicker.js"></script>
    <script type="text/javascript" src="../js/d3.min.js"></script>
    <script type="text/javascript" src="../js/d3graphhelper.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <script type="text/javascript" src="../js/routeparser.js"></script>
  

    <title>Route Builder</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <?php
      include('../menu.php');
    ?>
    <script src="https://www.google.com/jsapi"></script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAmLcM0z1weNroKQhtoe4VFurx3zljP_3s&sensor=true&libraries=geometry"></script>

    <script>

    var rendererOptions = {
      draggable: true,
      preserveViewport:true,
      suppressBicyclingLayer:true
    };

      var directionsService = new google.maps.DirectionsService();
      var directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);
      var startMarker, endMarker;
      var clickCount = 0;
      var _climb = 0;
      var distance = 0;
      var time = 0;
      var endPoint;
      var waypts = [];
      var elevator;
      var elevations;
      var geocoder;
      var route;
      var updated = false;
      var coords
      var uploadedRoute = false;
      var elevationData = []; //D3GraphHelper initializes this variable

      var biketime = {//THIS IS HOW GLOBABL VARIABLES WILL BE HANDLED FROM NOW ONE
        googleMaps: {
          map: null,
          startPoint: null,
        }
      };

      function initialize() {
        var mapOptions = {
          zoom: 12,
          mapTypeControl: true,
          mapTypeControlOptions: {
              style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
              position: google.maps.ControlPosition.TOP_LEFT
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
          },
          disableDoubleClickZoom: true,
          scrollwheel: false,
          draggableCursor: "crosshair"
        };

        biketime.googleMaps.map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);
        geocoder = new google.maps.Geocoder();

        directionsDisplay.setMap(biketime.googleMaps.map);
        directionsDisplay.setPanel(document.getElementById('directionsPanel'));
          google.maps.event.addListener(directionsDisplay, 'directions_changed', function() {
              getRouteInfoFromDirectionDisplay(directionsDisplay.getDirections()); //show route info

              biketime.googleMaps.startPoint = directionsDisplay.getDirections().routes[0].legs[0].start_location;//update biketime.googleMaps.startPoint variable if the start marker was moved

              coords = directionsDisplay.getDirections().routes[0].legs[0].steps;

              drawPath(directionsDisplay.getDirections());//draw elevation path

              var legs = directionsDisplay.getDirections().routes[0].legs;
              endPoint = directionsDisplay.getDirections().routes[0].legs[legs.length - 1].end_location;//get endpoint of route

              var waypoints = legs[0].via_waypoints; //get each via_waypoint

              waypts = [];//reset waypoint array so it doesnt duplicate

              for(var i = 0; i < legs[0].via_waypoints.length; i++){
               waypts.push({
                      location: new google.maps.LatLng(waypoints[i].lat(), waypoints[i].lng()),
                      stopover: false
                     });
              } 
              geocodeLatLng(biketime.googleMaps.startPoint);

              updated = true;
        });
            
      // Create an ElevationService.
      elevator = new google.maps.ElevationService();

        google.maps.event.addListener(biketime.googleMaps.map, "click", function(evt) {

          if(!uploadedRoute){
             if(clickCount == 0){ //dont want to add waypoint on first click
              calcRoute(biketime.googleMaps.startPoint,evt.latLng, biketime.googleMaps.map);
              endPoint = evt.latLng;
             }else{
              
                if(waypts.length < 8){
                  waypts.push({
                    location: endPoint,
                    stopover: false
                   });
                }
               
                calcRoute(biketime.googleMaps.startPoint,evt.latLng,biketime.googleMaps.map);  
             }
           endPoint = evt.latLng;
           clickCount++;
         }
        });

        if(navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var pos = new google.maps.LatLng(position.coords.latitude,
                                             position.coords.longitude);

            biketime.googleMaps.startPoint = pos;

            var infowindow = new google.maps.InfoWindow({
              map: biketime.googleMaps.map,
              position: pos,
              content: '<div style = "display: inline-block">Your Location</div>'
            });

            biketime.googleMaps.map.setCenter(pos);
          }, function() {
            handleNoGeolocation(true);
          });
        } else {
          // Browser doesn't support Geolocation
          handleNoGeolocation(false);
        }

      }

      function calcRoute(start, end, map) { //calculate directions

        var request = {
          origin: start,
          destination: end,
          travelMode: google.maps.DirectionsTravelMode.BICYCLING,
          optimizeWaypoints: false,
          waypoints: waypts
        };

        directionsService.route(request, function(response, status) {
          if (status == google.maps.DirectionsStatus.OK) {
            directionsDisplay.setDirections(response);
            biketime.googleMaps.startPoint = response.routes[0].legs[0].start_location;
          }
        });
      }

      function geocodeLatLng(latlng) {
        geocoder.geocode({'latLng': latlng}, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {//if successfule
             // document.getElementById('biketime.googleMaps.startPoint').innerHTML = results[0].formatted_address;
            } else {
              //alert('No results found');
            }
          } else {
            //alert('Geocoder failed due to: ' + status);
          }
        });
      }

      function getRouteInfoFromDirectionDisplay(result) {
        var total = 0;
        var myroute = result.routes[0];
        for (var i = 0; i < myroute.legs.length; i++) {
          total += myroute.legs[i].distance.value;
        }
        total = total / 1000.0;
        document.getElementById('distance').innerHTML = (Math.round(total * 100) / 100) + ' km';
        document.getElementById('duration').innerHTML = result.routes[0].legs[0].duration.text + '';
        distance = (Math.round(total * 100) / 100);
        time = (result.routes[0].legs[0].duration.value)/60

        route = {distance: distance};
      }

      function showRouteInfoFromFile(_distance,_time){
        document.getElementById('distance').innerHTML = (Math.round(_distance * 100) / 100) + ' km';
        document.getElementById('duration').innerHTML = rideDuration(_time);
        distance = _distance;
        time = _time;
        //climb is handled by d3GraphHelper
      }

    function handleNoGeolocation(errorFlag) {

      $.getJSON("http://ipinfo.io", function(ipinfo){
          var latLong = ipinfo.loc.split(",");

          pos = new google.maps.LatLng(latLong[0],
            latLong[1]);

          var infowindow = new google.maps.InfoWindow({
                content: '<div style="width: 150px; height: 30px;"><center><p>Your General Location</p></center></div>'
            });
          
          var marker = new google.maps.Marker({
            position: pos,
            map: biketime.googleMaps.map,
            animation: google.maps.Animation.DROP
          });

          google.maps.event.addListener(marker, 'mouseover', function() {
            infowindow.open(biketime.googleMaps.map, this);
          });

          google.maps.event.addListener(marker, 'mouseout', function() {
            infowindow.close();
          });

          biketime.googleMaps.map.setCenter(pos);

          biketime.googleMaps.startPoint = pos;
      });

    }    

     function drawPath(result) {
        // Create a PathElevationRequest object using this array.
        // Ask for 256 samples along that path.
        var pathRequest = {
          'path': result.routes[0].overview_path,
          'samples': 512
      } 
      // Initiate the path request.
      if(updated){
        elevator.getElevationAlongPath(pathRequest, updateGraph);
      }else{
        $('#elevationGraph').height(120);//this is setting the height of the div here so that when the page loads there isnt empty white space
        elevator.getElevationAlongPath(pathRequest, initializeGraph);
      }
    }

    function rideDuration(minutes){
        var text = "hours"
        if(parseInt(minutes/60) == 1){
          text = "hour";
        }
        return parseInt(minutes/60)+" "+text+" "+minutes%60+" min";
    }

      // Takes an array of ElevationResult objects, draws the path on the map
      // and plots the elevation profile on a Visualization API ColumnChart.
      

      google.maps.event.addDomListener(window, 'load', initialize);
    </script>
  </head>
  <body>
      <div id="map-canvas">
      </div>
      <div class = "infoContainer">
        <div id="uploadRoute">
          <div  class="file-field input-field card">
            <div class="btn" id="fileButton">
              <span>File</span>
              <input type="file" id="fileChooser" accept=".gpx">
            </div>
            <div id="filePath" class="file-path-wrapper">
              <input class="file-path validate" type="text" placeholder="Upload a GPX file">
            </div>
          </div>
        </div>
      <div id="routeInfo">
        <table style="width: 100%">
          <tr>
          <td>
          <p2 id="distance">0km</p2><br>
          <p1>Distance</p1>
          </td>
          <td>
          <p2 id="elevation">0m</p2><br>
          <p1>Elev. Gain</p1>
          </td>
          <td>
          <p2 id="duration">0 minutes</p2><br>
          <p1>Est. Moving Time</p1>
          </td>
          <td>
          <a class="waves-effect waves-light btn" id="saveRoute"><i class="material-icons right"></i>Save</a>
          </td>
          </tr>
        </table>
        </div>
        <div id="wrapper">
       <center><div id="elevationGraph"></div></center> 
       </div>
      </div>

    <div class = 'saveDialog'>
      <div class = 'content'>
          <h4>Save</h4><hr>
          <p id ="prompt"> Enter a name a name and description for your route. </p>
          <form id = "form" action="processroute.php" method="POST">
          <input type="hidden" id="coords" name="coordinates" value=""/>
          <input type="hidden" id="waypoints" name="waypoints" value=""/>
          <input type="hidden" id="distance" name="distance" value=""/>
          <input type="hidden" id="climb" name="climb" value=""/>
          <input type="hidden" id="time" name="time" value=""/>
          <input type="hidden" id="elevationData" name="elevationData" value=""/>

          <div class="routeInput">
            <label>Route Name (required)</label><br>
            <input type="textbox" id = "name" name = "name"/><br><br>

            <label>Description</label><br>
            <textarea id = "description" name = "description"></textarea>

            <div class = "private">
            <input type="checkbox" id ="cbPrivate" name ="private" value="false" /> <label for="cbPrivate">Private (Only Friends Can See)</label>
            </div>
          </div>
          <div class = "actions">
            <div class="cancel">Cancel</div>
            <div id="bSave" class="save">Save</div>
          </div>
          </form>
      </div>

    </div>

      <script type="text/javascript">
        $(".saveDialog").hide();

        $("#routeInfo").click(function (event) {
          if (event.target.id == 'saveRoute') return;

          if($('#elevationGraph').height() == 120){//only toggle if graph has been initialized
            $("#wrapper").slideToggle();
          }

        });

        $("#saveRoute").click(function(){
          if(endPoint != null && !uploadedRoute){
           $(".saveDialog").fadeToggle();
            drawPath(directionsDisplay.getDirections());
          }else if(uploadedRoute){
            $(".saveDialog").fadeToggle();
          }
        });

        $(".cancel").click(function(){
           $(".saveDialog").fadeToggle();
        });

        $("#bSave").click(function(){
           $("#form").submit();
        });

        $("#form").submit(function(){
            if(!$('#name').val().trim()){
              $('#name').css("border", "1px solid red");
              console.log("false");
              return false;
            }else{
              if(!uploadedRoute){
                coords = directionsDisplay.getDirections().routes[0].overview_path;

                var newCoords = [];

                //keys = Object.keys(coords[0]);
                for(var i = 0; i<coords.length;i++){

                  var coord = {
                    lat: coords[i].lat(),//this is so that if google changes the key it wont matter, if they .lat() change function though this will not work
                    lon: coords[i].lng()
                  };

                  newCoords[i] = coord;
                  //delete coords[i][keys[0]]
                  //delete coords[i][keys[1]]
                }

                //waypointKeys = Object.keys(waypts[0]['location']);
                var newWaypts = [];

                for(var i = 0; i<waypts.length;i++){
                  var coord = {
                    lat:  waypts[i]['location'].lat(),//this is so that if google changes the key it wont matter, if they .lat() change function though this will not work
                    lon:  waypts[i]['location'].lng()
                  };

                  newWaypts[i] = coord;
                 // delete waypts[i]['location'][waypointKeys[0]];
                  //delete waypts[i]['location'][waypointKeys[1]];
                }

                //console.log(JSON.stringify(newWaypts))

                $('input[name="coordinates"]').val(JSON.stringify(newCoords));

                var waypoints = JSON.stringify(newWaypts); //check for waypoints
                $('input[name="waypoints"]').val(waypoints);

                $('input[name="distance"]').val(distance);
                $('input[name="climb"]').val(_climb);
                $('input[name="time"]').val(time);
                $('input[name="elevationData"]').val(JSON.stringify(elevationData));
              }else{
                $('input[name="coordinates"]').val(JSON.stringify(route.coords));

                var waypoints = JSON.stringify(waypts); //will be empty
                $('input[name="waypoints"]').val(waypoints);

                $('input[name="distance"]').val(route.distance);
                $('input[name="climb"]').val(_climb);
                $('input[name="time"]').val(route.time);
                $('input[name="elevationData"]').val(JSON.stringify(route.elevationData));

              }
              console.log("tru");
              return true;
           }
        });

        $(document).ready(function(){
          var fileChooser = document.getElementById('fileChooser');
          
          fileChooser.addEventListener('change', handleFileSelection, false);

          $(window).resize(function() {
            updateGraphWidth();
          });
          $(".button-collapse").sideNav();
          $(".dropdown-button").dropdown({hover:true});
        });

      </script>
  </body>
</html>