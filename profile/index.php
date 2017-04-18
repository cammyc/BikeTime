<?php

include('../databasehelper/databasehelper.php');

loginCheck();

$mysqli = getDB();

$userID = $_SESSION['UserID'];//default in case no id set

if(isset($_GET['id'])){//cant put myAccount check in here because then it would be true anytime there is an id in the URL
  if((int) $_GET['id'] != null){
      $userID = $_GET['id'];
  }else{
      header("Location: ../profile");
  }
}

$myAccount = false;

if($userID == $_SESSION['UserID']){
  $myAccount = true;//check if users account
}

$profileExists = accountExists($mysqli,$userID);

if(!$profileExists){
  header("Location: ../profile");
}

$profile = getUserProfileFromDB($mysqli,$userID);

$friendshipStatus = (!$myAccount) ? getFriendshipStatus($mysqli,$_SESSION['UserID'],$userID) : 2;

?>


<html>
<title><?php echo $profile->firstName." ".$profile->lastName.""; ?></title>
<head>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<link href="../css/styleprof.css" rel="stylesheet" type="text/css"/>
  <link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
  <link rel="stylesheet" type="text/css" href="../datetimepicker/jquery.datetimepicker.css"/>

 <!-- <script src="datepicker/pikaday.js"></script> -->
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  <script type="text/javascript" src="../js/materialize.min.js"></script>
  <script type="text/javascript" src="../js/profilehelper.js"></script>
  <script type="text/javascript" src="../js/friendhelper.js"></script>
  <script src="../datetimepicker/jquery.datetimepicker.js"></script>
  <script type="text/javascript" src="../js/displayrides.js"></script>
  <script type="text/javascript" src="../js/jstz-1.0.4.min.js"></script>
  <script type="text/javascript" src="../js/ridehelper.js"></script>
  <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css" />
  <script src="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js"></script>
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAmLcM0z1weNroKQhtoe4VFurx3zljP_3s"></script>

<?php
include('../menu.php');
?>

  <script src="../bin/oms.min.js"></script>

  <script type="text/javascript">
    var map;
    var markers = [];
    var oms;
    var friendshipStatus = <?php echo $friendshipStatus ?>;
    var myAccount = <?php echo ($myAccount) ? 'true' : 'false'; ?>;
    var userID = <?php echo $userID ?>;
    var routeIncrementerT = 0;
    var routeIncrementerH = 5;
    var routes;

    function initialize() {
      var mapOptions = {
        zoom: 10,
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

      if(friendshipStatus != 1 && !myAccount){
        return;//if not friends and not myAccount then dont bother with below
      }else{
        map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);
      }

      var iw = new google.maps.InfoWindow();

      google.maps.event.addListener(iw, 'domready', function() {

        if(userID == -1){
          $('#bRideDetails').hide()//eventually show this, I just have to make adjustmens to the ride page for if no one is logged in.
        }else{
          var intervals = $('#intervals').val();
          var rideID = $('#rideID').val()
            hasUserJoinedRide(userID,rideID,intervals,'../');//if this wasnt called from the homepage the last arg would be ../
          $('#bJoinRide, #bLeaveRide').click(function(e){
            joinRide(userID,rideID,intervals,false,'../');
          });
        }
      });

      oms = new OverlappingMarkerSpiderfier(map,{markersWontMove: true, markersWontHide: true});


       oms.addListener('click', function(marker) {
            iw.setContent(marker.desc);
            iw.open(map, marker);
        });

        oms.addListener('spiderfy', function(markers) {
            // for(var i = 0; i < markers.length; i ++) {
            //   markers[i].setIcon("images/cycling_inverse_blue.png");
            // } 
            iw.close();
          });
          oms.addListener('unspiderfy', function(markers) {
            // for(var i = 0; i < markers.length; i ++) {
            //     markers[i].setIcon("images/cycling_blue.png");
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
        showRides(getRideArgs());
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


google.maps.event.addDomListener(window, 'load', initialize);


</script>

</head>
<body>

<div class="main">
  <div class="leftPanel">

      <div class="profilePicPlusText card">
        <?php
            if(isset($profile->profPicURL)){
            echo "<div id='profileImageContainer'><img id='profileImage' src = '../profilepics/".$profile->profPicURL."'/></div>";
            }
            echo "<div id='nameAgeGender'><p id='name'> ".$profile->firstName." ".$profile->lastName."</p><br>
            <p id = 'age'> <script type='text/javascript'>document.write('Age:' + getAge('".$profile->birthday."'))</script> </p>
            <p id = 'gender'> <script type='text/javascript'>document.write('".$profile->gender."'[0].toUpperCase() + '".$profile->gender."'.slice(1))</script></p>";

            if($myAccount){
              echo "<br><a href='../settings'>Update Account</a></div>";
            }else{
              echo "</div>";
              echo '<div id="addFriendContainer"><a class="waves-effect waves-light btn" id="addFriend"><i class="mdi-social-person-add left"></i><text id="buttonText">Add Friend<text></a></div>';

            }

        ?>
      </div>

        <div class="row card" id="alertsFriendsGroups">
          <div class="col s12">
            <ul class="tabs blue-text">
              <?php
                if($myAccount){
                  echo '<li class="tab col s4"><a class="active" href="#notifications">Alerts</a></li>';
                }
              ?>
              
              <li class="tab col s4"><a href="#friends" id="friendsTab">Friends</a></li>
              <li class="tab col s4"><a href="#groups" id="groupsTab">Groups</a></li>
            </ul>
          </div>
          
            <?php
              if($myAccount){
                echo '<div id="notifications" class="col s12">';
                  echo "<ul id='alerts'>";
                  echo "Loading Alerts...";
                  echo "</ul>";
                echo '</div>';
              }
            ?>
          <div id="friends" class="col s12">
              <ul id='friendsList'>
                Loading Friends...
              </ul>
          </div>
          <div id="groups" class="col s12">
              <ul id='groupsList'>
                Loading Groups...
              </ul>
          </div>
        </div>
    </div>

  <div class="rightPanel">
    <p>Upcoming Rides</p>
    <div id="upcomingRides" class="card" style="visibility:hidden;">
        <div id="timerange">
          <div class="col s12">
            <div class="row">
              <input  type="text" id="datetimepicker1" class = "datepicker col s5"/>
              <p class="center-align col s2" id="dash"> - </p>
              <input type="text" id="datetimepicker2" class="datepicker col s5"/>
            </div>
          </div>
        </div>

        <div id="creatorType">
          <div class="row center-align">
            <div class="switch col s6">
                <label>
                  <input name="joinedRides" id="joinedRides" type="checkbox" value="true" checked>
                  <span class="lever"></span>
                  <br>
                  Joined Rides
                </label>
              </div>

              <div class="switch col s6">
                <label>
                  <input name="createdRides" id="createdRides" type="checkbox" value="true" checked>
                  <span class="lever"></span>
                  <br>
                  Created Rides
                </label>
              </div>
          </div>
        </div>

        <div id="map-canvas"> </div>

    </div>

    <p>Routes</p>
    <div id="routes" class="card">
      <ul id="routeList">
      </ul>
      </div>
    </div>
  </div>

  <script type="text/javascript">

    function getRideArgs(){
      var args = {
        ajaxURL: "http://www.fondobike.com/ajax/ajax_get_user_rides.php",
        startDate: $('#datetimepicker1').val(),
        endDate: $('#datetimepicker2').val(),
        userID: userID,
        timezone: jstz.determine().name(),
        joinedRides: $('#joinedRides').is(':checked'),
        createdRides: $('#createdRides').is(':checked'),
        page: 2
        // level: document.getElementById('level').value will add more detail in beta
      };

      return args;
    }

    $(document).ready(function(){
      $(".button-collapse").sideNav();
      $(".dropdown-button").dropdown({hover:true});
      $('ul.tabs').tabs();      

      if(friendshipStatus != 1 && !myAccount){
        $('.rightPanel').empty().append('<h5 class="center-align">You must be friends to view this users profile.</h5>'); //if not friends and not my account dont show right panel
      }else{
        $('#upcomingRides').css('visibility','visible'); //make upcomingRides visible, reason it's invisible is if users arent friends then the map flashes before it is hidden which looks bad
      }

      var today = new Date();
      var tomorrow = new Date(today.getTime() +  24* 60 * 60 * 1000);

      $("#datetimepicker1").val(today.getFullYear() + "/" + ('0' + (today.getMonth()+1)).slice(-2) + "/" + ('0' + today.getDate()).slice(-2));
      $("#datetimepicker2").val(tomorrow.getFullYear() + "/" + ('0' + (tomorrow.getMonth()+1)).slice(-2) + "/" +('0' + tomorrow.getDate()).slice(-2));//month is +1 because it starts at 0


      if(!myAccount){
        var args = getArgs(<?php echo $_SESSION['UserID']; ?>,<?php echo $userID; ?>,'../ajax/ajax_friendship_status.php');
        getFriendshipStatus(args);//still need this method to show button
      }

        $("#addFriend").on('click', function(){
          if(friendshipStatus == 2){
            var args = getArgsAddFriend(<?php echo $_SESSION['UserID']; ?>,<?php echo $userID; ?>,<?php echo $_SESSION['UserID']; ?>,'../ajax/ajax_add_friend.php');//userID1,userID2,requesterID
            addFriend(args);
          }else if (friendshipStatus == 1){
            var args = getArgs(<?php echo $_SESSION['UserID']; ?>,<?php echo $userID; ?>,'../ajax/ajax_remove_friend.php');
            removeFriend(args);
          }else if (friendshipStatus == 0){
            Materialize.toast('Friend request is pending', 4000);
          }
        });

        $('#alerts').on('click',"a.acceptRequest", function(){
          var friendshipID = $(this).parent().find('input').val();
          respondToFriendRequest(friendshipID,1,'../ajax/ajax_respond_to_friend_request.php',$('#alerts'),<?php echo $userID ?>)
          //make alert on profile disappear and then add decline function
        });

         $('#alerts').on('click',"a.declineRequest", function(){
          console.log("test")
          var friendshipID = $(this).parent().find('input').val();
          respondToFriendRequest(friendshipID,0,'../ajax/ajax_respond_to_friend_request.php',$('#alerts'),<?php echo $userID ?>)
          //make alert on profile disappear and then add decline function
        })

         $('#friendsTab').click(function(){
            getFriends(userID,'../ajax/ajax_get_friends.php',$('#friendsList'));
         })

        if(!myAccount){
           getFriends(userID,'../ajax/ajax_get_friends.php',$('#friendsList'));//load because friends is shown by default
        }else{
          getFriendRequests(userID,'../ajax/ajax_get_friend_requests.php',$('#alerts'))//if it's myAccount then alerts is shown by default
        }

        $('#groupsTab').click(function(){
            getGroups(userID,'../ajax/ajax_get_user_groups.php',$('#groupsList'));
         })

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

                    showRides(getRideArgs());
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
                    showRides(getRideArgs());
                }
          });

          $("select, :checkbox").change(function(){
              showRides(getRideArgs());
          });

          $('#routeList').scroll(function() {//this function will prevent every map from loading, will save API usage and be more efficient
             var withinTen = $('#routeList').scrollLeft() - ($('#routeList').get(0).scrollWidth-$('#routeList').width())
              if(withinTen <10 && withinTen > -10) { //this will allow user to use scroll wheel because sometimes routeList.scrollLeft == routeList.scrollWidth-width would be off by 1 pixel and be false

                if(routeIncrementerT < routes.length){
                  showRoutes(routes,routeIncrementerT,routeIncrementerH);
                  routeIncrementerT +=5;
                  routeIncrementerH += 5;
                }
              }
          });

         $("#routeList").mousewheel(function(event, delta) { // #element - your element id which has horizontal overflow
            this.scrollLeft -= (delta * 30);
            event.preventDefault();
         });

         <?php
            $mysqli = getDB();
            $groupQuery = ($myAccount) ? '' : 'AND Public = 1';
            $routes = getUsersRoutes($mysqli,$userID,$groupQuery);
          ?>

          routes = <?php echo json_encode($routes); ?>; //this code block displays all the routes
          if(routes.length > 0){
            showRoutes(routes,routeIncrementerT,routeIncrementerH);
          }
          routeIncrementerT +=5;
          routeIncrementerH += 5;

    });

    </script>

</body>
</html>
<?php
 //$mysqli->close(); not needed because $mysqli is closed in the menu.php file
 ?>