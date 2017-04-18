<!DOCTYPE html>
<?php
	include('databasehelper/databasehelper.php');
	session_start();
 //    $timezone = isset($_SESSION['time']) ? $_SESSION['time'] : ;
	// $timezoneName = timezone_name_from_abbr("", $timezone*3600, false); //make sure $timezone session variable doesnt get GMT text in it
	// date_default_timezone_set($timezoneName);

	// $today = date("Y/m/d");
	// $tomorrow = date('Y/m/d',strtotime("+1 day",strtotime($today)));
	$userID = isset($_COOKIE['UserID']) ? $_COOKIE['UserID'] : -1;
?>
<html>
<title>Bike Time</title>
<head>
<!-- 	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
 -->
	<link href="css/styleindex.css" rel="stylesheet" type="text/css"/>
 	<link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script type="text/javascript" src="js/materialize.min.js"></script>
</head>
<body>

				<div style="height: 100px;">

			<div class="row">

				<div class="input-field col s6">
				
					<select id = "rideType" name="rideType" >
			              <option value="-1" selected="selected">All Types</option>
			              <option value="0">Easy Recovery</option>
			              <option value="1">Interval Training</option>
			              <option value="2">Long Endurance Ride</option>
			              <option value="3">KOM Hunting</option>
			              <option value="4">Group Ride</option>
			              <option value="5">Unstructered/Other</option>
			        </select>
			        <label>Ride Type</label>
					
				</div>

				<div class="input-field col s6">
					<select id = "level" name="level">
			              <option value="-1"selected="selected">All Levels</option>
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
				
	
</body>
	<script type="text/javascript">

		$(document).ready(function() {
	    	$('select').material_select();

		 });
		
        //convertTimeToUTC(document.getElementById('timepicker1').value);

	</script>
</html>