<!DOCTYPE html>
<html>
  <head>
  <?php
    include_once("../databasehelper/databasehelper.php");

    loginCheck();

    $mysqli = getDB();

    $rideID = mysql_escape_string($_GET['rideID']);
    $startDateInterval = (isset($_GET['d'])) ? mysql_escape_string($_GET['d']) : null;

    if(!isset($rideID)){
      header('Location: rideNotFound.php');
    }

    $profile = getUserProfileFromDB($mysqli,$_SESSION['UserID']);

    $ride = getRideDetails($mysqli,$rideID,$profile->timezone);

    if($ride->rideID == null || ($ride->repeatInterval && $startDateInterval == null)){
      header('Location: rideNotFound.php');
    }

    $myRide = ($profile->userID == $ride->creatorID) ? true : false;

    $startDate = $ride->startDate;

    $intervals = 0;


    //echo $startDate."<br>";

    //echo strtotime('last sunday', $startDate)."<br>";
    $weekday = date('N', $startDateInterval); // 1-7

    if($weekday != 7){
      $startDateInterval = strtotime('last sunday', $startDateInterval); //WHY THIS WORKING!?!
    }

    if($weekday != 7){
      $startDate = strtotime('last sunday', $startDate);
    }


    while($startDate < $startDateInterval){
      $intervals++;
      $startDate += $ride->repeatInterval;
    }

    //echo $intervals; //Interval is weeks since sunday

    $skipDate = getRideSkipDate($mysqli,$rideID);
    $rideIsSkipped = false;

    if($skipDate != -2){
      for($i = 0; $i < sizeof($skipDate); $i++){
        if($skipDate[$i]['skipDate'] == $startDate){
          header( 'Location: ../ride/?rideID='.$skipDate[$i]['newRideID'].''); //Is 0 okay?
          break;
        }else if($skipDate[$i]['skipDate'] == -1){
          $rideIsSkipped = true;
        }
      }
    }

    $userHasJoined = hasRiderJoinedRide($mysqli,$rideID,$intervals,$_SESSION['UserID']);

    $rideMembers = getRideMembers($mysqli,$rideID,$intervals);

    $elevationData = ($ride->routeID != -1) ? getRouteElevationData($mysqli,$ride->routeID) : "";

  ?>
  <link href="../css/styleride.css" rel="stylesheet" type="text/css"/>
  <link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="../css/styled3graphhelper.css"  media="screen,projection"/>
  <link rel="stylesheet" type="text/css" href="../datetimepicker/jquery.datetimepicker.css"/>
  <link rel="stylesheet" type="text/css" href="../timepicker/jquery.timepicker.css" /> 


  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>    
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>


    <script src="../datetimepicker/jquery.js"></script>
    <script src="../datetimepicker/jquery.datetimepicker.js"></script>
    <script type="text/javascript" src="../timepicker/jquery.timepicker.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <script type="text/javascript" src="../js/jstz-1.0.4.min.js"></script>
    <script type="text/javascript" src="../js/d3.min.js"></script>
    <script type="text/javascript" src="../js/d3graphhelper.js"></script>
    <script type="text/javascript" src="../js/displayrides.js"></script>
    <script type="text/javascript" src="../js/ridehelper.js"></script>
    <script type="text/javascript" src="../js/formvalidator.js"></script>
    
    <title>Ride Details</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <?php
      include('../menu.php');
    ?>
    <script src="https://www.google.com/jsapi"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAmLcM0z1weNroKQhtoe4VFurx3zljP_3s&libraries=geometry"></script>

    <script>

    var rendererOptions = {
      draggable: true,
      preserveViewport:true,
      suppressBicyclingLayer:true
    };


      var routeID = <?php echo $ride->routeID; ?> ;
      var userID = <?php echo $_SESSION['UserID']; ?>;
      var rideID = <?php echo $rideID; ?>;
      var userHasJoined = <?php echo $userHasJoined; ?>;
      var intervals = <?php echo $intervals; ?> ;

      var mousemarker;
      var elevator;
      var route = <?php echo json_encode($ride->route); ?>;
      var coords = route['routeCoords'];
      var hasRoute = (routeID == -1) ? false : true;
      var startCoord = new google.maps.LatLng(<?php echo $ride->startLat; ?>,<?php echo $ride->startLon; ?>);

      var elevationData = <?php echo json_encode($elevationData); ?>;

      var biketime = {
        googleMaps: {
          map: null,
        },
        updateRide: {
          dayDifference: 0,
          repeatStartDate: 0,
        }
      };

      function initialize() {
        var mapOptions = {
          zoom: 13,//dont think zoom is needed with setBounds() being used
          mapTypeControl: true,
          mapTypeControlOptions: {
              style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
              position: google.maps.ControlPosition.TOP_LEFT
          },
          panControl: true,
          panControlOptions: {
              position: google.maps.ControlPosition.RIGHT_CENTER
          },
          zoomControl: true,
          zoomControlOptions: {
              position: google.maps.ControlPosition.RIGHT_CENTER
          },
          scaleControl: true,
          scaleControlOptions: {
              position: google.maps.ControlPosition.TOP_LEFT
          },
          streetViewControl: true,
          streetViewControlOptions: {
              position: google.maps.ControlPosition.RIGHT_CENTER
          },
          disableDoubleClickZoom: true,
          scrollwheel: false,
          draggableCursor: "crosshair"
        };

        biketime.googleMaps.map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);
            
        // Create an ElevationService.
        elevator = new google.maps.ElevationService();
        
        if(hasRoute){

          var path = new google.maps.MVCArray();//create Array containing route coordinates
          var bounds = new google.maps.LatLngBounds(); //creaate boundary object for map
           for(var k = 0; k < coords.length; k++){
            var coord = new google.maps.LatLng(coords[k][0],coords[k][1]);
              path.push(coord);
              bounds.extend(coord);
           }

           biketime.googleMaps.map.fitBounds(bounds);
           zoomChangeBoundsListener =  google.maps.event.addListenerOnce(biketime.googleMaps.map, 'bounds_changed', function(event) {
                    if (this.getZoom()){//not sure if the if statement is needed...
                        this.setZoom(this.getZoom()-1);//zoom default out by 1
                    }
            });

           var polyline = new google.maps.Polyline({ //create line to put on map
                path: path,
                geodesic: true,
                strokeColor: '#FF0000',
                strokeOpacity: 1.0,
                strokeWeight: 3
              });

              polyline.setMap(biketime.googleMaps.map);//add poly line to map

              if(elevationData.length > 0){
                initializeRouteViewerGraph(elevationData,route);//draw graph of elevation, distance, time profile
              }else{
                drawPath(path.getArray())
              }
        }else{
          biketime.googleMaps.map.setCenter(startCoord);
        }

            var infowindow = new google.maps.InfoWindow({
              content: '<div style="width: 150px; height: 30px;"><center><p>Start Location</p></center></div>'//start location window when hovered over
            });
        
            var marker = new google.maps.Marker({
              position: startCoord,//set position of start marker
              map: biketime.googleMaps.map,
              animation: google.maps.Animation.DROP
            });

            google.maps.event.addListener(marker, 'mouseover', function() {
              infowindow.open(map, this);//open info window on mouse over
            });

            google.maps.event.addListener(marker, 'mouseout', function() {
              infowindow.close();//close info window when no longer moused over
            });

        }


       function rideLength(minutes){
        var text = "hours"
        if(parseInt(minutes/60) == 1){
          text = "hour";
        }
         return parseInt(minutes/60)+" "+text+" "+minutes%60+" min";
       }

      function drawPath(result) {
          // Create a PathElevationRequest object using this array.
          // Ask for 256 samples along that path.
          var pathRequest = {
            'path': result,
            'samples': 512
          } 
        // Initiate the path request.
        elevator.getElevationAlongPath(pathRequest, initializeGraph); //retrieve elevation of coordinate points in route
      }

      // Takes an array of ElevationResult objects, draws the path on the map
      // and plots the elevation profile on a Visualization API ColumnChart.
     
      google.maps.event.addDomListener(window, 'load', initialize);

      //  $(function() {
      //   $( "#map-container" ).resizable({
      //     maxHeight: 400,
      //     maxWidth: 893,
      //     minHeight: 400,
      //     minWidth: 395,
      //     aspectRatio: true
      //   });

      //   $("#map-container").on( "resize", function( event, ui ) {

      //     $("#rideInfoContainer").width(1400 - ($("#map-container").width() + 10));
      //     google.maps.event.trigger(map,'resize');
      //   });
      // });
    </script>
  </head>
  <body>
    
    <div id="main">


    <div id="ride">

      <?php
        $creatorName = ($ride->creatorType == 0) ? $ride->creatorProfile->firstName." ".$ride->creatorProfile->lastName : $ride->group->name;

        $link =  ($ride->creatorType == 0) ? '../profile/?id='.$ride->creatorID.'' : '../group/?id='.$ride->group->groupID.'';

        $title = !empty($ride->title) ? "<h5  style='margin: 0px; border: 1px solid #ccc; border-bottom: none; padding: 5px; padding-left: 20px; background: #F7F7F7;'>".strtoupper($ride->title)."</h5>" : "";

        $levelText = ($ride->level == "Z") ? $ride->level." (Mixed Levels)" : $ride->level;

        $level = "<p><b>Level:</b> ".$levelText."</p>";

        $description = !empty($ride->description) ? '<p style="max-height:150px; overflow: auto;">'.$ride->description.'</p>' : "";

        $date = '<p><b>Date: <span id="rideDate"></span></b></p>';

        $rideType = '<p><b>Ride Type:</b> <span id="rideType"></span></p>';

        $time = "<p><b>Time: <span id='rideTime'></span></b></p>";

        echo ($rideIsSkipped) ? "<h5  style='margin: 0px; border: 1px solid #ccc; border-bottom: none; padding: 5px; padding-left: 20px; background: #e74c3c; color:#FFF;'><b>".strtoupper("This Ride is Canceled")."</b></h5>" : "";

        echo $title;
      ?>

      <div id="rideDetails" class="border">
        <?php

          echo $description;

          echo $level;

          echo $rideType;

          echo "<div id='startInfo'>".$time;

          echo $date."</div>";

          echo "<p><b>Creator:</b> <a href='".$link."'>".$creatorName."</a></p>";

          function formatTime($minutes){
            $text = "hours";
            if($minutes/60 == 1){
              $text = "hour";
            }
             return floor($minutes/60).":".sprintf("%02d", ($minutes%60)).":00";
          }

          $distance = ($ride->routeID != "-1") ? $ride->route->distance : "";
          $climb = ($ride->route != "-1") ? $ride->route->climb : "";
          $time = ($ride->route != "-1") ? $ride->route->time : "";


        ?>

       </div>

       <div id="riders" class="border">

          <ul id="routeDetails">
            <li>
              <h5><?php echo $distance; ?>km</h5>
              <label>Distance</label>
            </li>
            <li>
              <h5><?php  echo $climb; ?>m</h5>
              <label>Elev. Gain</label>
            </li>
            <li>
              <h5><?php  echo formatTime($time); ?></h5>
              <label>Est. Moving Time</label>
            </li>
          </ul>

          <p><b>Riders</b></p> <!-- what about runners? -->
          <ul id="ridersList">
            <?php
              foreach ($rideMembers as $r) {
                $imgURL = empty($r->profPicURL) ? "../images/noProfPic.png" : '../profilepics/'.$r->profPicURL;
                echo "<li><a title='".$r->firstName." ".$r->lastName."' href='../profile/?id=".$r->userID."'><div class='riderListImageContainer'><img class='riderListImg' src='".$imgURL."'/></div></a></li>";               
              }
              if(sizeof($rideMembers) == 0){
                echo "<li>No riders have joined this ride yet...</li>";
              }
            ?>
          </ul> 
          
          <a id="bJoinRide" class="waves-effect waves-light btn" style="display: none;"><i class="mdi-social-person-add left"></i>Join Ride</a>
          <a id="bLeaveRide" class="waves-effect waves-light btn" style="display: none;"><i class="mdi-content-clear left"></i>Leave Ride</a>

          <a id="bLeaveRide" class="waves-effect waves-light btn"><i class="mdi-content-clear left"></i>Share Ride</a>

        </div>

        <div id="map-canvas" class="border">
        </div>

        <div id="elevationGraph">
          </div>

       </div>

       <div id="msgBoard">
        <ul id="msgBoardList">
        </ul>
        <div id="textField">
          <input id="inputMessage" type="text" placeholder='Press "Enter" to Send Message' class="browser-default" />
        </div>
      </div>
      
    </div>
       
    <?php
        
      if($myRide){
        $skipOrRestore = ($rideIsSkipped) ? '<li><span title="Restore Ride"><a class="btn-floating green modal-trigger" href="#modal2"><i class="large mdi-content-add"></i></a></span></li>' : '<li><span title="Cancel Ride"><a class="btn-floating red modal-trigger" href="#modal2"><i class="large mdi-content-clear"></i></a></span></li>';

        $actionButton = '<div class="fixed-action-btn" style="bottom: 24px; right: 24px;">
                          <a class="btn-floating btn-large purple">
                            <i class="large mdi-navigation-menu"></i>
                          </a>
                          <ul>
                            '.$skipOrRestore.'
                            <li><span title="Edit Ride"><a class="btn-floating blue modal-trigger" href="#modal1"><i class="large mdi-editor-mode-edit"></i></a></span></li>
                          </ul>
                        </div>';

        $rideTitleField = '<input type="text" id="rideTitle" maxlength="100" name="rideTitle"/>
                        <label for="rideTitle">Title (Required For Group Ride)</label>';

        $rideTypeField = '<select id = "rideTypeSelect" name="rideType">
                      <option value="0">Easy Recovery</option>
                      <option value="1">Interval Training</option>
                      <option value="2">Long Endurance Ride</option>
                      <option value="3">KOM Hunting</option>
                      <option value="4">Group Ride</option><!-- //change to name like looking for friends, to confusing -->
                      <option value="5">Unstructered/Other</option>
                    </select>
                    <label>Ride Type</label>';

        $rideLevelField = '<select id = "rideLevel" name="level">
                      <option value="Z">Z (Mixed Levels)</option>
                      <option value="A">A (33+ km/h)</option>
                      <option value="B">B (30-32 km/h)</option>
                      <option value="C">C (28-30 km/h)</option>
                      <option value="D">D (26-28 km/h)</option>
                      <option value="E">E (less then 25km/h)</option>
                      </select>
                      <label>Ride Level</label>';

        $dateField = '<div class="input-field col s6"><input type="text" id="datetimepicker1" name="startDate" />
                          <label for="datetimepicker1">Date</label>
                        </div>';

        $dayOfWeek = ($ride->repeatInterval == null) ? '' : '<div id="divDayOfWeek"class="row">
                        <div class="col s7">
                          <ul id="ulDayOfWeek">
                            <li class="waves-effect waves-light btn">S</li>
                            <li class="waves-effect waves-light btn">M</li>
                            <li class="waves-effect waves-light btn">T</li>
                            <li class="waves-effect waves-light btn">W</li>
                            <li class="waves-effect waves-light btn">T</li>
                            <li class="waves-effect waves-light btn">F</li>
                            <li class="waves-effect waves-light btn">S</li>
                          </ul>
                        </div>
                      </div>';

        $timeField = ' <div class="input-field col s3">
                          <input id="timepicker1" type="text" name="startTime" class="validate"/> 
                          <label for="timepicker1">Start Time</label>
                        </div>
                        <div class="input-field col s3">
                          <input id="timepicker2" type="text" name="endTime" class="validate"/>
                          <label for="timepicker2">End Time</label>
                        </div>';

        $repeatOptions = ($ride->repeatInterval == null) ? '' : '<div class="input-field col s3">
                            <input name="repeatOption" type="radio" val="0" id="thisRide" checked/>
                            <label for="thisRide">Change the <b>date</b> for just this ride</label>
                            </div>
                            <div class="input-field col s3">
                              <input name="repeatOption" type="radio" id="futureRides" />
                              <label for="futureRides">Change the <b>day</b> of all future rides</label>
                            </div>';
                          

        $repeatDetails = ($ride->repeatInterval == null) ? '' : '<div class="row">
                        <div class="col s6">
                          <p id="repeatInfo"></p>
                        </div>
                      </div>';


        $modal1 = '<div id="modal1" class="modal modal-fixed-footer">
                    <div class="modal-content">
                      <h4>Edit Ride</h4>
                      <div class="row">
                        <div class="input-field col s6">
                          '.$rideTitleField.'
                        </div>
                        <div class="input-field col s6">
                          '.$rideTypeField.'
                        </div>
                      </div>
                      <div class="row">
                          '.$timeField.'
                        <div class="input-field col s6">
                          '.$rideLevelField.'
                        </div>
                      </div>
                      '.$repeatDetails.'
                      <div id="divDate" class="row">
                        '.$dateField.'
                      </div>
                        '.$dayOfWeek.'
                      <div class="row">
                      '.$repeatOptions.'
                      </div>
                    </div>
                    <div class="modal-footer">
                      <a class=" modal-action modal-close waves-effect waves-red btn-flat">Cancel</a>
                      <a id="bSave" class=" modal-action waves-effect waves-green btn-flat">Save</a>
                    </div>
                  </div>';

        $wordChoice = ($rideIsSkipped) ? "Restore" : "Cancel";

        $cancelAllRides = ($ride->repeatInterval == null) ? '' : ' <div class="row">
                                                                    <div class="input-field col s3">
                                                                      <input name="rbCancelType" type="radio" id="rbThisRide" checked/>
                                                                      <label for="rbThisRide">Only '.$wordChoice.' This Ride</label>
                                                                    </div>
                                                                    <div class="input-field col s3">
                                                                      <input name="rbCancelType" type="radio" id="rbAllRides"/>
                                                                      <label for="rbAllRides">'.$wordChoice.' All Future Rides</label>
                                                                    </div>
                                                                  </div>';

        $prompt = ($rideIsSkipped) ? "<h4>Restore Ride</h4><p>Are you sure you want to restore this ride? All future rides will be restored.</p>" : "<h4>Cancel Ride</h4><p>Are you sure you want to cancel this ride?</p>";
        $restoreOrCancel = ($rideIsSkipped) ? '<a id="bRestoreRide" href="#!" class=" modal-action waves-effect waves-green btn-flat">Yes</a>' : '<a id="bCancelRide" href="#!" class=" modal-action waves-effect waves-green btn-flat">Yes</a>';

        $modal2 = ' <div id="modal2" class="modal">
                      <div class="modal-content">
                        '.$prompt.'
                        '.$cancelAllRides.'
                      </div>
                      <div class="modal-footer">
                        <a href="#!" class=" modal-action modal-close waves-effect waves-red btn-flat">No</a>
                        '.$restoreOrCancel.'
                      </div>
                    </div>';
        echo $actionButton;
        echo $modal1;
        echo $modal2;
      }
    ?>

     

      <script type="text/javascript">

      <?php
        $interval = !empty($ride->repeatInterval) ? $ride->repeatInterval : 0;//in case there is no repeatinterval for the ride
        $endTime = !empty($ride->endTime) ? "adjustTimeFormat('".$ride->endTime."')" : "'Not Sure'";
        $startTime = "adjustTimeFormat('".$ride->startTime."')";
        $time = $startTime." + ' - ' + ".$endTime."";

        $startDate = 'getIntervalAdjustedDate('.$ride->startDate.','.$interval.','.(intval($startDateInterval)*1000).')';
      ?>

        $(document).ready(function(){
          var repeatStartDate = <?php echo $startDate ?>;
          var date = formatDate(repeatStartDate,0,null);
          biketime.updateRide.repeatStartDate = date;
          var time = <?php echo $time; ?>;

          $('.modal-trigger').leanModal();

          <?php echo ($myRide) ? 'initializeEditRideFields()' : ''; ?>;


          var weekday = new Array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

          var dayOfWeekStart = weekday[repeatStartDate.getDay()];
          var dayofWeekButtons = {}

          var repeatinterval = <?php echo $interval; ?>;
          var numWeeks = repeatinterval/604800;

   
            $('#repeatInfo').text("This ride is on " + dayOfWeekStart + " every " + numWeeks + " weeks");
            $('#ulDayOfWeek').children().eq(repeatStartDate.getDay()).addClass("green");

          if(repeatinterval != 0){
            $('#divDayOfWeek').hide();
          }
          
          $('#futureRides').click(function(){
            $('#divDate').hide();
            $('#divDayOfWeek').show();
          });

          $('#thisRide').click(function(){
            $('#divDate').show();
            $('#divDayOfWeek').hide();
          });


          //$('#modal1').openModal();

         if(userHasJoined){
            $('#bLeaveRide').show();
         }else{
             $('#bJoinRide').show();

         }

          $('#bJoinRide, #bLeaveRide').click(function (e) {
            joinRide(userID, rideID, intervals,true,'../'); //for interval rides just record number of intervals since 
          });

          $("#rideDetails").click(function (e) {

          });

          var rideType = <?php  echo 'getRideType("'.$ride->rideType.'");'; ?>//need to make ridetype into string for getridetype function to work

          $('#rideDate').text(date);
          $('#rideTime').text(time);
          $('#rideType').text(rideType);

          updateMessageBoard(rideID,intervals, <?php  date_default_timezone_set("UTC"); echo time(); ?>)

          $('#ulDayOfWeek li').click(function(){
           // alert($(this).index());
             // $(this).siblings().find(".green").removeClass("green");
              $(this).addClass("green").siblings().removeClass("green");
              biketime.updateRide.dayDifference = $(this).index() - repeatStartDate.getDay();
              alert(biketime.updateRide.dayDifference);
          });

        });

        if(routeID == -1){
          $('#routeDetails').hide();//want to hide before document is showing it
          $('#elevationGraph').hide();
        }


        $('#bSave').on('click', function(){
            formStartTime = '#timepicker1';
            formEndTime = '#timepicker2';

            var validForm = true;

            if(!validateTime(formStartTime)){
              $('#timepicker1').addClass('invalid')
              $('#timepicker1').removeClass('valid');
              validForm = false;
            }else{
              $('#timepicker1').addClass('valid')
              $('#timepicker1').removeClass('invalid');            
            }

            if(!validateTime(formEndTime)){
              $('#timepicker2').addClass('invalid')
              $('#timepicker2').removeClass('valid');
              validForm = false;
            }else{
              $('#timepicker2').addClass('valid')
              $('#timepicker2').removeClass('invalid'); 
            }

            if(validForm){
              var title = $('#rideTitle').val();
              var rideType = $('#rideTypeSelect').val();
              var date = $('#datetimepicker1').val();
              var rideLevel = $('#rideLevel').val();
              var startTime = $('#timepicker1').val();
              var endTime = $('#timepicker2').val();
              var isAllRides = $('#futureRides').is(':checked');
              var dayDiff = biketime.updateRide.dayDifference;
              var originalDate = biketime.updateRide.repeatStartDate;
              updateRide(<?php echo $_SESSION['UserID']; ?>, rideID, title, rideType, date, rideLevel, startTime, endTime, isAllRides, dayDiff, originalDate);
            }
        });

        
        $('#bCancelRide').on('click', function(){
            var val = $('#rbAllRides').is(':checked');
            var allRides = (val == false || val == null) ? 0 : 1;
            var rideRepeats = <?php echo $interval; ?>;
            var dateOfRide = (rideRepeats == 0) ? -1 : biketime.updateRide.repeatStartDate;
            cancelRide(rideID, allRides, dateOfRide);
        });

        $('#restoreRide').on('click', function(){
            var rideRepeats = <?php echo $interval; ?>;
            var dateOfRide = (rideRepeats == 0) ? -1 : biketime.updateRide.repeatStartDate;
            restoreRide(rideID, dateOfRide);
        });

        $(document).keypress(function(e) {
            if(e.which == 13 && $.trim($('#inputMessage').val()) != '') {
              sendMessage(rideID,intervals,userID,<?php  date_default_timezone_set("UTC"); echo time(); ?>);
            }
        });

        function initializeEditRideFields(){
          var rideTitle = <?php echo '"'.$ride->title.'"';?>;

          var rideType = <?php echo '"'.$ride->rideType.'"'; ?>;
          var rideLevel = <?php echo '"'.$ride->level.'"'; ?>;

          var startTime = <?php echo $startTime; ?>;
          var endTime = <?php echo $endTime; ?>;
          var tempDate = <?php echo $startDate; ?>;
          var date = new Date(tempDate);

          $('#rideTitle').val(rideTitle);

          initializeEditRideSelector('#rideTypeSelect',rideType)
          initializeEditRideSelector('#rideLevel',rideLevel)

          $('#timepicker1').val(startTime);
          $('#timepicker2').val(endTime);

          var month = ('0' + (date.getMonth() + 1)).slice(-2)
          var day = ('0' + date.getDate()).slice(-2)

          $('#datetimepicker1').val(date.getFullYear() + "/" + month + "/" + day);

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

          $('select').material_select();
        }

        function initializeEditRideSelector(id,val){
          $(id + ' option').each(function() {//initializing rideType selector
            var isRideType = $(this).val() == val
            if(isRideType){
              $(this).attr('selected','true')
            }
          });
        }
      </script>
  </body>
</html>