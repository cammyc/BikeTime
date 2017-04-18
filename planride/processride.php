<?php
include "../databasehelper/databasehelper.php";

if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

$userID = $_SESSION['UserID'];

$mysqli = getDB();
if ($mysqli->connect_errno) {
   echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$rideType = $mysqli->real_escape_string($_POST['rideType']);
$startDate = $mysqli->real_escape_string($_POST['startDate']);
$startTime = $mysqli->real_escape_string($_POST['startTime']);
$endTime = $mysqli->real_escape_string($_POST['endTime']);
$rideTitle = $mysqli->real_escape_string($_POST['rideTitle']);
$description = $mysqli->real_escape_string($_POST['description']);
$level = $mysqli->real_escape_string($_POST['level']);
$visibility = $mysqli->real_escape_string($_POST['visibility']);
sscanf($mysqli->real_escape_string($_POST['latLng']), '(%f, %f)', $lat, $lng);
$routeID = $mysqli->real_escape_string($_POST['routeID']);
$timezone = $mysqli->real_escape_string($_POST['timezoneName']);
$groupID = $mysqli->real_escape_string($_POST['rideIsForGroup']);

$repeatInterval = $mysqli->real_escape_string($_POST['repeatInterval']);
$endDate = $mysqli->real_escape_string($_POST['endDate']);

$groupType = 0; //0 means user

date_default_timezone_set("UTC");

function to24Hour($time){
	$unixtime = strtotime($time);
	return date("H:i:s", $unixtime);
}

	if($groupID != -1){
		$groupType = 1;
	}

	$startTime = to24Hour($startTime);

	$user = getUserProfileFromDB($mysqli,$_SESSION['UserID']);


	if($routeID == null){
		$routeID = -1;
	}

	if($endTime == null || strcasecmp($endTime, "Not Sure") == 0){//below makes it so that end time is null, could make it a few less lines...
		$endTime = "NULL";

		$mysqli->query('INSERT INTO `rides` (ID,CreatorID,RouteID,CreatorType, RideType, StartTime, tzName, Title, Description,Level,Visibility,StartLat, StartLon)VALUES ("NULL",'.$userID.','.$routeID.','.$groupType.','.$rideType.',"'.$startTime.'"
					,"'.$user->timezone.'","'.$rideTitle.'","'.$description.'","'.$level.'",'.$visibility.','.$lat.','.$lng.')');
	}else{
		$endTime = to24Hour($endTime);

		$mysqli->query('INSERT INTO `rides` VALUES ("NULL",'.$userID.','.$routeID.','.$groupType.','.$rideType.',"'.$startTime.'","'.$endTime.'","'.$user->timezone.'"
					,"'.$rideTitle.'","'.$description.'","'.$level.'",'.$visibility.','.$lat.','.$lng.')');
	}


	$rideID = $mysqli->insert_id;

	if($repeatInterval == null){
		$repeatInterval = "NULL";
	}else{
		$repeatInterval = $repeatInterval*604800;
	}

	$mysqli->query('INSERT INTO `rides_repeat` VALUES ("NULL",'.$rideID.',"'.strtotime($startDate).'","'.strtotime($endDate).'",'.$repeatInterval.')');

	addUserToRide($mysqli,$rideID,0,$userID);

	if($groupID != -1){
		addMultiRiderRide($mysqli,$groupID,$rideID,$groupType,$_SESSION['UserID']);//will have to change groupType initialization once race/event is added
	}

header("Location: ../ride/?rideID=".$rideID."&d=".strtotime($startDate)."");//using startdate so interval is 0
$mysqli->close();

?>