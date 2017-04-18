<!DOCTYPE html>
<html>
  <head>
  <?php
    include_once("../databasehelper/databasehelper.php");
    loginCheck();
    $mysqli = getDB();
    $route = (isset($_GET['id'])) ? getRoute($mysqli, mysql_escape_string($_GET['id'])) : false;

    if($route == false || $route->routeID == null){
      header('Location: RouteNotFound.php');
    }

    $friendshipStatus = ($route->creatorID == $_SESSION['UserID']) ? 1 : getFriendshipStatus($mysqli,$route->creatorID,$_SESSION['UserID']);

    if($friendshipStatus != 1){
      header('Location: PrivateRoute.php');
    }

    $elevationData = getRouteElevationData($mysqli,$route->routeID);

    $graphData = array();


  ?>

    <link href="../css/stylerouteviewer.css" rel="stylesheet" type="text/css"/>
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
    <link type="text/css" rel="stylesheet" href="../css/styled3graphhelper.css"  media="screen,projection"/>

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

    <script type="text/javascript" src="../js/d3.min.js"></script>
    <script type="text/javascript" src="../js/d3graphhelper.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
  

    <title>Route Viewer</title>
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

      var climb = 0;
      var distance = 0;
      var time = 0;
      var directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);
      var elevator;
      var route = <?php echo json_encode($route); ?>;
      var coords = route['routeCoords'];
      var elevationData = <?php echo json_encode($elevationData); ?>;

      var biketime = {
        googleMaps: {
          map: null,
        },

        route: {
          routeID: <?php echo $route->routeID; ?>
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
          }
        };

      biketime.googleMaps.map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);
      geocoder = new google.maps.Geocoder();
            
      // Create an ElevationService.
      elevator = new google.maps.ElevationService();

      document.getElementById('distance').innerHTML = route['distance'] + ' km';
      document.getElementById('duration').innerHTML = rideDuration(route['time']);
      document.getElementById('elevation').innerHTML = route['climb'] + 'm';


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
          strokeOpacity: .7,
          strokeWeight: 4
        });

      polyline.setMap(biketime.googleMaps.map);//add poly line to map

      if(elevationData.length > 0){
        initializeRouteViewerGraph(elevationData,route);//draw graph of elevation, distance, time profile
      }else{
        drawPath(path.getArray())
      }

      var startImage = new google.maps.MarkerImage('images/routeStart.png',
          new google.maps.Size(25, 25),
          new google.maps.Point(0, 0),
          new google.maps.Point(10, 10));

      var finishImage = new google.maps.MarkerImage('images/routeEnd.png',
          new google.maps.Size(25, 25),
          new google.maps.Point(0, 0),
          new google.maps.Point(10, 10));

      var startMarker = new google.maps.Marker({
          position: path.getArray()[0],
          map: biketime.googleMaps.map,
          icon: startImage
        });

      var finishMarker = new google.maps.Marker({
          position: path.getArray()[path.getLength()-1],
          map: biketime.googleMaps.map,
          icon: finishImage
        });
    }


    function rideDuration(minutes){
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
    </script>
  </head>
  <body>
      <div id="map-canvas">
      </div>



      
        <?php
        if($route->creatorID == $_SESSION['UserID'])
        {
          echo '<div class="card" id="deleteRoute">
                  <a id="bDeleteRoute" class="waves-effect waves-light btn red"><i class="mdi-content-send right"></i>Delete Route</a>
                </div>';
        }
        ?> 
      

      <div class = "infoContainer">
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
          </tr>
        </table>
        </div>
        <div id="wrapper">
       <center><div id="elevationGraph"></div></center> 
       </div>
      </div>

      <div id="modal1" class="modal">
        <div class="modal-content">
          <h4>Delete Route</h4>
          <p>Are you sure you want to delete this route? All rides using this route will have only the start location.</p>
        </div>
        <div class="modal-footer">
          <a id='bCancel' class="modal-action modal-close waves-effect waves-red btn-flat ">Cancel</a>
          <a id='bYes' class="modal-action modal-close waves-effect waves-green btn-flat ">Yes</a>
        </div>
      </div>

   

      <script type="text/javascript">

        $("#routeInfo").click(function (event) {
          if($('#elevationGraph').height() == 120){//only toggle if graph has been initialized
            $("#wrapper").slideToggle();
          }

        });


        $(document).ready(function(){

          $('.modal-trigger').leanModal();

          $(window).resize(function() {
            updateGraphWidth();
          });
          $(".button-collapse").sideNav();
          $(".dropdown-button").dropdown({hover:true});

          $("#bDeleteRoute").click(function(){
            $('#modal1').openModal();
          });

          $("#bYes").click(function(){
              deleteRoute(biketime.route.routeID);
          })



        });


        function deleteRoute(_routeID){
          args = {
            ajaxURL: '../ajax/ajax_delete_route.php',
            routeID: _routeID
          }

          var deleteRouteRequest = ajaxRequest(args);

              deleteRouteRequest.done(function(result) {

                if(result == 1){
                  Materialize.toast('Route Has Successfully been Deleted. Redirecting to Profile', 4000,'',function(){window.location.replace('../profile')});
                }else{
                  Materialize.toast('Unable to delete route. Please check your internet connection.', 4000);
                }

            }).fail(function() {
                alert("Unable to delete route. Please check your internet connection.");
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

      </script>
  </body>
</html>