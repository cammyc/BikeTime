<?php
	$groupMembers = $group->groupMembers;

	$onlyAdmin = 'true';

	foreach ($groupMembers as $gm) {
		if($gm->isAdmin && $gm->profile->userID != $_SESSION['UserID']){//skip current user because they may be admin
			$onlyAdmin = 'false';//string because being used for js variable
		}
	}
?>

<script type="text/javascript" src="../js/imageparser.js"></script>
<script type="text/javascript" src="../js/publicgrouphelper.js"></script>
<script type="text/javascript" src="../js/jstz-1.0.4.min.js"></script>
<script type="text/javascript">

		var map;
		var oms;
		var markers = [];
		var groupID = <?php echo $groupID; ?>;
		var userID = <?php echo $_SESSION['UserID']; ?>;
		var isAdmin = <?php echo ($memberProfile->isAdmin) ? 'true' : 'false'; ?>;
		var onlyAdmin = <?php echo $onlyAdmin; ?>;
		var messageArray;
		var aborted = false;


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
			    streetViewControl: false
  
			};

			map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);

			var iw = new google.maps.InfoWindow();

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
		showRides(getArgs());
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

		window.onload = initialize;

		function getArgs(){
			var args = {
				ajaxURL: "../ajax/ajax-single-group-rides.php",
				startDate: document.getElementById('datetimepicker1').value,
				endDate: document.getElementById('datetimepicker2').value,
				groupID: groupID,
				userID: <?php echo $_SESSION['UserID']; ?>,
				timezone: jstz.determine().name(),
				groupRides: $('#groupRides').is(':checked'),
        		memberRides: $('#memberRides').is(':checked'),
				page: 1
				// level: document.getElementById('level').value will add more detail in beta
			};

			return args;
		}

		


		$(document).ready(function () {


			var today = new Date();
			var tomorrow = new Date(today.getTime() +  24* 60 * 60 * 1000);

			$("#datetimepicker1").val(today.getFullYear() + "/" + ('0' + (today.getMonth()+1)).slice(-2) + "/" + ('0' + today.getDate()).slice(-2));
			$("#datetimepicker2").val(tomorrow.getFullYear() + "/" + ('0' + (tomorrow.getMonth()+1)).slice(-2) + "/" +('0' + tomorrow.getDate()).slice(-2));//month is +1 because it starts at 0

			$('#bJoinGroup').click(function(){
				var f = document.createElement("form");
					f.setAttribute('method',"post");
					f.setAttribute('action',"join_group_action_form.php");

				var gID = document.createElement("input"); //input element, text
					gID.setAttribute('type',"hidden");
					gID.setAttribute('name',"groupID");
					gID.setAttribute('value',groupID);

				var uID = document.createElement("input"); //input element, text
					uID.setAttribute('type',"hidden");
					uID.setAttribute('name',"userID");
					uID.setAttribute('value',userID);

				var isAc = document.createElement("input"); //input element, text
					isAc.setAttribute('type',"hidden");
					isAc.setAttribute('name',"isAccepted");
					isAc.setAttribute('value',true);

				f.appendChild(gID);
				f.appendChild(uID);
				f.appendChild(isAc);

				f.submit();

			});

			$('#bLeaveGroup').click(function(){
				if(onlyAdmin){
					alert("You are the only group admin, you must assign another admin before leaving. You can do that by going to the MEMBERS page.")
				}else{
					if(confirm('Are you sure you want to leave this group?')){
						removeMember(groupID,userID)//no list to refresh so null for 3rd param
					}
				}
			});

			$('#bShowRides').click(function(){
				$('#main').slideUp( "slow", function() {
				    $('#map-canvas').css('opacity','1');
				    $('#map-canvas').hide();
				    $('#styleContainer').css('pointer-events','auto');
				    $('#timerange').slideDown( "slow");
				 	$('#map-canvas').slideDown( "slow",function(){
				 		google.maps.event.trigger(map, 'resize');
				 	});
				 	$("#creatorType").slideDown( "slow");
				 	$("#toggleOptions").slideDown( "slow");
				 });
			});

			$('#bViewFeed').click(function(){
				$('#timerange').slideUp( "slow");
				$("#creatorType").slideUp( "slow");
				$("#toggleOptions").slideUp( "slow");

				$('#main').show();
				$('#main').css('z-index','-1');//makes for a cool animation

				$('#map-canvas').slideUp( "slow", function() {
					$('#main').css('z-index','auto');
				});

			});


			showUpcomingRideList(userID);
			updateMessageBoard(groupID,<?php  date_default_timezone_set("UTC"); echo time(); ?>);

			$('#messageButton').click(function(){
				if($('#message').val().trim() != ''){
					sendMessage(<?php  date_default_timezone_set("UTC"); echo time(); ?>);
					$('#message').val('');
				}
			});

			$('#msgBoard').on('click',"a.bComment", function(){
			        var msgID = messageArray[$(this).closest('li').index()]['messageID']
			        $(this).closest('li').find('.writeComment').slideToggle(function(){
			        	getComments(msgID,$(this).closest('li').find('.commentList'),<?php  date_default_timezone_set("UTC"); echo time(); ?>);
			        });
			 });

			$('#msgBoard').on('keypress',"input.commentInput", function(event){
				var msgID = messageArray[$(this).closest('li').index()]['messageID']
				if (event.which == 13) {
					var commentText = $(this).val();
					if(commentText.trim() != ''){
						var commentList = $(this).closest('li').find('.commentList');
			        	postComment(msgID,userID,commentText,this,commentList,<?php  date_default_timezone_set("UTC"); echo time(); ?>);
					}
				}
			});

			$("select, :checkbox").change(function(){
              showRides(getArgs());
          	});

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

		           	showRides(getArgs());
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
		           	showRides(getArgs());
				}
	        });

	        var fileInput = document.getElementById('groupLogoFile');
	        $('#groupLogoFile').on("change", function(){ handleFileSelection(fileInput.files[0],0,groupID); });
		});

	</script>

	<body>
		<div id="styleContainer">
			<div id="timerange">
				<div class="col s12">
					<div class="row">
						<input  type="text" id="datetimepicker1" class = "datepicker col s5"/>
						<p class="center-align col s2" id="dash"> - </p>
						<input type="text" id="datetimepicker2" class="datepicker col s5"/>
					</div>
				</div>
			</div>

			<div id="map-canvas"></div>

			<div id="creatorType">
				<div class="row center-align">
					<div class="switch col s6">
					    <label>
					      <input name="groupRides" id="groupRides" type="checkbox" value="true" checked>
					      <span class="lever"></span>
					      <br>
					      Group Rides
					    </label>
			  		</div>

			  		<div class="switch col s6">
					    <label>
					      <input name="memberRides" id="memberRides" type="checkbox" value="true" checked>
					      <span class="lever"></span>
					      <br>
					      Member Rides
					    </label>
			  		</div>
				</div>
			</div>

			<div id="toggleOptions" class="card">
				<a id="bViewFeed" class="waves-effect waves-light btn"><i class="mdi-content-send right"></i>View Feed</a>
			</div>
		</div>

		<div id="main">

			<div class="row card" id="tabs">
			    <div class="col s12 center-align">
			      <ul>
			        <li class="tab col s3"><a class="black-text" href="#">Upcoming Rides</a></li><!-- black text is active -->
			        <li class="tab col s3"><a class="blue-text" href="members.php?id=<?php echo $groupID; ?>">Members</a></li>
			        <li class="tab col s3"><a class="blue-text" href="#">Events</a></li>
			        <li class="tab col s3"><a class="blue-text" href="#">About</a></li>
			      </ul>
			    </div>
		    </div>

			<div id="basicInfo" >

				<div class="card"><center><a class="waves-effect waves-light btn" id="bShowRides"><i class="mdi-communication-location-on left"></i>Show Rides</a></center></div>

				<div id="infoText" class="card">

					<?php
						$oof = ($memberProfile->isAdmin) ? '<div class="file-field input-field" id="bUploadLogo"><div class="btn" >
						        	<span id="uploadIcon"><i class="mdi-file-file-upload"></i></span>
						        	<input id="groupLogoFile" type="file" title="Upload Group Photo" accept=".jpg, .png"/>
						      	</div></div>' : '';
						$foo = "<div id='groupLogoContainer'><img id='groupLogoPhoto' src='../grouplogos/".$group->logoURL."'/>".$oof."</div>";
						echo "<center>".$foo."</center>";
						echo "<p class='noMarginTop'><b>".$group->name."</b></p>";
						echo "<p><b>Sport:</b> ".ucfirst($group->sport)."</p>";
						echo "<p><b>Members:</b> ".sizeof($groupMembers)."</p>";
					?>
				</div>

				<?php

					if($memberProfile->isMember == 0 || $memberProfile->isMember == 2){
						echo '<div class="card">';
							if($memberProfile->isMember == 0){
								echo '<center><a class="waves-effect waves-light btn white-text" id="bJoinGroup"><i class="mdi-file-cloud left"></i>Join Group</a></center>';
							}else{
								echo "<center><p>Membership Request Pending</p></center>";
							}
						echo '</div>';
					}else{
						echo '<div class="card"><center><a class="waves-effect waves-light btn-flat blue-text grey lighten-2" id="bLeaveGroup"><i class="mdi-file-cloud left"></i>Leave Group</a></center></div>';
					}
				?>


			</div>

			<div id="groupStream">

				<div id="rideListContainer">
					<center><h4>Upcoming Group Rides</h4></center>
					<div id="rideList" class="card">

						<table style="wid" class="hoverable centered">
					        <thead>
					          <tr>
					              <th data-field="id">Title</th>
					              <th data-field="level">Level</th>
					              <th data-field="name">Time</th>
					              <th data-field="price">Date</th>
					          </tr>
					        </thead>

					        <tbody>
					          
					        </tbody>
					      </table>
					        <div id="rideListSpinner" class="preloader-wrapper small active ">
						      <div class="spinner-layer spinner-blue">
						        <div class="circle-clipper left">
						          <div class="circle"></div>
						        </div><div class="gap-patch">
						          <div class="circle"></div>
						        </div><div class="circle-clipper right">
						          <div class="circle"></div>
						        </div>
						      </div>

						      <div class="spinner-layer spinner-red">
						        <div class="circle-clipper left">
						          <div class="circle"></div>
						        </div><div class="gap-patch">
						          <div class="circle"></div>
						        </div><div class="circle-clipper right">
						          <div class="circle"></div>
						        </div>
						      </div>

						      <div class="spinner-layer spinner-yellow">
						        <div class="circle-clipper left">
						          <div class="circle"></div>
						        </div><div class="gap-patch">
						          <div class="circle"></div>
						        </div><div class="circle-clipper right">
						          <div class="circle"></div>
						        </div>
						      </div>

						      <div class="spinner-layer spinner-green">
						        <div class="circle-clipper left">
						          <div class="circle"></div>
						        </div><div class="gap-patch">
						          <div class="circle"></div>
						        </div><div class="circle-clipper right">
						          <div class="circle"></div>
						        </div>
						      </div>
						    </div>
					</div>
				</div>

				<div id="msgBoardContainer">

					<div id="sendMessage" class="card">
						<a href='../profile/?id=<?php echo $memberProfile->profile->userID ?>'><img class='msgImage' src='../profilepics/<?php echo $memberProfile->profile->profPicURL; ?>'/></a>

				        <input id="message" type="text" placeholder="What's up?" class="validate">

				        <a id="messageButton" class="waves-effect waves-light btn"><i class="mdi-content-send center"></i></a>
				    </div>

					<div id="msgBoard">

						<ul id="msgBoardList">
						</ul>
						
					</div>
				</div>
			</div>
			
		</div>
		
	</body>