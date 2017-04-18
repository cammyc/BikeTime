<!DOCTYPE html>
<html>
  <head>
  <?php
    include_once("../databasehelper/databasehelper.php");
    loginCheck();

    $mysqli = getDB();

     $profile = getUserProfileFromDB($mysqli,$_SESSION['UserID']);
  ?>
    <link href="../css/styleplanride.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="../datetimepicker/jquery.datetimepicker.css"/>
    <link rel="stylesheet" type="text/css" href="../timepicker/jquery.timepicker.css" />  
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
    <link type="text/css" rel="stylesheet" href="../css/styled3graphhelper.css"  media="screen,projection"/>
    <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.5/leaflet.css" />




    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

    <script src="../datetimepicker/jquery.js"></script>
    <script src="../datetimepicker/jquery.datetimepicker.js"></script>
    <script type="text/javascript" src="../timepicker/jquery.timepicker.js"></script>
    <script type="text/javascript" src="../js/d3.min.js"></script>
    <script type="text/javascript" src="../js/d3graphhelper.js"></script>

    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <script type="text/javascript" src="../js/jstz-1.0.4.min.js"></script>
  

    <title>Plan a Ride</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <?php
      include('../menu.php');
    ?>

    <script type="text/javascript" src="../js/planridehelper.js"></script>
    <script src="https://www.google.com/jsapi"></script>
    <script src="http://cdn.leafletjs.com/leaflet-0.7.5/leaflet.js"></script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAmLcM0z1weNroKQhtoe4VFurx3zljP_3s"></script>

    <script>

    var rendererOptions = {
      draggable: true,
      preserveViewport:true,
      suppressBicyclingLayer:true
    };

      var mousemarker;
      var clickCount = 0;
      var startMarker;
      var climb = 0;
      var elevator;
      var elevations;
      var geocoder;
      var formComplete = true;
      var route;
      var biketime = {//THIS IS HOW GLOBABL VARIABLES WILL BE HANDLED FROM NOW ONE
        googleMaps: {
          map: null,
        },

        routeViewer: {
          routeIncrementerT: 0,
          routeIncrementerH: 5,
        },

        rideData: {
          routeID: -1,
          startLocation: -1
        },

        formCheck: {
          routeSelected: false
        }

      };

      // Load the Visualization API and the columnchart package.

      function initialize() {
        var mapOptions = {
          zoom: 13,
          mapTypeControl: true,
          mapTypeControlOptions: {
              style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
              position: google.maps.ControlPosition.TOP_LEFT
          },
          panControl: true,
          panControlOptions: {
              position: google.maps.ControlPosition.LEFT_CENTER
          },
          zoomControl: true,
          zoomControlOptions: {
              position: google.maps.ControlPosition.LEFT_CENTER
          },
          scaleControl: true,
          scaleControlOptions: {
              position: google.maps.ControlPosition.TOP_LEFT
          },
          streetViewControl: true,
          streetViewControlOptions: {
              position: google.maps.ControlPosition.LEFT_CENTER
          },
          disableDoubleClickZoom: true,
          draggableCursor: "crosshair"
        };

        biketime.googleMaps.map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);
        geocoder = new google.maps.Geocoder();
            
      // Create an ElevationService.
      elevator = new google.maps.ElevationService();

      // Create a new chart in the elevation_chart DIV.

        google.maps.event.addListener( biketime.googleMaps.map, "click", function(evt) {
          if(biketime.formCheck.routeSelected != null && !biketime.formCheck.routeSelected){
            if(startMarker){
              startMarker.setPosition(evt.latLng);
            }else{
             startMarker = new google.maps.Marker({
              position: evt.latLng,
              map:  biketime.googleMaps.map,
              draggable: true,
              });

               google.maps.event.addListener(startMarker, "dragend", function(event) {
                  geocodeLatLng(event.latLng);
                  biketime.rideData.startLocation = event.latLng
              });
            }
              geocodeLatLng(evt.latLng);
              biketime.rideData.startLocation = evt.latLng;

          }
        });

        if(navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var pos = new google.maps.LatLng(position.coords.latitude,
                                             position.coords.longitude);

            startPoint = pos;

            var infowindow = new google.maps.InfoWindow({
              map:  biketime.googleMaps.map,
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


      function geocodeLatLng(latlng) {
        geocoder.geocode({'latLng': latlng}, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {//if successfule
              document.getElementById('biketime.rideData.startLocation').innerHTML = results[0].formatted_address;
            } else {
              //alert('No results found');
            }
          } else {
            //alert('Geocoder failed due to: ' + status);
          }
        });
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
      });

    }    

     function drawPath(result) {
        // Create a PathElevationRequest object using this array.
        // Ask for 256 samples along that path.
        var pathRequest = {
          'path': result,
          'samples': 512
      } 
      // Initiate the path request.
      elevator.getElevationAlongPath(pathRequest, initializeGraph);
    }

      // Takes an array of ElevationResult objects, draws the path on the map
      // and plots the elevation profile on a Visualization API ColumnChart.
     
      google.maps.event.addDomListener(window, 'load', initialize);
    </script>
  </head>
  <body>
      <div id="prompt_infoContainer">
        <div id="prompt" class="card">
          <h5>Would you like to select one of your routes?</h5>

          <div id="routes">
            <ul id="routeList">
            </ul>
          </div>
                
          <p>OR</p>
            <a id="actionSelectStart" class="blue-text waves-effect waves btn-flat">Select a Starting Point On The Map</a>
            <script type="text/javascript">
              $('#actionSelectStart').click(function(){
                biketime.formCheck.routeSelected = false;
                $("#prompt").hide();
                $(".infoContainer").slideToggle();
              });
            </script>
        </div>

        <div id="infoContainer" class="card">
          <div id="routeInfo">
              <div id="infoDist">
                <h5 id="distance">0km</h5>
                  <p>Distance</p>
              </div>
              <div id="infoElev">
                <h5 id = "elevation">0m</h5>
                  <p>Elev. Gain</p>
              </div>
              <div id="infoTime">
                <h5 id = "duration">0 minutes</h5>
                  <p>Est. Moving Time</p>
              </div>
          </div>
          <div id="wrapper">
           <center><div id="elevationGraph"></div></center> 
          </div>
      </div>
    </div>

      <div id="map-canvas">
      </div>

      <div id="rideDetailsContainer"> 
          <form id="form" method="post" action="processride.php">

             <h5 class="center-align card" style="margin-top: 50px; padding: 10px;">Ride Details</h5>
      
          <div class="rideDetails">

            <div class="row">
              <div class="col s5">
                <p class="center-align"><b>Start Location (Estimate)</b></p>
              </div>
              <div class="col s7">
                <p class="center-align" id="biketime.rideData.startLocation" style="color: #7f8c8d; font-size: 10pt;"></p>
              </div>
            </div>

              <div id="rideDetails" class="collapsible-header header"><i class="mdi-editor-mode-edit"></i>Ride Details</div>
              <div class="content">
                <div class="row">
                  <div class="input-field col s12">
                    <select id = "rideType" name="rideType" >
                      <option value="0">Easy Recovery</option>
                      <option value="1">Interval Training</option>
                      <option value="2">Long Endurance Ride</option>
                      <option value="3">KOM Hunting</option>
                      <option value="4">Group Ride</option><!-- //change to name like looking for friends, to confusing -->
                      <option value="5">Unstructered/Other</option>
                    </select>
                    <label>Ride Type</label>
                  </div>
                </div>

                <div class="row">
                  <div class="input-field col s12">
                    <select id = "level" name="level">
                      <option value="Z"selected="selected">Z (Mixed Levels)</option>
                      <option value="A">A (33+ km/h)</option>
                      <option value="B">B (30-32 km/h)</option>
                      <option value="C">C (28-30 km/h)</option>
                      <option value="D">D (26-28 km/h)</option>
                      <option value="E">E (less then 25km/h)</option>
                    </select>
                    <label>Ride Level</label>
                  </div>
                </div>

                <div class="row">
                  <div class="input-field col s12">
                    <label for="description">Description (Optional)</label>
                    <textarea id="description" name = "description" class="materialize-textarea"></textarea>
                  </div>
                </div>

              </div>
         
              <div id="dateAndTime" class="collapsible-header header"><i class="mdi-device-access-time"></i>Date and Time</div>
              <div class="content">
                 <div class="row">
                    <div class="input-field col s6">
                      <input type="text" id="datetimepicker1" name="startDate" />
                      <label for="datetimepicker1">Date</label>
                    </div>
                  </div>

                  <div class="row">
                    <div class="input-field col s6">
                      <input id="timepicker1" type="text" name="startTime" /> 
                      <label for="timepicker1">Start Time</label>
                    </div>
                    <div class="input-field col s6">
                      <input id="timepicker2" type="text" name="endTime" value="Not Sure" />
                      <label for="timepicker2">End Time</label>
                    </div>
                  </div>
              </div>
           
              <?php
                  $isAdmin = false;
                  $selector = '<option value="-1">Me</option>';
                  foreach($profile->groups as $g){
                    if($g->userIsAdmin){
                       $isAdmin = true;
                       $selector = $selector.'<option value="'.$g->groupID.'" >'.$g->name.'</option>';
                    }
                  }
               ?>

              <div id="privacy" class="collapsible-header header"><i class="mdi-hardware-security"></i>Privacy<?php if($isAdmin) echo " & Group Settings";?></div>
              <div class="content">
              <br>
              
              <?php
                 if($isAdmin){
                    echo "<div class='row'>";
                        echo '<div class="col s12">
                              <label>Create Ride For</label>
                              <select id="rideIsForGroup" name="rideIsForGroup">'
                              .$selector.
                              '</select>
                              </div>';
                      echo "</div>";
                  }else{
                    echo "<input type='hidden' name='rideIsForGroup' value='-1'/>";//so that creatorType is user
                  }
              ?>

              <div class="row">
                <div class="input-field col s12">
                  <input type="text" id="rideTitle" maxlength="100" name="rideTitle" />
                  <label for="rideTitle">Title (Required For Group)</label>
                </div>
              </div>

              <div class="row">
                <div class="col s8">
                  <label>Visibility</label>
                    <select id = "rideVisibility" name="visibility" class="browser-default">
                      <option value="0">Friends Only</option>
                      <option value="1">Invite Only</option>
                      <option value="2">Everyone</option>
                    </select>
                  </div>
              </div>
              
              </div>

              <div id="repeat" class="collapsible-header header"><i class="mdi-action-history"></i>Repeat (Optional)</div>
              <div class="content">
                <div class="row"> 
                  <div class="col s4">
                    <p>Every</p>
                  </div>
                   <div class="input-field col s4">
                      <input type="number" min = "1" id="repeatInterval" name="repeatInterval"/>
                  </div>
                  <div class="col s4">
                    <p class="left-align">Weeks</p>
                  </div>
                </div>

                <div class="row"> 
                  <div class="col s4">
                    <p>Until</p>
                  </div>
                   <div class="input-field col s6">
                      <label for="datetimepicker2">End Date</label>
                      <input type="text" id="datetimepicker2" name="endDate" />
                  </div>
                </div>

              </div>

            </div>

            <input type="hidden" name="latLng"/>
            <input type="hidden" name="routeID"/>
            <input type="hidden" name="timezoneName"/>

            <button class="btn waves-effect waves-light" type="submit" id="createRide" name="action">Create Ride
              <i class="mdi-content-send right"></i>
            </button>
          </form>
      </div>

      <script type="text/javascript">

      var titleRequired = false;

        $(document).ready(function() {
          $(".button-collapse").sideNav();
          $(".dropdown-button").dropdown({hover:true});
          $('select').material_select();

            $('#rideIsForGroup').change(function(){

              var groupOptions = {"Group Members": "3",
                                  "Everyone": "2"
                                };

              var soloOptions = {"Friends Only": "0",
                                "Invite Only": "1",
                                "Everyone": "2"
                               };

              if($(this).val() == "-1"){
                titleRequired = false;

                var $el = $("#rideVisibility");
                $el.empty(); // remove old options
                $.each(soloOptions, function(key,value) {
                  $el.append($("<option value='"+value+"'>"+key+"</option>"));
                });
              }else{
                 titleRequired = true;

                var $el = $("#rideVisibility");
                $el.empty(); // remove old options
                $.each(groupOptions, function(key,value) {
                  $el.append($("<option></option>")
                     .attr("value", value).text(key));
                });
              }

            });

           jQuery('#datetimepicker1').datetimepicker({
            lang:'en',
             timepicker:false,
             format:'Y/m/d',
             minDate:'0',
             scrollInput: false,
             onShow:function( ct ){
               this.setOptions({
                maxDate:jQuery('#datetimepicker2').val()?jQuery('#datetimepicker2').val():false
               })
            }
          });

         jQuery('#datetimepicker2').datetimepicker({
          lang:'en',
           timepicker:false,
           format:'Y/m/d',
           minDate:'0',
           scrollInput: false,
           onShow:function( ct ){
             this.setOptions({
              minDate:jQuery('#datetimepicker1').val()?jQuery('#datetimepicker1').val():false
             })
            }
          });

        $('#timepicker1').timepicker({ 'scrollDefault': 'now' });
        $('#timepicker2').timepicker({ 'scrollDefault': 'now' });

          $(".header").click(function () {

            $header = $(this);
            //getting the next element
            $content = $header.next();
            //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
            $content.slideToggle(500, function () {
                //execute this after slideToggle is done
                //change text of header based on visibility of content div
                $header.text(function () {
                    //change text based on condition
                    //return $content.is(":visible") ? "Collapse" : "Expand";
                });
            });

        });



        $('#routeList').scroll(function() {//this function will prevent every map from loading, will save API usage and be more efficient
           var withinTen = $('#routeList').scrollLeft() - ($('#routeList').get(0).scrollWidth-$('#routeList').width())
            if(withinTen <10 && withinTen > -10) { //this will allow user to use scroll wheel because sometimes routeList.scrollLeft == routeList.scrollWidth-width would be off by 1 pixel and be false

              if(biketime.routeViewer.routeIncrementerT < routes.length){
                showRoutes(routes,biketime.routeViewer.routeIncrementerT,biketime.routeViewer.routeIncrementerH);
                biketime.routeViewer.routeIncrementerT +=5;
                biketime.routeViewer.routeIncrementerH += 5;
              }
            }
        });

         $("#routeList").mousewheel(function(event, delta) { // #element - your element id which has horizontal overflow
            this.scrollLeft -= (delta * 30);
            event.preventDefault();
         });

         <?php
            $mysqli = getDB();
            $routes = getUsersRoutes($mysqli,$_SESSION['UserID'],'');
          ?>

          routes = <?php echo json_encode($routes); ?>; //this code block displays all the routes
          if(routes.length > 0){
            showRoutes(routes,biketime.routeViewer.routeIncrementerT,biketime.routeViewer.routeIncrementerH);
          }
          biketime.routeViewer.routeIncrementerT +=5;//this initializes the map containers...
          biketime.routeViewer.routeIncrementerH += 5;


      
          $("#routeList").on('click', 'li', function(){

            var index = routes.length - 1 - $(this).closest("li").index()

            var route = routes[index];

            biketime.rideData.routeID = route['routeID'];

            var coords = route['routeCoords'];

            var path = [];

            var bounds = new google.maps.LatLngBounds();

            for(i = 0; i < coords.length; i++)
            {
              var coord = new google.maps.LatLng(parseFloat(coords[i][0]),parseFloat(coords[i][1]));

              bounds.extend(coord);
              path[i] = coord;
            }


            var polyline = new google.maps.Polyline({
                  path: path,
                  geodesic: true,
                  strokeColor: '#FF0000',
                  strokeOpacity: 1.0,
                  strokeWeight: 4
            });

            polyline.setMap(biketime.googleMaps.map);

            biketime.googleMaps.map.fitBounds(bounds);

            biketime.rideData.startLocation = path[0]; //dont need to put this in elevationdata function because even if graph doesnt work upload route wil be fine

            biketime.formCheck.routeSelected = true;

            document.getElementById('distance').innerHTML = route['distance'] + "km"

            document.getElementById('duration').innerHTML = rideDuration(route['time']);

            $('#elevationGraph').height(120);//this is setting the height of the div here so that when the page loads there isnt empty white space

            getElevationData(biketime.rideData.routeID,path);

            $("#prompt").hide();
            $(".infoContainer").slideToggle();
          });

        });

       

        $("#routeInfo").click(function (e) {
        //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
        $("#wrapper").slideToggle();
        });

        function isNull(id){
          if($(id).val() == null || $(id).val().trim() == ""){
            return true;
          }else{
            return false;
          }
        }

        function repeatIsNull(id1,id2){
          if($(id1).val() == "" && $(id2).val() != ""){
            return true;
          }else{
           return false;
          }
        }

        function validateTime(time){
           // regular expression to match required time format
          re = /^\d{1,2}:\d{2}([ap]m)?$/;
          if($(time).val() == null || $.trim($(time).val()) == '') {
            return false;
          }else{
            if(time == "#timepicker2" && $.trim($(time).val()).toLowerCase() == "not sure"){//if time is not sure for timepicker 2 than accept it
              return true;
            }else{
              if($(time).val().match(re)){
                return true;
              }else{
                return false;
              }
            }
          }
        }

        function showWarnings(){
          formComplete = true; //set back to true for each check
          if(biketime.formCheck.routeSelected == null){
            $("#prompt").css("border", "1px solid red");
            formComplete = false;//will prevent form from submiting if incomplete
          }
          
          if(isNull("#rideType") || isNull("#level")){
            $("#rideDetails").css("color", "red");
            formComplete = false
          }else{
            $("#rideDetails").css("color", "black");
          }

          if(isNull("#datetimepicker1") || !validateTime("#timepicker1") || !validateTime("#timepicker2")){//may be null error
            $("#dateAndTime").css("color", "red");
            formComplete = false
          }else{
            $("#dateAndTime").css("color", "black");
          }
          
          if(isNull("#rideVisibility") || (isNull('#rideTitle') && titleRequired)){
             $("#privacy").css("color", "red");
             formComplete = false
          }else{
            $("#privacy").css("color", "black");
          }

          if(repeatIsNull("#repeatInterval","#datetimepicker2") || repeatIsNull("#datetimepicker2","#repeatInterval")){
            $("#repeat").css("color", "red");
            formComplete = false
          }else{//if both false then move on
            $("#repeat").css("color", "black");
          }

        }

        $('select').change(function(){
          if(formComplete == false){//only check if user has tried to submit the form already (it will be false)
            showWarnings();
          }
        });

        $('input').focusout(function(){
          if(formComplete == false){//only check if user has tried to submit the form already (it will be false)
            showWarnings();
          }
        })

         $("#form").submit(function(){

          showWarnings();

          if(biketime.rideData.startLocation == null && !biketime.formCheck.routeSelected && formComplete){//only show if everything else complete, also seperate from showwarnings because dont want to show everytime input is focused
            formComplete = false;
            alert("Please Select a Start Location.");
          }

          $('input[name="latLng"]').val(biketime.rideData.startLocation);
          $('input[name="routeID"]').val(biketime.rideData.routeID);
          $('input[name="timezoneName"]').val(jstz.determine().name);//new Date().getTimezoneOffset()/60*-1

          return formComplete;
         });

         function getElevationData(_routeID, path){
          args = {
            routeID: _routeID,
            ajaxURL: '../ajax/ajax_get_route_elevation.php'
          }

          var getElevationDataRequest = ajaxRequest(args);

            getElevationDataRequest.done(function(result) {
                
                var routeInfo = [];
                var totaltime = 0;
                var totalDist = 0;
                var elevationData = JSON.parse(result);


                for (var i = 0; i<path.length; i++) {
                
                  var point = {
                    location: path[i],
                    elevation: elevationData[i]
                  }

                  routeInfo[i] = point;
                }
                
                initializeGraph(routeInfo,google.maps.ElevationStatus.OK);

            }).fail(function() {
                alert("Unable to get route data. Please check your internet connection.");
            });
          }

         function ajaxRequest(args){
          return $.ajax({
                url: args['ajaxURL'],
                data: args,
                success: function(response) {
                    result = response;
                }
            });
        }

        function rideDuration(minutes){
          var text = "hours"
          if(parseInt(minutes/60) == 1){
            text = "hour";
          }
          return parseInt(minutes/60)+" "+text+" "+minutes%60+" min";
        }

      </script>
  </body>
</html>